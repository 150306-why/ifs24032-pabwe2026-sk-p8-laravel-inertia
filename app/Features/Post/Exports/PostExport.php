<?php

namespace App\Features\Post\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExport
{
    public const HEADERS = ['Judul', 'Kategori', 'Konten', 'Status', 'Tanggal Publikasi', 'Tanggal Dibuat'];

    public function download(Collection $posts, string $filename, bool $withAuthor = false): StreamedResponse
    {
        $spreadsheet = $this->build($posts, $withAuthor);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function build(Collection $posts, bool $withAuthor = false): Spreadsheet
    {
        $headers = $withAuthor ? [...self::HEADERS, 'Penulis'] : self::HEADERS;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        foreach ($headers as $index => $header) {
            $sheet->getCell([$index + 1, 1])->setValueExplicit($header, DataType::TYPE_STRING);
        }

        $row = 2;
        foreach ($posts as $post) {
            $values = [
                $post->title,
                $post->category,
                $post->content,
                $post->is_published ? 'Published' : 'Draft',
                $post->published_at?->format('Y-m-d H:i') ?? '',
                $post->created_at->format('Y-m-d H:i'),
            ];

            if ($withAuthor) {
                $values[] = $post->user->name;
            }

            // Selalu string eksplisit agar teks berawalan "=" tidak dieksekusi sebagai formula.
            foreach ($values as $index => $value) {
                $sheet->getCell([$index + 1, $row])->setValueExplicit((string) $value, DataType::TYPE_STRING);
            }

            $row++;
        }

        $lastRow = $row - 1;
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);

        $widths = ['A' => 40, 'B' => 18, 'C' => 80, 'D' => 12, 'E' => 20, 'F' => 20, 'G' => 24];
        foreach (array_slice($widths, 0, count($headers), true) as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        if ($lastRow >= 2) {
            $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        }
        $sheet->freezePane('A2');

        return $spreadsheet;
    }
}
