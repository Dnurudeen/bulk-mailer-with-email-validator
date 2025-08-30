<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl">Validation: {{ $batch->filename }}</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8" x-data="validationProgress({{ $batch->id }}, {{ json_encode($initialStats) }}, {{ json_encode($initialResults) }})" x-init="init()">

        <!-- Progress + stats -->
        <div class="border p-4 rounded mb-4">
            <div class="flex justify-between">
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="text-lg font-semibold" x-text="stats.status"></div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Progress</div>
                    <div class="text-lg font-semibold" x-text="progress + '%'"></div>
                </div>
            </div>

            <div class="w-full h-3 bg-gray-200 rounded overflow-hidden my-3">
                <div class="h-3 bg-green-500" :style="`width:${progress}%; transition: width .3s`"></div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center mb-4">
                <div>
                    <div class="text-xs">Total</div>
                    <div class="text-xl font-bold" x-text="stats.total"></div>
                </div>
                <div>
                    <div class="text-xs">Valid</div>
                    <div class="text-xl font-bold text-green-600" x-text="stats.valid"></div>
                </div>
                <div>
                    <div class="text-xs">Invalid</div>
                    <div class="text-xl font-bold text-red-600" x-text="stats.invalid"></div>
                </div>
            </div>
        </div>

        <!-- Tabs + actions -->
        <div class="flex justify-between items-center mb-3">
            <div>
                <button :class="tab === 'valid' ? 'bg-green-600 text-white' : 'bg-gray-200'" @click="tab='valid'"
                    class="px-3 py-1 rounded">Valid</button>
                <button :class="tab === 'invalid' ? 'bg-red-600 text-white' : 'bg-gray-200'" @click="tab='invalid'"
                    class="px-3 py-1 rounded ml-2">Invalid</button>
                <button @click="refresh()" class="px-3 py-1 bg-blue-500 text-white rounded ml-4">Refresh</button>
            </div>

            <div>
                <form method="POST" :action="saveListUrl" @submit.prevent="saveList()" class="inline-block">
                    @csrf
                    <input type="text" x-model="listName" placeholder="save valid to list name"
                        class="p-1 border rounded">
                    <button class="px-3 py-1 bg-indigo-600 text-white rounded">Save Valid To List</button>
                </form>
            </div>
        </div>

            <!-- Tabs -->
    {{-- <div class="flex justify-between items-center mb-3">
        <div>
            <button :class="tab==='all' ? 'bg-gray-700 text-white' : 'bg-gray-200'" @click="tab='all'" class="px-3 py-1 rounded">All</button>
            <button :class="tab==='valid' ? 'bg-green-600 text-white' : 'bg-gray-200'" @click="tab='valid'" class="px-3 py-1 rounded ml-2">Valid</button>
            <button :class="tab==='invalid' ? 'bg-red-600 text-white' : 'bg-gray-200'" @click="tab='invalid'" class="px-3 py-1 rounded ml-2">Invalid</button>
        </div>
        ...
    </div> --}}

        <!-- Logs -->
        <div class="bg-black text-green-400 font-mono p-2 h-48 overflow-y-auto rounded mb-4">
            <template x-for="(l, idx) in logLines" :key="idx">
                <div x-text="l"></div>
            </template>
        </div>

        <!-- Results -->
        <div class="overflow-auto border rounded p-2" style="max-height:400px;">
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b text-gray-500">
                        <th class="p-2">Email</th>
                        <th class="p-2">Name</th>
                        <th class="p-2">Score</th>
                        <th class="p-2">State</th>
                        <th class="p-2">Checked At</th>
                    </tr>
                </thead>
                <!-- Table -->
                <tbody>
                    <template x-for="(item, idx) in displayList" :key="item.id ?? idx">
                        <tr class="border-b">
                            <td class="p-2" x-text="item.email"></td>
                            <td class="p-2" x-text="item.name ?? ''"></td>
                            <td class="p-2" x-text="item.score ?? ''"></td>
                            <td class="p-2"
                                x-text="item.is_valid === true ? 'valid' : (item.is_valid === false ? 'invalid' : 'pending')">
                            </td>
                            <td class="p-2" x-text="item.checked_at ?? ''"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- <button :class="tab === 'all' ? 'bg-gray-700 text-white' : 'bg-gray-200'" @click="tab='all'"
                class="px-3 py-1 rounded ml-2">All</button> --}}
        </div>
    </div>

    @push('scripts')
        <script>
            window.validationProgress = (batchId, initialStats = {}, initialResults = []) => ({
                batchId,
                stats: {
                    total: initialStats.total ?? 0,
                    valid: initialStats.valid ?? 0,
                    invalid: initialStats.invalid ?? 0,
                    status: initialStats.status ?? 'queued',
                },
                results: initialResults ?? [],
                logLines: [`[${new Date().toLocaleTimeString()}] Subscribed to validation.${batchId}`],
                tab: 'valid',
                listName: '',
                get progress() {
                    const done = this.stats.valid + this.stats.invalid;
                    return this.stats.total > 0 ? Math.round((done / this.stats.total) * 100) : 0;
                },
                get validList() {
                    return this.results.filter(r => r.is_valid);
                },
                get invalidList() {
                    return this.results.filter(r => !r.is_valid);
                },
                get displayList() {
                    if (this.tab === 'valid') {
                        return this.results.filter(r => r.is_valid === true);
                    } else if (this.tab === 'invalid') {
                        return this.results.filter(r => r.is_valid === false);
                    }
                    return this.results; // fallback = all
                },
                get saveListUrl() {
                    return `/validation/${this.batchId}/save-list`;
                },
                init() {
                    window.Echo.private(`validation.${this.batchId}`)
                        .listen('.progress', (e) => {
                            console.log('VALIDATION EVENT:', e);
                            if (e?.stats) Object.assign(this.stats, e.stats);
                            if (e?.result) {
                                const idx = this.results.findIndex(x => x.id === e.result.id);
                                if (idx !== -1) {
                                    this.results.splice(idx, 1, Object.assign(this.results[idx], e.result));
                                } else {
                                    this.results.unshift(e.result);
                                }
                            }
                            if (e?.line) {
                                this.logLines.push(e.line);
                                this.$nextTick(() => {
                                    const c = this.$root.querySelector('.bg-black');
                                    if (c) c.scrollTop = c.scrollHeight;
                                });
                            }
                        });
                },
                refresh() {
                    fetch(`/validation/${this.batchId}`)
                        .then(() => this.logLines.push(`[${new Date().toLocaleTimeString()}] Refreshed`))
                        .catch(console.error);
                },
                async saveList() {
                    if (!this.listName.trim()) {
                        alert('Enter list name');
                        return;
                    }
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch(this.saveListUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            list_name: this.listName
                        })
                    });
                    if (res.ok) {
                        this.logLines.push(`[${new Date().toLocaleTimeString()}] Saved list: ${this.listName}`);
                        this.listName = '';
                    } else {
                        alert('Failed to save list');
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
