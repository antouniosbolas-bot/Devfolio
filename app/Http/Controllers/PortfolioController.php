<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePortfolioContent;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolio = Auth::user()->portfolios()->latest()->first();

        return view('portfolio.index', compact('portfolio'));
    }

    public function create()
    {
        return view('portfolio.form', ['portfolio' => new Portfolio, 'projects' => Auth::user()->projects()->where('is_private', false)->latest('pushed_at')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['theme'] = $this->theme($request);
        $portfolio = $request->user()->portfolios()->create($data);
        GeneratePortfolioContent::dispatch($portfolio->id)->afterCommit();

        return redirect()->route('portfolio.edit', $portfolio)->with('status', 'Portfolio saved.');
    }

    public function edit(Portfolio $portfolio)
    {
        $this->owned($portfolio);

        return view('portfolio.form', ['portfolio' => $portfolio, 'projects' => Auth::user()->projects()->where('is_private', false)->latest('pushed_at')->get()]);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $this->owned($portfolio);
        $portfolio->update(array_merge($this->validated($request), ['theme' => $this->theme($request, $portfolio)]));

        return redirect()->route('portfolio.edit', $portfolio)->with('status', 'Portfolio updated.');
    }

    public function preview(Portfolio $portfolio)
    {
        $this->owned($portfolio);

        return view('portfolio.public', ['portfolio' => $portfolio, 'preview' => true]);
    }

    public function publish(Portfolio $portfolio)
    {
        $this->owned($portfolio);
        $portfolio->update(['is_published' => true]);

        return back()->with('status', 'Your portfolio is now live.');
    }

    public function unpublish(Portfolio $portfolio)
    {
        $this->owned($portfolio);
        $portfolio->update(['is_published' => false]);

        return back()->with('status', 'Your portfolio is unpublished.');
    }

    public function regenerate(Portfolio $portfolio)
    {
        $this->owned($portfolio);
        if (in_array($portfolio->ai_status, ['pending', 'processing'], true)) {
            return back()->with('status', 'AI generation is already in progress.');
        }

        $portfolio->update(['ai_status' => 'pending', 'ai_error' => null]);
        GeneratePortfolioContent::dispatch($portfolio->id, true)->afterCommit();

        return back()->with('status', 'AI generation queued.');
    }

    public function show(string $subdomain)
    {
        $portfolio = Portfolio::where('subdomain', $subdomain)->where('is_published', true)->firstOrFail();

        return view('portfolio.public', ['portfolio' => $portfolio, 'preview' => false]);
    }

    private function owned(Portfolio $portfolio): void
    {
        abort_unless($portfolio->user_id === Auth::id(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'subdomain' => ['required', 'string', 'alpha_dash', 'min:3', 'max:40', Rule::unique('portfolios', 'subdomain')->ignore($request->route('portfolio'))],
            'template' => ['required', Rule::in(['developer', 'freelancer', 'open-source'])],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', Rule::exists('projects', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())->where('is_private', false))],
            'project_descriptions' => ['nullable', 'array'],
            'project_descriptions.*' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function theme(Request $request, ?Portfolio $portfolio = null): array
    {
        $theme = $portfolio?->theme ?: [];

        return array_merge($theme, [
            'hero_title' => trim($request->input('hero_title', '')),
            'hero_bio' => trim($request->input('hero_bio', '')),
            'project_ids' => array_map('intval', $request->input('project_ids', [])),
            'project_descriptions' => collect($request->input('project_descriptions', []))->map(fn ($description) => trim($description))->all(),
        ]);
    }
}
