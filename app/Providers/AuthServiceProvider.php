<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
