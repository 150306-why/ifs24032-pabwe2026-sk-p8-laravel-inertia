<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Manifest Vite tidak dibutuhkan saat test.
        $this->withoutVite();

        // Test tidak boleh bergantung pada .env lokal (mis. OPENAI_BASE_URL ke Gemini) atau memanggil jaringan.
        config([
            'services.openai.base_url' => 'https://api.openai.com/v1',
            'services.openai.model' => 'gpt-4o-mini',
            'services.openai.key' => null,
        ]);
        Http::preventStrayRequests();

        // Inertia v3 mencari halaman di "js/pages" (huruf kecil); proyek ini memakai "js/Pages".
        // Linux membedakan huruf besar/kecil, jadi pengecekan file halaman dimatikan.
        // Keberadaan halaman tetap terjamin oleh `npm run build`.
        config([
            'inertia.testing.ensure_pages_exist' => false,
            'inertia.pages.ensure_pages_exist' => false,
            'inertia.testing.page_paths' => [resource_path('js/Pages')],
            'inertia.pages.paths' => [resource_path('js/Pages')],
        ]);
    }
}
