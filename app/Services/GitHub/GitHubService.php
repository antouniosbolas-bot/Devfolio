<?php

namespace App\Services\GitHub;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    private const API = 'https://api.github.com';

    private function client(User $user): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->withToken($user->github_token)
            ->retry(3, 200, throw: false)
            ->timeout(15);
    }

    public function clearCache(User $user): void
    {
        Cache::forget("github:{$user->id}:repositories");

        foreach ($user->projects()->pluck('name') as $repository) {
            Cache::forget("github:{$user->id}:languages:{$repository}");
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function repositories(User $user): array
    {
        return Cache::remember("github:{$user->id}:repositories", now()->addMinutes(5), function () use ($user) {
            $repositories = [];
            for ($page = 1; $page <= 100; $page++) {
                $response = $this->client($user)->get('/user/repos', [
                    'per_page' => 100, 'page' => $page, 'sort' => 'updated', 'visibility' => 'public', 'affiliation' => 'owner',
                ]);
                if ($response->failed()) {
                    Log::warning('GitHub repositories request failed.', ['user_id' => $user->id, 'status' => $response->status()]);
                    $response->throw();
                }
                $batch = $response->json();
                if (! is_array($batch) || $batch === []) {
                    break;
                }
                $repositories = [...$repositories, ...$batch];
                if (count($batch) < 100) {
                    break;
                }
            }

            return $repositories;
        });
    }

    /** @return array<string, int> */
    public function languages(User $user, int|string $repository): array
    {
        return Cache::remember("github:{$user->id}:languages:{$repository}", now()->addMinutes(5), function () use ($user, $repository) {
            return $this->client($user)->get("/repos/{$user->github_username}/{$repository}/languages")->throw()->json();
        });
    }

    public function readme(User $user, Project $project): ?string
    {
        return Cache::remember("github:{$user->id}:readme:{$project->github_id}", now()->addMinutes(5), function () use ($user, $project) {
            $response = $this->client($user)->get("/repos/{$project->full_name}/readme");

            if ($response->status() === 404) {
                return null;
            }

            if ($response->failed()) {
                Log::warning('GitHub README request failed.', [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'status' => $response->status(),
                ]);
                $response->throw();
            }

            $encoded = data_get($response->json(), 'content');
            if (! is_string($encoded)) {
                return null;
            }

            $decoded = base64_decode(str_replace(["\r", "\n"], '', $encoded), true);
            if ($decoded === false) {
                return null;
            }

            $text = preg_replace([
                '/-----BEGIN [A-Z ]*PRIVATE KEY-----.*?-----END [A-Z ]*PRIVATE KEY-----/s',
                '/\b(?:gh[pousr]_[A-Za-z0-9_]{20,}|github_pat_[A-Za-z0-9_]{20,})\b/',
                '/\bAKIA[0-9A-Z]{16}\b/',
                '/\b(?:api[_-]?key|access[_-]?token|secret(?:[_-]?(?:access[_-]?)?key)?|password)\s*[:=]\s*["\']?[^\s"\',;]{8,}/i',
                '/\bBearer\s+[A-Za-z0-9._~-]{20,}/i',
            ], '[REDACTED]', $decoded) ?? $decoded;

            return mb_substr(trim(strip_tags($text)), 0, 6000);
        });
    }

    public function clearReadmeCache(User $user, Project $project): void
    {
        Cache::forget("github:{$user->id}:readme:{$project->github_id}");
    }

    public function upsertProject(User $user, array $data): Project
    {
        return $user->projects()->updateOrCreate(
            ['github_id' => $data['id']],
            [
                'name' => $data['name'], 'full_name' => $data['full_name'], 'description' => $data['description'] ?? null,
                'html_url' => $data['html_url'], 'clone_url' => $data['clone_url'] ?? null,
                'default_branch' => $data['default_branch'] ?? null, 'language' => $data['language'] ?? null,
                'stars' => $data['stargazers_count'] ?? 0, 'forks' => $data['forks_count'] ?? 0,
                'is_private' => $data['private'] ?? false, 'pushed_at' => $data['pushed_at'] ?? null,
                'is_archived' => $data['archived'] ?? false, 'is_fork' => $data['fork'] ?? false,
                'synced_at' => now(),
            ],
        );
    }
}
