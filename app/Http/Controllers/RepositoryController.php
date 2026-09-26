<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RepositoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->projects()->where('is_private', false);

        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('language', 'like', "%{$search}%");
            });
        }

        match ($request->query('filter')) {
            'forks' => $query->where('is_fork', true),
            'archived' => $query->where('is_archived', true),
            'owned' => $query->where('is_fork', false)->where('is_archived', false),
            default => null,
        };

        $projects = $query->with('languages')->latest('pushed_at')->paginate(20)->withQueryString();
        $user = $request->user();

        return view('repositories.index', [
            'projects' => $projects,
            'publicProjectCount' => $user->projects()->where('is_private', false)->count(),
            'selectedCount' => $user->projects()->where('is_private', false)->where('selected_for_profile', true)->count(),
            'languageCount' => $user->projects()->where('is_private', false)->distinct()->count('language'),
            'profile' => $user->careerProfile,
            'filters' => ['q' => $request->query('q'), 'filter' => $request->query('filter', 'owned')],
        ]);
    }

    public function updateSelection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'selected_project_ids' => ['nullable', 'array', 'max:100'],
            'selected_project_ids.*' => [
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('is_private', false)),
            ],
            'visible_project_ids' => ['required', 'array', 'max:100'],
            'visible_project_ids.*' => [
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('is_private', false)),
            ],
            'category' => ['nullable', 'array'],
            'category.*' => ['required', Rule::in(['personal', 'academic', 'open-source'])],
            'featured_project_ids' => ['nullable', 'array'],
            'featured_project_ids.*' => [
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('is_private', false)),
            ],
        ]);

        $user = $request->user();
        $selected = array_map('intval', $data['selected_project_ids'] ?? []);
        $visible = array_map('intval', $data['visible_project_ids']);
        $featured = array_map('intval', $data['featured_project_ids'] ?? []);
        $categories = $data['category'] ?? [];

        $user->projects()->where('is_private', false)->whereIn('id', $visible)->get()->each(function ($project) use ($selected, $featured, $categories) {
            $project->update([
                'selected_for_profile' => in_array($project->id, $selected, true),
                'is_featured' => in_array($project->id, $selected, true) && in_array($project->id, $featured, true),
                'category' => $categories[$project->id] ?? $project->category,
            ]);
        });

        return redirect()->route('repositories.index')->with('status', count($selected).' repositories selected for your career profile.');
    }
}
