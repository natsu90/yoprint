<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Upload;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportService extends AbstractImportService
{
    public function process(Upload $upload)
    {
        Excel::import(new ProductsImport($upload), $upload->filepath);
    }
}
