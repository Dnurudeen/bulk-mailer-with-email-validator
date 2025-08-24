<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            {{-- <h1 class="font-semibold text-xl text-gray-800 leading-tight">Create Campaign</h1> --}}
            {{-- <a class="px-4 py-2 bg-blue-600 btn btn-dark rounded" href="{{ route('campaigns.create') }}">New Campaign</a> --}}
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <fieldset class="border border-gray-300 dark:border-gray-400 rounded px-4">
                <legend><h1 class="font-semibold text-xl text-white-800 leading-tight">Create Campaign</h1></legend>
                <form method="POST" action="{{ route('campaigns.store') }}"
                    class="p-6 rounded shadow space-y-4">
                    @csrf
                    <div>
                        <label class="block font-medium">Name</label>
                        <input name="name" class="w-full border border-white-400 dark:bg-gray-800 rounded p-2" required>
                    </div>
                    <div>
                        <label class="block font-medium">Subject</label>
                        <input name="subject" class="w-full border border-white-400 dark:bg-gray-800 rounded p-2" required>
                    </div>
                    <div>
                        <label class="block font-medium">HTML Body</label>
                        <textarea name="html_body" rows="8" class="w-full border border-white-400 dark:bg-gray-800 rounded p-2" required><h1>Hello!</h1><p>Your content here.</p></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium">Batch Size</label>
                            <input name="batch_size" type="number" value="100" class="w-full border border-white-400 dark:bg-gray-800 rounded p-2">
                        </div>
                        <div>
                            <label class="block font-medium">Schedule At (optional)</label>
                            <input name="scheduled_at" type="datetime-local" class="w-full border border-white-400 dark:bg-gray-800 rounded p-2">
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button class="px-5 py-2 bg-red-600 btn btn-dark rounded">Save</button>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</x-app-layout>
