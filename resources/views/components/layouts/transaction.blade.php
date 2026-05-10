@props([
    'title' => 'Transaksi',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ $title }} - SiKaMo</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="antialiased bg-gray-50 dark:bg-gray-950">
        {{ $slot ?? $__env->yieldContent('content') }}

        @livewireScripts
        @stack('scripts')
    </body>
</html>
