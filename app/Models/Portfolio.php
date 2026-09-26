<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = ['subdomain', 'template', 'theme', 'is_published', 'ai_status', 'ai_error', 'ai_generated_at'];

    protected function casts(): array
    {
        return ['theme' => 'array', 'is_published' => 'boolean', 'ai_generated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function selectedProjects()
    {
        $ids = $this->theme['project_ids'] ?? [];

        return $this->user->projects()
            ->where('is_private', false)
            ->whereIn('id', $ids);
    }

    public function displayHeroTitle(): string
    {
        return data_get($this->theme, 'hero_title') ?: data_get($this->theme, 'generated_hero_title') ?: $this->user->name;
    }

    public function displayHeroBio(): string
    {
        return data_get($this->theme, 'hero_bio') ?: data_get($this->theme, 'generated_hero_bio') ?: ($this->user->bio ?: 'Welcome to my portfolio.');
    }

    public function projectDescription(int $projectId, ?string $fallback = null): string
    {
        return data_get($this->theme, "project_descriptions.$projectId") ?: data_get($this->theme, "generated_project_descriptions.$projectId") ?: ($fallback ?: 'A project I enjoyed building.');
    }
}
