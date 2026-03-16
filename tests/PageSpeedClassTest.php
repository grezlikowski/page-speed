<?php

use Grezlikowski\PageSpeed\PageSpeedClass;

it('returns the package version', function () {
    expect(PageSpeedClass::version())->toBe('1.0.0');
});

it('returns version as string', function () {
    expect(PageSpeedClass::version())->toBeString();
});
