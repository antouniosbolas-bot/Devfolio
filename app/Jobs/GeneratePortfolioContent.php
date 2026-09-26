<?php

namespace App\Jobs;

use App\Models\Portfolio;
use App\Services\PortfolioAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePortfolioContent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $portfolioId, public bool $force = false) {}

    public function handle(PortfolioAiService $service): void
    {
        $portfolio = Portfolio::with('user')->find($this->portfolioId);
        if (! $portfolio) {
            return;
        }
        $portfolio->update(['ai_status' => 'processing', 'ai_error' => null]);
        try {
            $generated = $service->generate($portfolio);
            $theme = $portfolio->theme ?: [];
            if ($this->force || blank($theme['hero_title'] ?? null)) {
                $theme['generated_hero_title'] = trim($generated['hero_title']);
            }
            if ($this->force || blank($theme['hero_bio'] ?? null)) {
                $theme['generated_hero_bio'] = trim($generated['hero_bio']);
            }
            $descriptions = $theme['generated_project_descriptions'] ?? [];
            foreach ($generated['projects'] as $project) {
                if ($this->force || blank(data_get($theme, "project_descriptions.{$project['id']}"))) {
                    $descriptions[$project['id']] = $project['description'];
                }
            }
            $theme['generated_project_descriptions'] = $descriptions;
            $portfolio->update(['theme' => $theme, 'ai_status' => 'completed', 'ai_generated_at' => now(), 'ai_error' => null]);
        } catch (\Throwable $exception) {
            $service->logFailure($portfolio, $exception);
            $portfolio->update([
                'ai_status' => 'failed',
                'ai_error' => "We couldn't generate your content right now. Please try again in a moment.",
            ]);
        }
    }
}
