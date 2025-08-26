<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl">Email Validation</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8">
        <form method="POST" action="{{ route('validation.store') }}" enctype="multipart/form-data" class="space-y-4 border p-4 rounded">
            @csrf
            <div>
                <label class="block text-sm mb-1">CSV file (headers: email, name)</label>
                <input type="file" name="csv" class="border border-white-400 dark:bg-gray-800 p-2" accept=".csv,text/csv" required>
                @error('csv') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Optional: Name this list (you can also save later)</label>
                <input type="text" name="list_name" class="border border-white-400 dark:bg-gray-800 rounded p-2 w-full" placeholder="e.g. March leads">
            </div>

            <button class="px-4 py-2 bg-gray-600 text-white rounded">Upload & Validate</button>
        </form>
    </div>
</x-app-layout>
