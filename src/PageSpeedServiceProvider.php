<?php

namespace Grezlikowski\PageSpeed;

use Grezlikowski\PageSpeed\Http\Middleware\Authorize;
use Grezlikowski\PageSpeed\Services\PageSpeedApiService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PageSpeedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/page-speed.php', 'page-speed');

        $this->app->singleton(PageSpeedApiService::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerResources();
        $this->registerPublishing();
    }

    private function registerRoutes(): void
    {
        if (! config('page-speed.enabled', true)) {
            return;
        }

        Route::group([
            'domain' => config('page-speed.domain'),
            'prefix' => config('page-speed.path', 'page-speed'),
            'middleware' => array_merge(
                config('page-speed.middleware', ['web']),
                [Authorize::class],
            ),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    private function registerResources(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'page-speed');
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/page-speed.php' => config_path('page-speed.php'),
        ], 'page-speed-config');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'page-speed-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/page-speed'),
        ], 'page-speed-views');
    }
}
