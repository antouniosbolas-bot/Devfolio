<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CvController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $profile = $user->careerProfile;

        return view('career.cv', [
            'profile' => $profile,
            'projects' => $profile
                ? $profile->selectedProjects()->get()
                : collect(),
            'skills' => $profile?->skills() ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $request->user()->careerProfile;
        if (! $profile || $profile->analysis_status !== 'completed') {
            return back()->withErrors(['cv' => 'Analyse repositories and review your career profile before creating a CV.']);
        }

        $data = $request->validate([
            'target_role' => ['nullable', 'string', 'max:120'],
            'cv_language' => ['required', Rule::in(['en', 'ar'])],
            'cv_template' => ['required', Rule::in(['classic', 'two-column', 'ats'])],
            'cv_project_ids' => ['nullable', 'array', 'max:100'],
            'cv_project_ids.*' => [
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('is_private', false)
                    ->where('selected_for_profile', true)),
            ],
        ]);

        $profile->update([
            ...$data,
            'cv_project_ids' => array_map('intval', $data['cv_project_ids'] ?? []),
        ]);

        return redirect()->route('cv.index')->with('status', 'CV settings saved.');
    }

    public function download(Request $request)
    {
        $profile = $request->user()->careerProfile;
        abort_unless($profile && $profile->analysis_status === 'completed', 404);

        $pdf = Pdf::loadView('career.cv-document', [
            'profile' => $profile,
            'projects' => $profile->selectedProjects()
                ->when($profile->cv_project_ids !== null, fn ($query) => $query->whereIn('id', $profile->cv_project_ids))
                ->get(),
            'skills' => $profile->skills(),
            'user' => $request->user(),
        ])->setPaper('a4');

        return $pdf->download('career-profile-cv.pdf');
    }
}
