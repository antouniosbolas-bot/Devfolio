<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvTest extends TestCase
{
    use RefreshDatabase;

    private function profileWithProject(User $user): Project
    {
        $project = $user->projects()->create([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'verified-app',
            'full_name' => 'octocat/verified-app',
            'description' => 'Built a tested app.',
            'html_url' => 'https://github.com/octocat/verified-app',
            'language' => 'PHP',
            'selected_for_profile' => true,
        ]);
        $project->languages()->create(['language' => 'PHP', 'bytes' => 500]);
        $user->careerProfile()->create([
            'headline' => 'PHP developer',
            'summary' => 'A user-edited summary.',
            'analysis_status' => 'completed',
            'cv_project_ids' => [$project->id],
        ]);

        return $project;
    }

    public function test_cv_preview_and_pdf_download_use_only_saved_profile_data(): void
    {
        $user = User::factory()->create(['github_username' => 'octocat']);
        $this->profileWithProject($user);

        $this->actingAs($user)->get(route('cv.index'))
            ->assertOk()
            ->assertSee('A user-edited summary.')
            ->assertSee('verified-app');

        $this->actingAs($user)->get(route('cv.download'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_cv_builder_can_preview_projects_not_yet_selected_for_the_cv(): void
    {
        $user = User::factory()->create();
        $this->profileWithProject($user);
        $project = $user->projects()->create([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'optional-project',
            'full_name' => 'octocat/optional-project',
            'html_url' => 'https://github.com/octocat/optional-project',
            'selected_for_profile' => true,
        ]);

        $response = $this->actingAs($user)->get(route('cv.index'));

        $response
            ->assertOk()
            ->assertSee('optional-project');
        $this->assertMatchesRegularExpression('/data-cv-project="'.$project->id.'"\s+hidden/', $response->getContent());
    }

    public function test_cv_share_link_can_be_created_opened_and_revoked(): void
    {
        $user = User::factory()->create();
        $project = $this->profileWithProject($user);

        $this->actingAs($user)->post(route('cv.share'))->assertRedirect();
        $token = $user->careerProfile()->firstOrFail()->shared_token;
        $this->assertNotEmpty($token);

        $this->get(route('cv.shared', $token))
            ->assertOk()
            ->assertSee('A user-edited summary.')
            ->assertSee($project->name);

        $this->actingAs($user)->delete(route('cv.share.revoke'))->assertRedirect();
        $this->get(route('cv.shared', $token))->assertNotFound();
    }

    public function test_cv_project_selection_rejects_another_users_project(): void
    {
        $user = User::factory()->create();
        $this->profileWithProject($user);
        $otherUser = User::factory()->create();
        $otherProject = $otherUser->projects()->create([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'not-yours',
            'full_name' => 'somebody/not-yours',
            'html_url' => 'https://github.com/somebody/not-yours',
            'selected_for_profile' => true,
        ]);

        $this->actingAs($user)->put(route('cv.update'), [
            'cv_language' => 'en',
            'cv_template' => 'classic',
            'cv_project_ids' => [$otherProject->id],
        ])->assertSessionHasErrors('cv_project_ids.0');
    }
}
