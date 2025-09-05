<?php
// app/Services/SMTPValidator.php
namespace App\Services;

class SMTPValidator
{
    protected $timeout = 10;

    // simple RFC-ish check (not full RFC 5322 but practical)
    public function isValidSyntax(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function isRoleAddress(string $email): bool
    {
        $roleLocalParts = [
            'admin',
            'administrator',
            'hostmaster',
            'postmaster',
            'info',
            'sales',
            'support',
            'contact',
            'billing',
            'noreply',
            'no-reply'
        ];

        $local = strtolower(explode('@', $email)[0]);
        foreach ($roleLocalParts as $r) {
            if ($local === $r || str_starts_with($local, $r . '.') || str_ends_with($local, '.' . $r)) {
                return true;
            }
        }
        return false;
    }

    // maintain your disposable list locally (load from file)
    public function isDisposableDomain(string $domain): bool
    {
        $path = storage_path('app/disposable_domains.txt');
        if (!file_exists($path)) return false;
        $list = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $list = array_map('trim', $list);
        return in_array(strtolower($domain), $list, true);
    }

    public function getMxHosts(string $domain): array
    {
        $records = dns_get_record($domain, DNS_MX);
        if (!$records) {
            // fallback to A record
            $a = dns_get_record($domain, DNS_A);
            if ($a) {
                return [$domain];
            }
            return [];
        }
        // sort by priority
        usort($records, function ($a, $b) {
            return $a['pri'] <=> $b['pri'];
        });
        $hosts = array_map(fn($r) => rtrim($r['target'], '.'), $records);
        return $hosts;
    }

    // test SMTP RCPT TO for a single host
    protected function smtpRcptTest(string $host, string $from, string $to): array
    {
        $result = ['code' => null, 'message' => null];
        // try connect on port 25 (or 587)
        $ports = [25, 587];

        foreach ($ports as $port) {
            $errno = 0;
            $errstr = '';
            $fp = @fsockopen($host, $port, $errno, $errstr, $this->timeout);
            if (!$fp) continue;

            stream_set_timeout($fp, $this->timeout);

            $read = fgets($fp, 1024); // banner
            // EHLO/HELO
            fputs($fp, "EHLO example.com\r\n");
            fgets($fp, 1024);
            // MAIL FROM
            fputs($fp, "MAIL FROM:<{$from}>\r\n");
            fgets($fp, 1024);
            // RCPT TO
            fputs($fp, "RCPT TO:<{$to}>\r\n");
            $resp = fgets($fp, 1024);
            // QUIT
            fputs($fp, "QUIT\r\n");
            fclose($fp);

            if (preg_match('/^(\d{3})\s?(.*)$/', trim($resp), $m)) {
                $result['code'] = (int)$m[1];
                $result['message'] = $m[2] ?? '';
            } else {
                $result['code'] = null;
                $result['message'] = trim($resp);
            }
            return $result;
        }

        return $result;
    }

    // main validate function
    public function validate(string $email): array
    {
        $email = trim($email);
        $domain = strtolower(explode('@', $email)[1] ?? '');

        // Step 1: Syntax
        if (!$this->isValidSyntax($email)) {
            return ['status' => 'invalid', 'reason' => 'Invalid email syntax'];
        }

        // Step 2: Disposable / Role
        if ($this->isRoleAddress($email)) {
            return ['status' => 'unknown', 'reason' => 'Role-based address (info/support/admin)'];
        }

        if ($this->isDisposableDomain($domain)) {
            return ['status' => 'invalid', 'reason' => 'Disposable email domain', 'is_disposable' => true];
        }

        // Step 3: DNS / MX
        $mxHosts = $this->getMxHosts($domain);
        if (empty($mxHosts)) {
            return ['status' => 'invalid', 'reason' => 'No MX or A records for domain'];
        }

        // Step 4: SMTP RCPT TO
        $from = 'validator@example.com'; // adjust to your own domain/address
        foreach ($mxHosts as $host) {
            $resp = $this->smtpRcptTest($host, $from, $email);
            $code = $resp['code'] ?? null;
            $msg = $resp['message'] ?? null;

            if ($code === 250 || $code === 251) {
                // accepted
                return ['status' => 'valid', 'reason' => "Accepted by {$host}"];
            }

            if (in_array($code, [550, 551, 553])) {
                // mailbox unavailable
                return ['status' => 'invalid', 'reason' => "Rejected by {$host}: {$code} {$msg}"];
            }

            // code could be null (timeout) or other 4xx temp failures
            // continue to next host
        }

        // If no host gave 250 but at least one had a neutral response, we consider unknown.
        // Additional catch-all detection:
        $catch = $this->detectCatchAll($domain, $from, $mxHosts);
        if ($catch['is_catch_all']) {
            return ['status' => 'catch-all', 'reason' => 'Domain appears to be catch-all'];
        }

        return ['status' => 'unknown', 'reason' => 'No definitive response from any MX host'];
    }

    // detect catch-all by testing random mailbox
    public function detectCatchAll(string $domain, string $from, array $mxHosts): array
    {
        // random mailbox unlikely to exist
        $randomLocal = 'randomcheck' . bin2hex(random_bytes(6));
        $randomEmail = $randomLocal . '@' . $domain;

        foreach ($mxHosts as $host) {
            $resp = $this->smtpRcptTest($host, $from, $randomEmail);
            $code = $resp['code'] ?? null;
            // If server accepts the random address => catch-all
            if ($code === 250 || $code === 251) {
                return ['is_catch_all' => true, 'host' => $host];
            }
        }
        return ['is_catch_all' => false];
    }
}
