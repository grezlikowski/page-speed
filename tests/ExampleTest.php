<?php

use Grezlikowski\PageSpeed\PageSpeedServiceProvider;
use Grezlikowski\PageSpeed\Services\PageSpeedApiService;

it('registers the service provider', function () {
    expect(app()->getProviders(PageSpeedServiceProvider::class))
        ->not->toBeEmpty();
});

it('registers PageSpeedApiService as singleton', function () {
    expect(app(PageSpeedApiService::class))
        ->toBeInstanceOf(PageSpeedApiService::class);
});

it('merges package config', function () {
    expect(config('page-speed.path'))->toBe('page-speed')
        ->and(config('page-speed.default_strategy'))->toBe('mobile')
        ->and(config('page-speed.history_limit'))->toBe(50)
        ->and(config('page-speed.timeout'))->toBe(60);
});
