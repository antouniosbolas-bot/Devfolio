<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'github_id', 'name', 'full_name', 'description', 'html_url', 'clone_url',
        'default_branch', 'language', 'stars', 'forks', 'is_private', 'pushed_at', 'synced_at',
        'selected_for_profile', 'is_archived', 'is_fork', 'is_featured', 'category', 'readme_excerpt',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'selected_for_profile' => 'boolean',
            'is_archived' => 'boolean',
            'is_fork' => 'boolean',
            'is_featured' => 'boolean',
            'pushed_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(ProjectLanguage::class);
    }
}
