<?php

namespace Tests\Unit;

use App\Features\Post\Imports\PostImport;
use Tests\Concerns\HandlesSpreadsheets;
use Tests\TestCase;

class PostImportTest extends TestCase
{
    use HandlesSpreadsheets;

    private function parse(array $rows): array
    {
        return (new PostImport)->parse($this->xlsx($rows)->getRealPath());
    }

    public function test_unreadable_file_returns_error(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'bad');
        file_put_contents($path, 'bukan file excel');

        $result = (new PostImport)->parse($path);

        $this->assertSame([], $result['rows']);
        $this->assertStringContainsString('tidak dapat dibaca', $result['errors'][0]);
    }

    public function test_headers_are_case_insensitive_and_extra_columns_ignored(): void
    {
        $result = $this->parse([
            ['no', 'JUDUL', ' kategori ', 'konten', 'STATUS', 'Penulis'],
            [1, 'Satu', 'A', 'Isi', 'PUBLISHED', 'Budi'],
            [2, 'Dua', 'B', 'Isi', 'draft', 'Budi'],
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertTrue($result['rows'][0]['is_published']);
        $this->assertFalse($result['rows'][1]['is_published']);
    }

    public function test_status_column_is_optional(): void
    {
        $result = $this->parse([['Judul', 'Kategori', 'Konten'], ['Satu', 'A', 'Isi']]);

        $this->assertSame([], $result['errors']);
        $this->assertFalse($result['rows'][0]['is_published']);
    }

    public function test_reports_every_invalid_row_with_excel_row_numbers(): void
    {
        $result = $this->parse([
            ['Judul', 'Kategori', 'Konten', 'Status'],
            [str_repeat('x', 151), 'A', 'Isi', ''],
            ['OK', str_repeat('k', 61), 'Isi', ''],
            ['OK', 'A', '', ''],
        ]);

        $this->assertSame([], $result['rows']);
        $this->assertCount(3, $result['errors']);
        $this->assertStringStartsWith('Baris 2:', $result['errors'][0]);
        $this->assertStringStartsWith('Baris 4:', $result['errors'][2]);
    }

    public function test_rejects_missing_columns_empty_data_and_too_many_rows(): void
    {
        $this->assertStringContainsString('Judul, Kategori', $this->parse([['Foo', 'Bar'], ['a', 'b']])['errors'][0]);
        $this->assertSame(['File tidak berisi data artikel.'], $this->parse([['Judul', 'Kategori', 'Konten'], [null, null, null]])['errors']);

        $rows = [['Judul', 'Kategori', 'Konten']];
        for ($i = 1; $i <= PostImport::MAX_ROWS + 1; $i++) {
            $rows[] = ["Judul {$i}", 'A', 'Isi'];
        }
        $this->assertSame(['Maksimal '.PostImport::MAX_ROWS.' artikel per impor.'], $this->parse($rows)['errors']);
    }

    public function test_error_list_is_capped_at_fifty(): void
    {
        $rows = [['Judul', 'Kategori', 'Konten']];
        for ($i = 0; $i < 80; $i++) {
            $rows[] = ['', 'A', 'Isi'];
        }

        $this->assertCount(50, $this->parse($rows)['errors']);
    }
}
