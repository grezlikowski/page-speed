@php
    $dotColor = match(true) {
        $score === null => 'bg-gray-400',
        $score >= 0.9 => 'bg-green-500',
        $score >= 0.5 => 'bg-orange-500',
        default => 'bg-red-500',
    };
@endphp

<div x-data="{ open: false }" class="border-b border-gray-100 last:border-0">
    <button
        type="button"
        @click="open = !open"
        class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left hover:bg-gray-50"
    >
        <div class="flex-shrink-0">
            @if(in_array($audit['scoreDisplayMode'], ['binary', 'numeric']))
                <div class="h-2.5 w-2.5 rounded-full {{ $dotColor }}"></div>
            @else
                <div class="h-2.5 w-2.5 rounded-full bg-gray-300"></div>
            @endif
        </div>
        <span class="flex-1 truncate text-sm font-medium">{{ $audit['title'] }}</span>
        @if($audit['displayValue'] ?? null)
            <span class="flex-shrink-0 rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-600">{{ $audit['displayValue'] }}</span>
        @endif
        <svg
            class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform"
            :class="open ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-collapse class="px-3 pb-3">
        <p class="mb-3 text-xs text-gray-500">
            {{ preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $audit['description'] ?? '') }}
        </p>

        @if(isset($audit['details']))
            @php $details = $audit['details']; @endphp

            @if(in_array($details['type'] ?? '', ['table', 'opportunity']) && !empty($details['headings']))
                @include('page-speed::components.audit-details-table', ['details' => $details])
            @elseif(($details['type'] ?? '') === 'checklist' && !empty($details['items']))
                @include('page-speed::components.audit-details-checklist', ['items' => $details['items']])
            @elseif(($details['type'] ?? '') === 'list' && !empty($details['items']))
                @include('page-speed::components.audit-details-list', ['items' => $details['items']])
            @endif
        @endif
    </div>
</div>
