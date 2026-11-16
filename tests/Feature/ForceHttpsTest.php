<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ForceHttpsTest extends TestCase
{
    public function test_urls_use_https_when_force_https_is_enabled(): void
    {
        config(['blog.force_https' => true]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', route('login'));
    }

    public function test_urls_follow_request_scheme_when_force_https_is_disabled(): void
    {
        config(['blog.force_https' => false]);
        URL::forceScheme('http');

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', route('login'));
    }
}
