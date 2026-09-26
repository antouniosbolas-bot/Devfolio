<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

class QwenAIService
{
    public function generatePortfolioContent(array $source): array
    {
        $projects = $source['projects'] ?? [];
        $projectIds = collect($projects)
            ->pluck('id')
            ->filter(fn ($id) => is_int($id))
            ->values()
            ->all();

        $content = $this->complete(
            'You are a professional developer portfolio writer. Use only facts explicitly present in the supplied GitHub profile and repositories. Do not invent technologies, employers, clients, dates, metrics, achievements, education, certifications, users, or results. Improve wording only. Return a JSON object with exactly these fields: hero_title (string), hero_bio (string), projects (array of objects containing only id (integer) and description (string)). Only describe supplied project IDs.',
            $source,
            true,
        );
        $result = $this->decodeJson($content);

        if (
            count(array_diff(array_keys($result), ['hero_title', 'hero_bio', 'projects'])) !== 0
            || count($result) !== 3
            || ! is_string($result['hero_title'] ?? null)
            || blank($result['hero_title'])
            || mb_strlen($result['hero_title']) > 200
            || ! is_string($result['hero_bio'] ?? null)
            || blank($result['hero_bio'])
            || mb_strlen($result['hero_bio']) > 1200
            || ! is_array($result['projects'] ?? null)
        ) {
            throw new RuntimeException('Qwen returned portfolio content with an invalid structure.');
        }

        $generatedIds = [];
        foreach ($result['projects'] as $project) {
            if (
                ! is_array($project)
                || count(array_diff(array_keys($project), ['id', 'description'])) !== 0
                || count($project) !== 2
                || ! is_int($project['id'] ?? null)
                || ! in_array($project['id'], $projectIds, true)
                || in_array($project['id'], $generatedIds, true)
                || ! is_string($project['description'] ?? null)
                || blank($project['description'])
                || mb_strlen($project['description']) > 2000
            ) {
                throw new RuntimeException('Qwen returned an invalid project description.');
            }

            $generatedIds[] = $project['id'];
        }

        return [
            'hero_title' => trim($result['hero_title']),
            'hero_bio' => trim($result['hero_bio']),
            'projects' => array_map(
                fn (array $project) => ['id' => $project['id'], 'description' => trim($project['description'])],
                $result['projects'],
            ),
        ];
    }

    public function generateProjectDescription(array $project): string
    {
        return $this->generateText('Write a concise, accurate description of this software project.', $project);
    }

    public function generateProjectBulletPoints(array $project): array
    {
        $result = $this->decodeJson($this->complete(
            'Write 1 to 5 concise project bullet points using only supplied facts. Do not invent outcomes or metrics. Return JSON exactly as {"bullet_points":["string"]}.',
            $project,
            true,
        ));

        $points = $result['bullet_points'] ?? null;
        if (
            count(array_diff(array_keys($result), ['bullet_points'])) !== 0
            || count($result) !== 1
            || ! is_array($points)
            || count($points) < 1
            || count($points) > 5
        ) {
            throw new RuntimeException('Qwen returned invalid project bullet points.');
        }

        foreach ($points as $point) {
            if (! is_string($point) || blank($point) || mb_strlen($point) > 300) {
                throw new RuntimeException('Qwen returned an invalid project bullet point.');
            }
        }

        return array_map('trim', $points);
    }

    public function generateProfessionalBio(array $profile): string
    {
        return $this->generateText('Write a professional developer bio using only the supplied profile facts.', $profile);
    }

    public function generateSkillsSummary(array $skills): string
    {
        return $this->generateText('Summarize these verified skills without adding skills that are not listed.', $skills);
    }

    public function generateCVSummary(array $profile): string
    {
        return $this->generateText('Write a concise CV summary using only the supplied profile and work facts.', $profile);
    }

    private function generateText(string $instruction, array $source): string
    {
        $content = trim($this->complete(
            "You are a factual developer profile writer. {$instruction} Do not invent employers, clients, technologies, experience duration, metrics, achievements, education, or certifications. Return plain text only.",
            $source,
        ));

        if ($content === '' || mb_strlen($content) > 3000) {
            throw new RuntimeException('Qwen returned empty or oversized text.');
        }

        return $content;
    }

    private function complete(string $systemPrompt, array $source, bool $json = false): string
    {
        $apiKey = (string) config('services.qwen.api_key', '');
        $baseUrl = (string) config('services.qwen.base_url', '');
        $model = (string) config('services.qwen.model', '');

        if ($apiKey === '') {
            throw new RuntimeException('Qwen is not configured. Set QWEN_API_KEY in the application environment.');
        }
        if (! filter_var($baseUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($baseUrl, PHP_URL_SCHEME), ['http', 'https'], true) || $model === '') {
            throw new RuntimeException('Qwen endpoint configuration is invalid.');
        }

        try {
            $sourceJson = json_encode($source, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            Log::warning('Qwen request data could not be encoded.', ['model' => $model]);
            throw new RuntimeException('Qwen request data was invalid.', previous: $exception);
        }

        $payload = [
            'model' => $model,
            'temperature' => 0.4,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $sourceJson],
            ],
        ];
        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(max(1, (int) config('services.qwen.timeout', 30)))
                ->retry(max(1, (int) config('services.qwen.retries', 2) + 1), 250, throw: false)
                ->post($baseUrl, $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Qwen API connection failed.', ['model' => $model]);
            throw new RuntimeException('Qwen API could not be reached.', previous: $exception);
        }

        if ($response->failed()) {
            Log::warning('Qwen API request failed.', ['model' => $model, 'status' => $response->status()]);
            throw new RuntimeException(match ($response->status()) {
                401, 403 => 'Qwen rejected the configured credentials.',
                404 => 'The configured Qwen model or endpoint is unavailable.',
                429 => 'Qwen rate limit reached.',
                default => 'Qwen API request failed with status '.$response->status().'.',
            });
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('Qwen returned an empty response.');
        }

        return trim($content);
    }

    private function decodeJson(string $content): array
    {
        if (preg_match('/\A```(?:json)?\s*(.*?)\s*```\z/is', $content, $matches) === 1) {
            $content = $matches[1];
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Qwen returned malformed JSON.', previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Qwen returned JSON with an invalid shape.');
        }

        return $decoded;
    }
}
