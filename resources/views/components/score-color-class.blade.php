@php
    echo match(true) {
        $score === null => 'text-gray-400',
        $score >= 90 => 'text-green-600',
        $score >= 50 => 'text-orange-500',
        default => 'text-red-500',
    };
@endphp