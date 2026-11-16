<?php

namespace Tests\Unit;

use App\Features\Shared\Support\Like;
use App\Features\Shared\Support\PostFilters;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function test_like_escapes_wildcards_and_lowercases(): void
    {
        $this->assertSame('%100\%\_\\\\x%', Like::contains('100%_\\X'));
        $this->assertSame('%halo%', Like::contains('HALO'));
    }

    public function test_post_filters_only_accept_strings(): void
    {
        $request = Request::create('/x', 'GET', ['q' => '  kata ', 'category' => ['a'], 'status' => 'draft']);

        $this->assertSame(['q' => 'kata', 'category' => '', 'status' => 'draft'], PostFilters::fromRequest($request));
        $this->assertSame(['q' => '', 'category' => '', 'status' => ''], PostFilters::fromRequest(Request::create('/x')));
    }
}
