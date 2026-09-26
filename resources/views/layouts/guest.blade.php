<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DevFolio') }}</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="devfolio-auth-shell">
    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
        <span data-theme-icon>☾</span>
        <span data-theme-label>Dark mode</span>
    </button>
    <div class="auth-orb auth-orb-one"></div>
    <div class="auth-orb auth-orb-two"></div>
    <main class="auth-layout">
        <section class="auth-showcase">
            <a href="{{ url('/') }}" class="auth-brand" wire:navigate>
                <span class="auth-brand-mark">&lt;/&gt;</span>
                <span>Dev<span>Folio</span></span>
            </a>
            <div class="auth-showcase-copy">
                <p class="auth-eyebrow">YOUR CODE. YOUR STORY.</p>
                <h1>Make your GitHub<br><span>work for you.</span></h1>
                <p class="auth-showcase-text">Turn your best repositories into a career profile and portfolio you can share.</p>
            </div>
            <div class="auth-code-card" aria-hidden="true">
                <div class="auth-code-dots"><i></i><i></i><i></i><span>devfolio.config</span></div>
                <div class="auth-code-line"><b>const</b> <em>developer</em> = {</div>
                <div class="auth-code-line indent"><span>passion:</span> <strong>true</strong>,</div>
                <div class="auth-code-line indent"><span>projects:</span> <strong>∞</strong>,</div>
                <div class="auth-code-line indent"><span>nextStep:</span> <strong>'hired'</strong></div>
                <div class="auth-code-line">};</div>
            </div>
            <div class="auth-stars" aria-hidden="true">✦ <span>Built for developers who build.</span></div>
        </section>
        <section class="auth-form-panel">
            <div class="auth-form-card">
                {{ $slot }}
            </div>
            <p class="auth-legal">By continuing, you agree to DevFolio's terms and privacy policy.</p>
        </section>
    </main>
    <x-site-footer />
</body>
</html>
