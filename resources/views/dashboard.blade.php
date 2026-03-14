@extends('page-speed::layout')

@section('title', 'Dashboard')

@section('content')
<div x-data="pageSpeedDashboard()" class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Page Speed</h1>
            <p class="mt-1 text-sm text-gray-500">
                Test your website speed with Google PageSpeed Insights API
            </p>
        </div>
    </div>

    {{-- No API Key Warning --}}
    @unless($hasApiKey)
        <div class="rounded-lg border border-gray-200 bg-white p-6 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            <p class="mt-3 font-medium">API Key Not Configured</p>
            <p class="mt-1 text-sm text-gray-500">
                Set <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">GOOGLE_PAGESPEED_API_KEY</code> in your <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">.env</code> file to start using Page Speed tests.
            </p>
        </div>
    @endunless

    {{-- Test Form --}}
    @if($hasApiKey)
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex flex-wrap items-end gap-3">
                {{-- URL Select --}}
                <div class="min-w-[250px] flex-1">
                    <label for="url" class="mb-1 block text-xs font-medium text-gray-700">URL</label>
                    <select
                        id="url"
                        x-model="selectedUrl"
                        :disabled="isRunning"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 disabled:opacity-50"
                    >
                        @foreach($testableUrls as $urlItem)
                            <option value="{{ $urlItem['url'] }}">{{ $urlItem['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Strategy Toggle --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-700">Strategy</label>
                    <div class="inline-flex rounded-md border border-gray-300 bg-white">
                        <button
                            type="button"
                            @click="strategy = 'mobile'"
                            :disabled="isRunning"
                            :class="strategy === 'mobile' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50'"
                            class="inline-flex items-center gap-1.5 rounded-l-md px-3 py-2 text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                            Mobile
                        </button>
                        <button
                            type="button"
                            @click="strategy = 'desktop'"
                            :disabled="isRunning"
                            :class="strategy === 'desktop' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50'"
                            class="inline-flex items-center gap-1.5 rounded-r-md px-3 py-2 text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />
                            </svg>
                            Desktop
                        </button>
                    </div>
                </div>

                {{-- Categories Select --}}
                <div class="min-w-[170px]">
                    <label for="categories" class="mb-1 block text-xs font-medium text-gray-700">Test</label>
                    <select
                        id="categories"
                        x-model="selectedCategory"
                        :disabled="isRunning"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 disabled:opacity-50"
                    >
                        <option value="all">All Categories</option>
                        <option value="performance">Performance</option>
                        <option value="accessibility">Accessibility</option>
                        <option value="best-practices">Best Practices</option>
                        <option value="seo">SEO</option>
                    </select>
                </div>

                {{-- Run Button --}}
                <button
                    type="button"
                    @click="runTest()"
                    :disabled="isRunning || !selectedUrl"
                    class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <template x-if="isRunning">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!isRunning">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                    </template>
                    <span x-text="isRunning ? 'Running...' : 'Run Test'"></span>
                </button>
            </div>

            {{-- Error --}}
            <p x-show="error" x-text="error" class="mt-3 text-sm text-red-600"></p>
        </div>
    @endif

    {{-- Empty State --}}
    @if($hasApiKey && $tests->isEmpty())
        <div class="flex flex-col items-center justify-center gap-4 py-16 text-center">
            <div class="rounded-full bg-gray-100 p-4">
                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium">No test results yet</h3>
                <p class="mt-1 text-sm text-gray-500">Run your first page speed test to see results here.</p>
            </div>
        </div>
    @endif

    {{-- Latest Result --}}
    @if($tests->isNotEmpty())
        <div x-show="latestTest" class="rounded-lg border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold">Latest Result</h2>
                    <p class="mt-0.5 text-sm text-gray-500">
                        {{ $tests->first()->url }} &middot; {{ $tests->first()->strategy }} &middot; {{ $tests->first()->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-gray-200 px-2.5 py-0.5 text-xs font-medium">
                        {{ $tests->first()->strategy === 'mobile' ? 'Mobile' : 'Desktop' }}
                    </span>
                    <a
                        href="{{ route('page-speed.show', $tests->first()->id) }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                        Details
                    </a>
                </div>
            </div>
            <div class="p-6">
                {{-- Score Cards --}}
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @include('page-speed::components.score-card', ['label' => 'Performance', 'score' => $tests->first()->performance_score])
                    @include('page-speed::components.score-card', ['label' => 'Accessibility', 'score' => $tests->first()->accessibility_score])
                    @include('page-speed::components.score-card', ['label' => 'Best Practices', 'score' => $tests->first()->best_practices_score])
                    @include('page-speed::components.score-card', ['label' => 'SEO', 'score' => $tests->first()->seo_score])
                </div>

                {{-- Metrics --}}
                @if($tests->first()->metrics)
                    <div class="mt-6">
                        <h4 class="mb-3 text-sm font-medium text-gray-700">Core Web Vitals</h4>
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                            @include('page-speed::components.metric-card', ['label' => 'FCP', 'value' => number_format($tests->first()->metrics['first_contentful_paint'], 1) . 's'])
                            @include('page-speed::components.metric-card', ['label' => 'LCP', 'value' => number_format($tests->first()->metrics['largest_contentful_paint'], 1) . 's'])
                            @include('page-speed::components.metric-card', ['label' => 'TBT', 'value' => number_format($tests->first()->metrics['total_blocking_time'], 0) . 'ms'])
                            @include('page-speed::components.metric-card', ['label' => 'CLS', 'value' => number_format($tests->first()->metrics['cumulative_layout_shift'], 3)])
                            @include('page-speed::components.metric-card', ['label' => 'SI', 'value' => number_format($tests->first()->metrics['speed_index'], 1) . 's'])
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- History Table --}}
        @if($tests->count() > 1)
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-base font-semibold">Test History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Strategy</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Perf.</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">A11y</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">BP</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">SEO</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($tests->slice(1) as $test)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-600">{{ $test->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="max-w-[200px] truncate px-6 py-3 text-sm text-gray-600" title="{{ $test->url }}">{{ $test->url }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center rounded-full border border-gray-200 px-2 py-0.5 text-xs font-medium">
                                            {{ $test->strategy === 'mobile' ? 'Mobile' : 'Desktop' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="font-semibold @include('page-speed::components.score-color-class', ['score' => $test->performance_score])">
                                            {{ $test->performance_score ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="font-semibold @include('page-speed::components.score-color-class', ['score' => $test->accessibility_score])">
                                            {{ $test->accessibility_score ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="font-semibold @include('page-speed::components.score-color-class', ['score' => $test->best_practices_score])">
                                            {{ $test->best_practices_score ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="font-semibold @include('page-speed::components.score-color-class', ['score' => $test->seo_score])">
                                            {{ $test->seo_score ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a
                                            href="{{ route('page-speed.show', $test->id) }}"
                                            class="text-sm font-medium text-gray-600 hover:text-gray-900"
                                        >
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
function pageSpeedDashboard() {
    return {
        selectedUrl: '{{ $testableUrls[0]["url"] ?? "" }}',
        strategy: '{{ config("page-speed.default_strategy", "mobile") }}',
        selectedCategory: 'all',
        isRunning: false,
        error: '',
        latestTest: @json($tests->first()),

        async runTest() {
            this.isRunning = true;
            this.error = '';

            try {
                const response = await fetch('{{ route("page-speed.run") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        url: this.selectedUrl,
                        strategy: this.strategy,
                        categories: this.selectedCategory === 'all' ? [] : [this.selectedCategory],
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.error = data.message || 'An error occurred during the test.';
                    return;
                }

                // Reload page to show updated results
                window.location.reload();
            } catch (e) {
                this.error = 'A connection error occurred. Please try again.';
            } finally {
                this.isRunning = false;
            }
        }
    };
}
</script>
@endsection
