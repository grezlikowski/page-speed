@php
    use Grezlikowski\PageSpeed\Helpers\AuditFormatter;
@endphp

<div class="space-y-3">
    @foreach($items as $item)
        @php $itemType = $item['type'] ?? 'text'; @endphp

        @if($itemType === 'node')
            <div class="text-xs">
                <span class="text-gray-500">{{ $item['nodeLabel'] ?? $item['selector'] ?? '' }}</span>
                @if($item['snippet'] ?? null)
                    <div class="mt-0.5 font-mono text-gray-700">{{ $item['snippet'] }}</div>
                @endif
            </div>
        @elseif($itemType === 'text')
            <p class="text-xs text-gray-600">{{ $item['text'] ?? '' }}</p>
        @elseif($itemType === 'section-table' && !empty($item['headings']) && !empty($item['items']))
            <div class="space-y-1">
                @if($item['title'] ?? null)
                    <h5 class="text-xs font-semibold underline">{{ $item['title'] }}</h5>
                @endif
                @if($item['description'] ?? null)
                    <p class="text-xs text-gray-500">{{ preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $item['description']) }}</p>
                @endif
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-200">
                                @foreach($item['headings'] as $heading)
                                    <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $heading['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['items'] as $row)
                                <tr class="border-b border-gray-100 last:border-0">
                                    @foreach($item['headings'] as $heading)
                                        <td class="max-w-[256px] truncate px-2 py-1.5">
                                            @php $cellValue = $row[$heading['key']] ?? null; @endphp
                                            @if(is_array($cellValue) && (($cellValue['type'] ?? '') === 'node'))
                                                <span class="text-gray-500">{{ $cellValue['nodeLabel'] ?? $cellValue['selector'] ?? '' }}</span>
                                                @if($cellValue['snippet'] ?? null)
                                                    <div class="font-mono text-gray-700">{{ $cellValue['snippet'] }}</div>
                                                @endif
                                            @elseif(is_string($cellValue) && (str_starts_with($cellValue, 'http://') || str_starts_with($cellValue, 'https://')))
                                                <a href="{{ $cellValue }}" target="_blank" rel="noopener noreferrer" class="text-gray-700 hover:underline">
                                                    {{ preg_replace('#^https?://[^/]+#', '', $cellValue) ?: '/' }}
                                                </a>
                                            @else
                                                {{ AuditFormatter::formatCellValue($cellValue, $heading['valueType'] ?? 'text') }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($itemType === 'section-text')
            <div class="space-y-0.5">
                @if($item['title'] ?? null)
                    <h5 class="text-xs font-semibold underline">{{ $item['title'] }}</h5>
                @endif
                @if($item['description'] ?? null)
                    <p class="text-xs text-gray-500">{{ preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $item['description']) }}</p>
                @endif
                @if($item['text'] ?? null)
                    <p class="text-xs">{{ $item['text'] }}</p>
                @endif
            </div>
        @elseif($itemType === 'embedded-table' && !empty($item['headings']) && !empty($item['items']))
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200">
                            @foreach($item['headings'] as $heading)
                                <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $heading['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['items'] as $row)
                            <tr class="border-b border-gray-100 last:border-0">
                                @foreach($item['headings'] as $heading)
                                    <td class="max-w-[256px] truncate px-2 py-1.5">
                                        {{ AuditFormatter::formatCellValue($row[$heading['key']] ?? null, $heading['valueType'] ?? 'text') }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
</div>
