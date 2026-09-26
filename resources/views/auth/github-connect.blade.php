<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect GitHub · DevFolio</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="connection-page">
    <header class="connection-header">
        <a href="{{ url('/') }}" class="workspace-brand"><span class="workspace-brand-icon">&lt;/&gt;</span><span>Dev<span>Folio</span></span></a>
        <a href="{{ url('/') }}" class="connection-back">Back to home</a>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
            <span data-theme-icon>☾</span><span data-theme-label>Dark mode</span>
        </button>
    </header>
    <main class="connection-main">
        <section class="connection-card">
            <p class="design-eyebrow">PRIVATE BY DESIGN</p>
            <h1>Connect your GitHub</h1>
            <p class="connection-intro">We'll send you to GitHub to approve access, then bring you back here to choose your projects.</p>
            @if (session('status'))
                <div role="status" class="workspace-alert">{{ session('status') }}</div>
            @endif
            @if ($errors->has('github'))
                <div role="alert" class="workspace-alert is-error">{{ $errors->first('github') }}</div>
            @endif
            <h2 class="connection-section-label">What we read</h2>
            <ul class="connection-permissions">
                <li><span>Your public profile</span><code>read:user</code></li>
                <li><span>Your email address</span><code>user:email</code></li>
                <li><span>Public repositories you choose</span><code>public only</code></li>
            </ul>
            <h2 class="connection-section-label">What we never do</h2>
            <ul class="connection-promises">
                <li>Write to your repositories or push code</li>
                <li>Send your GitHub token to an AI provider</li>
                <li>Publish anything without your review</li>
            </ul>
            <a href="{{ route('auth.github.redirect') }}" class="connection-github-button">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 .8a11.2 11.2 0 0 0-3.54 21.83c.56.1.77-.24.77-.54v-2.1c-3.14.68-3.8-1.34-3.8-1.34-.51-1.3-1.25-1.64-1.25-1.64-1.02-.7.08-.69.08-.69 1.12.08 1.71 1.15 1.71 1.15 1 .1.99 2.38 3.9 1.68.1-.72.39-1.21.7-1.49-2.5-.29-5.14-1.25-5.14-5.57 0-1.23.44-2.23 1.16-3.02-.12-.29-.5-1.43.11-2.98 0 0 .95-.3 3.08 1.15a10.7 10.7 0 0 1 5.6 0c2.13-1.45 3.07-1.15 3.07-1.15.61 1.55.23 2.69.11 2.98.72.79 1.15 1.79 1.15 3.02 0 4.33-2.64 5.27-5.16 5.56.4.35.75 1.03.75 2.08v3.08c0 .3.2.65.78.54A11.2 11.2 0 0 0 12 .8Z"/></svg>
                Continue with GitHub
            </a>
            <p class="connection-note">You can revoke DevFolio's authorization at any time in GitHub Settings → Applications. Only repositories you select are used in your profile.</p>
            <section class="oauth-help" aria-labelledby="oauth-help-title">
                <h2 id="oauth-help-title">Still seeing “redirect_uri is not associated with this application”?</h2>
                <p>GitHub is rejecting the callback before it returns to DevFolio. This is the exact callback DevFolio sends:</p>
                <div class="oauth-callback">
                    <code>{{ config('services.github.redirect') }}</code>
                    <button type="button" class="workspace-button workspace-button-muted" data-copy-text="{{ config('services.github.redirect') }}" data-copy-success="Callback copied" data-copy-fallback="Select the callback above and copy it">Copy callback</button>
                </div>
                <p>In GitHub, open the <strong>OAuth App whose Client ID is configured for this project</strong>, then set <strong>Authorization callback URL</strong> to the exact address above. Keep the same protocol, host, port, and path; <code>localhost</code> and <code>127.0.0.1</code> are different addresses.</p>
                <a class="oauth-settings-link" href="https://github.com/settings/developers" target="_blank" rel="noopener noreferrer">Open GitHub Developer settings ↗</a>
                <p class="oauth-config-note">After saving the OAuth App, make sure <code>GITHUB_REDIRECT_URI</code> in <code>.env</code> matches, then run <code>php artisan config:clear</code>.</p>
            </section>
        </section>
    </main>
    <x-site-footer />
</body>
</html>
