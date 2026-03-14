<?php

use Grezlikowski\PageSpeed\Models\PageSpeedTest;
use Grezlikowski\PageSpeed\PageSpeedPanel;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    PageSpeedPanel::auth(fn () => true);
});

afterEach(function () {
    PageSpeedPanel::$authUsing = null;
});

it('shows the dashboard', function () {
    $response = $this->get(config('page-speed.path', 'page-speed'));

    $response->assertStatus(200);
    $response->assertViewIs('page-speed::dashboard');
});

it('shows test detail page', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'performance_score' => 85,
        'raw_response' => fakeApiResponse(),
    ]);

    $response = $this->get(config('page-speed.path', 'page-speed').'/'.$test->id);

    $response->assertStatus(200);
    $response->assertViewIs('page-speed::show');
});

it('returns 404 for non-existent test', function () {
    $response = $this->get(config('page-speed.path', 'page-speed').'/99999');

    $response->assertStatus(404);
});

it('runs a test via post request', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response(fakeApiResponse()),
    ]);

    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'url' => 'https://example.com',
        'strategy' => 'mobile',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'test' => [
                'id',
                'url',
                'strategy',
                'performance_score',
                'accessibility_score',
                'best_practices_score',
                'seo_score',
                'metrics',
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('page_speed_tests', [
        'url' => 'https://example.com',
        'strategy' => 'mobile',
    ]);
});

it('validates url is required', function () {
    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'strategy' => 'mobile',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

it('validates strategy is required', function () {
    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'url' => 'https://example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['strategy']);
});

it('validates strategy must be mobile or desktop', function () {
    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'url' => 'https://example.com',
        'strategy' => 'invalid',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['strategy']);
});

it('validates url must be a valid url', function () {
    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'url' => 'not-a-url',
        'strategy' => 'mobile',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

it('returns error when api fails', function () {
    Http::fake([
        'www.googleapis.com/pagespeedonline/*' => Http::response([
            'error' => ['message' => 'API Error'],
        ], 400),
    ]);

    $response = $this->postJson(config('page-speed.path', 'page-speed').'/run', [
        'url' => 'https://example.com',
        'strategy' => 'mobile',
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'PageSpeed test failed: API Error']);
});

it('deletes a test result', function () {
    $test = PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
    ]);

    $response = $this->deleteJson(config('page-speed.path', 'page-speed').'/'.$test->id);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Test deleted.']);

    $this->assertDatabaseMissing('page_speed_tests', ['id' => $test->id]);
});

it('returns 404 when deleting non-existent test', function () {
    $response = $this->deleteJson(config('page-speed.path', 'page-speed').'/99999');

    $response->assertStatus(404);
});

it('blocks access when unauthorized', function () {
    PageSpeedPanel::auth(fn () => false);

    $response = $this->get(config('page-speed.path', 'page-speed'));

    $response->assertStatus(403);
});

it('passes dashboard data to view', function () {
    PageSpeedTest::create([
        'url' => 'https://example.com',
        'strategy' => 'mobile',
        'performance_score' => 90,
    ]);

    $response = $this->get(config('page-speed.path', 'page-speed'));

    $response->assertStatus(200)
        ->assertViewHas('tests')
        ->assertViewHas('hasApiKey')
        ->assertViewHas('testableUrls');
});

function fakeApiResponse(): array
{
    return [
        'lighthouseResult' => [
            'categories' => [
                'performance' => [
                    'score' => 0.92,
                    'auditRefs' => [
                        ['id' => 'first-contentful-paint'],
                    ],
                ],
                'accessibility' => ['score' => 0.85, 'auditRefs' => []],
                'best-practices' => ['score' => 0.90, 'auditRefs' => []],
                'seo' => ['score' => 0.95, 'auditRefs' => []],
            ],
            'audits' => [
                'first-contentful-paint' => [
                    'id' => 'first-contentful-paint',
                    'title' => 'First Contentful Paint',
                    'description' => 'FCP description',
                    'score' => 0.95,
                    'numericValue' => 1500,
                    'displayValue' => '1.5 s',
                    'scoreDisplayMode' => 'numeric',
                ],
                'largest-contentful-paint' => [
                    'id' => 'largest-contentful-paint',
                    'title' => 'Largest Contentful Paint',
                    'score' => 0.85,
                    'numericValue' => 2000,
                    'scoreDisplayMode' => 'numeric',
                ],
                'total-blocking-time' => [
                    'id' => 'total-blocking-time',
                    'title' => 'Total Blocking Time',
                    'score' => 0.90,
                    'numericValue' => 200,
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
            ],
        ],
    ];
}
