<?php

namespace App\Providers;

use App\Models\MenuList;
use App\Models\User;
use App\Support\MailConfiguration;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        MailConfiguration::apply();

        Gate::before(function ($user, string $ability) {
            return $user->isAdmin() ? true : null;
        });

        $abilities = collect(['dashboard', 'profile', 'master-data', 'users', 'role-akses', 'config-mail']);

        if (Schema::hasTable('menu_list') && Schema::hasTable('role_menu_access')) {
            $abilities = $abilities->merge(
                MenuList::query()->orderBy('sort_order')->pluck('key')
            );
        }

        $abilities->unique()->each(function (string $ability) {
            Gate::define($ability, fn($user) => $user->hasAccess($ability));
        });

        View::composer('layouts.header', function ($view) {
            $user = Auth::user();

            if (! $user instanceof User) {
                $view->with('headerNotifications', collect());
                $view->with('unreadNotificationCount', 0);

                return;
            }

            $unreadNotifications = DatabaseNotification::query()
                ->where('notifiable_type', $user::class)
                ->where('notifiable_id', $user->getKey())
                ->whereNull('read_at')
                ->latest();

            $view->with('headerNotifications', (clone $unreadNotifications)->take(6)->get());
            $view->with('unreadNotificationCount', (clone $unreadNotifications)->count());
        });
    }
}
