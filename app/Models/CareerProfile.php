<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerProfile extends Model
{
    protected $fillable = [
        'headline',
        'summary',
        'experience',
        'education',
        'certifications',
        'cv_project_ids',
        'analysis_status',
        'analyzed_at',
        'target_role',
        'cv_language',
        'cv_template',
        'shared_token',
    ];

    protected function casts(): array
    {
        return [
            'experience' => 'array',
            'education' => 'array',
            'certifications' => 'array',
            'cv_project_ids' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): array
    {
        return $this->user->projects()
            ->where('is_private', false)
            ->where('selected_for_profile', true)
            ->where('is_fork', false)
            ->with('languages')
            ->get()
            ->flatMap(fn (Project $project) => $project->languages->pluck('language'))
            ->countBy()
            ->sortDesc()
            ->all();
    }

    public function selectedProjects()
    {
        return $this->user->projects()
            ->where('is_private', false)
            ->where('selected_for_profile', true)
            ->where('is_fork', false)
            ->with('languages')
            ->latest('pushed_at');
    }
}
