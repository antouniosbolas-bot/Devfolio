<?php

namespace App\Http\Controllers;

use App\Models\CareerProfile;
use App\Services\GitHub\GitHubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerProfileController extends Controller
{
    public function analysis(Request $request): View
    {
        $profile = $request->user()->careerProfile;

        return view('career.analysis', [
            'profile' => $profile,
            'selectedCount' => $request->user()->projects()->where('selected_for_profile', true)->count(),
            'latestProjects' => $request->user()->projects()->where('selected_for_profile', true)->latest('pushed_at')->take(5)->get(),
        ]);
    }

    public function analyze(Request $request, GitHubService $github): RedirectResponse
    {
        $user = $request->user();
        $projects = $user->projects()
            ->where('is_private', false)
            ->where('is_fork', false)
            ->where('selected_for_profile', true)
            ->with('languages')
            ->get();

        if ($projects->isEmpty()) {
            return redirect()->route('repositories.index')->withErrors([
                'analysis' => 'Select at least one public repository before analysing your career profile.',
            ]);
        }

        try {
            $readmes = [];
            foreach ($projects as $project) {
                $github->clearReadmeCache($user, $project);
                $readmes[$project->id] = $github->readme($user, $project);
            }

            DB::transaction(function () use ($projects, $readmes, $user) {
                foreach ($projects as $project) {
                    $project->update(['readme_excerpt' => $readmes[$project->id] ?? null]);
                }
                $languages = $projects
                    ->flatMap(fn ($project) => $project->languages->pluck('language'))
                    ->unique()
                    ->sort()
                    ->values();
                $existing = $user->careerProfile;
                $evidenceSummary = $languages->isEmpty()
                    ? 'This profile uses descriptions from '.$projects->count().' selected public GitHub repositories. Add personal experience and education yourself if you want them included.'
                    : 'This profile is based on '.$projects->count().' selected public GitHub repositories and the languages recorded by GitHub: '.$languages->join(', ').'. Add personal experience and education yourself if you want them included.';

                $user->careerProfile()->updateOrCreate([], [
                    'headline' => $existing?->headline ?: ($user->bio ?: $languages->take(3)->join(' · ')),
                    'summary' => $existing?->summary ?: $evidenceSummary,
                    'experience' => $existing?->experience ?? [],
                    'education' => $existing?->education ?? [],
                    'certifications' => $existing?->certifications ?? [],
                    'analysis_status' => 'completed',
                    'analyzed_at' => now(),
                    'target_role' => $existing?->target_role,
                    'cv_language' => $existing?->cv_language ?? 'en',
                    'cv_template' => $existing?->cv_template ?? 'classic',
                    'shared_token' => $existing?->shared_token,
                ]);
            });
        } catch (\Throwable $exception) {
            Log::error('Career profile analysis failed.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return back()->withErrors(['analysis' => 'We could not read your GitHub project data. Please check your connection and try again.']);
        }

        return redirect()->route('career-profile.index')->with('status', 'Repository evidence analysed. Review the profile and add any personal details you want included.');
    }

    public function show(Request $request): View
    {
        $profile = $request->user()->careerProfile;

        return view('career.profile', [
            'profile' => $profile,
            'projects' => $profile?->selectedProjects()->get() ?? collect(),
            'skills' => $profile?->skills() ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'experience_text' => ['nullable', 'string', 'max:10000'],
            'education_text' => ['nullable', 'string', 'max:10000'],
            'certifications_text' => ['nullable', 'string', 'max:10000'],
        ]);

        $toLines = fn (?string $text) => collect(preg_split('/\r\n|\r|\n/', $text ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $request->user()->careerProfile()->updateOrCreate([], [
            'headline' => trim($data['headline'] ?? ''),
            'summary' => trim($data['summary'] ?? ''),
            'experience' => $toLines($data['experience_text'] ?? null),
            'education' => $toLines($data['education_text'] ?? null),
            'certifications' => $toLines($data['certifications_text'] ?? null),
            'analysis_status' => $request->user()->careerProfile?->analysis_status ?? 'not_started',
        ]);

        return redirect()->route('career-profile.index')->with('status', 'Career profile saved.');
    }

    public function shared(string $token): View
    {
        $profile = CareerProfile::query()
            ->where('shared_token', $token)
            ->with('user')
            ->firstOrFail();

        return view('career.shared-cv', [
            'profile' => $profile,
            'projects' => $profile->selectedProjects()
                ->when($profile->cv_project_ids !== null, fn ($query) => $query->whereIn('id', $profile->cv_project_ids))
                ->get(),
            'skills' => $profile->skills(),
        ]);
    }

    public function share(Request $request): RedirectResponse
    {
        $profile = $request->user()->careerProfile;
        if (! $profile || $profile->analysis_status !== 'completed') {
            return back()->withErrors(['cv' => 'Analyse repositories and save your career profile before sharing a CV.']);
        }

        $profile->update(['shared_token' => $profile->shared_token ?: Str::random(48)]);

        return back()->with('share_url', route('cv.shared', $profile->shared_token));
    }

    public function revokeShare(Request $request): RedirectResponse
    {
        $request->user()->careerProfile?->update(['shared_token' => null]);

        return back()->with('status', 'Your public CV link has been disabled.');
    }
}
