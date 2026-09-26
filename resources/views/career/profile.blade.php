<x-app-layout>
    <x-slot name="header"><h1 class="workspace-page-title">Career Profile</h1></x-slot>
    <div class="workspace-page">
        @if (session('status'))
            <div role="status" class="workspace-alert">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="workspace-alert is-error">{{ $errors->first() }}</div>
        @endif
        <div class="workspace-page-intro">
            <div>
                <p class="design-eyebrow">EVIDENCE, NOT INVENTION</p>
                <p class="workspace-page-description">{{ $profile?->analysis_status === 'completed' ? 'Analysis complete · review and edit the details before creating your CV.' : 'Select and analyse repositories to build a profile from verified project data.' }}</p>
            </div>
            <div class="workspace-actions">
                <a href="{{ route('analysis.index') }}" class="workspace-button workspace-button-muted">Re-analyse</a>
                <button form="career-profile-form" class="workspace-button" type="submit">Save profile</button>
            </div>
        </div>
        <div class="career-profile-layout">
            <form id="career-profile-form" method="POST" action="{{ route('career-profile.update') }}" class="career-profile-main">
                @csrf
                @method('PUT')
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Professional summary</h2><span>{{ $profile?->analysis_status === 'completed' ? 'EDITABLE' : 'NOT ANALYSED' }}</span></div>
                    <label class="workspace-label" for="headline">Headline</label>
                    <input id="headline" name="headline" maxlength="180" class="workspace-input" value="{{ old('headline', $profile?->headline) }}" placeholder="{{ auth()->user()->name }}">
                    <label class="workspace-label" for="summary">Summary</label>
                    <textarea id="summary" name="summary" rows="5" maxlength="3000" class="workspace-input">{{ old('summary', $profile?->summary) }}</textarea>
                    <p class="workspace-field-hint">Edit the summary freely. Add only details you can verify or provide yourself.</p>
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Skills</h2><span>FROM GITHUB LANGUAGES</span></div>
                    @if (count($skills))
                        <div class="career-skill-list">
                            @foreach ($skills as $skill => $count)
                                <span>{{ $skill }} <small>{{ $count }}</small></span>
                            @endforeach
                        </div>
                        <p class="workspace-field-hint">Counts show how many selected repositories list that language in GitHub.</p>
                    @else
                        <p class="workspace-empty-copy">No language data yet. Sync repositories, select projects, then analyse them.</p>
                    @endif
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Projects</h2><a href="{{ route('repositories.index') }}">Manage selection</a></div>
                    @forelse ($projects as $project)
                        <article class="career-project">
                            <div class="career-project-title"><strong>{{ $project->name }}</strong><a href="{{ $project->html_url }}" target="_blank" rel="noopener noreferrer">Source ↗</a></div>
                            <p>{{ $project->description ?: 'No description was provided by GitHub.' }}</p>
                            @if ($project->readme_excerpt)
                                <details><summary>README evidence</summary><pre>{{ Str::limit($project->readme_excerpt, 1200) }}</pre></details>
                            @else
                                <span class="workspace-field-hint">No README evidence was available.</span>
                            @endif
                        </article>
                    @empty
                        <p class="workspace-empty-copy">No repositories have been selected yet.</p>
                    @endforelse
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Experience</h2><span>YOU PROVIDE</span></div>
                    <label class="workspace-label" for="experience_text">One verified role or responsibility per line</label>
                    <textarea id="experience_text" name="experience_text" rows="4" class="workspace-input">{{ old('experience_text', implode("\n", $profile?->experience ?? [])) }}</textarea>
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Education</h2><span>YOU PROVIDE</span></div>
                    <label class="workspace-label" for="education_text">One verified education item per line</label>
                    <textarea id="education_text" name="education_text" rows="3" class="workspace-input">{{ old('education_text', implode("\n", $profile?->education ?? [])) }}</textarea>
                </section>
                <section class="workspace-panel">
                    <div class="workspace-panel-heading"><h2>Certifications</h2><span>YOU PROVIDE</span></div>
                    <label class="workspace-label" for="certifications_text">One certification per line</label>
                    <textarea id="certifications_text" name="certifications_text" rows="3" class="workspace-input">{{ old('certifications_text', implode("\n", $profile?->certifications ?? [])) }}</textarea>
                </section>
            </form>
            <aside class="career-profile-aside">
                <section class="workspace-panel">
                    <h2>Profile completeness</h2>
                    @php($completeSections = ($profile?->headline ? 1 : 0) + ($profile?->summary ? 1 : 0) + (count($skills) ? 1 : 0) + (count($profile?->experience ?? []) ? 1 : 0) + (count($projects) ? 1 : 0))
                    <div class="analysis-progress-bar"><span style="width: {{ (int) ($completeSections / 5 * 100) }}%"></span></div>
                    <p>{{ $completeSections }} of 5 sections have content</p>
                </section>
                <section class="workspace-panel">
                    <h2>Not found in GitHub</h2>
                    <p>We do not guess these. Add your own verified details if you want them in your CV.</p>
                    @foreach (['Education' => 'education_text', 'Work experience' => 'experience_text', 'Certifications' => 'certifications_text'] as $label => $field)
                        <a class="career-add-link" href="#{{ $field }}"><span>{{ $label }}</span><span>Add ↓</span></a>
                    @endforeach
                </section>
                <a href="{{ route('cv.index') }}" class="workspace-button workspace-button-full">Continue to CV →</a>
            </aside>
        </div>
    </div>
</x-app-layout>
