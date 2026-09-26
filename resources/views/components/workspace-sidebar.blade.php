@php
    $links = [
        ['Repositories', 'repositories.index', 'repositories.*'],
        ['Analysis', 'analysis.index', 'analysis.*'],
        ['Career Profile', 'career-profile.index', 'career-profile.*'],
        ['CV', 'cv.index', 'cv.*'],
        ['Portfolio', 'portfolio.index', 'portfolio.*'],
        ['Settings', 'settings.index', 'settings.*'],
    ];
@endphp

<aside class="workspace-sidebar" x-data="{ open: false }">
    <div class="workspace-sidebar-top">
        <a href="{{ route('dashboard') }}" class="workspace-brand" wire:navigate aria-label="DevFolio dashboard">
            <span class="workspace-brand-icon">&lt;/&gt;</span>
            <span>Dev<span>Folio</span></span>
        </a>
        <button type="button" class="workspace-menu-button" @click="open = !open" :aria-expanded="open" aria-label="Toggle navigation">
            <span x-text="open ? 'Close' : 'Menu'"></span>
        </button>
    </div>

    <nav class="workspace-nav" :class="{ 'is-open': open }" aria-label="Workspace">
        <a href="{{ route('dashboard') }}" class="workspace-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" wire:navigate>Overview</a>
        @foreach ($links as [$label, $route, $active])
            <a href="{{ route($route) }}" class="workspace-nav-link {{ request()->routeIs($active) ? 'is-active' : '' }}" wire:navigate>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="workspace-sidebar-user">
        <span class="workspace-user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span class="workspace-user-details">
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ auth()->user()->github_username ? '@'.auth()->user()->github_username : auth()->user()->email }}</span>
        </span>
        <button type="button" wire:click="logout" class="workspace-logout" aria-label="Log out">↗</button>
    </div>
</aside>
