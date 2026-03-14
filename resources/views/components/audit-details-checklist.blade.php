<table class="w-full text-xs">
    <tbody>
        @foreach($items as $item)
            <tr class="border-b border-gray-100 last:border-0">
                <td class="py-1.5 pr-3">
                    @if($item['value'] ?? false)
                        <span class="text-green-600">&#x2714;</span>
                    @else
                        <span class="text-red-500">&#x2718;</span>
                    @endif
                </td>
                <td class="py-1.5">{{ $item['label'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
