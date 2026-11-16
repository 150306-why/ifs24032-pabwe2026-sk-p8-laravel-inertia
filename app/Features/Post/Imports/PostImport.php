<?php

namespace App\Features\Post\Imports;

use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class PostImport
{
    public const MAX_ROWS = 200;

    private const REQUIRED = ['Judul', 'Kategori', 'Konten'];

    private const KNOWN = ['Judul', 'Kategori', 'Konten', 'Status'];

    /**
     * Baca & validasi seluruh baris. Bila ada satu saja error, `rows` dikosongkan (all-or-nothing).
     *
     * @return array{rows: array<int, array{title: string, category: string, content: string, is_published: bool}>, errors: array<int, string>}
     */
    public function parse(string $path): array
    {
        try {
            $data = IOFactory::load($path, 0, [IOFactory::READER_XLSX, IOFactory::READER_XLS])->getActiveSheet()->toArray(null, false, true, false);
        } catch (Throwable) {
            return $this->fail('File Excel tidak dapat dibaca. Pastikan file berformat .xlsx atau .xls yang valid.');
        }

        $header = array_shift($data) ?? [];
        $columns = $this->mapColumns($header);
        $missing = array_diff(self::REQUIRED, array_keys($columns));

        if ($missing !== []) {
            return $this->fail('Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Header harus: Judul, Kategori, Konten (opsional: Status).');
        }

        $data = array_filter($data, fn (array $row) => collect($row)->contains(fn ($cell) => trim((string) $cell) !== ''));

        if ($data === []) {
            return $this->fail('File tidak berisi data artikel.');
        }

        if (count($data) > self::MAX_ROWS) {
            return $this->fail('Maksimal '.self::MAX_ROWS.' artikel per impor.');
        }

        $rows = [];
        $errors = [];

        foreach ($data as $index => $values) {
            $input = [
                'title' => trim((string) ($values[$columns['Judul']] ?? '')),
                'category' => trim((string) ($values[$columns['Kategori']] ?? '')),
                'content' => trim((string) ($values[$columns['Konten']] ?? '')),
                'status' => isset($columns['Status']) ? mb_strtolower(trim((string) ($values[$columns['Status']] ?? ''))) : '',
            ];

            $validator = Validator::make($input, [
                'title' => ['required', 'string', 'max:150'],
                'category' => ['required', 'string', 'max:60'],
                'content' => ['required', 'string'],
                'status' => ['nullable', 'in:draft,published'],
            ], [], ['title' => 'Judul', 'category' => 'Kategori', 'content' => 'Konten', 'status' => 'Status']);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = 'Baris '.($index + 2).': '.$message;
                }

                continue;
            }

            $rows[] = [
                'title' => $input['title'],
                'category' => $input['category'],
                'content' => $input['content'],
                'is_published' => $input['status'] === 'published',
            ];
        }

        if ($errors !== []) {
            return ['rows' => [], 'errors' => array_slice($errors, 0, 50)];
        }

        return ['rows' => $rows, 'errors' => []];
    }

    /** @return array<string, int> nama kolom kanonik => indeks kolom */
    private function mapColumns(array $header): array
    {
        $canonical = collect(self::KNOWN)->mapWithKeys(fn ($name) => [mb_strtolower($name) => $name]);
        $columns = [];

        foreach ($header as $index => $cell) {
            $name = $canonical[mb_strtolower(trim((string) $cell))] ?? null;

            if ($name !== null && ! isset($columns[$name])) {
                $columns[$name] = $index;
            }
        }

        return $columns;
    }

    /** @return array{rows: array, errors: array<int, string>} */
    private function fail(string $message): array
    {
        return ['rows' => [], 'errors' => [$message]];
    }
}
