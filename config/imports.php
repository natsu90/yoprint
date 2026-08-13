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

    /*
    |--------------------------------------------------------------------------
    | DuckDB Library Path
    |--------------------------------------------------------------------------
    |
    | Directory holding libduckdb and its FFI header. AppServiceProvider defines
    | it as the DUCKDB_PHP_PATH constant the library looks for, because relying
    | on the environment variable alone does not survive `artisan serve`, which
    | only passes an allow list of variables to its workers.
    |
    */

    'library_path' => env('DUCKDB_PHP_PATH', '/opt/duckdb'),

];
