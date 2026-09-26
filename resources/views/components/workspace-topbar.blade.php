<header class="workspace-topbar">
    <div class="workspace-topbar-context">
        <span class="workspace-overline">Developer workspace</span>
        <span class="workspace-breadcrumb">{{ $title }}</span>
    </div>
    <div class="workspace-topbar-actions">
        <span class="workspace-connection-indicator">
            <span class="workspace-status-dot {{ auth()->user()->github_token ? 'is-connected' : '' }}"></span>
            {{ auth()->user()->github_token ? 'GitHub connected' : 'GitHub not connected' }}
        </span>
        <button class="theme-toggle app-theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
            <span data-theme-icon>☾</span>
            <span data-theme-label>Dark mode</span>
        </button>
    </div>
</header>
