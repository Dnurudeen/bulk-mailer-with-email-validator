<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl">{{ isset($smtpSetting) ? 'Edit SMTP' : 'Add SMTP' }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8">
        <form action="{{ isset($smtpSetting) ? route('smtp.update', $smtpSetting) : route('smtp.store') }}" method="POST"
            class="space-y-4 border p-4 rounded">
            @csrf
            @if (isset($smtpSetting))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Mailer</label>
                    <input type="text" name="mailer"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('mailer', $smtpSetting->mailer ?? 'smtp') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Host</label>
                    <input type="text" name="host"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('host', $smtpSetting->host ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Port</label>
                    <input type="number" name="port"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('port', $smtpSetting->port ?? 587) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Encryption</label>
                    <select name="encryption"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full">
                        <option value=""
                            {{ old('encryption', $smtpSetting->encryption ?? '') == '' ? 'selected' : '' }}>None
                        </option>
                        <option value="tls"
                            {{ old('encryption', $smtpSetting->encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS
                        </option>
                        <option value="ssl"
                            {{ old('encryption', $smtpSetting->encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL
                        </option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Username</label>
                    <input type="text" name="username"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('username', $smtpSetting->username ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">Password</label>
                    <input type="password" name="password"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('password', $smtpSetting->password ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">From Address</label>
                    <input type="email" name="from_address"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('from_address', $smtpSetting->from_address ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="block text-sm mb-1">From Name</label>
                    <input type="text" name="from_name"
                        class="form-control border border-white-400 dark:bg-gray-800 rounded w-full"
                        value="{{ old('from_name', $smtpSetting->from_name ?? '') }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="block text-sm mb-1"><input type="checkbox" name="is_active"
                            {{ old('is_active', $smtpSetting->is_active ?? false) ? 'checked' : '' }}> Set as
                        Active</label>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded">Save</button>
            <a href="{{ route('smtp.index') }}" class="px-4 py-2 bg-red-600 text-white rounded">Cancel</a>
        </form>


        <form method="POST" action="{{ route('validation.store') }}" enctype="multipart/form-data"
            class="space-y-4 border p-4 rounded">
            @csrf
            <div>
                <label class="block text-sm mb-1">CSV file (headers: email, name)</label>
                <input type="file" name="csv" class="border border-white-400 dark:bg-gray-800 p-2"
                    accept=".csv,text/csv" required>
                @error('csv')
                    <div class="text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Optional: Name this list (you can also save later)</label>
                <input type="text" name="list_name"
                    class="border border-white-400 dark:bg-gray-800 rounded p-2 w-full" placeholder="e.g. March leads">
            </div>

            <button class="px-4 py-2 bg-gray-600 text-white rounded">Upload & Validate</button>
        </form>
    </div>
</x-app-layout>
