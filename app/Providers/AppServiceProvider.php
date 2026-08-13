<?php

namespace App\Providers;

use App\Contracts\ImportServiceInterface;
use App\Contracts\UploadRepositoryInterface;
use App\Models\Upload;
use App\Observers\UploadObserver;
use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $libraryPath = config('imports.library_path');

        if ($libraryPath && ! defined('DUCKDB_PHP_PATH')) {
            define('DUCKDB_PHP_PATH', $libraryPath);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(UploadRepositoryInterface::class, UploadRepository::class);

        $this->app->bind(ImportServiceInterface::class, function ($app) {
            $driver = config('imports.driver');
            $service = config("imports.drivers.{$driver}");

            if (! $service) {
                throw new InvalidArgumentException("Unsupported import driver [{$driver}].");
            }

            return $app->make($service);
        });

        Upload::observe(UploadObserver::class);
    }
}
