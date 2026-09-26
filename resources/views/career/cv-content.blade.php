@php($isArabic = $profile->cv_language === 'ar')
<article class="cv-paper cv-template-{{ $profile->cv_template }}" lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}" data-cv-preview>
    <header class="cv-paper-header">
        <h2>{{ $profile->user->name }}</h2>
        <p class="cv-target-role" data-cv-preview-role @if (! $profile->target_role) hidden @endif>{{ $profile->target_role }}</p>
        @if ($profile->headline)<p>{{ $profile->headline }}</p>@endif
        <div class="cv-contact-line">
            <span>{{ $profile->user->email }}</span>
            @if ($profile->user->github_username)<span>github.com/{{ $profile->user->github_username }}</span>@endif
        </div>
    </header>
    @if ($profile->summary)
        <section class="cv-paper-section" data-cv-section="summary"><h3><span data-cv-label="summary" data-label-en="Summary" data-label-ar="الملخص المهني">{{ $isArabic ? 'الملخص المهني' : 'Summary' }}</span></h3><p>{{ $profile->summary }}</p></section>
    @endif
    @if (count($skills))
        <section class="cv-paper-section" data-cv-section="skills"><h3><span data-cv-label="skills" data-label-en="Skills" data-label-ar="المهارات">{{ $isArabic ? 'المهارات' : 'Skills' }}</span></h3><p>{{ collect($skills)->keys()->join(', ') }}</p></section>
    @endif
    @if ($projects->isNotEmpty())
        <section class="cv-paper-section" data-cv-section="projects">
            <h3><span data-cv-label="projects" data-label-en="Projects" data-label-ar="المشاريع">{{ $isArabic ? 'المشاريع' : 'Projects' }}</span></h3>
            @foreach ($projects as $project)
                <article class="cv-project" data-cv-project="{{ $project->id }}" @if ($profile->cv_project_ids !== null && ! in_array($project->id, $profile->cv_project_ids, true)) hidden @endif>
                    <h4>{{ $project->name }} <span>{{ collect($project->languages)->pluck('language')->join(', ') ?: $project->language }}</span></h4>
                    @if ($project->description)<p>{{ $project->description }}</p>@endif
                    <p class="cv-source-line"><a href="{{ $project->html_url }}">{{ $project->full_name }}</a></p>
                </article>
            @endforeach
        </section>
    @endif
    @foreach ([['experience', 'Experience', 'الخبرة'], ['education', 'Education', 'التعليم'], ['certifications', 'Certifications', 'الشهادات']] as [$key, $english, $arabic])
        @if (count($profile->{$key} ?? []))
            <section class="cv-paper-section" data-cv-section="{{ $key }}">
                <h3><span data-cv-label="{{ $key }}" data-label-en="{{ $english }}" data-label-ar="{{ $arabic }}">{{ $isArabic ? $arabic : $english }}</span></h3>
                @foreach ($profile->{$key} as $entry)<p class="cv-user-fact">{{ $entry }}</p>@endforeach
            </section>
        @endif
    @endforeach
</article>
