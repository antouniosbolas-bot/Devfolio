<?php

namespace Tests\Feature;

use App\Jobs\GeneratePortfolioContent;
use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    private function project(User $user): int
    {
        return $user->projects()->create([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'Launch kit',
            'full_name' => 'octocat/launch-kit',
            'description' => 'A useful project.',
            'html_url' => 'https://github.com/octocat/launch-kit',
        ])->id;
    }

    public function test_user_can_create_a_portfolio_with_selected_projects(): void
    {
        $user = User::factory()->create();
        $projectId = $this->project($user);

        $response = $this->actingAs($user)->post(route('portfolio.store'), [
            'subdomain' => 'alex-dev',
            'template' => 'developer',
            'hero_title' => 'I build things',
            'hero_bio' => 'Hello world',
            'project_ids' => [$projectId],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('portfolios', ['user_id' => $user->id, 'subdomain' => 'alex-dev']);
        $this->assertSame([$projectId], Portfolio::first()->theme['project_ids']);
    }

    public function test_user_cannot_edit_another_users_portfolio(): void
    {
        $portfolio = Portfolio::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('portfolio.edit', $portfolio))
            ->assertForbidden();
    }

    public function test_owner_can_publish_a_portfolio(): void
    {
        $user = User::factory()->create();
        $portfolio = $user->portfolios()->create(['subdomain' => 'published', 'template' => 'freelancer']);

        $this->actingAs($user)->post(route('portfolio.publish', $portfolio))->assertRedirect();

        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'is_published' => true]);
    }

    public function test_unpublished_portfolio_is_not_public(): void
    {
        $portfolio = Portfolio::factory()->create(['subdomain' => 'private-draft']);

        $this->get(route('portfolio.public', 'private-draft'))->assertNotFound();
    }

    public function test_public_portfolio_displays_generated_content_when_no_manual_copy_is_set(): void
    {
        $portfolio = Portfolio::factory()->create([
            'subdomain' => 'generated-copy',
            'is_published' => true,
            'theme' => [
                'project_ids' => [],
                'generated_hero_title' => 'Verified project builder',
                'generated_hero_bio' => 'A profile based on imported GitHub work.',
            ],
        ]);

        $this->get(route('portfolio.public', 'generated-copy'))
            ->assertOk()
            ->assertSee('Verified project builder')
            ->assertSee('A profile based on imported GitHub work.');
    }

    public function test_private_github_project_cannot_be_added_to_a_public_portfolio(): void
    {
        $user = User::factory()->create();
        $privateProjectId = $this->project($user);
        $user->projects()->whereKey($privateProjectId)->update(['is_private' => true]);

        $this->actingAs($user)->post(route('portfolio.store'), [
            'subdomain' => 'private-project-page',
            'template' => 'developer',
            'project_ids' => [$privateProjectId],
        ])->assertSessionHasErrors('project_ids.0');
    }

    public function test_ai_generation_uses_qwen_endpoint_and_saves_valid_content(): void
    {
        config([
            'services.qwen.api_key' => 'test-key',
            'services.qwen.base_url' => 'https://qwen.example.test/v1/chat/completions',
            'services.qwen.model' => 'qwen-test-model',
        ]);
        $user = User::factory()->create(['bio' => 'Real user bio']);
        $projectId = $this->project($user);
        Http::fake([
            '*' => Http::response(['choices' => [['message' => ['content' => json_encode([
                'hero_title' => 'Builder of useful things', 'hero_bio' => 'A concise profile.',
                'projects' => [['id' => $projectId, 'description' => 'A polished project.']],
            ])]]]]),
        ]);
        $portfolio = $user->portfolios()->create(['subdomain' => 'ai-content', 'template' => 'developer', 'theme' => ['project_ids' => [$projectId]]]);

        (new GeneratePortfolioContent($portfolio->id))->handle(app(PortfolioAiService::class));

        $portfolio->refresh();
        $this->assertSame('completed', $portfolio->ai_status);
        $this->assertSame('Builder of useful things', $portfolio->theme['generated_hero_title']);
        Http::assertSent(fn ($request) => $request->url() === 'https://qwen.example.test/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && str_contains($request->body(), 'Real user bio')
            && ! str_contains($request->body(), 'test-key'));
    }

    public function test_ai_failure_is_stored_as_a_friendly_message(): void
    {
        config([
            'services.qwen.api_key' => 'test-key',
            'services.qwen.base_url' => 'https://qwen.example.test/v1/chat/completions',
            'services.qwen.model' => 'qwen-test-model',
        ]);
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => 'not json']]]])]);
        $portfolio = Portfolio::factory()->create(['ai_status' => 'pending']);

        (new GeneratePortfolioContent($portfolio->id))->handle(app(PortfolioAiService::class));

        $portfolio->refresh();
        $this->assertSame('failed', $portfolio->ai_status);
        $this->assertSame("We couldn't generate your content right now. Please try again in a moment.", $portfolio->ai_error);
        $this->assertStringNotContainsString('malformed JSON', $portfolio->ai_error);
    }

    public function test_editing_portfolio_does_not_trigger_an_unrequested_ai_generation(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $portfolio = $user->portfolios()->create([
            'subdomain' => 'my-portfolio',
            'template' => 'developer',
            'theme' => ['project_ids' => [], 'hero_title' => 'Original title'],
        ]);

        $this->actingAs($user)->put(route('portfolio.update', $portfolio), [
            'subdomain' => 'my-portfolio',
            'template' => 'developer',
            'hero_title' => 'Updated title',
            'hero_bio' => 'Updated bio',
            'project_ids' => [],
        ])->assertRedirect(route('portfolio.edit', $portfolio));

        Queue::assertNothingPushed();
        $this->assertSame('Updated title', $portfolio->fresh()->theme['hero_title']);
    }

    public function test_portfolio_cannot_be_regenerated_while_generation_is_pending(): void
    {
        Queue::fake();
        $portfolio = Portfolio::factory()->create(['ai_status' => 'pending']);

        $this->actingAs($portfolio->user)
            ->post(route('portfolio.regenerate', $portfolio))
            ->assertSessionHas('status', 'AI generation is already in progress.');

        Queue::assertNothingPushed();
    }
}
