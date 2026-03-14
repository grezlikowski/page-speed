@php
    use Grezlikowski\PageSpeed\Helpers\AuditFormatter;
@endphp

<div class="overflow-x-auto">
    @if($details['overallSavingsMs'] ?? null)
        <div class="mb-2 text-xs font-medium text-orange-600">
            Estimated savings: {{ AuditFormatter::formatMs($details['overallSavingsMs']) }}
        </div>
    @endif
    <table class="w-full text-xs">
        <thead>
            <tr class="border-b border-gray-200">
                @foreach($details['headings'] as $heading)
                    <th class="px-2 py-1.5 text-left font-medium text-gray-500">{{ $heading['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($details['items'] ?? [] as $item)
                <tr class="border-b border-gray-100 last:border-0">
                    @foreach($details['headings'] as $heading)
                        <td class="max-w-[256px] truncate px-2 py-1.5">
                            @php
                                $key = $heading['key'];
                                $value = $item[$key] ?? null;
                                $valueType = $heading['valueType'] ?? 'text';
                            @endphp

                            @if($valueType === 'thumbnail' && $value)
                                <img src="{{ $value }}" class="h-6 w-6 rounded object-cover" alt="" />
                            @elseif(is_array($value) && (($value['type'] ?? '') === 'node'))
                                <span class="text-gray-500">{{ $value['nodeLabel'] ?? $value['selector'] ?? '' }}</span>
                                @if($value['snippet'] ?? null)
                                    <div class="font-mono text-xs text-gray-700">{{ $value['snippet'] }}</div>
                                @endif
                            @elseif(is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')))
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="text-gray-700 hover:underline" title="{{ $value }}">
                                    {{ preg_replace('#^https?://[^/]+#', '', $value) ?: '/' }}
                                </a>
                            @else
                                {{ AuditFormatter::formatCellValue($value, $valueType) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
