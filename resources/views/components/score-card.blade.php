@php
    $bgClass = match(true) {
        $score === null => 'bg-gray-50',
        $score >= 90 => 'bg-green-50',
        $score >= 50 => 'bg-orange-50',
        default => 'bg-red-50',
    };
    $textClass = match(true) {
        $score === null => 'text-gray-400',
        $score >= 90 => 'text-green-600',
        $score >= 50 => 'text-orange-500',
        default => 'text-red-500',
    };
@endphp

<div class="rounded-lg border border-gray-200 p-4 text-center {{ $bgClass }}">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-bold {{ $textClass }}">{{ $score ?? '—' }}</p>
</div>
