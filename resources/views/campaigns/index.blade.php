{{-- @extends('layouts.app', ['title' => 'Campaigns']) --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl light:text-gray-800 leading-tight">Campaigns</h1>
            <div>
                <a class="px-4 py-2 bg-red-600 btn btn-danger rounded" href="{{ route('campaigns.create') }}">New Campaign</a>
                <a class="px-4 py-2 bg-gray-800 btn btn-danger rounded" href="{{ route('validation.index') }}">Validate Email</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <table class="w-full dark:bg-gray shadow rounded table table-hover">
                <thead class="">
                    <tr class="bg-gray-500">
                        <th class="text-left p-3">Name</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Scheduled</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $c)
                        <tr class="border-t light:hover:bg-gray-100 dark:hover:bg-gray-700">
                            <td class="p-3">{{ $c->name }}</td>
                            <td class="p-3">{{ $c->status }}</td>
                            <td class="p-3">{{ optional($c->scheduled_at)->toDayDateTimeString() }}</td>
                            <td class=""><a class="btn btn-dark bg-red-600 text-white btn px-2 py-1 rounded" href="{{ route('campaigns.show', $c) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="mx-auto py-12">
                            <td class="p-3" colspan="4">No campaigns yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center my-4">
        {{ $campaigns->links('pagination::bootstrap-5') }}
    </div>

</x-app-layout>
