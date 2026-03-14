<?php

namespace Grezlikowski\PageSpeed\Services;

use Grezlikowski\PageSpeed\Models\PageSpeedTest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use RuntimeException;

class PageSpeedApiService
{
    private const API_URL = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    /**
     * @var array<string, string>
     */
    private const CATEGORY_MAP = [
        'performance' => 'PERFORMANCE',
        'accessibility' => 'ACCESSIBILITY',
        'best-practices' => 'BEST_PRACTICES',
        'seo' => 'SEO',
    ];

    /**
     * Run a PageSpeed test for the given URL and strategy.
     *
     * @param  array<int, string>  $categories
     */
    public function runTest(string $url, string $strategy, array $categories = [], ?int $userId = null): PageSpeedTest
    {
        $apiKey = $this->getApiKey();
        $categoriesToTest = ! empty($categories) ? $categories : array_keys(self::CATEGORY_MAP);
        $timeout = (int) config('page-speed.timeout', 60);

        $query = http_build_query([
            'url' => $url,
            'strategy' => $strategy,
            'key' => $apiKey,
            'locale' => app()->getLocale(),
        ]);

        foreach ($categoriesToTest as $category) {
            $apiValue = self::CATEGORY_MAP[$category] ?? strtoupper(str_replace('-', '_', $category));
            $query .= '&category=' . urlencode($apiValue);
        }

        $response = Http::timeout($timeout)->get(self::API_URL . '?' . $query);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Unknown error');

            throw new RuntimeException("PageSpeed test failed: {$error}");
        }

        $data = $response->json();

