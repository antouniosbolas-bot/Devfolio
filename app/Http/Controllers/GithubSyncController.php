<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGithubProjects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GithubSyncController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (blank($user->github_token)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Connect your GitHub account before syncing repositories.'], 422);
            }

            return redirect()->route('auth.github.redirect');
        }

        $activeSync = $user->githubSyncs()->whereIn('status', ['pending', 'running'])->latest()->first();
        if ($activeSync) {
            if ($request->expectsJson()) {
                return response()->json(['sync_id' => $activeSync->id, 'status' => $activeSync->status], 202);
            }

            return redirect()->route('dashboard')->with('status', 'A GitHub synchronization is already in progress.');
        }

        $sync = $user->githubSyncs()->create(['status' => 'pending']);
        SyncGithubProjects::dispatch($user, $sync->id);

        if (! $request->expectsJson()) {
            return redirect()->route('dashboard')->with('status', 'GitHub synchronization started. Keep the queue worker running to import your projects.');
        }

        return response()->json(['sync_id' => $sync->id, 'status' => $sync->status], 202);
    }
}
