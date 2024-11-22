<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\GlobalObserver;
use App\Observers\ProductLoggerObserver;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductRepositoryInterface;
use App\Repository\Roles\RolesRepository;
use App\Repository\Roles\RolesRepositoryInterface;
use App\Repository\UserStatus\UserStatusRepository;
use App\Repository\UserStatus\UserStatusRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(RolesRepositoryInterface::class, RolesRepository::class);
        $this->app->singleton(UserStatusRepositoryInterface::class, UserStatusRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
