<?php

namespace Tests\Feature;

use Tests\TestCase;

class GithubOAuthConfigurationTest extends TestCase
{
    public function test_github_redirect_route_uses_the_configured_callback_url(): void
    {
        $this->assertSame(
            rtrim((string) config('app.url'), '/').'/auth/github/callback',
            config('services.github.redirect')
        );
    }

    public function test_github_authorization_uses_only_profile_and_email_scopes(): void
    {
        config([
            'services.github.client_id' => 'test-client',
            'services.github.client_secret' => 'test-secret',
            'services.github.redirect' => 'http://127.0.0.1:8000/auth/github/callback',
        ]);

        $response = $this->get(route('auth.github.redirect'));
        $response->assertRedirect();
        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

        $this->assertSame('http://127.0.0.1:8000/auth/github/callback', $query['redirect_uri']);
        $this->assertEqualsCanonicalizing(['read:user', 'user:email'], explode(',', $query['scope']));
        $this->assertStringNotContainsString('repo', $query['scope']);
    }

    public function test_github_connect_page_shows_exact_callback_required_by_the_oauth_app(): void
    {
        config(['services.github.redirect' => 'http://127.0.0.1:8000/auth/github/callback']);

        $this->get(route('github.connect'))
            ->assertOk()
            ->assertSee('http://127.0.0.1:8000/auth/github/callback')
            ->assertSee('This is the exact callback DevFolio sends')
            ->assertSee('localhost')
            ->assertSee('are different addresses')
            ->assertSee('https://github.com/settings/developers')
            ->assertSee('data-copy-text="http://127.0.0.1:8000/auth/github/callback"', false);
    }
}
