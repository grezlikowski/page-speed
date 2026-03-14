@extends('page-speed::layout')

@section('title', 'Test #' . $test->id)

@section('content')
<div class="space-y-6">
    {{-- Back link + Header --}}
    <div>
        <a href="{{ route('page-speed.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to Dashboard
        </a>
        <h1 class="mt-2 text-xl font-semibold">PageSpeed Test Result</h1>
        <div class="mt-0.5 flex flex-wrap items-center gap-2 text-sm text-gray-500">
            <a href="{{ $test->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 hover:underline">
                {{ $test->url }}
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>
            <span>&middot;</span>
            <span class="inline-flex items-center rounded-full border border-gray-200 px-2 py-0.5 text-xs font-medium">
                {{ $test->strategy === 'mobile' ? 'Mobile' : 'Desktop' }}
            </span>
            <span>&middot;</span>
            <span>{{ $test->created_at->format('Y-m-d H:i') }}</span>
        </div>
    </div>

    {{-- Score Gauges --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('page-speed::components.score-gauge', ['label' => 'Performance', 'score' => $test->performance_score])
        @include('page-speed::components.score-gauge', ['label' => 'Accessibility', 'score' => $test->accessibility_score])
        @include('page-speed::components.score-gauge', ['label' => 'Best Practices', 'score' => $test->best_practices_score])
        @include('page-speed::components.score-gauge', ['label' => 'SEO', 'score' => $test->seo_score])
    </div>

    {{-- Core Web Vitals --}}
    @if($test->metrics)
        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-base font-semibold">Core Web Vitals</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">First Contentful Paint</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($test->metrics['first_contentful_paint'], 1) }}s</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Largest Contentful Paint</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($test->metrics['largest_contentful_paint'], 1) }}s</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Total Blocking Time</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($test->metrics['total_blocking_time'], 0) }}ms</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Cumulative Layout Shift</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($test->metrics['cumulative_layout_shift'], 3) }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Speed Index</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($test->metrics['speed_index'], 1) }}s</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Audits by Category --}}
    @php
        $categoryLabels = [
            'performance' => 'Performance',
            'accessibility' => 'Accessibility',
            'best-practices' => 'Best Practices',
            'seo' => 'SEO',
        ];
        $categoryScores = [
            'performance' => $test->performance_score,
            'accessibility' => $test->accessibility_score,
            'best-practices' => $test->best_practices_score,
            'seo' => $test->seo_score,
        ];
        $isFirstCategory = true;
    @endphp

    @foreach($categoryLabels as $categoryKey => $categoryLabel)
        @if(!empty($audits[$categoryKey]))
            @php $isFirst = $isFirstCategory && $screenshot; @endphp

            <div class="{{ $isFirst ? 'grid gap-6 lg:grid-cols-5' : '' }}">
                <div class="{{ $isFirst ? 'lg:col-span-4' : '' }} rounded-lg border border-gray-200 bg-white">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h2 class="text-base font-semibold">{{ $categoryLabel }}</h2>
                        @if($categoryScores[$categoryKey] !== null)
                            @php
                                $scoreColor = match(true) {
                                    $categoryScores[$categoryKey] >= 90 => 'text-green-600',
                                    $categoryScores[$categoryKey] >= 50 => 'text-orange-500',
                                    default => 'text-red-500',
                                };
                            @endphp
                            <span class="text-lg font-bold {{ $scoreColor }}">{{ $categoryScores[$categoryKey] }}</span>
                        @endif
                    </div>
                    <div class="p-4">
                        @foreach($audits[$categoryKey] as $audit)
                            @include('page-speed::components.audit-item', ['audit' => $audit, 'score' => $audit['score']])
                        @endforeach
                    </div>
                </div>

                @if($isFirst)
                    <div class="hidden items-start justify-center lg:col-span-1 lg:flex">
                        <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                            <img src="{{ $screenshot }}" alt="Page screenshot" class="h-auto w-full max-w-[200px]" />
                        </div>
                    </div>
                @endif
            </div>

            @php $isFirstCategory = false; @endphp
        @endif
    @endforeach
</div>
@endsection
