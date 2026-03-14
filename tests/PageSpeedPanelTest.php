<?php

use Grezlikowski\PageSpeed\PageSpeedPanel;

afterEach(function () {
    PageSpeedPanel::$authUsing = null;
});

it('has null auth callback by default', function () {
    expect(PageSpeedPanel::$authUsing)->toBeNull();
});

it('default behavior checks for local environment', function () {
    // Default callback returns app()->environment('local').
    // Testbench runs in 'testing' env, so default should deny.
    expect(PageSpeedPanel::check(request()))->toBeFalse();
});

it('registers custom auth callback', function () {
    $result = PageSpeedPanel::auth(fn () => true);

    expect($result)->toBeInstanceOf(PageSpeedPanel::class)
        ->and(PageSpeedPanel::$authUsing)->toBeInstanceOf(Closure::class);
});

it('uses custom auth callback when registered', function () {
    PageSpeedPanel::auth(fn () => true);

    expect(PageSpeedPanel::check(request()))->toBeTrue();
});

it('custom auth can deny access', function () {
    PageSpeedPanel::auth(fn () => false);

    expect(PageSpeedPanel::check(request()))->toBeFalse();
});

it('passes request to auth callback', function () {
    $receivedRequest = null;

    PageSpeedPanel::auth(function ($request) use (&$receivedRequest) {
        $receivedRequest = $request;

        return true;
    });

    PageSpeedPanel::check(request());

    expect($receivedRequest)->toBe(request());
});
