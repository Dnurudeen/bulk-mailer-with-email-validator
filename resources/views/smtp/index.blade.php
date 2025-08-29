{{-- @extends('layouts.app', ['title' => 'Campaigns']) --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl light:text-gray-800 leading-tight">SMTP Servers</h1>
            <div>
                <a class="px-4 py-2 bg-red-600 btn btn-danger rounded" href="{{ route('smtp.create') }}">Add New SMTP</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="w-full dark:bg-gray shadow rounded table table-hover">
                <thead>
                    <tr class="bg-gray-500">
                        <th class="text-left p-3">Host</th>
                        <th class="text-left p-3">Port</th>
                        <th class="text-left p-3">Username</th>
                        <th class="text-left p-3">From</th>
                        <th class="text-left p-3">Active</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $smtp)
                        <tr class="border-t light:hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="p-3">{{ $smtp->host }}</td>
                            <td class="p-3">{{ $smtp->port }}</td>
                            <td class="p-3">{{ $smtp->username }}</td>
                            <td class="p-3">{{ $smtp->from_name }} &lt;{{ $smtp->from_address }}&gt;</td>
                            <td class="p-3">{!! $smtp->is_active ? '<span class="badge bg-success">Yes</span>' : 'No' !!}</td>
                            <td class="flex gap-2 p-3">
                                <a href="{{ route('smtp.edit', $smtp) }}"
                                    class="btn btn-dark bg-blue-600 text-white btn px-2 py-1 rounded">Edit</a>
                                <form action="{{ route('smtp.destroy', $smtp) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger bg-red-600 text-white btn px-2 py-1 rounded"
                                        onclick="return confirm('Delete this SMTP?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="mx-auto py-12">
                            <td colspan="6" class="text-center">No SMTP servers configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
