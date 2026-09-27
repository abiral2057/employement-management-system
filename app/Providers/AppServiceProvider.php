<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function () {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.navbar', function ($view) {
            if (Auth::check()) {
                try {
                    $service = app(NotificationService::class);
                    $notifications = $service->getNavbarNotifications(6);
                    $unreadCount = $service->getUnreadCount();
                    $view->with([
                        'navbarNotifications' => $notifications,
                        'navbarUnreadCount' => $unreadCount,
                    ]);
                } catch (\Throwable $e) {
                    $view->with([
                        'navbarNotifications' => [],
                        'navbarUnreadCount' => 0,
                    ]);
                }
            } else {
                $view->with([
                    'navbarNotifications' => [],
                    'navbarUnreadCount' => 0,
                ]);
            }
        });
    }
}
