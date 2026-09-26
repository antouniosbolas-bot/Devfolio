<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->user->name }} · Career Profile CV</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-portfolio-shell">
    <header class="public-cv-header">
        <a href="{{ url('/') }}" class="workspace-brand"><span class="workspace-brand-icon">&lt;/&gt;</span><span>Dev<span>Folio</span></span></a>
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode"><span data-theme-icon>☾</span><span data-theme-label>Dark mode</span></button>
    </header>
    <main class="public-cv-main">
        <p class="design-eyebrow">SHARED CAREER PROFILE</p>
        <button type="button" class="workspace-button workspace-button-muted" onclick="window.print()">Print / Save as PDF</button>
        @include('career.cv-content', ['isDocument' => false])
    </main>
    <x-site-footer />
</body>
</html>
