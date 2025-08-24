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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Events\CampaignProgressUpdated;
use App\Support\CampaignStats;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;
    // public \Carbon\Carbon $startedAt;

    public function __construct(
        public int $campaignId,
        public int $recipientId,
        // public int $jobId
    ) {
        // $this->startedAt = now();
    }

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
                // Requeue later instead of failing hard if paused
                $this->release(300); // 5 min
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

                return;
            }

            // Send
            Mail::to($recipient->email)->send(
                new CampaignMailable($campaign->subject, $campaign->html_body)
            );

            // Mark sent
            $campaign->recipients()->updateExistingPivot($recipient->id, [
                'status'  => 'sent',
                'sent_at' => now(),
                'error'   => null,
            ]);

            // After marking sent or failed, add:
            $stats = CampaignStats::snapshot($campaign);
            $line = '[' . now()->format('H:i:s') . "] Processed: {$recipient->email}";
            // event(new CampaignProgressUpdated($campaign->id, $stats, $line));
            broadcast(new CampaignProgressUpdated($campaign->id, $stats, $line))->toOthers();

            // $line = '[' . now()->format('H:i:s') . "] Paused: job requeued for {$campaign->id}";
            // event(new CampaignProgressUpdated($campaign->id, CampaignStats::snapshot($campaign), $line));
        } catch (ModelNotFoundException $e) {
            Log::warning('Campaign or recipient missing for job', [
                'campaignId' => $this->campaignId,
                'recipientId' => $this->recipientId
            ]);

            $this->fail($e);
        } catch (\Throwable $e) {
            // Mark failed
            if ($campaign ?? null) {
                $campaign->recipients()->updateExistingPivot($this->recipientId, [
                    'status' => 'failed',
                    'error'  => substr($e->getMessage(), 0, 190),
                ]);
            }
            throw $e;
        }
    }
}
