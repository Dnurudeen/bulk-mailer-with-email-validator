<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignEmail;
use App\Models\MailCampaign;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Events\CampaignProgressUpdated;
use App\Support\CampaignStats;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = MailCampaign::where('user_id', Auth::id())
            ->latest()->paginate(10);

        return view('campaigns.index', compact('campaigns'));
    }


    public function create()
    {
        return view('campaigns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'subject'     => ['required', 'string', 'max:255'],
            'html_body'   => ['required', 'string'],
            'batch_size'  => ['nullable', 'integer', 'min:1', 'max:10000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $data['user_id'] = Auth::id();
        $campaign = MailCampaign::create($data);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created.');
    }

    public function show(MailCampaign $campaign)
    {
        $this->authorizeOwner($campaign);
        $campaign->load(['recipients' => function ($q) {
            $q->limit(50);
        }]);
        return view('campaigns.show', compact('campaign'));
    }

    public function uploadRecipients(Request $request, MailCampaign $campaign)
    {
        $this->authorizeOwner($campaign);
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('csv')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows)); // e.g. email,name

        DB::transaction(function () use ($rows, $header, $campaign) {
            $emailIdx = array_search('email', array_map('strtolower', $header));
            $nameIdx  = array_search('name', array_map('strtolower', $header));

            foreach ($rows as $r) {
                $email = isset($r[$emailIdx]) ? trim($r[$emailIdx]) : null;
                if (!$email) continue;

                $name  = $nameIdx !== false ? trim($r[$nameIdx] ?? '') : null;

                $recipient = Recipient::firstOrCreate(['email' => $email], ['name' => $name]);
                $campaign->recipients()->syncWithoutDetaching([$recipient->id => ['status' => 'pending']]);
            }
        });

        return back()->with('success', 'Recipients uploaded.');
    }

    public function clearRecipients(MailCampaign $campaign)
    {
        // Assuming you have a relationship `recipients()`
        $campaign->recipients()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'All recipients cleared successfully.'
        ]);
    }

    public function schedule(MailCampaign $campaign, Request $request)
    {
        $this->authorizeOwner($campaign);
        $data = $request->validate([
            'scheduled_at' => ['nullable', 'date']
        ]);

        $campaign->update([
            'scheduled_at' => $data['scheduled_at'] ?? now(),
            'status' => 'scheduled',
        ]);

        event(new CampaignProgressUpdated(
            $campaign->id,
            CampaignStats::snapshot($campaign),
            '[' . now()->format('H:i:s') . "] Status changed to {$campaign->status}"
        ));

        return back()->with('success', 'Campaign scheduled.');
    }

    public function startNow(MailCampaign $campaign)
    {
        $this->authorizeOwner($campaign);
        $campaign->update(['status' => 'sending', 'scheduled_at' => now()]);

        event(new CampaignProgressUpdated(
            $campaign->id,
            CampaignStats::snapshot($campaign),
            '[' . now()->format('H:i:s') . "] Status changed to {$campaign->status}"
        ));

        $this->dispatchBatch($campaign);

        return back()->with('success', 'Dispatching started.');
    }

    public function pause(MailCampaign $campaign)
    {
        $this->authorizeOwner($campaign);
        $campaign->update(['status' => 'paused']);

        event(new CampaignProgressUpdated(
            $campaign->id,
            CampaignStats::snapshot($campaign),
            '[' . now()->format('H:i:s') . "] Status changed to {$campaign->status}"
        ));

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(MailCampaign $campaign)
    {
        $this->authorizeOwner($campaign);
        $campaign->update(['status' => 'sending']);

        event(new CampaignProgressUpdated(
            $campaign->id,
            CampaignStats::snapshot($campaign),
            '[' . now()->format('H:i:s') . "] Status changed to {$campaign->status}"
        ));

        $this->dispatchBatch($campaign);

        return back()->with('success', 'Campaign resumed.');
    }

    protected function dispatchBatch(MailCampaign $campaign): void
    {
        $batchSize = $campaign->batch_size;

        $pending = $campaign->recipients()
            ->wherePivotIn('status', ['pending'])
            ->limit($batchSize)
            ->pluck('recipients.id');

        foreach ($pending as $rid) {
            $campaign->recipients()->updateExistingPivot($rid, [
                'status' => 'queued',
                'queued_at' => now(),
            ]);
            dispatch(new SendCampaignEmail($campaign->id, $rid));
        }

        // If nothing left pending/queued, possibly completed
        $remaining = $campaign->recipients()->wherePivotIn('status', ['pending', 'queued'])->count();
        if ($remaining === 0) {
            $campaign->update(['status' => 'completed']);
        }

        // Broadcast batch snapshot once after queuing
        event(new CampaignProgressUpdated(
            $campaign->id,
            CampaignStats::snapshot($campaign),
            '[' . now()->format('H:i:s') . "] Queued next batch for campaign #{$campaign->id}"
        ));
    }

    protected function authorizeOwner(MailCampaign $campaign): void
    {
        abort_unless($campaign->user_id === Auth::id(), 403);
    }
}
