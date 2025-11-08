<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UploadRepository;
use App\Repositories\UploadRepositoryInterface;

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
    }
}
