<x-app-layout>
    <x-slot name="header"><h1 class="workspace-page-title">Building your career profile</h1></x-slot>
    <div class="workspace-page">
        @if ($errors->any())
            <div role="alert" class="workspace-alert is-error">{{ $errors->first() }}</div>
        @endif
        <div class="workspace-page-intro">
            <div>
                <p class="design-eyebrow">REPOSITORY ANALYSIS</p>
                <p class="workspace-page-description">Review the selected repository data, then build a profile from GitHub facts and information you provide.</p>
            </div>
            <a href="{{ route('repositories.index') }}" class="workspace-button workspace-button-muted">Review repositories</a>
        </div>
        <div class="analysis-layout">
            <section class="workspace-panel analysis-console">
                <div class="analysis-console-header"><span class="analysis-window-dots"><i></i><i></i><i></i></span><code>analysis.log</code><span>local evidence</span></div>
                <ul class="analysis-steps">
                    <li><span class="analysis-step-dot"></span><span>Loading selected public repositories (forks excluded from evidence)</span><strong>{{ $selectedCount }} selected</strong></li>
                    <li><span class="analysis-step-dot"></span><span>Reading repository descriptions and languages</span><strong>{{ $selectedCount ? 'ready' : 'waiting' }}</strong></li>
                    <li><span class="analysis-step-dot"></span><span>Redacting common credential patterns from README excerpts</span><strong>{{ $selectedCount ? 'on read' : 'waiting' }}</strong></li>
                    <li><span class="analysis-step-dot"></span><span>Building evidence linked to repository sources</span><strong>{{ $profile?->analysis_status === 'completed' ? 'complete' : 'waiting' }}</strong></li>
                    <li><span class="analysis-step-dot"></span><span>Adding experience, education, and certifications</span><strong>you provide</strong></li>
                </ul>
                <div class="analysis-notice">
                    This analysis currently uses saved GitHub descriptions, repository language statistics, and README excerpts. It does not call an AI provider or claim that unverified work experience was found.
                </div>
            </section>
            <aside class="analysis-side">
                <section class="workspace-panel analysis-progress">
                    <span>Status</span><strong>{{ $profile?->analysis_status === 'completed' ? 'Reviewed' : 'Ready' }}</strong>
                    <div class="analysis-progress-bar"><span style="width: {{ $profile?->analysis_status === 'completed' ? '100' : ($selectedCount ? '45' : '0') }}%"></span></div>
                    <p>{{ $selectedCount }} selected repositories</p>
                    @if ($profile?->analyzed_at)<p>Last analysed {{ $profile->analyzed_at->diffForHumans() }}</p>@endif
                </section>
                <section class="workspace-panel">
                    <h2>What is used</h2>
                    <p>Repository names, descriptions, GitHub language statistics, and a trimmed README excerpt from repositories you select. Your access token is never sent to an AI service.</p>
                </section>
                <form method="POST" action="{{ route('analysis.run') }}" data-ai-generation>
                    @csrf
                    <button type="submit" data-loading-text="Analysing repositories..." @disabled($selectedCount === 0) class="workspace-button workspace-button-full">
                        {{ $profile?->analysis_status === 'completed' ? 'Re-analyse selected repositories' : 'Analyse '.$selectedCount.' repositories' }}
                    </button>
                </form>
                @if ($profile?->analysis_status === 'completed')
                    <a class="workspace-button workspace-button-muted workspace-button-full" href="{{ route('career-profile.index') }}">Review career profile</a>
                @endif
            </aside>
        </div>
        @if ($latestProjects->isNotEmpty())
            <section class="workspace-panel analysis-projects">
                <h2>Repositories in this analysis</h2>
                <div class="analysis-project-list">
                    @foreach ($latestProjects as $project)
                        <article><strong>{{ $project->name }}</strong><span>{{ $project->language ?: 'Language not specified' }}</span><span>{{ $project->readme_excerpt ? 'README available' : 'README not analysed yet' }}</span></article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
