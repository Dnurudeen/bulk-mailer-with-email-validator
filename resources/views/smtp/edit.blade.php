<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl">
            {{ __('Edit SMTP Server') }}
        </h1>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8">
        <form action="{{ route('smtp.update', $smtp->id) }}" method="POST" class="space-y-4 border p-4 rounded">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Mailer</label>
                    <input type="text" name="mailer" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('mailer', $smtp->mailer ?? 'smtp') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Host</label>
                    <input type="text" name="host" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('host', $smtp->host ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Port</label>
                    <input type="number" name="port" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('port', $smtp->port ?? 587) }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Encryption</label>
                    <select name="encryption" class="form-control border dark:bg-gray-800 rounded w-full">
                        <option value="" @selected(old('encryption', $smtp->encryption ?? '') == '')>None</option>
                        <option value="tls" @selected(old('encryption', $smtp->encryption ?? '') == 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption', $smtp->encryption ?? '') == 'ssl')>SSL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Username</label>
                    <input type="text" name="username" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('username', $smtp->username ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Password</label>
                    <input type="password" name="password" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('password', $smtp->password ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">From Address</label>
                    <input type="email" name="from_address"
                        class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('from_address', $smtp->from_address ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">From Name</label>
                    <input type="text" name="from_name" class="form-control border dark:bg-gray-800 rounded w-full"
                        value="{{ old('from_name', $smtp->from_name ?? '') }}" required>
                </div>
            </div>

            <div class="mt-4">
                <label>
                    <input type="checkbox" name="is_active"
                        {{ old('is_active', $smtp->is_active ?? false) ? 'checked' : '' }}>
                    Set as Active
                </label>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded">Save</button>
                <a href="{{ route('smtp.index') }}" class="px-4 py-2 bg-red-600 text-white rounded">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
