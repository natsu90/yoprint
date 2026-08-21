<?php

use App\Services\DuckDbImportService;
use App\Services\ExcelImportService;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Import Driver
    |--------------------------------------------------------------------------
    |
    | The implementation of App\Contracts\ImportServiceInterface used to process
    | uploaded CSV files. "excel" reads the file with Laravel Excel, "duckdb"
    | reads it with the DuckDB PHP library.
    |
    */

    'driver' => env('IMPORT_DRIVER', 'excel'),

    'drivers' => [
        'excel' => ExcelImportService::class,
        'duckdb' => DuckDbImportService::class,
    ],

];
