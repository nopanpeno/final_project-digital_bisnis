<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_checkout_urls_use_current_request_host(): void
    {
        $this->app['request']->server->set('HTTP_HOST', 'localhost');
        $this->app['request']->server->set('HTTPS', 'off');
        $this->app['request']->server->set('HTTP_X_FORWARDED_HOST', 'abc123-1234.devtunnels.ms');
        $this->app['request']->server->set('HTTP_X_FORWARDED_PROTO', 'https');

        $this->app['url']->forceRootUrl('https://abc123-1234.devtunnels.ms');
        $this->app['url']->forceScheme('https');

        $url = route('checkout.success', ['order_id' => 'TRX-123']);

        $this->assertSame('https://abc123-1234.devtunnels.ms/success/TRX-123', $url);
    }
}
