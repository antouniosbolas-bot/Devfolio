<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CareerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function project(User $user): Project
    {
        return $user->projects()->create([
            'github_id' => fake()->unique()->numberBetween(1, 999999),
            'name' => 'portfolio-app',
            'full_name' => 'octocat/portfolio-app',
            'description' => 'A verified PHP application.',
            'html_url' => 'https://github.com/octocat/portfolio-app',
            'language' => 'PHP',
            'selected_for_profile' => true,
        ]);
    }

    public function test_analysis_saves_public_github_evidence_and_redacts_common_secrets(): void
    {
        $user = User::factory()->create([
            'github_token' => 'github-token',
            'github_username' => 'octocat',
            'bio' => 'Developer profile provided by GitHub.',
        ]);
        $project = $this->project($user);
        $project->languages()->create(['language' => 'PHP', 'bytes' => 1200]);
        Http::fake([
            'https://api.github.com/repos/octocat/portfolio-app/readme' => Http::response([
                'content' => base64_encode("# Portfolio\nStack: PHP\nAPI_KEY=super-secret-token-value"),
                'encoding' => 'base64',
            ]),
        ]);

        $this->actingAs($user)->post(route('analysis.run'))->assertRedirect(route('career-profile.index'));

        $profile = $user->careerProfile()->firstOrFail();
        $this->assertSame('completed', $profile->analysis_status);
        $this->assertSame('Developer profile provided by GitHub.', $profile->headline);
        $this->assertStringContainsString('[REDACTED]', $project->fresh()->readme_excerpt);
        $this->assertStringNotContainsString('super-secret-token-value', $project->fresh()->readme_excerpt);
        $this->assertSame(['PHP' => 1], $profile->skills());
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && ! str_contains($request->url(), 'github-token'));
    }

    public function test_forked_repository_is_not_used_as_career_evidence(): void
    {
        $user = User::factory()->create(['github_token' => 'github-token']);
        $project = $this->project($user);
        $project->update(['is_fork' => true]);

        Http::fake();
        $this->actingAs($user)->post(route('analysis.run'))->assertRedirect(route('repositories.index'));

        $this->assertDatabaseMissing('career_profiles', ['user_id' => $user->id]);
        Http::assertNothingSent();
    }

    public function test_user_can_edit_profile_and_add_only_user_provided_facts(): void
    {
        $user = User::factory()->create();
        $user->careerProfile()->create([
            'analysis_status' => 'completed',
            'headline' => 'PHP',
            'summary' => 'Repository evidence summary.',
        ]);

        $this->actingAs($user)->put(route('career-profile.update'), [
            'headline' => 'Backend developer',
            'summary' => 'My verified experience.',
            'experience_text' => "Role I provided\nSecond verified role",
            'education_text' => 'Education I provided',
            'certifications_text' => '',
        ])->assertRedirect(route('career-profile.index'));

        $profile = $user->careerProfile()->firstOrFail();
        $this->assertSame(['Role I provided', 'Second verified role'], $profile->experience);
        $this->assertSame(['Education I provided'], $profile->education);
        $this->assertSame([], $profile->certifications);
    }
}
