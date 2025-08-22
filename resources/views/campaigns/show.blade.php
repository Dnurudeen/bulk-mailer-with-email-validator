<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $campaign->name }}</h1>
            {{-- <h1 class="text-2xl font-bold mb-4">{{ $campaign->name }}</h1> --}}
        </div>
    </x-slot>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 rounded shadow mb-4">
                <p><strong>Status:</strong> {{ $campaign->status }}</p>
                <p><strong>Subject:</strong> {{ $campaign->subject }}</p>
                <p><strong>Scheduled At:</strong> {{ optional($campaign->scheduled_at)->toDayDateTimeString() ?? '—' }}
                </p>
            </div>

            <div class="flex gap-2 mb-6">
                <form method="POST" action="{{ route('campaigns.upload', $campaign) }}" enctype="multipart/form-data"
                    class="bg-white p-4 rounded shadow">
                    @csrf
                    <label class="block font-medium mb-2">Upload Recipients CSV (headers: email,name)</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="mb-2">
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Upload</button>
                </form>

                <form method="POST" action="{{ route('campaigns.schedule', $campaign) }}"
                    class="bg-white p-4 rounded shadow">
                    @csrf
                    <label class="block font-medium">Schedule At</label>
                    <input name="scheduled_at" type="datetime-local" class="border rounded p-2 mb-2">
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Set Schedule</button>
                </form>
            </div>

            <div class="flex gap-2 mb-6">
                <form method="POST" action="{{ route('campaigns.start', $campaign) }}">@csrf
                    <button class="px-4 py-2 btn btn-success rounded">Start Now</button>
                </form>
                <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf
                    <button class="px-4 py-2 btn btn-warning rounded">Pause</button>
                </form>
                <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf
                    <button class="px-4 py-2 btn btn-info rounded">Resume</button>
                </form>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold mb-2">Recent Recipients (first 50)</h2>
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="p-2">Email</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Queued</th>
                            <th class="p-2">Sent</th>
                            <th class="p-2">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaign->recipients as $r)
                            <tr class="border-b">
                                <td class="p-2">{{ $r->email }}</td>
                                <td class="p-2">{{ $r->pivot->status }}</td>
                                <td class="p-2">{{ optional($r->pivot->queued_at)->toDateTimeString() }}</td>
                                <td class="p-2">{{ optional($r->pivot->sent_at)->toDateTimeString() }}</td>
                                <td class="p-2">{{ $r->pivot->error }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>



            {{-- LIVE PROGRESS PANEL --}}
            <div class="bg-white p-4 rounded shadow mb-6 mt-6" x-data="campaignProgress({{ $campaign->id }}, {{ json_encode([
                'pending' => $campaign->recipients()->wherePivot('status', 'pending')->count(),
                'queued' => $campaign->recipients()->wherePivot('status', 'queued')->count(),
                'sent' => $campaign->recipients()->wherePivot('status', 'sent')->count(),
                'failed' => $campaign->recipients()->wherePivot('status', 'failed')->count(),
                'status' => $campaign->status,
            ]) }})" x-init="init()">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="text-lg font-semibold" x-text="stats.status"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Progress</div>
                        <div class="text-lg font-semibold" x-text="percent + '%'"></div>
                    </div>
                </div>

                <div class="w-full h-3 bg-gray-200 rounded overflow-hidden mb-3">
                    <div class="h-3 bg-green-500" :style="`width:${percent}%; transition: width .3s`"></div>
                </div>

                <div class="grid grid-cols-4 gap-4 text-center mb-4">
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Pending</div>
                        <div class="text-xl font-bold" x-text="stats.pending"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Queued</div>
                        <div class="text-xl font-bold" x-text="stats.queued"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Sent</div>
                        <div class="text-xl font-bold" x-text="stats.sent"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Failed</div>
                        <div class="text-xl font-bold" x-text="stats.failed"></div>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold mb-2">Live Worker Output</div>
                    <pre id="workerLog" class="bg-black text-green-400 p-3 rounded h-48 overflow-auto text-xs"></pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Alpine.js (tiny) for reactive widget; if you already have it, skip the CDN) --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script type="module">
  window.campaignProgress = (campaignId, initial) => ({
    campaignId,
    stats: {
      pending: initial.pending ?? 0,
      queued:  initial.queued ?? 0,
      sent:    initial.sent ?? 0,
      failed:  initial.failed ?? 0,
      status:  initial.status ?? 'draft',
      total() { return Math.max(1, this.pending + this.queued + this.sent + this.failed); }
    },
    get percent() {
      const done = this.stats.sent + this.stats.failed;
      return Number(((done / this.stats.total()) * 100).toFixed(1));
    },
    init() {
      const logEl = document.getElementById('workerLog');
      const appendLog = (line) => {
        if (!line) return;
        const atBottom = (logEl.scrollTop + logEl.clientHeight) >= (logEl.scrollHeight - 5);
        logEl.textContent += (logEl.textContent ? '\n' : '') + line;
        if (atBottom) logEl.scrollTop = logEl.scrollHeight;
      };

      // Subscribe to private channel via Echo (Reverb)
      window.Echo.private(`campaign.${this.campaignId}`)
        .listen('.progress', (e) => {
          if (e?.stats) {
            this.stats.pending = e.stats.pending ?? this.stats.pending;
            this.stats.queued  = e.stats.queued  ?? this.stats.queued;
            this.stats.sent    = e.stats.sent    ?? this.stats.sent;
            this.stats.failed  = e.stats.failed  ?? this.stats.failed;
            this.stats.status  = e.stats.status  ?? this.stats.status;
          }
          if (e?.line) appendLog(e.line);
        });

      // Initial line
      appendLog(`[${new Date().toLocaleTimeString()}] Subscribed: campaign.${this.campaignId}`);
    }
  });
</script>