<x-app-layout>
    <x-slot name="header"><h1 class="workspace-page-title">Choose your repositories</h1></x-slot>
    <div class="workspace-page">
        @if (session('status'))
            <div role="status" class="workspace-alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="workspace-alert is-error">{{ $errors->first() }}</div>
        @endif
        <div class="workspace-page-intro">
            <div>
                <p class="design-eyebrow">SOURCE DATA</p>
                <p class="workspace-page-description">Only selected public repositories are used in your career profile, CV, and portfolio.</p>
            </div>
            <div class="workspace-actions">
                @if (auth()->user()->github_token)
                    <form method="POST" action="{{ route('github.sync') }}">@csrf
                        <button class="workspace-button workspace-button-muted" type="submit">↻ Sync from GitHub</button>
                    </form>
                @else
                    <a class="workspace-button workspace-button-muted" href="{{ route('github.connect') }}">Connect GitHub</a>
                @endif
                <a class="workspace-button" href="{{ route('analysis.index') }}">Continue to analysis →</a>
            </div>
        </div>

        <section class="workspace-stats" aria-label="Repository statistics">
            <article class="workspace-card"><span>Public repositories</span><strong>{{ $publicProjectCount }}</strong></article>
            <article class="workspace-card"><span>Selected</span><strong>{{ $selectedCount }}</strong></article>
            <article class="workspace-card"><span>Languages</span><strong>{{ $languageCount }}</strong></article>
            <article class="workspace-card"><span>Career profile</span><strong class="workspace-stat-status">{{ $profile?->analysis_status === 'completed' ? 'analysed' : 'not analysed yet' }}</strong></article>
        </section>

        <form method="GET" action="{{ route('repositories.index') }}" class="repository-filter-form">
            <label class="sr-only" for="repository-search">Search repositories</label>
            <input id="repository-search" name="q" value="{{ $filters['q'] }}" class="workspace-input" type="search" placeholder="Search repositories">
            <div class="repository-filter-options">
                @foreach (['owned' => 'Owned', 'forks' => 'Forks', 'archived' => 'Archived'] as $value => $label)
                    <button type="submit" name="filter" value="{{ $value }}" class="workspace-filter-button {{ $filters['filter'] === $value ? 'is-active' : '' }}">{{ $label }}</button>
                @endforeach
            </div>
        </form>

        <form method="POST" action="{{ route('repositories.selection') }}" class="repository-list-card">
            @csrf
            @method('PUT')
            <div class="repository-table-header">
                <span>Include repository</span><span>Language</span><span>Category</span><span>Featured</span><span>Updated</span>
            </div>
            @forelse ($projects as $project)
                <article class="repository-row">
                    <input type="hidden" name="visible_project_ids[]" value="{{ $project->id }}">
                    <label class="repository-project-select">
                        <input type="checkbox" name="selected_project_ids[]" value="{{ $project->id }}" @checked($project->selected_for_profile)>
                        <span class="repository-project-copy">
                            <strong>{{ $project->name }} @if ($project->is_fork)<span class="repository-tag">fork</span>@endif @if ($project->is_archived)<span class="repository-tag">archived</span>@endif</strong>
                            <span>{{ $project->description ?: 'No description provided by GitHub.' }}</span>
                            @if ($project->is_fork && $project->selected_for_profile)
                                <small>Forked repositories are shown for context but are not used as evidence in generated text.</small>
                            @endif
                            <a href="{{ $project->html_url }}" target="_blank" rel="noopener noreferrer">Open repository ↗</a>
                        </span>
                    </label>
                    <span class="repository-language">{{ $project->language ?: '—' }}</span>
                    <label class="sr-only" for="category-{{ $project->id }}">Category for {{ $project->name }}</label>
                    <select id="category-{{ $project->id }}" name="category[{{ $project->id }}]" class="workspace-select">
                        @foreach (['personal' => 'Personal', 'academic' => 'Academic', 'open-source' => 'Open source'] as $value => $label)
                            <option value="{{ $value }}" @selected($project->category === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="repository-featured" aria-label="Feature {{ $project->name }}">
                        <input type="checkbox" name="featured_project_ids[]" value="{{ $project->id }}" @checked($project->is_featured)>
                        <span aria-hidden="true">{{ $project->is_featured ? '★' : '☆' }}</span>
                    </label>
                    <time class="repository-updated" datetime="{{ $project->pushed_at?->toDateString() }}">{{ $project->pushed_at?->diffForHumans() ?? 'Date unavailable' }}</time>
                </article>
            @empty
                <div class="workspace-empty">
                    <h2>No public repositories to show yet</h2>
                    <p>Connect GitHub and sync your public repositories. Private repository access is not requested.</p>
                    <a class="workspace-button" href="{{ route('github.connect') }}">Connect GitHub</a>
                </div>
            @endforelse
            @if ($projects->isNotEmpty())
                <div class="repository-list-footer">
                    {{ $projects->links() }}
                    <button type="submit" class="workspace-button">Save repository selection</button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
