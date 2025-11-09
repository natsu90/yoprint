<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Upload;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportService implements ImportServiceInterface
{
    public function process(Upload $upload)
    {
        Excel::import(new ProductsImport($upload), Storage::disk('local')->url('uploads/'. $upload->filename));
    }
}