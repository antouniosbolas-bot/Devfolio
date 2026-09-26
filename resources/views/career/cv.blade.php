<x-app-layout>
    <x-slot name="header"><h1 class="workspace-page-title">CV builder</h1></x-slot>
    @php($selectedCvProjectCount = $profile?->cv_project_ids === null ? $projects->count() : $projects->whereIn('id', $profile->cv_project_ids)->count())
    <div class="workspace-page">
        @if (session('status'))
            <div role="status" class="workspace-alert">{{ session('status') }}</div>
        @endif
        @if (session('share_url'))
            <div role="status" class="workspace-alert">Public CV link created: <a href="{{ session('share_url') }}" class="workspace-alert-link">{{ session('share_url') }}</a></div>
        @endif
        @if ($errors->any())
            <div role="alert" class="workspace-alert is-error">{{ $errors->first() }}</div>
        @endif
        <div class="cv-editor-heading">
            <div>
                <p class="design-eyebrow">YOUR CAREER, CLEARLY PRESENTED</p>
                <h2>Build your CV</h2>
                <p class="workspace-page-description">Choose a layout and projects, then review your A4 CV before downloading.</p>
            </div>
            @if ($profile?->analysis_status === 'completed')
                <div class="workspace-actions">
                    @if ($profile->shared_token)
                        <button type="button" class="workspace-button workspace-button-muted" data-copy-url="{{ route('cv.shared', $profile->shared_token) }}">Copy share link</button>
                    @endif
                    <a href="{{ route('cv.download') }}" class="workspace-button">↓ Download PDF</a>
                </div>
            @endif
        </div>
        @if (! $profile || $profile->analysis_status !== 'completed')
            <section class="workspace-panel workspace-empty">
                <h2>Build your evidence-based career profile first</h2>
                <p>Select public repositories, analyse their GitHub data, and review your profile before creating a CV.</p>
                <a href="{{ route('analysis.index') }}" class="workspace-button">Go to analysis →</a>
            </section>
        @else
            <div class="cv-builder-layout">
                <aside class="cv-editor-sidebar">
                    <form method="POST" action="{{ route('cv.update') }}" class="workspace-panel cv-controls" data-cv-controls>
                        @csrf
                        @method('PUT')
                        <div class="cv-controls-heading">
                            <span class="cv-editor-step">01</span>
                            <div>
                                <h3>Personalise your CV</h3>
                                <p>Changes appear in the preview as you edit.</p>
                            </div>
                        </div>

                        <div>
                            <label class="workspace-label" for="target_role">Target role</label>
                            <input id="target_role" name="target_role" class="workspace-input" maxlength="120" value="{{ old('target_role', $profile->target_role) }}" placeholder="For example, Backend Developer" data-cv-target-role>
                            <span class="workspace-field-hint">A heading for this CV, not a claim about past employment.</span>
                        </div>

                        <fieldset class="cv-control-group">
                            <legend class="workspace-label">Language</legend>
                            <div class="cv-choice-grid">
                                <label><input type="radio" name="cv_language" value="en" @checked(old('cv_language', $profile->cv_language) === 'en') data-cv-language><span>English</span></label>
                                <label><input type="radio" name="cv_language" value="ar" @checked(old('cv_language', $profile->cv_language) === 'ar') data-cv-language><span>العربية</span></label>
                            </div>
                        </fieldset>

                        <fieldset class="cv-control-group">
                            <legend class="workspace-label">Choose a template</legend>
                            <div class="cv-template-grid">
                                @foreach (['classic' => 'Classic', 'two-column' => 'Two-column', 'ats' => 'ATS-friendly'] as $value => $label)
                                    <label class="cv-template-choice">
                                        <input type="radio" name="cv_template" value="{{ $value }}" @checked(old('cv_template', $profile->cv_template) === $value) data-cv-template>
                                        <span class="cv-template-thumb cv-template-thumb-{{ $value }}" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="cv-control-group">
                            <legend class="workspace-label">Projects <span class="cv-project-count" data-cv-project-count>{{ $selectedCvProjectCount }} selected</span></legend>
                            @forelse ($profile->selectedProjects()->get() as $project)
                                <label class="cv-project-choice">
                                    <input type="checkbox" name="cv_project_ids[]" value="{{ $project->id }}" @checked($profile->cv_project_ids === null || in_array($project->id, $profile->cv_project_ids, true)) data-cv-project-toggle="{{ $project->id }}">
                                    <span>{{ $project->name }}</span>
                                </label>
                            @empty
                                <p class="workspace-field-hint">No repositories selected for this career profile.</p>
                            @endforelse
                            <span class="workspace-field-hint">Only projects you selected for your career profile can be included.</span>
                        </fieldset>

                        <div class="cv-save-actions">
                            <button type="submit" class="workspace-button workspace-button-full">Save CV settings</button>
                            <span class="workspace-field-hint">Save to apply your choices to the downloaded PDF and share link.</span>
                            <a href="{{ route('career-profile.index') }}" class="cv-edit-profile-link">Edit profile content <span>↗</span></a>
                        </div>
                    </form>

                    <section class="workspace-panel cv-share-panel">
                        <div class="cv-controls-heading">
                            <span class="cv-editor-step">02</span>
                            <div>
                                <h3>Share your CV</h3>
                                <p>A private link you can disable anytime.</p>
                            </div>
                        </div>
                        @if (! $profile->shared_token)
                            <form method="POST" action="{{ route('cv.share') }}">@csrf<button type="submit" class="workspace-button workspace-button-muted workspace-button-full">Create share link</button></form>
                        @else
                            <form method="POST" action="{{ route('cv.share.revoke') }}">@csrf @method('DELETE')<button type="submit" class="workspace-text-button">Disable public CV link</button></form>
                        @endif
                    </section>
                </aside>

                <section class="workspace-panel cv-preview-panel" aria-label="CV preview">
                    <div class="cv-preview-toolbar">
                        <div>
                            <p class="cv-preview-title">Live preview</p>
                            <span class="cv-preview-subtitle">A4 · <span data-cv-preview-language>{{ $profile->cv_language === 'ar' ? 'العربية' : 'English' }}</span></span>
                        </div>
                        <span class="cv-preview-source"><i class="analysis-step-dot"></i> Based on your saved profile</span>
                    </div>
                    <div class="cv-preview-stage">
                        @include('career.cv-content', ['isDocument' => false])
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>
