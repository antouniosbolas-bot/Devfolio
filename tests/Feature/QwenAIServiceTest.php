<?php

namespace Tests\Feature;

use App\Services\AI\QwenAIService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class QwenAIServiceTest extends TestCase
{
    public function test_qwen_service_exposes_project_bio_skills_and_cv_generators(): void
    {
        config([
            'services.qwen.api_key' => 'test-key',
            'services.qwen.base_url' => 'https://qwen.example.test/v1/chat/completions',
            'services.qwen.model' => 'qwen-test-model',
        ]);
        Http::fake([
            '*' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => 'A concise project description.']]]])
                ->push(['choices' => [['message' => ['content' => '{"bullet_points":["Built a PHP application."]}']]]])
                ->push(['choices' => [['message' => ['content' => 'A professional developer bio.']]]])
                ->push(['choices' => [['message' => ['content' => 'A summary of verified skills.']]]])
                ->push(['choices' => [['message' => ['content' => 'A concise CV summary.']]]]),
        ]);
        $service = app(QwenAIService::class);
        $project = ['name' => 'Portfolio app', 'language' => 'PHP', 'description' => 'A PHP application.'];
        $profile = ['name' => 'Alex', 'skills' => ['PHP', 'Laravel']];

        $this->assertSame('A concise project description.', $service->generateProjectDescription($project));
        $this->assertSame(['Built a PHP application.'], $service->generateProjectBulletPoints($project));
        $this->assertSame('A professional developer bio.', $service->generateProfessionalBio($profile));
        $this->assertSame('A summary of verified skills.', $service->generateSkillsSummary($profile['skills']));
        $this->assertSame('A concise CV summary.', $service->generateCVSummary($profile));
        Http::assertSentCount(5);
    }

    public function test_qwen_service_fails_explicitly_when_credentials_are_missing(): void
    {
        config(['services.qwen.api_key' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Qwen is not configured.');

        app(QwenAIService::class)->generateProfessionalBio(['name' => 'Alex']);
    }
}
