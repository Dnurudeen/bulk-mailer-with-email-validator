{{-- @extends('layouts.app', ['title' => 'Campaigns']) --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Campaigns</h1>
            <a class="px-4 py-2 bg-blue-600 btn btn-dark rounded" href="{{ route('campaigns.create') }}">New Campaign</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <table class="w-full bg-white shadow rounded table table-hover">
                <thead class="">
                    <tr class="bg-gray-50">
                        <th class="text-left p-3">Name</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Scheduled</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $c)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-3">{{ $c->name }}</td>
                            <td class="p-3">{{ $c->status }}</td>
                            <td class="p-3">{{ optional($c->scheduled_at)->toDayDateTimeString() }}</td>
                            <td class=""><a class="btn btn-dark" href="{{ route('campaigns.show', $c) }}">Open</a>
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

    {{-- <div class="mt-4">{{ $campaigns->links() }}</div> --}}
    {{-- @endsection --}}
</x-app-layout>
