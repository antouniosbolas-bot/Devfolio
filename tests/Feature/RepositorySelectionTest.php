<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositorySelectionTest extends TestCase
{
    use RefreshDatabase;

    private function project(User $user, array $overrides = []): Project
    {
        return $user->projects()->create(array_merge([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'portfolio-app',
            'full_name' => 'octocat/portfolio-app',
            'description' => 'A public repository.',
            'html_url' => 'https://github.com/octocat/portfolio-app',
        ], $overrides));
    }

    public function test_user_can_select_public_repositories_and_fork_evidence_is_not_used(): void
    {
        $user = User::factory()->create();
        $project = $this->project($user);

        $this->actingAs($user)->put(route('repositories.selection'), [
            'visible_project_ids' => [$project->id],
            'selected_project_ids' => [$project->id],
            'category' => [$project->id => 'academic'],
            'featured_project_ids' => [$project->id],
        ])->assertRedirect(route('repositories.index'));

        $project->refresh();
        $this->assertTrue($project->selected_for_profile);
        $this->assertTrue($project->is_featured);
        $this->assertSame('academic', $project->category);
    }

    public function test_user_cannot_select_another_users_repository(): void
    {
        $user = User::factory()->create();
        $otherProject = $this->project(User::factory()->create());

        $this->actingAs($user)->put(route('repositories.selection'), [
            'visible_project_ids' => [$otherProject->id],
            'selected_project_ids' => [$otherProject->id],
        ])->assertSessionHasErrors(['visible_project_ids.0', 'selected_project_ids.0']);
    }

    public function test_saving_a_page_does_not_clear_selections_from_other_pages(): void
    {
        $user = User::factory()->create();
        $visibleProject = $this->project($user, ['name' => 'visible']);
        $offPageProject = $this->project($user, [
            'name' => 'off-page',
            'selected_for_profile' => true,
            'is_featured' => true,
        ]);

        $this->actingAs($user)->put(route('repositories.selection'), [
            'visible_project_ids' => [$visibleProject->id],
            'selected_project_ids' => [$visibleProject->id],
        ])->assertRedirect();

        $this->assertTrue($visibleProject->fresh()->selected_for_profile);
        $this->assertTrue($offPageProject->fresh()->selected_for_profile);
        $this->assertTrue($offPageProject->fresh()->is_featured);
    }

    public function test_repository_page_hides_private_repositories(): void
    {
        $user = User::factory()->create();
        $this->project($user, ['name' => 'Public project']);
        $this->project($user, [
            'name' => 'Private project',
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'full_name' => 'octocat/private-project',
            'is_private' => true,
        ]);

        $this->actingAs($user)->get(route('repositories.index'))
            ->assertOk()
            ->assertSee('Public project')
            ->assertDontSee('Private project');
    }
}
