<?php

use Grezlikowski\PageSpeed\PageSpeedWithHistoryClass;

it('returns the package version', function () {
    expect(PageSpeedWithHistoryClass::version())->toBe('1.0.0');
});

it('returns version as string', function () {
    expect(PageSpeedWithHistoryClass::version())->toBeString();
});
