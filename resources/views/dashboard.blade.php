<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">Your workspace</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-800">Dashboard</h1>
        </div>
    </x-slot>

    <div class="dashboard-page min-h-[calc(100vh-65px)] bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-medium text-sky-900">
                    {{ session('status') }}
                </div>
            @endif

            <section class="flex flex-col justify-between gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-sky-700">Workspace overview</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Welcome back, {{ auth()->user()->name }}.
                    </h2>
                    <p class="mt-3 leading-7 text-slate-600">
                        Bring your GitHub work together, then shape it into a portfolio you can share.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($portfolio)
                        <a href="{{ route('portfolio.edit', $portfolio) }}" class="inline-flex items-center justify-center rounded-xl bg-sky-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-800">
                            Continue portfolio
                        </a>
                    @else
                        <a href="{{ route('portfolio.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-800">
                            Create portfolio
                        </a>
                    @endif
                    <a href="{{ route('portfolio.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        My portfolio
                    </a>
                </div>
            </section>

            <section aria-label="Workspace statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Imported projects</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $projectCount }}</p>
                    <p class="mt-2 text-sm text-slate-500">From your connected GitHub account</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">GitHub connection</p>
                    <p class="mt-3 text-2xl font-bold text-slate-900">{{ auth()->user()->github_username ?: 'Not connected' }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $latestSync?->status ? ucfirst($latestSync->status).' sync' : 'No sync yet' }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Portfolio</p>
                    <p class="mt-3 text-2xl font-bold text-slate-900">{{ $portfolio ? ($portfolio->is_published ? 'Published' : 'Draft') : 'Not created' }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $portfolio ? '/p/'.$portfolio->subdomain : 'Create a page for your work' }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">AI writing</p>
                    <p class="mt-3 text-2xl font-bold text-slate-900">{{ $portfolio ? ucfirst($portfolio->ai_status) : 'Not started' }}</p>
                    <p class="mt-2 text-sm text-slate-500">AI content is generated for your portfolio</p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(18rem,0.85fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Recent GitHub projects</h2>
                            <p class="mt-1 text-sm text-slate-500">The latest repositories imported for your portfolio.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $projectCount }} total</span>
                    </div>
                    @if ($latestProjects->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($latestProjects as $project)
                                <article class="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                                    <div class="min-w-0">
                                        <h3 class="truncate font-semibold text-slate-900">{{ $project->name }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $project->description ?: 'No repository description provided.' }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3 text-sm text-slate-500">
                                        @if ($project->language)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $project->language }}</span>
                                        @endif
                                        <a href="{{ $project->html_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-700 hover:text-sky-900">GitHub ↗</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-lg font-bold text-sky-700" aria-hidden="true">&lt;/&gt;</div>
                            <h3 class="mt-4 font-semibold text-slate-900">Your projects will show up here</h3>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">Connect GitHub and sync your repositories to start building a portfolio from your real work.</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-100 font-bold text-slate-700" aria-hidden="true">GH</span>
                            <div>
                                <h2 class="font-bold text-slate-900">GitHub sync</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    @if (auth()->user()->github_token)
                                        {{ $latestSync?->status === 'failed' ? 'The last sync did not finish. You can try again.' : 'Refresh your imported repositories and language data.' }}
                                    @else
                                        Connect GitHub to import your public projects and profile.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="mt-5">
                            @if (auth()->user()->github_token)
                                @if ($latestSync?->status === 'pending' || $latestSync?->status === 'running')
                                    <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-slate-200 px-4 py-3 text-sm font-semibold text-slate-500">Sync in progress</button>
                                @else
                                    <form method="POST" action="{{ route('github.sync') }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Sync GitHub projects</button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('auth.github.redirect') }}" class="block w-full rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-slate-700">Connect GitHub</a>
                            @endif
                        </div>
                        @if ($latestSync?->completed_at)
                            <p class="mt-3 text-xs text-slate-500">Last updated {{ $latestSync->completed_at->diffForHumans() }}</p>
                        @endif
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <p class="text-sm font-semibold text-sky-700">Next step</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $portfolio ? 'Review your portfolio' : 'Turn your work into a portfolio' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $portfolio ? 'Edit your featured projects, preview the page, or publish it when it is ready.' : 'Choose your best repositories, review the AI-assisted copy, and share a polished public page.' }}
                        </p>
                        <a href="{{ $portfolio ? route('portfolio.edit', $portfolio) : route('portfolio.create') }}" class="mt-5 inline-flex font-semibold text-sky-700 hover:text-sky-900">
                            {{ $portfolio ? 'Open portfolio editor' : 'Start building' }} <span class="ml-2" aria-hidden="true">→</span>
                        </a>
                    </section>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
