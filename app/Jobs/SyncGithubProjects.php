<?php

namespace App\Jobs;

use App\Models\GithubSync;
use App\Models\User;
use App\Services\GitHub\GitHubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncGithubProjects implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public ?int $syncId = null) {}

    public function handle(GitHubService $github): void
    {
        $sync = $this->syncId ? GithubSync::findOrFail($this->syncId) : $this->user->githubSyncs()->create();
        $sync->update(['status' => 'running', 'started_at' => now(), 'error' => null]);
        try {
            $github->clearCache($this->user);
            $count = 0;
            foreach ($github->repositories($this->user) as $data) {
                $project = $github->upsertProject($this->user, $data);
                $languages = $github->languages($this->user, $data['name']);
                $project->languages()->delete();
                foreach ($languages as $language => $bytes) {
                    $project->languages()->create(['language' => $language, 'bytes' => $bytes]);
                }
                $count++;
            }
            $sync->update(['status' => 'completed', 'projects_synced' => $count, 'completed_at' => now()]);
        } catch (\Throwable $exception) {
            Log::error('GitHub project sync failed.', ['user_id' => $this->user->id, 'exception' => $exception]);
            $sync->update(['status' => 'failed', 'error' => $exception->getMessage(), 'completed_at' => now()]);
            throw $exception;
        }
    }
}
