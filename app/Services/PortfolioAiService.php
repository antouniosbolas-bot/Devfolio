<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Services\AI\QwenAIService;
use Illuminate\Support\Facades\Log;

class PortfolioAiService
{
    public function __construct(private QwenAIService $qwen) {}

    public function generate(Portfolio $portfolio): array
    {
        $user = $portfolio->user;
        $projects = $portfolio->selectedProjects()->get(['id', 'name', 'description', 'language', 'html_url'])->map(fn ($project) => [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'language' => $project->language,
            'url' => $project->html_url,
        ])->values()->all();

        return $this->qwen->generatePortfolioContent([
            'profile' => ['name' => $user->name, 'bio' => $user->bio, 'github_username' => $user->github_username],
            'template' => $portfolio->template,
            'projects' => $projects,
        ]);
    }

    public function logFailure(Portfolio $portfolio, \Throwable $exception): void
    {
        Log::error('Portfolio AI generation failed.', [
            'portfolio_id' => $portfolio->id,
            'exception' => $exception,
        ]);
    }
}
