<?php

namespace App\Jobs;

use App\Mail\CampaignMailable;
use App\Models\MailCampaign;
use App\Models\Recipient;
use App\Services\EmailValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\MailHelper;
use App\Events\CampaignProgressUpdated;
use App\Support\CampaignStats;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public int $timeout = 120;
    public int $tries = 3;     // max retry attempts
    public $timeout = 120;
    // public int $backoff = 5;  // wait 5s before next retry

    public function __construct(
        public int $campaignId,
        public int $recipientId,
    ) {}

    public function middleware(): array
    {
        // optional global rate limit key
        return [
            (new RateLimited('emails'))->dontRelease()
        ];
    }

    public function handle(EmailValidator $validator): void
    {
        try {
            /** @var MailCampaign $campaign */
            $campaign = MailCampaign::findOrFail($this->campaignId);
            if (in_array($campaign->status, ['paused', 'completed', 'draft'])) {
                $this->release(300); // requeue later if paused
                return;
            }

            /** @var Recipient $recipient */
            $recipient = Recipient::findOrFail($this->recipientId);

            // Validate email
            if (!$validator->isValid($recipient->email)) {
                $campaign->recipients()->updateExistingPivot($recipient->id, [
                    'status' => 'failed',
                    'error'  => 'Invalid email',
                ]);

                $stats = CampaignStats::snapshot($campaign);
                $line  = '[' . now()->format('H:i:s') . "] Invalid email: {$recipient->email}";
                event(new CampaignProgressUpdated($campaign->id, $stats, $line, [
                    'id' => $recipient->id,
                    'email' => $recipient->email,
                    'status' => 'failed',
                    'queued_at' => null,
                    'sent_at' => null,
                    'error' => 'Invalid email',
                ]));

                return;
            }

            // Configure active SMTP
            MailHelper::configureMail();

            // Send
            Mail::to($recipient->email)->queue(
                new CampaignMailable($campaign->subject, $campaign->html_body)
            );

            // Mark as sent
            $campaign->recipients()->updateExistingPivot($recipient->id, [
                'status'  => 'sent',
                'sent_at' => now(),
                'error'   => null,
            ]);

            // Broadcast success update
            $stats = CampaignStats::snapshot($campaign);
            $line  = '[' . now()->format('H:i:s') . "] Sent: {$recipient->email}";
            event(new CampaignProgressUpdated($campaign->id, $stats, $line, [
                'id' => $recipient->id,
                'email' => $recipient->email,
                'status' => 'sent',
                'queued_at' => null,
                'sent_at' => now()->toDateTimeString(),
                'error' => null,
            ]));
        } catch (ModelNotFoundException $e) {
            Log::warning('Campaign or recipient missing for job', [
                'campaignId' => $this->campaignId,
                'recipientId' => $this->recipientId,
            ]);
            $this->fail($e);
        } catch (\Throwable $e) {
            Log::error("❌ Send attempt {$this->attempts()} for recipient {$this->recipientId} failed: " . $e->getMessage());

            if (($this->attempts() >= $this->tries) && isset($campaign)) {
                // Mark as permanently failed
                $campaign->recipients()->updateExistingPivot($this->recipientId, [
                    'status' => 'failed',
                    'error'  => substr($e->getMessage(), 0, 190),
                ]);

                $recipient = $recipient ?? Recipient::find($this->recipientId);

                $stats = CampaignStats::snapshot($campaign);
                $line  = '[' . now()->format('H:i:s') . "] Failed: {$recipient?->email}";
                event(new CampaignProgressUpdated($campaign->id, $stats, $line, [
                    'id' => $recipient?->id,
                    'email' => $recipient?->email,
                    'status' => 'failed',
                    'queued_at' => null,
                    'sent_at' => null,
                    'error' => substr($e->getMessage(), 0, 190),
                ]));
            }

            throw $e; // rethrow so Laravel retries
        }
    }
}

// if ($campaign ?? null) {
//     // If we've hit the last attempt, mark as failed
//     if ($this->attempts() >= $this->tries) {
//         $campaign->recipients()->updateExistingPivot($this->recipientId, [
//             'status' => 'failed',
//             'error'  => substr($e->getMessage(), 0, 190),
//         ]);

//         // 🔥 broadcast updated stats so progress updates in real-time
//         $stats = \App\Support\CampaignStats::snapshot($campaign);
//         $line  = '[' . now()->format('H:i:s') . "] Failed: {$recipient->email}";
//         event(new \App\Events\CampaignProgressUpdated($campaign->id, $stats, $line));
//     }
// }