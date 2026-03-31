<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\StaffPermission;

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
        // Register custom Blade directive for permission checking
        Blade::if('permission', function ($permission) {
            if (!auth()->check()) {
                return false;
            }

            // Admin has all permissions
            if (auth()->user()->role === 'admin') {
                return true;
            }

            // Check if staff has permission
            if (auth()->user()->role === 'staff') {
                // If staff has no permissions assigned, grant full access by default
                $hasAnyPermissions = StaffPermission::where('user_id', auth()->id())->exists();
                if (!$hasAnyPermissions) {
                    return true; // Full access when no permissions are set
                }

                // If permissions are assigned, check for specific permission
                return StaffPermission::hasPermission(auth()->id(), $permission);
            }

            return false;
        });

        // Check if user is admin
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->role === 'admin';
        });

        // Check if user is staff
        Blade::if('staff', function () {
            return auth()->check() && auth()->user()->role === 'staff';
        });
    }
}
