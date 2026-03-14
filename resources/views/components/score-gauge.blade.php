@php
    $scoreRingColor = match(true) {
        $score === null => 'stroke-gray-300',
        $score >= 90 => 'stroke-green-500',
        $score >= 50 => 'stroke-orange-500',
        default => 'stroke-red-500',
    };
    $scoreTextColor = match(true) {
        $score === null => 'text-gray-400',
        $score >= 90 => 'text-green-600',
        $score >= 50 => 'text-orange-500',
        default => 'text-red-500',
    };
    $bgClass = match(true) {
        $score === null => 'bg-gray-50',
        $score >= 90 => 'bg-green-50',
        $score >= 50 => 'bg-orange-50',
        default => 'bg-red-50',
    };
@endphp

<div class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 p-6 {{ $bgClass }}">
    <div class="relative h-24 w-24">
        <svg class="h-24 w-24 -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" fill="none" stroke-width="6" class="stroke-gray-200" />
            <circle cx="50" cy="50" r="45" fill="none" stroke-width="6" stroke-linecap="round"
                class="{{ $scoreRingColor }}"
                stroke-dasharray="{{ ($score ?? 0) * 2.827 }} 282.7"
            />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-2xl font-bold {{ $scoreTextColor }}">{{ $score ?? '—' }}</span>
        </div>
    </div>
    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
</div>
