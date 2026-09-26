<x-app-layout>
    <x-slot name="header"><h1 class="workspace-page-title">Settings</h1></x-slot>
    <div class="workspace-page settings-page">
        <p class="design-eyebrow">ACCOUNT & PRIVACY</p>
        <p class="workspace-page-description">Manage your account information and GitHub connection.</p>
        <section class="workspace-panel settings-card">
            <div><h2>Profile details</h2><p>{{ auth()->user()->name }} · {{ auth()->user()->email }}</p></div>
            <a class="workspace-button workspace-button-muted" href="{{ route('profile') }}">Edit account details</a>
        </section>
        <section class="workspace-panel settings-card">
            <div><h2>GitHub connection</h2><p>{{ auth()->user()->github_username ? 'Connected as @'.auth()->user()->github_username : 'No GitHub account is connected.' }}</p><p>Your access token is encrypted in the database and never included in browser responses. You can revoke it in GitHub Settings → Applications.</p></div>
            @if (auth()->user()->github_token)
                <span class="settings-connected-indicator"><i class="analysis-step-dot"></i> Connected</span>
            @else
                <a class="workspace-button workspace-button-muted" href="{{ route('github.connect') }}">Connect GitHub</a>
            @endif
        </section>
        <section class="workspace-panel settings-card">
            <div><h2>Appearance</h2><p>Your Light/Dark theme is stored in this browser and stays selected when you move between pages.</p></div>
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode"><span data-theme-icon>☾</span><span data-theme-label>Dark mode</span></button>
        </section>
    </div>
</x-app-layout>
