<?php
// app/Jobs/ValidateEmail.php
namespace App\Jobs;

use App\Events\EmailUpdated;
use App\Models\Email;
use App\Services\SMTPValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emailRecord;

    public function __construct(Email $email)
    {
        $this->emailRecord = $email;
    }

    public function handle(SMTPValidator $validator)
    {
        $email = $this->emailRecord->email;

        // run validation
        $res = $validator->validate($email);

        // Save results
        $this->emailRecord->update([
            'status' => $res['status'] ?? 'unknown',
            'reason' => $res['reason'] ?? null,
            'is_disposable' => $res['is_disposable'] ?? false,
            'is_catch_all_tested' => true
        ]);

        event(new EmailUpdated($this->emailRecord->fresh()));
    }
}
