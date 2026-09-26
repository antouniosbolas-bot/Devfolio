<?php

namespace Tests\Feature;

use App\Jobs\SyncGithubProjects;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GithubIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_dispatch_a_sync(): void
    {
        Queue::fake();
        $user = User::factory()->create(['github_token' => 'token']);

        $response = $this->actingAs($user)->postJson(route('github.sync'));

        $response->assertAccepted()->assertJson(['status' => 'pending']);
        Queue::assertPushed(SyncGithubProjects::class, fn (SyncGithubProjects $job) => $job->user->is($user));
        $this->assertDatabaseHas('github_syncs', ['user_id' => $user->id, 'status' => 'pending']);
    }

    public function test_user_without_github_connection_is_redirected_to_oauth(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('github.sync'))
            ->assertRedirect(route('auth.github.redirect'));
    }

    public function test_github_token_is_not_included_in_user_serialization(): void
    {
        $user = User::factory()->create(['github_token' => 'private-token']);

        $this->assertArrayNotHasKey('github_token', $user->toArray());
    }

    public function test_manual_sync_does_not_enqueue_a_duplicate_active_sync(): void
    {
        Queue::fake();
        $user = User::factory()->create(['github_token' => 'token']);
        $active = $user->githubSyncs()->create(['status' => 'running']);

        $response = $this->actingAs($user)->postJson(route('github.sync'));

        $response->assertAccepted()->assertJson(['sync_id' => $active->id, 'status' => 'running']);
        Queue::assertNothingPushed();
    }

    public function test_sync_paginates_repositories_and_persists_languages(): void
    {
        $user = User::factory()->create(['github_token' => 'token', 'github_username' => 'octocat']);
        Http::fake([
            'https://api.github.com/user/repos*' => Http::sequence()
                ->push([['id' => 42, 'name' => 'portfolio', 'full_name' => 'octocat/portfolio', 'html_url' => 'https://github.com/octocat/portfolio', 'language' => 'PHP', 'stargazers_count' => 2, 'forks_count' => 1, 'private' => false, 'pushed_at' => null]])
                ->push([]),
            'https://api.github.com/repos/octocat/portfolio/languages' => Http::response(['PHP' => 1200, 'Blade' => 300]),
        ]);

        SyncGithubProjects::dispatchSync($user);

        $this->assertDatabaseHas('projects', ['user_id' => $user->id, 'github_id' => 42, 'name' => 'portfolio']);
        $this->assertDatabaseHas('project_languages', ['language' => 'PHP', 'bytes' => 1200]);
        $this->assertDatabaseHas('github_syncs', ['user_id' => $user->id, 'status' => 'completed', 'projects_synced' => 1]);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer token'));
    }
}
