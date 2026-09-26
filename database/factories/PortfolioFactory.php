<?php

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subdomain' => fake()->unique()->slug(2),
            'template' => 'developer',
            'theme' => ['hero_title' => 'Hello', 'hero_bio' => 'Welcome', 'project_ids' => []],
            'is_published' => false,
        ];
    }
}
