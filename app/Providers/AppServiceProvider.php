<?php

namespace App\Providers;

use App\Models\MenuList;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(function ($user, string $ability) {
            return $user->isAdmin() ? true : null;
        });

        $abilities = collect(['dashboard', 'profile', 'master-data', 'users', 'role-akses']);

        if (Schema::hasTable('menu_list') && Schema::hasTable('role_menu_access')) {
            $abilities = $abilities->merge(
                MenuList::query()->orderBy('sort_order')->pluck('key')
            );
        }

        $abilities->unique()->each(function (string $ability) {
            Gate::define($ability, fn($user) => $user->hasAccess($ability));
        });
    }
}