        return $this->storeResult($data, $url, $strategy, $userId);
    }

    /**
     * Run tests for multiple strategies.
     *
     * @param  array<int, string>  $strategies
     * @param  array<int, string>  $categories
     * @return array<int, PageSpeedTest>
     */
    public function runTests(string $url, array $strategies, array $categories = [], ?int $userId = null): array
    {
        $results = [];

        foreach ($strategies as $strategy) {
            $results[] = $this->runTest($url, $strategy, $categories, $userId);
        }

        return $results;
    }

    /**
     * Get the configured API key.
     */
    public function getApiKey(): string
    {
        $apiKey = config('page-speed.api_key', '');

        if (blank($apiKey)) {
            throw new RuntimeException('Google PageSpeed Insights API key is not configured. Set GOOGLE_PAGESPEED_API_KEY in your .env file.');
        }

        return $apiKey;
    }

    /**
     * Check if the API key is configured.
     */
    public function hasApiKey(): bool
    {
        return filled(config('page-speed.api_key', ''));
    }

    /**
     * Get testable URLs from the application routes.
     *
     * @return array<int, array{url: string, label: string}>
     */
    public function getTestableUrls(): array
    {
        $urls = [];

        $appUrl = rtrim(config('app.url', 'http://localhost'), '/');

        $urls[] = [
            'url' => $appUrl . '/',
            'label' => 'Homepage (/)',
        ];

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            if (! in_array('GET', $route->methods())) {
                continue;
            }

            $uri = $route->uri();

            if (str_starts_with($uri, '_') || str_starts_with($uri, 'api/') || str_starts_with($uri, 'sanctum/')) {
                continue;
            }

            if (str_contains($uri, '{')) {
                continue;
            }

            if ($uri === '/' || $uri === '') {
                continue;
            }

            $name = $route->getName();
            $label = $name ? "{$name} (/{$uri})" : "/{$uri}";

            $urls[] = [
                'url' => $appUrl . '/' . ltrim($uri, '/'),
                'label' => $label,
            ];
        }

        $defaultUrl = config('page-speed.default_url');
        if ($defaultUrl && ! collect($urls)->contains('url', $defaultUrl)) {
            array_unshift($urls, [
                'url' => $defaultUrl,
                'label' => 'Default (' . $defaultUrl . ')',
            ]);
        }

        return $urls;
    }

    /**
     * Extract categorized audits from the raw Lighthouse response.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function extractAudits(PageSpeedTest $test): array
    {
        $raw = $test->raw_response;

        if (! $raw) {
            return [];
        }

        $allAudits = $raw['lighthouseResult']['audits'] ?? [];
        $categoryRefs = $raw['lighthouseResult']['categories'] ?? [];

        $result = [];

        foreach ($categoryRefs as $categoryKey => $category) {
            $auditRefs = $category['auditRefs'] ?? [];
            $items = [];

            foreach ($auditRefs as $ref) {
                $auditId = $ref['id'] ?? null;
                $audit = $allAudits[$auditId] ?? null;

                if (! $audit || ($audit['scoreDisplayMode'] ?? '') === 'notApplicable') {
                    continue;
                }

                $score = $audit['score'] ?? null;
                $details = $audit['details'] ?? null;

                $item = [
                    'id' => $auditId,
                    'title' => $audit['title'] ?? '',
                    'description' => $audit['description'] ?? '',
                    'score' => $score,
                    'displayValue' => $audit['displayValue'] ?? null,
                    'scoreDisplayMode' => $audit['scoreDisplayMode'] ?? 'numeric',
                ];

                if ($details) {
                    $item['details'] = $this->formatAuditDetails($details);
                }

                $items[] = $item;
            }

            $result[$categoryKey] = $items;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeResult(array $data, string $url, string $strategy, ?int $userId): PageSpeedTest
    {
        $categories = $data['lighthouseResult']['categories'] ?? [];
        $audits = $data['lighthouseResult']['audits'] ?? [];

        return PageSpeedTest::query()->create([
            'url' => $url,
            'strategy' => $strategy,
            'performance_score' => isset($categories['performance']) ? (int) round($categories['performance']['score'] * 100) : null,
            'accessibility_score' => isset($categories['accessibility']) ? (int) round($categories['accessibility']['score'] * 100) : null,
            'best_practices_score' => isset($categories['best-practices']) ? (int) round($categories['best-practices']['score'] * 100) : null,
            'seo_score' => isset($categories['seo']) ? (int) round($categories['seo']['score'] * 100) : null,
            'metrics' => [
                'first_contentful_paint' => ($audits['first-contentful-paint']['numericValue'] ?? 0) / 1000,
                'largest_contentful_paint' => ($audits['largest-contentful-paint']['numericValue'] ?? 0) / 1000,
                'total_blocking_time' => $audits['total-blocking-time']['numericValue'] ?? 0,
                'cumulative_layout_shift' => $audits['cumulative-layout-shift']['numericValue'] ?? 0,
                'speed_index' => ($audits['speed-index']['numericValue'] ?? 0) / 1000,
            ],
            'raw_response' => $data,
            'user_id' => $userId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>|null
     */
    private function formatAuditDetails(array $details): ?array
    {
        $type = $details['type'] ?? null;

        if ($type === 'table' || $type === 'opportunity') {
            $headings = $details['headings'] ?? [];
            $items = $details['items'] ?? [];

            if (empty($headings) || empty($items)) {
                return null;
            }

            return [
                'type' => $type,
                'headings' => array_map(fn(array $h) => [
                    'key' => $h['key'] ?? '',
                    'label' => $h['label'] ?? $h['text'] ?? '',
                    'valueType' => $h['valueType'] ?? 'text',
                ], $headings),
                'items' => array_slice(array_map(fn(array $item) => $this->formatDetailItem($item, $headings), $items), 0, 20),
                'overallSavingsMs' => $details['overallSavingsMs'] ?? null,
                'overallSavingsBytes' => $details['overallSavingsBytes'] ?? null,
            ];
        }

        if ($type === 'checklist') {
            $items = $details['items'] ?? [];

            if (empty($items)) {
                return null;
            }

            $rows = [];

            foreach ($items as $item) {
                if (is_array($item)) {
                    $rows[] = [
                        'label' => $item['label'] ?? '',
                        'value' => (bool) ($item['value'] ?? false),
                    ];
                }
            }

            return [
                'type' => 'checklist',
                'items' => $rows,
            ];
        }

        if ($type === 'list') {
            $listItems = $details['items'] ?? [];

            if (empty($listItems)) {
                return null;
            }

            $formattedItems = [];

            foreach ($listItems as $item) {
                $itemType = $item['type'] ?? null;

                if ($itemType === 'node') {
                    $formattedItems[] = [
                        'type' => 'node',
                        'snippet' => $item['snippet'] ?? '',
                        'selector' => $item['selector'] ?? '',
                        'nodeLabel' => $item['nodeLabel'] ?? '',
                    ];
                } elseif (isset($item['node'])) {
                    $formattedItems[] = [
                        'type' => 'node',
                        'snippet' => $item['node']['snippet'] ?? '',
                        'selector' => $item['node']['selector'] ?? '',
                    ];
                } else {
                    $formattedItems[] = [
                        'type' => 'text',
                        'text' => $item['text'] ?? json_encode($item),
                    ];
                }
            }

            return [
                'type' => 'list',
                'items' => array_slice($formattedItems, 0, 20),
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, array<string, mixed>>  $headings
     * @return array<string, mixed>
     */
    private function formatDetailItem(array $item, array $headings): array
    {
        $row = [];

        foreach ($headings as $heading) {
            $key = $heading['key'] ?? '';

            if ($key === '') {
                continue;
            }

            $value = $item[$key] ?? null;

            if (is_array($value)) {
                if (($value['type'] ?? '') === 'node') {
                    $row[$key] = [
                        'type' => 'node',
                        'snippet' => $value['snippet'] ?? '',
                        'selector' => $value['selector'] ?? '',
                        'nodeLabel' => $value['nodeLabel'] ?? '',
                    ];
                } else {
                    $row[$key] = $value['url'] ?? $value['text'] ?? $value['snippet'] ?? $value['value'] ?? json_encode($value);
                }
            } else {
                $row[$key] = $value;
            }
        }

        return $row;
    }
}
