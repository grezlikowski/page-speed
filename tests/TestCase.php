<?php

namespace Grezlikowski\PageSpeed\Tests;

use Grezlikowski\PageSpeed\PageSpeedServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            PageSpeedServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('page-speed.api_key', 'test-api-key');
        $app['config']->set('page-speed.enabled', true);
        $app['config']->set('app.url', 'http://localhost');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
