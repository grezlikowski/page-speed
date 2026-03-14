<?php

use Grezlikowski\PageSpeed\Models\PageSpeedTest;

it('can create a page speed test', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'performance_score' => 85,
        'accessibility_score' => 90,
        'best_practices_score' => 95,
        'seo_score' => 100,
        'metrics' => [
            'first_contentful_paint' => 1.2,
            'largest_contentful_paint' => 2.5,
            'total_blocking_time' => 150,
            'cumulative_layout_shift' => 0.05,
            'speed_index' => 3.1,
        ],
        'raw_response' => ['lighthouseResult' => ['categories' => []]],
    ]);

    expect($test)->toBeInstanceOf(PageSpeedTest::class)
        ->and($test->id)->toBeInt()
        ->and($test->url)->toBe('https://example.com')
        ->and($test->strategy)->toBe('mobile');
});

it('has correct fillable attributes', function () {
    $model = new PageSpeedTest();

    expect($model->getFillable())->toBe([
        'url',
        'strategy',
        'performance_score',
        'accessibility_score',
        'best_practices_score',
        'seo_score',
        'metrics',
        'raw_response',
    ]);
});

it('casts scores to integers', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'desktop',
        'performance_score' => 85,
        'accessibility_score' => 90,
        'best_practices_score' => 95,
        'seo_score' => 100,
    ]);

    $test->refresh();

    expect($test->performance_score)->toBeInt()
        ->and($test->accessibility_score)->toBeInt()
        ->and($test->best_practices_score)->toBeInt()
        ->and($test->seo_score)->toBeInt();
});

it('casts metrics to array', function () {
    $metrics = [
        'first_contentful_paint' => 1.2,
        'largest_contentful_paint' => 2.5,
    ];

    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'metrics' => $metrics,
    ]);

    $test->refresh();

    expect($test->metrics)->toBeArray()
        ->and($test->metrics['first_contentful_paint'])->toBe(1.2)
        ->and($test->metrics['largest_contentful_paint'])->toBe(2.5);
});

it('casts raw_response to array', function () {
    $raw = ['lighthouseResult' => ['audits' => []]];

    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'raw_response' => $raw,
    ]);

    $test->refresh();

    expect($test->raw_response)->toBeArray()
        ->and($test->raw_response['lighthouseResult']['audits'])->toBe([]);
});

it('allows nullable scores', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
    ]);

    $test->refresh();

    expect($test->performance_score)->toBeNull()
        ->and($test->accessibility_score)->toBeNull()
        ->and($test->best_practices_score)->toBeNull()
        ->and($test->seo_score)->toBeNull();
});

it('uses the correct table', function () {
    $test = new PageSpeedTest();

    expect($test->getTable())->toBe('page_speed_tests');
});
