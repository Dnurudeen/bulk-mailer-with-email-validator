<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl">Email Validation</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8">
        <form action="{{ route('validation.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border p-4 rounded">
            @csrf
            <div>
                <label class="block text-sm mb-1">CSV file (headers: email, name)</label>
                <input type="file" name="csv" accept=".csv,text/csv" class="border p-2 w-full" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Optional: List name</label>
                <input type="text" name="list_name" class="border p-2 w-full" placeholder="Marketing list March">
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Upload & Validate</button>
        </form>

        <h2 class="mt-6">Recent Batches</h2>
        <ul>
            @foreach($batches as $b)
                <li><a href="{{ route('validation.show', $b) }}">{{ $b->filename }} — {{ $b->total }} rows — {{ $b->status }}</a></li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
