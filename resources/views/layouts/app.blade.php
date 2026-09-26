<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DevFolio') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <x-theme-init />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="devfolio-app-shell font-sans antialiased">
        <div class="workspace-frame">
            <livewire:layout.navigation />
            <div class="workspace-main-column">
                <x-workspace-topbar :title="isset($header) ? trim(strip_tags($header)) : 'Workspace'" />
                <main class="workspace-main-content">
                    {{ $slot }}
                </main>
                <x-site-footer />
            </div>
        </div>
    </body>
</html>
