<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\HandlesSpreadsheets;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use HandlesSpreadsheets;
    use RefreshDatabase;

    public function test_member_exports_only_own_posts(): void
    {
        $me = User::factory()->create();
        Post::factory()->published()->for($me)->create(['title' => 'Milik Saya', 'content' => '=1+1']);
        Post::factory()->draft()->for($me)->create(['title' => 'Draft Saya']);
        Post::factory()->create(['title' => 'Milik Orang']);

        $response = $this->actingAs($me)->get('/dashboard/posts/export');

        $response->assertOk()->assertDownload('artikel-'.$me->username.'-'.now()->format('Ymd').'.xlsx');
        $rows = $this->readXlsx($response->streamedContent());

        $this->assertSame(['Judul', 'Kategori', 'Konten', 'Status', 'Tanggal Publikasi', 'Tanggal Dibuat'], $rows[0]);
        $this->assertCount(3, $rows);
        $titles = array_column($rows, 0);
        $this->assertContains('Milik Saya', $titles);
        $this->assertNotContains('Milik Orang', $titles);
        $this->assertContains('=1+1', array_column($rows, 2)); // tetap teks, bukan formula
    }

    public function test_import_valid_file_creates_posts(): void
    {
        $me = User::factory()->create();
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Konten', 'Status'],
            ['Artikel Satu', 'Teknologi', '# Isi satu', 'Published'],
            ['Artikel Dua', 'Bisnis', 'Isi dua', ''],
            ['', '', '', ''],
        ]);

        $this->actingAs($me)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('success', '2 artikel berhasil diimpor.');

        $this->assertSame(2, $me->posts()->count());
        $this->assertTrue($me->posts()->where('title', 'Artikel Satu')->first()->is_published);
        $this->assertFalse($me->posts()->where('title', 'Artikel Dua')->first()->is_published);
    }

    public function test_export_then_import_roundtrip(): void
    {
        $me = User::factory()->create();
        Post::factory()->count(2)->published()->for($me)->create();
        $content = $this->actingAs($me)->get('/dashboard/posts/export')->streamedContent();
        $path = tempnam(sys_get_temp_dir(), 'rt').'.xlsx';
        file_put_contents($path, $content);

        $other = User::factory()->create();
        $this->actingAs($other)->post('/dashboard/posts/import', ['file' => new UploadedFile($path, 'x.xlsx', null, null, true)])
            ->assertSessionHas('success');

        $this->assertSame(2, $other->posts()->count());
    }

    public function test_invalid_rows_abort_whole_import(): void
    {
        $me = User::factory()->create();
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Konten', 'Status'],
            ['Valid', 'Teknologi', 'Isi', 'Draft'],
            ['', 'Bisnis', 'Isi', 'Draft'],
            ['Status salah', 'Bisnis', 'Isi', 'terbit'],
        ]);

        $this->actingAs($me)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('error')
            ->assertSessionHas('import_errors', fn (array $errors) => count($errors) === 2
                && str_starts_with($errors[0], 'Baris 3:') && str_starts_with($errors[1], 'Baris 4:'));

        $this->assertSame(0, Post::count());
    }

    public function test_missing_required_columns_and_empty_file_are_rejected(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/import', ['file' => $this->xlsx([['Judul', 'Konten'], ['A', 'B']])])
            ->assertSessionHas('import_errors', fn (array $e) => str_contains($e[0], 'Kategori'));

        $this->actingAs($me)->post('/dashboard/posts/import', ['file' => $this->xlsx([['Judul', 'Kategori', 'Konten']])])
            ->assertSessionHas('import_errors', fn (array $e) => $e[0] === 'File tidak berisi data artikel.');

        $this->assertSame(0, Post::count());
    }

    public function test_import_rejects_invalid_file_type_and_missing_file(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/import', ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertSessionHasErrors('file');
        $this->actingAs($me)->post('/dashboard/posts/import', [])->assertSessionHasErrors('file');
    }

    public function test_other_roles_cannot_use_member_import_export(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/dashboard/posts/export')->assertForbidden();
        $this->actingAs($admin)->post('/dashboard/posts/import', [])->assertForbidden();
        auth()->logout();
        $this->get('/dashboard/posts/export')->assertRedirect(route('login'));
    }
}
