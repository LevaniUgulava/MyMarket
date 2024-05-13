<?php

namespace App\Providers;

use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductRepositoryInterface;
use App\Repository\Roles\RolesRepository;
use App\Repository\Roles\RolesRepositoryInterface;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app()->bind(ProductRepositoryInterface::class, ProductRepository::class);
        app()->bind(RolesRepositoryInterface::class, RolesRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
