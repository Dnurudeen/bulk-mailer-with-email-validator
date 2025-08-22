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
        </div>
    </div>
</x-app-layout>
