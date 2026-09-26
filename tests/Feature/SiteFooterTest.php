<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_footer_links_to_antounios_portfolio_on_public_and_auth_pages(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Founder &amp; developed by', false)
            ->assertSee('https://tony7.freedev.app/');

        $this->get('/login')->assertOk()
            ->assertSee('Founder &amp; developed by', false)
            ->assertSee('https://tony7.freedev.app/');
    }

    public function test_site_footer_appears_on_public_portfolios(): void
    {
        $user = User::factory()->create();
        $user->portfolios()->create([
            'subdomain' => 'footer-check',
            'template' => 'developer',
            'theme' => [],
            'is_published' => true,
        ]);

        $this->get('/p/footer-check')->assertOk()
            ->assertSee('Founder &amp; developed by', false)
            ->assertSee('https://tony7.freedev.app/');
    }
}
