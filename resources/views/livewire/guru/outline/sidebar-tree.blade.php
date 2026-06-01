<aside class="space-y-3">
    <flux:card class="space-y-4">
        <div>
            <div class="font-semibold">Struktur Kegiatan Belajar</div>
            <flux:text>Pilih bagian untuk mengubah isi yang dilihat murid.</flux:text>
        </div>

        <div class="xl:hidden">
            <flux:field>
                <flux:label>Pilih Bagian</flux:label>
                <select wire:change="selectSection($event.target.value)" class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                    @foreach ($tree as $node)
                        <option value="{{ $node['id'] }}" @selected($selectedSection?->id === $node['id'])>{{ $node['title'] }}</option>
                        @foreach ($node['children'] as $child)
                            <option value="{{ $child['id'] }}" @selected($selectedSection?->id === $child['id'])>-- {{ $child['title'] }}</option>
                        @endforeach
                    @endforeach
                </select>
            </flux:field>
        </div>

        <div class="hidden space-y-2 xl:block">
            @forelse ($tree as $node)
                @include('livewire.guru.outline.sidebar-tree-node', ['node' => $node, 'level' => 0])
            @empty
                <flux:text>Belum ada bagian.</flux:text>
            @endforelse
        </div>
    </flux:card>
</aside>
