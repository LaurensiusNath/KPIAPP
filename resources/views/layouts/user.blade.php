<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $title ?? config('app.name', 'KPI Portal') }}</title>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    @livewire('user.sidebar')
    <main class="p-4 md:ml-64 h-auto pt-20">
        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>
