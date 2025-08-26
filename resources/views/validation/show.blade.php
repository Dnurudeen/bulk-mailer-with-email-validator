<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h1 class="font-semibold text-xl">Validation: {{ $batch->original_name }}</h1>
            <form method="POST" action="{{ route('validation.storeList', $batch) }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" class="border rounded p-2" placeholder="Recipient list name" required>
                <button class="px-3 py-2 bg-green-600 text-white rounded">Save Valid as List</button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8"
         x-data="validationProgress({{ $batch->id }}, {{ json_encode([
            'valid' => $batch->valid_count,
            'invalid' => $batch->invalid_count,
            'total' => $batch->total,
            'status' => $batch->status,
         ]) }})" x-init="init()">

        <div class="border rounded p-4 mb-6">
            <div class="flex justify-between">
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="font-semibold" x-text="stats.status"></div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Progress</div>
                    <div class="font-semibold" x-text="percent + '%'"></div>
                </div>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded mt-3">
                <div class="h-2 bg-green-500" :style="`width:${percent}%;transition:width .3s`"></div>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center mt-4">
                <div><div class="text-xs text-gray-500">Valid</div><div class="text-xl font-bold text-green-600" x-text="stats.valid"></div></div>
                <div><div class="text-xs text-gray-500">Invalid</div><div class="text-xl font-bold text-red-600" x-text="stats.invalid"></div></div>
                <div><div class="text-xs text-gray-500">Total</div><div class="text-xl font-bold" x-text="stats.total"></div></div>
            </div>
            <div class="bg-black text-green-400 font-mono p-2 h-40 overflow-y-scroll rounded mt-4 log-output">
                <template x-for="item in logLines" :key="item.id">
                    <div x-text="item.text"></div>
                </template>
            </div>
        </div>

        <div class="border rounded p-4">
            <div class="flex gap-4 mb-4">
                <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white" @click="tab='valid'">Valid Emails</button>
                <button type="button" class="px-3 py-1 rounded bg-gray-600 text-white" @click="tab='invalid'">Invalid Emails</button>
            </div>

            <template x-if="tab==='valid'">
                <div>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="p-2">Email</th><th class="p-2">Name</th></tr></thead>
                        <tbody>
                        @foreach ($valid as $row)
                            <tr class="border-b"><td class="p-2">{{ $row->email }}</td><td class="p-2">{{ $row->name }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </template>

            <template x-if="tab==='invalid'">
                <div>
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="p-2">Email</th><th class="p-2">Name</th><th class="p-2">Reason</th></tr></thead>
                        <tbody>
                        @foreach ($invalid as $row)
                            <tr class="border-b"><td class="p-2">{{ $row->email }}</td><td class="p-2">{{ $row->name }}</td><td class="p-2">{{ $row->reason }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>

    <script type="module">
        window.validationProgress = (batchId, initial) => ({
            tab: 'valid',
            batchId,
            stats: { valid: initial.valid ?? 0, invalid: initial.invalid ?? 0, total: initial.total ?? 0, status: initial.status ?? 'pending' },
            logLines: [],
            get percent() { const done = this.stats.valid + this.stats.invalid; return this.stats.total ? Math.round((done/this.stats.total)*100) : 0; },
            init() {
                const channel = `validation.${this.batchId}`;
                // avoid duplicates if Alpine comp re-init
                if (window.__joinedValidation?.[channel]) Echo.leave(channel);
                window.__joinedValidation = window.__joinedValidation || {};
                window.__joinedValidation[channel] = true;

                Echo.private(channel)
                    .listen('.progress', (e) => {
                        if (e?.stats) {
                            this.stats.valid   = e.stats.valid   ?? this.stats.valid;
                            this.stats.invalid = e.stats.invalid ?? this.stats.invalid;
                            this.stats.total   = e.stats.total   ?? this.stats.total;
                            this.stats.status  = e.stats.status  ?? this.stats.status;
                        }
                        if (e?.line) this.pushLine(e.line);
                    });
                this.pushLine(`[${new Date().toLocaleTimeString()}] Subscribed: ${channel}`);
            },
            pushLine(text) {
                // give each line a unique id to satisfy Alpine's :key and avoid duplicates
                this.logLines.push({ id: Date.now() + Math.random(), text });
                this.$nextTick(() => {
                    const el = document.querySelector('.log-output');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        });
    </script>
</x-app-layout>
