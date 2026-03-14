<?php

namespace Grezlikowski\PageSpeed\Http\Controllers;

use Grezlikowski\PageSpeed\Models\PageSpeedTest;
use Grezlikowski\PageSpeed\Services\PageSpeedApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PageSpeedController extends Controller
{
    public function __construct(
        private readonly PageSpeedApiService $pageSpeedService,
    ) {}

    /**
     * Display the PageSpeed dashboard.
     */
    public function index(): View
    {
        $tests = PageSpeedTest::query()
            ->select(['id', 'url', 'strategy', 'performance_score', 'accessibility_score', 'best_practices_score', 'seo_score', 'metrics', 'created_at'])
            ->latest()
            ->limit((int) config('page-speed.history_limit', 50))
            ->get();

        return view('page-speed::dashboard', [
            'tests' => $tests,
            'hasApiKey' => $this->pageSpeedService->hasApiKey(),
            'testableUrls' => $this->pageSpeedService->getTestableUrls(),
        ]);
    }

    /**
     * Display a single PageSpeed test result.
     */
    public function show(int $id): View
    {
        $test = PageSpeedTest::query()->findOrFail($id);

        return view('page-speed::show', [
            'test' => $test,
            'audits' => $this->pageSpeedService->extractAudits($test),
            'screenshot' => $test->raw_response['lighthouseResult']['audits']['final-screenshot']['details']['data'] ?? null,
        ]);
    }

    /**
     * Run a PageSpeed test.
     */
    public function runTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'strategy' => ['required', 'in:mobile,desktop'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['in:performance,accessibility,best-practices,seo'],
        ]);

        try {
            $results = $this->pageSpeedService->runTests(
                $validated['url'],
                [$validated['strategy']],
                $validated['categories'] ?? [],
            );

            $lastTest = end($results);

            return response()->json([
                'message' => 'Test completed successfully.',
                'test' => [
                    'id' => $lastTest->id,
                    'url' => $lastTest->url,
                    'strategy' => $lastTest->strategy,
                    'performance_score' => $lastTest->performance_score,
                    'accessibility_score' => $lastTest->accessibility_score,
                    'best_practices_score' => $lastTest->best_practices_score,
                    'seo_score' => $lastTest->seo_score,
                    'metrics' => $lastTest->metrics,
                    'created_at' => $lastTest->created_at->format('Y-m-d H:i'),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete a PageSpeed test result.
     */
    public function destroy(int $id): JsonResponse
    {
        PageSpeedTest::query()->findOrFail($id)->delete();

        return response()->json(['message' => 'Test deleted.']);
    }
}
