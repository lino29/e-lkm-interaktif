@props(['items' => []])

<div class="overflow-hidden rounded-lg border">
    <table class="w-full text-sm">
        <thead class="bg-blue-600 text-white">
            <tr>
                <th class="w-40 px-4 py-3 text-left">Aspek</th>
                <th class="px-4 py-3 text-left">Uraian</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([
                'konsep' => 'Konsep',
                'fakta' => 'Fakta',
                'prosedur' => 'Prosedur',
                'metakognitif' => 'Metakognitif',
            ] as $key => $label)
                <tr class="border-t">
                    <td class="bg-blue-50 px-4 py-3 font-semibold dark:bg-zinc-800">{{ $label }}</td>
                    <td class="px-4 py-3">{{ $items[$key] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
