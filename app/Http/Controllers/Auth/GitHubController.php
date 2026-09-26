<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGithubProjects;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (
            ! Config::get('services.github.client_id')
            || ! Config::get('services.github.client_secret')
            || ! Config::get('services.github.redirect')
        ) {
            return redirect()->route('login')->withErrors([
                'github' => 'GitHub sign-in is not configured. Add the GitHub OAuth credentials and callback URL to .env, then run php artisan config:clear.',
            ]);
        }

        return Socialite::driver('github')
            ->scopes(['read:user', 'user:email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Throwable $exception) {
            Log::warning('GitHub OAuth callback failed.', ['message' => $exception->getMessage()]);

            return redirect()->route('login')->withErrors([
                'github' => 'We could not sign you in with GitHub. Please try again.',
            ]);
        }

        if (! $githubUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'github' => 'Your GitHub account must provide an email address to continue.',
            ]);
        }

        $user = User::query()->where('github_id', $githubUser->getId())->first();

        if (! $user && $githubUser->getEmail()) {
            $user = User::query()->where('email', $githubUser->getEmail())->first();
        }

        $attributes = array_filter([
            'name' => $githubUser->getName() ?: $githubUser->getNickname(),
            'email' => $githubUser->getEmail(),
            'email_verified_at' => now(),
            'github_id' => $githubUser->getId(),
            'github_token' => $githubUser->token,
            'github_username' => $githubUser->getNickname(),
            'avatar_url' => $githubUser->getAvatar(),
        ], static fn ($value) => $value !== null);

        if ($user) {
            $user->forceFill($attributes)->save();
        } else {
            $user = User::create($attributes);
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        SyncGithubProjects::dispatch($user);

        return redirect()->intended(route('dashboard'));
    }
}
