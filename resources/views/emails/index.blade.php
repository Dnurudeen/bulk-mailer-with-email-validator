<!-- resources/views/emails/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl light:text-gray-800 leading-tight">Bulk Email Validator</h1>
            {{-- <div>
                <a class="px-4 py-2 bg-red-600 btn btn-danger rounded" href="{{ route('campaigns.create') }}">New Campaign</a>
                <a class="px-4 py-2 bg-gray-800 btn btn-danger rounded" href="{{ route('validation.index') }}">Validate Email</a>
            </div> --}}
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- <h2>Bulk Email Validator</h2> --}}

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('emails.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Upload CSV (one email per row)</label>
                            <input type="file" name="file" accept=".csv,.txt" class="border p-2 w-full" required>
                        </div>
                        <button
                            class="px-3 py-3 mr-5 border light:border-transparent dark:border-white-600 dark:bg-blue-800 dark:text-white text-sm leading-4 font-medium rounded-md text-gray-500 bg-white light:hover:text-gray-700 dark:hover:text-gray-200">Upload
                            & Queue</button>
                        <a href="{{ route('emails.export') }}"
                            class="px-3 py-3 border light:border-transparent dark:border-white-600 dark:bg-gray-800 dark:text-white text-sm leading-4 font-medium rounded-md text-gray-500 bg-white light:hover:text-gray-700 dark:hover:text-gray-300">Export
                            Results</a>
                    </form>
                </div>
            </div>

            <div class="mb-3">
                <h5>Status Summary</h5>
                <div id="status-summary" class="d-flex gap-3">
                    <div class="px-4 py-1 rounded-full mb-2 bg-blue-400">Pending: <span id="pending">0</span></div>
                    <div class="px-4 py-1 rounded-full mb-2 bg-green-400">Valid: <span id="valid">0</span></div>
                    <div class="px-4 py-1 rounded-full mb-2 bg-red-400">Invalid: <span id="invalid">0</span></div>
                    <div class="px-4 py-1 rounded-full mb-2 bg-gray-400 text-dark">Catch-all: <span
                            id="catch-all">0</span></div>
                    <div class="px-4 py-1 rounded-full mb-2 bg-blue-800 text-dark">Unknown: <span
                            id="unknown">0</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5>Emails</h5>
                    <table class="w-full dark:bg-gray shadow rounded table table-hover">
                        <thead>
                            <tr class="bg-gray-500">
                                <th class="text-left p-3">#</th>
                                <th class="text-left p-3">Email</th>
                                <th class="text-left p-3">Status</th>
                                <th class="text-left p-3">Disposable</th>
                                <th class="text-left p-3">Reason</th>
                                <th class="text-left p-3">Queued At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($emails as $e)
                                <tr data-id="{{ $e->id }}" id="email-row-{{ $e->id }}"
                                    class="border-t light:hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="p-3">{{ $e->id }}</td>
                                    <td class="p-3">{{ $e->email }}</td>

                                    {{-- add classes for easy DOM updates --}}
                                    <td class="p-3">{{ $e->status }}</td>
                                    <td class="p-3">{{ $e->is_disposable ? 'yes' : 'no' }}</td>
                                    <td class="p-3" style="max-width:300px; word-break:break-word;">
                                        {{ $e->reason }}</td>
                                    <td class="p-3">{{ $e->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center my-4">
                        {{ $emails->links('pagination::bootstrap-5') }}
                    </div>

                    <div class="flex justify-end mb-3">
                        <form action="{{ route('emails.clear') }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete all emails? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Clear All Emails
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const updateSummary = async () => {
            try {
                const res = await fetch("{{ route('emails.statuses') }}");
                const data = await res.json();
                const map = {
                    pending: 0,
                    valid: 0,
                    invalid: 0,
                    'catch-all': 0,
                    unknown: 0
                };
                data.forEach(r => {
                    map[r.status] = r.total;
                });
                document.getElementById('pending').textContent = map.pending || 0;
                document.getElementById('valid').textContent = map.valid || 0;
                document.getElementById('invalid').textContent = map.invalid || 0;
                document.getElementById('catch-all').textContent = map['catch-all'] || 0;
                document.getElementById('unknown').textContent = map.unknown || 0;
            } catch (e) {
                console.error(e);
            }
        };

        // poll every 6 seconds 
        updateSummary();
        setInterval(updateSummary, 6000);
    </script>

</x-app-layout>
