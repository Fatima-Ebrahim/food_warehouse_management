<?php

namespace App\Providers;

use App\Models\Warehouse;
use App\Models\WarehouseCoordinate;
use App\Repositories\WarehouseDesignRepository;
use App\Repositories\WarehouseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WarehouseDesignRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
