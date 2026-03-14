<?php

use Grezlikowski\PageSpeed\Models\PageSpeedTest;
use Grezlikowski\PageSpeed\Services\PageSpeedApiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = app(PageSpeedApiService::class);
});

it('is registered as a singleton', function () {
    $instance1 = app(PageSpeedApiService::class);
    $instance2 = app(PageSpeedApiService::class);

    expect($instance1)->toBe($instance2);
});

it('returns the configured api key', function () {
    config()->set('page-speed.api_key', 'my-test-key');

    expect($this->service->getApiKey())->toBe('my-test-key');
});

it('throws exception when api key is missing', function () {
    config()->set('page-speed.api_key', '');

    $this->service->getApiKey();
})->throws(RuntimeException::class, 'API key is not configured');

it('checks if api key is configured', function () {
    config()->set('page-speed.api_key', 'exists');
    expect($this->service->hasApiKey())->toBeTrue();

    config()->set('page-speed.api_key', '');
    expect($this->service->hasApiKey())->toBeFalse();
});

it('returns testable urls including homepage', function () {
    config()->set('app.url', 'http://localhost');

    $urls = $this->service->getTestableUrls();

    expect($urls)->toBeArray()
        ->and($urls[0])->toMatchArray([
            'url' => 'http://localhost/',
            'label' => 'Homepage (/)',
        ]);
});

it('includes default_url when configured', function () {
    config()->set('app.url', 'http://localhost');
    config()->set('page-speed.default_url', 'https://custom.example.com');

    $urls = $this->service->getTestableUrls();

    $firstUrl = $urls[0];
    expect($firstUrl['url'])->toBe('https://custom.example.com');
});

it('runs a test and stores result', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response(fakePageSpeedResponse()),
    ]);

    config()->set('page-speed.api_key', 'fake-key');

    $result = $this->service->runTest('https://example.com', 'mobile');

    expect($result)->toBeInstanceOf(PageSpeedTest::class)
        ->and($result->url)->toBe('https://example.com')
        ->and($result->strategy)->toBe('mobile')
        ->and($result->performance_score)->toBe(92)
        ->and($result->accessibility_score)->toBe(85)
        ->and($result->best_practices_score)->toBe(90)
        ->and($result->seo_score)->toBe(95)
        ->and($result->metrics)->toBeArray()
        ->and($result->metrics['first_contentful_paint'])->toBe(1.5)
        ->and((float) $result->metrics['largest_contentful_paint'])->toBe(2.0)
        ->and($result->id)->not->toBeNull();
});

it('throws exception on api failure', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response([
            'error' => ['message' => 'Invalid API key'],
        ], 400),
    ]);

    config()->set('page-speed.api_key', 'bad-key');

    $this->service->runTest('https://example.com', 'mobile');
})->throws(RuntimeException::class, 'PageSpeed test failed');

it('runs tests for multiple strategies', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response(fakePageSpeedResponse()),
    ]);

    config()->set('page-speed.api_key', 'fake-key');

    $results = $this->service->runTests('https://example.com', ['mobile', 'desktop']);

    expect($results)->toHaveCount(2)
        ->and($results[0])->toBeInstanceOf(PageSpeedTest::class)
        ->and($results[1])->toBeInstanceOf(PageSpeedTest::class);
});

it('extracts audits from test result', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'raw_response' => fakePageSpeedResponse(),
    ]);

    $audits = $this->service->extractAudits($test);

    expect($audits)->toBeArray()
        ->and($audits)->toHaveKey('performance');

    $performanceAudits = $audits['performance'];
    expect($performanceAudits)->toBeArray()
        ->and(count($performanceAudits))->toBeGreaterThan(0);

    $firstAudit = $performanceAudits[0];
    expect($firstAudit)->toHaveKeys(['id', 'title', 'score', 'scoreDisplayMode']);
});

it('returns empty array when raw_response is null', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'raw_response' => null,
    ]);

    expect($this->service->extractAudits($test))->toBe([]);
});

it('stores user_id when provided', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response(fakePageSpeedResponse()),
    ]);

    config()->set('page-speed.api_key', 'fake-key');

    $result = $this->service->runTest('https://example.com', 'mobile', [], 42);

    expect($result->user_id)->toBe(42);
});

/**
 * Generate a fake PageSpeed API response for testing.
 */
function fakePageSpeedResponse(): array
{
    return [
        'lighthouseResult' => [
            'categories' => [
                'performance' => [
                    'score' => 0.92,
                    'auditRefs' => [
                        ['id' => 'first-contentful-paint'],
                        ['id' => 'largest-contentful-paint'],
                        ['id' => 'total-blocking-time'],
                    ],
                ],
                'accessibility' => [
                    'score' => 0.85,
                    'auditRefs' => [
                        ['id' => 'color-contrast'],
                    ],
                ],
                'best-practices' => [
                    'score' => 0.90,
                    'auditRefs' => [],
                ],
                'seo' => [
                    'score' => 0.95,
                    'auditRefs' => [],
                ],
            ],
            'audits' => [
                'first-contentful-paint' => [
                    'id' => 'first-contentful-paint',
                    'title' => 'First Contentful Paint',
                    'description' => 'First Contentful Paint marks the time at which the first text or image is painted.',
                    'score' => 0.95,
                    'numericValue' => 1500,
                    'displayValue' => '1.5 s',
                    'scoreDisplayMode' => 'numeric',
                ],
                'largest-contentful-paint' => [
                    'id' => 'largest-contentful-paint',
                    'title' => 'Largest Contentful Paint',
                    'description' => 'Largest Contentful Paint marks the time at which the largest text or image is painted.',
                    'score' => 0.85,
                    'numericValue' => 2000,
                    'displayValue' => '2.0 s',
                    'scoreDisplayMode' => 'numeric',
                ],
                'total-blocking-time' => [
                    'id' => 'total-blocking-time',
                    'title' => 'Total Blocking Time',
                    'description' => 'Sum of all time periods between FCP and Time to Interactive.',
                    'score' => 0.90,
                    'numericValue' => 200,
                    'displayValue' => '200 ms',
                    'scoreDisplayMode' => 'numeric',
                ],
                'cumulative-layout-shift' => [
                    'id' => 'cumulative-layout-shift',
                    'title' => 'Cumulative Layout Shift',
                    'score' => 0.98,
                    'numericValue' => 0.05,
                    'scoreDisplayMode' => 'numeric',
                ],
                'speed-index' => [
                    'id' => 'speed-index',
                    'title' => 'Speed Index',
                    'score' => 0.88,
                    'numericValue' => 3100,
                    'scoreDisplayMode' => 'numeric',
                ],
                'color-contrast' => [
                    'id' => 'color-contrast',
                    'title' => 'Background and foreground colors have a sufficient contrast ratio',
                    'description' => 'Low-contrast text is difficult or impossible for many users to read.',
                    'score' => 1,
                    'scoreDisplayMode' => 'binary',
                ],
            ],
        ],
    ];
}
