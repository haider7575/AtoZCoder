<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Order;


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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        Gate::define('manage-products', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-all-orders', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-assigned-orders', function (User $user) {
            return $user->isAdmin() || $user->isStaff();
        });

        Gate::define('update-order-status', function (User $user, Order $order) {
            return $user->isAdmin() || ($user->isStaff() && $order->assigned_staff_id === $user->id);
        });
    }
}
