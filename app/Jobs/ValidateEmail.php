<?php

namespace App\Jobs;

use App\Events\ValidationProgressUpdated;
use App\Models\EmailValidationBatch;
use App\Models\EmailValidationResult;
use App\Models\Recipient;
use App\Models\RecipientList;
use App\Services\EmailValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ValidateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $batchId,
        public string $email,
        public ?string $name = null
    ) {}

    public function middleware(): array
    {
        return [(new RateLimited('email-validation'))->dontRelease()];
    }

    public function handle(EmailValidator $validator): void
    {
        /** @var EmailValidationBatch $batch */
        $batch = EmailValidationBatch::findOrFail($this->batchId);

        $reason = null;
        $isValid = false;

        if (!$validator->isSyntaxValid($this->email)) {
            $isValid = false;
            $reason = 'syntax';
        } elseif (!$validator->hasValidMx($this->email)) {
            $isValid = false;
            $reason = 'no_mx';
        } else {
            // Optional: third-party API
            $apiOk = $validator->optionalApiCheck($this->email); // return true/false/null
            if ($apiOk === false) {
                $isValid = false;
                $reason = 'api_fail';
            } else {
                $isValid = true;
            }
        }

        EmailValidationResult::create([
            'batch_id' => $batch->id,
            'email'    => $this->email,
            'name'     => $this->name,
            'is_valid' => $isValid,
            'reason'   => $reason,
        ]);

        // Update counters atomically
        DB::transaction(function () use ($batch, $isValid) {
            $batch->increment($isValid ? 'valid_count' : 'invalid_count');
        });

        $stats = [
            'valid'   => $batch->valid_count,
            'invalid' => $batch->invalid_count,
            'total'   => $batch->total,
            'status'  => $batch->status,
        ];

        broadcast(new ValidationProgressUpdated(
            $batch->id,
            $stats,
            '[' . now()->format('H:i:s') . '] Checked: ' . $this->email . ' => ' . ($isValid ? 'valid' : 'invalid')
        ));
    }
}