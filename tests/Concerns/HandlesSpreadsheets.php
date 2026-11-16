<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait HandlesSpreadsheets
{
    protected function xlsx(array $rows, string $name = 'artikel.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    protected function readXlsx(string $binary): array
    {
        $path = tempnam(sys_get_temp_dir(), 'rd').'.xlsx';
        file_put_contents($path, $binary);

        return IOFactory::load($path)->getActiveSheet()->toArray(null, false, true, false);
    }
}
