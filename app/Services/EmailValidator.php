<?php

namespace App\Services;

class EmailValidator
{
    public function isValid(string $email): bool
    {
        // Basic format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Extract domain & MX check
        $domain = substr(strrchr($email, "@"), 1);
        if (!$domain) return false;

        // checkdnsrr requires PHP DNS functions enabled
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return false;
        }

        return true;
    }
}
