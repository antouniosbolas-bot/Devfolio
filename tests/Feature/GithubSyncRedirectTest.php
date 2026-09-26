<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GithubSyncRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconnected_web_sync_requests_start_github_oauth(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('github.sync'));

        $response->assertRedirect(route('auth.github.redirect'));
    }
}
