<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_a_github_connect_action_for_email_users(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Connect GitHub')
            ->assertSee('Recent GitHub projects')
            ->assertSee('devfolio-theme');
    }

    public function test_dashboard_does_not_render_a_users_github_access_token(): void
    {
        $user = User::factory()->create([
            'github_token' => 'private-github-token',
            'github_username' => 'octocat',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Sync GitHub projects')
            ->assertDontSee('private-github-token');
    }
}
