<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl light:text-gray-800 leading-tight">{{ $campaign->name }}</h1>
            {{-- <h1 class="text-2xl font-bold mb-4">{{ $campaign->name }}</h1> --}}
        </div>
    </x-slot>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="border border-white-400 p-4 rounded shadow mb-4">
                <p><strong>Status:</strong> {{ $campaign->status }}</p>
                <p><strong>Subject:</strong> {{ $campaign->subject }}</p>
                <p><strong>Scheduled At:</strong> {{ optional($campaign->scheduled_at)->toDayDateTimeString() ?? '—' }}
                </p>
            </div>

            <div class="flex gap-2 mb-6">
                <form method="POST" action="{{ route('campaigns.upload', $campaign) }}" enctype="multipart/form-data"
                    class="border border-white-400 p-4 rounded shadow">
                    @csrf
                    <label class="block font-medium mb-2 text-gray-500">Upload Recipients CSV (headers:
                        email,name)</label>
                    <input type="file" name="csv" accept=".csv,text/csv" class="mb-2">
                    <button class="px-4 py-2 bg-red-600 text-white rounded">Upload</button>
                </form>

                <form method="POST" action="{{ route('campaigns.schedule', $campaign) }}"
                    class="border border-white-400 p-4 rounded shadow">
                    @csrf
                    <label class="block font-medium text-gray-500">Schedule At</label>
                    <input name="scheduled_at" type="datetime-local"
                        class="border border-white-400 dark:bg-gray-800 rounded p-2 mb-2">
                    <button class="px-4 py-2 bg-red-600 text-white rounded">Set Schedule</button>
                </form>
            </div>

            <div class="border border-white-400 p-4 rounded shadow" x-data="campaignProgress({{ $campaign->id }}, {{ json_encode([
                'pending' => $campaign->recipients()->wherePivot('status', 'pending')->count(),
                'queued' => $campaign->recipients()->wherePivot('status', 'queued')->count(),
                'sent' => $campaign->recipients()->wherePivot('status', 'sent')->count(),
                'failed' => $campaign->recipients()->wherePivot('status', 'failed')->count(),
                'status' => $campaign->status,
                'recipients' => $initialRecipients,
            ]) }})">
                <div class="flex justify-between items-center my-auto mb-5">
                    <h2 class="font-semibold">Recent Recipients (first 50)</h2>

                    <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow"
                        @click="clearRecipients">
                        Clear All Recipients
                    </button>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b text-gray-500">
                            <th class="p-2">Email</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Queued</th>
                            <th class="p-2">Sent</th>
                            <th class="p-2">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="recipient in stats.recipients" :key="recipient.id">
                            <tr class="border-b">
                                <td class="p-2" x-text="recipient.email"></td>
                                <td class="p-2" x-text="recipient.status"></td>
                                <td class="p-2" x-text="recipient.queued_at ?? ''"></td>
                                <td class="p-2" x-text="recipient.sent_at ?? ''"></td>
                                <td class="p-2" x-text="recipient.error ?? ''"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <br>

            <div class="flex gap-2 mb-6">
                <form method="POST" action="{{ route('campaigns.start', $campaign) }}">@csrf
                    <button class="px-4 py-2 btn btn-success bg-green-500 rounded">Start Now</button>
                </form>
                <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf
                    <button
                        class="px-4 py-2 btn btn-warning dark:bg-white bg-black dark:text-gray-800 text-white rounded">Pause</button>
                </form>
                <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf
                    <button class="px-4 py-2 btn btn-info bg-blue-600 rounded">Resume</button>
                </form>
            </div>



            {{-- LIVE PROGRESS PANEL --}}
            <div class="border border-white-400 p-4 rounded shadow mb-6 mt-6" x-data="campaignProgress({{ $campaign->id }}, {{ json_encode([
                'pending' => $campaign->recipients()->wherePivot('status', 'pending')->count(),
                'queued' => $campaign->recipients()->wherePivot('status', 'queued')->count(),
                'sent' => $campaign->recipients()->wherePivot('status', 'sent')->count(),
                'failed' => $campaign->recipients()->wherePivot('status', 'failed')->count(),
                'status' => $campaign->status,
                'recipients' => $initialRecipients,
            ]) }})"
                x-init="init()">
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
                        <div class="text-xl font-bold text-gray-500" x-text="stats.pending"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Queued</div>
                        <div class="text-xl font-bold text-gray-500" x-text="stats.queued"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Sent</div>
                        <div class="text-xl font-bold text-green-500" x-text="stats.sent"></div>
                    </div>
                    <div class="p-2 rounded bg-gray-50">
                        <div class="text-xs text-gray-500">Failed</div>
                        <div class="text-xl font-bold text-red-600" x-text="stats.failed"></div>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold mb-2">Live Worker Output</div>
                    {{-- <pre id="workerLog" class="bg-black text-green-400 p-3 rounded h-48 overflow-auto text-xs"></pre> --}}
                    <div class="bg-black text-green-400 font-mono p-2 h-40 overflow-y-scroll rounded log-output">
                        <template x-for="(line, index) in logLines" :key="index">
                            <div x-text="line"></div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js (tiny) for reactive widget; if you already have it, skip the CDN) --}}
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script type="module">
        window.campaignProgress = (campaignId, initial) => ({
            campaignId,
            stats: {
                pending: initial.pending ?? 0,
                queued: initial.queued ?? 0,
                sent: initial.sent ?? 0,
                failed: initial.failed ?? 0,
                status: initial.status ?? 'draft',
                recipients: initial.recipients ?? [], // <-- add this
                total() {
                    return Math.max(1, this.stats.pending + this.stats.queued + this.stats.sent + this.stats
                    .failed);
                }

            },
            logLines: [], // <-- ✅ add this so Alpine tracks log messages
            get percent() {
                const done = this.stats.sent + this.stats.failed;
                return Number(((done / this.stats.total()) * 100).toFixed(1));
            },
            init() {
                // Subscribe to private channel via Echo (Reverb)
                window.Echo.private(`campaign.${this.campaignId}`)
                    .listen('.progress', (e) => {
                        if (e?.stats) {
                            this.stats.pending = e.stats.pending ?? this.stats.pending;
                            this.stats.queued = e.stats.queued ?? this.stats.queued;
                            this.stats.sent = e.stats.sent ?? this.stats.sent;
                            this.stats.failed = e.stats.failed ?? this.stats.failed;
                            this.stats.status = e.stats.status ?? this.stats.status;
                        }
                        if (e?.recipient) {
                            // find recipient and update its pivot fields
                            let r = this.stats.recipients.find(x => x.id === e.recipient.id);
                            if (r) {
                                Object.assign(r, e.recipient); // update status, queued_at, sent_at, error
                            }
                        }
                        if (e?.line) {
                            this.logLines.push(e.line); // ✅ push into reactive array
                            this.$nextTick(() => {
                                const container = this.$root.querySelector('.log-output');
                                container.scrollTop = container.scrollHeight;
                            });
                        }
                    });

                // Initial line
                this.logLines.push(`[${new Date().toLocaleTimeString()}] Subscribed: campaign.${this.campaignId}`);
            }
        });
    </script>
</x-app-layout>
