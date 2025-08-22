<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">Email Sending Progress</h1>
            {{-- <h1 class="text-2xl font-bold mb-4">{{ $campaign->name }}</h1> --}}
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container">
                <h3>Email Sending Progress</h3>
                <table class="table table-striped" id="progressTable">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Completed At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
        function loadProgress() {
            fetch('/campaigns/progress')
                .then(res => res.json())
                .then(data => {
                    let tbody = '';
                    data.forEach(job => {
                        tbody += `
                    <tr>
                        <td>${job.recipient}</td>
                        <td>
                            ${job.status === 'RUNNING' ? '<span class="text-warning">RUNNING</span>' :
                              job.status === 'DONE' ? '<span class="text-success">DONE</span>' :
                              job.status === 'FAILED' ? '<span class="text-danger">FAILED</span>' : job.status}
                        </td>
                        <td>${job.duration ? job.duration + ' ms' : '-'}</td>
                        <td>${job.completed_at ?? '-'}</td>
                    </tr>`;
                    });
                    document.querySelector('#progressTable tbody').innerHTML = tbody;
                });
        }

        setInterval(loadProgress, 2000); // refresh every 2s
        loadProgress();
    </script>
</x-app-layout>
