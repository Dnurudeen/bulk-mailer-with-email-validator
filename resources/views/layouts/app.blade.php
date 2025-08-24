<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Bulk Mailer') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
            crossorigin="anonymous"> --}}

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://kit.fontawesome.com/3df60fe6e2.js" crossorigin="anonymous"></script>

        
    </head>

    <body class="font-sans antialiased dark:bg-gray-800 dark:text-white">
        {{-- <body class="font-sans text-gray-900 antialiased"> --}}
        <div class="min-h-screen light:bg-gray-100">
            @include('layouts.navigation')

            {{-- <hr> --}}

            <!-- Page Heading -->
            @isset($header)
                <header class="light:bg-white bg-gray-500 bg-shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- <hr><hr> --}}
            
            @if (session('success'))
                <div class="p-3 bg-green-500 border border-green-500 rounded mb-4">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-600 border border-red-600 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot ?? '' }}

            <!-- Page Content -->
            <main>
                {{-- {{ $slot }} --}}
                @yield('content')
            </main>
        </div>


        <script>
            function toggleDarkMode() {
                const html = document.documentElement;
                const element = document.getElementById('dark-mode');

                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    element.classList.remove('fa-sun');
                    element.classList.add('fa-moon');
                    localStorage.setItem('theme', 'light');
                } else {
                    html.classList.add('dark');
                    element.classList.add('fa-sun');
                    element.classList.remove('fa-moon');
                    localStorage.setItem('theme', 'dark');
                }
            }

            // Load saved theme on page load
            if (localStorage.getItem('theme') === 'dark' ||
                (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
    </body>

</html>


{{-- <!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Bulk Mailer' }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="max-w-5xl mx-auto p-6">
    <nav class="flex justify-between mb-6">
      <a href="{{ route('campaigns.index') }}" class="font-semibold">Bulk Mailer</a>
      <div>
        @auth
          <span class="mr-2">{{ auth()->user()->name }}</span>
          <form action="{{ route('logout') }}" method="POST" class="inline">@csrf<button class="underline">Logout</button></form>
        @else
          <a href="{{ route('login') }}" class="underline">Login</a>
        @endauth
      </div>
    </nav>
    @if (session('success'))<div class="p-3 bg-green-100 border border-green-300 rounded mb-4">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="p-3 bg-red-100 border border-red-300 rounded mb-4"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    {{ $slot ?? '' }}
    @yield('content')
  </div>
</body>
</html> --}}
