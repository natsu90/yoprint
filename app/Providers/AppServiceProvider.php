<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UploadRepository;
use App\Contracts\UploadRepositoryInterface;
use App\Services\ImportService;
use App\Contracts\ImportServiceInterface;
use App\Models\Upload;
use App\Observers\UploadObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(UploadRepositoryInterface::class, UploadRepository::class);
        $this->app->bind(ImportServiceInterface::class, ImportService::class);

        Upload::observe(UploadObserver::class);
    }
}
