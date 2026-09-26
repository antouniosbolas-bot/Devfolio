<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DevFolio - Your GitHub, presented professionally</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="devfolio-landing">
    <header class="landing-header">
        <a href="{{ url('/') }}" class="workspace-brand"><span class="workspace-brand-icon">&lt;/&gt;</span><span>Dev<span>Folio</span></span></a>
        <nav aria-label="Main navigation">
            <a href="#how-it-works">How it works</a>
            <a href="#evidence">Evidence</a>
            <a href="{{ route('github.connect') }}">Privacy</a>
        </nav>
        <div class="landing-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-login">Workspace</a>
            @else
                <a href="{{ route('login') }}" class="landing-login">Log in</a>
                <a href="{{ route('register') }}" class="workspace-button workspace-button-muted">Get started</a>
            @endauth
            <button class="theme-toggle landing-theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode">
                <span data-theme-icon>☾</span><span data-theme-label>Dark mode</span>
            </button>
        </div>
    </header>

    <main class="landing-main">
        <section class="landing-hero">
            <div class="landing-hero-copy">
                <a href="#evidence" class="landing-kicker">Built on evidence from your repositories <span>›</span></a>
                <h1>Turn your GitHub into your career profile.</h1>
                <p>Connect GitHub, pick the projects that matter, and build a CV and portfolio from what you actually made. Nothing invented.</p>
                <div class="landing-hero-actions">
                    <a href="{{ route('github.connect') }}" class="workspace-button">◉ &nbsp; Continue with GitHub</a>
                    <a href="#how-it-works" class="landing-text-link">How it works <span>›</span></a>
                </div>
            </div>
            <div class="landing-code-window" aria-label="Example of evidence-based career profile data">
                <div class="landing-code-top"><span class="analysis-window-dots"><i></i><i></i><i></i></span><code>career_profile.json</code></div>
                <pre><span class="code-muted">{</span>
  <span class="code-key">"headline"</span>: <span class="code-string">"From your profile"</span>,
  <span class="code-key">"skills"</span>: <span class="code-muted">[</span><span class="code-string">"GitHub languages"</span><span class="code-muted">]</span>,
  <span class="code-key">"projects"</span>: <span class="code-muted">[</span>
    <span class="code-muted">{</span>
      <span class="code-key">"source"</span>: <span class="code-string">"selected repository"</span>,
      <span class="code-key">"evidence"</span>: <span class="code-string">"README + metadata"</span>
    <span class="code-muted">}</span>
  <span class="code-muted">]</span>,
  <span class="code-key">"experience"</span>: <span class="code-muted">[]</span>
  <span class="code-comment">// add your own verified details
}</span></pre>
                <div class="landing-code-status"><span><i class="analysis-step-dot"></i> Source-linked</span><span>Only selected repositories</span></div>
            </div>
        </section>

        <section id="how-it-works" class="landing-section">
            <p class="design-eyebrow">HOW IT WORKS</p>
            <h2>From repositories to a CV in three steps.</h2>
            <div class="landing-steps">
                @foreach ([
                    ['01 / connect', 'Sign in with GitHub', 'Read-only access to your public profile and public repositories. You choose what to include.'],
                    ['02 / analyse', 'Extract the evidence', 'Repository descriptions, languages, and README excerpts become a profile linked to their source.'],
                    ['03 / generate', 'CV and portfolio', 'Review and edit your career profile, download a CV, or publish a portfolio link.'],
                ] as [$eyebrow, $title, $description])
                    <article class="landing-step-card"><p class="design-eyebrow">{{ $eyebrow }}</p><h3>{{ $title }}</h3><p>{{ $description }}</p></article>
                @endforeach
            </div>
        </section>

        <section id="evidence" class="landing-evidence">
            <div>
                <p class="design-eyebrow">EVIDENCE, NOT INVENTION</p>
                <h2>Every line on your CV points back to your work.</h2>
                <p>No invented employers, degrees, achievements, or years of experience. If GitHub does not show it, add it yourself only if it is true.</p>
            </div>
            <div class="landing-evidence-card">
                <article><strong>Repository information</strong><span>Project description · GitHub</span></article>
                <article><strong>Skills</strong><span>Language statistics · selected repositories</span></article>
                <article><strong>Work experience</strong><span class="evidence-not-found">Not found in GitHub · add it yourself</span></article>
            </div>
        </section>

        <section class="landing-privacy">
            <div><h2>Your tokens never leave our server.</h2><p>GitHub access tokens are encrypted at rest. Your access token is not sent to AI providers. Only the repositories you select are used.</p></div>
            <a href="{{ route('github.connect') }}" class="workspace-button workspace-button-muted">Get started</a>
        </section>
    </main>
    <x-site-footer />
</body>
</html>
