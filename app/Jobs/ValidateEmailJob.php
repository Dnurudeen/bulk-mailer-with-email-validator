<?php

namespace App\Jobs;

use App\Models\ValidationBatch;
use App\Models\ValidationResult;
use App\Events\ValidationProgressUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
        public int $resultId
    ) {}

    public function handle(): void
    {
        $result = ValidationResult::find($this->resultId);
        if (! $result) {
            Log::warning("ValidationResult missing: {$this->resultId}");
            return;
        }

        $batch = ValidationBatch::find($this->batchId);
        if (! $batch) {
            Log::warning("ValidationBatch missing: {$this->batchId}");
            return;
        }

        $email = trim($result->email);
        $domain = (strrpos($email, '@') !== false) ? substr($email, strpos($email, '@') + 1) : null;

        // 1) Syntax check
        $syntaxOk = (bool) filter_var($email, FILTER_VALIDATE_EMAIL);

        // 2) MX check (if domain available)
        $mxOk = false;
        if ($domain) {
            // checkdnsrr preferred; fallback to dns_get_record if necessary
            $mxOk = (function_exists('checkdnsrr') && @checkdnsrr($domain, 'MX'))
                || (function_exists('dns_get_record') && @dns_get_record($domain, DNS_MX));
        }

        // 3) Optional remote API (ValidEmail.net)
        $apiResponse = null;
        $apiOk = null;
        $score = null;
        $state = null;
        $reason = null;
        $mxRecord = null;
        $extra = [];

        if (config('services.validemail.enabled') && config('services.validemail.key')) {
            try {
                $resp = Http::acceptJson()->get(config('services.validemail.endpoint'), [
                    'email' => $email,
                    'token' => config('services.validemail.key'),
                ]);

                if ($resp->successful()) {
                    $apiResponse = $resp->json();
                    $apiOk = $apiResponse['IsValid'] ?? null;
                    $score = isset($apiResponse['Score']) ? (int)$apiResponse['Score'] : null;
                    $state = $apiResponse['State'] ?? null;
                    $reason = $apiResponse['Reason'] ?? null;
                    $mxRecord = $apiResponse['MXRecord'] ?? null;
                    $extra = $apiResponse['EmailAdditionalInfo'] ?? [];
                }
            } catch (\Throwable $e) {
                Log::warning("ValidEmail API error for {$email}: {$e->getMessage()}");
            }
        }

        // Decide final validity using combined checks
        $minScore = config('services.validemail.min_score', 80);
        $finalValid = false;

        // Simple logic:
        // - must pass syntax
        // - either MX OK OR API IsValid true (and score >= threshold) OR both
        if ($syntaxOk) {
            if ($mxOk) {
                $finalValid = true;
            } elseif ($apiOk === true && ($score === null || $score >= $minScore)) {
                $finalValid = true;
            }
        }

        // update result row
        $result->update([
            'is_valid' => $finalValid,
            'score' => $score,
            'state' => $state,
            'reason' => $reason,
            'domain' => $domain,
            'free' => $apiResponse['Free'] ?? null,
            'role' => $apiResponse['Role'] ?? null,
            'disposable' => $apiResponse['Disposable'] ?? null,
            'accept_all' => $apiResponse['AcceptAll'] ?? null,
            'tag' => $apiResponse['Tag'] ?? null,
            'mx_record' => $mxRecord,
            'checked_at' => now(),
        ]);

        // update batch counts (atomic-ish)
        if ($finalValid) {
            $batch->increment('valid_count');
        } else {
            $batch->increment('invalid_count');
        }

        // Optionally set batch status to 'processing' (if queued -> processing)
        $batch->update(['status' => 'processing']);

        // Build stats snapshot to broadcast
        $batch->refresh();
        $stats = [
            'total' => $batch->total,
            'valid' => $batch->valid_count,
            'invalid' => $batch->invalid_count,
            'status' => $batch->status,
        ];

        $line = '[' . now()->format('H:i:s') . '] Checked: ' . $email . ' => ' . ($finalValid ? 'valid' : 'invalid');

        // broadcast single-result + stats
        event(new ValidationProgressUpdated(
            $batch->id,
            $stats,
            $line,
            [
                'id' => $result->id,
                'email' => $result->email,
                'is_valid' => $result->is_valid,
                'score' => $result->score,
                'state' => $result->state,
                'reason' => $result->reason,
                'checked_at' => $result->checked_at?->toDateTimeString(),
            ]
        ));

        // If finished: mark batch completed (done check)
        $totalDone = $batch->valid_count + $batch->invalid_count;
        if ($totalDone >= $batch->total) {
            $batch->update(['status' => 'completed']);
            $stats['status'] = 'completed';
            event(new ValidationProgressUpdated($batch->id, $stats, '[' . now()->format('H:i:s') . '] Batch completed', null));
        }
    }
}
