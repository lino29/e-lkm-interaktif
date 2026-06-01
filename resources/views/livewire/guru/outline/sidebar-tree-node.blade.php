<div class="space-y-2" style="margin-left: {{ $level * 14 }}px">
    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800 {{ $selectedSection?->id === $node['id'] ? 'bg-blue-50 ring-1 ring-blue-200 dark:bg-blue-950/30 dark:ring-blue-900' : 'bg-white dark:bg-zinc-900' }}">
        <div class="flex items-start justify-between gap-3">
            <button type="button" class="min-w-0 text-left" wire:click="selectSection({{ $node['id'] }})">
                <div class="truncate text-sm font-medium">{{ $node['title'] }}</div>
                <div class="mt-1 flex flex-wrap gap-1">
                    <flux:badge size="sm">{{ $node['label'] }}</flux:badge>
                    @unless ($node['is_visible'])
                        <flux:badge size="sm" color="zinc">Disembunyikan</flux:badge>
                    @endunless
                    @if ($node['is_required'])
                        <flux:badge size="sm" color="amber">Wajib</flux:badge>
                    @endif
                    @if ($node['is_locked'])
                        <flux:badge size="sm" color="red">Terkunci</flux:badge>
                    @endif
                </div>
            </button>

            <details class="relative">
                <summary class="list-none rounded-md px-2 py-1 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">...</summary>
                <div class="absolute right-0 z-20 mt-2 w-48 rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                    <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:click="openAddSectionModal({{ $node['id'] }})">Tambah Subbagian</button>
                    <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:click="moveUp({{ $node['id'] }})">Pindah ke Atas</button>
                    <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:click="moveDown({{ $node['id'] }})">Pindah ke Bawah</button>
                    <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:click="duplicateSection({{ $node['id'] }})">Duplikasi Bagian</button>
                    <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:click="toggleVisibility({{ $node['id'] }})">{{ $node['is_visible'] ? 'Sembunyikan dari Murid' : 'Tampilkan untuk Murid' }}</button>
                </div>
            </details>
        </div>
    </div>

    @foreach ($node['children'] as $child)
        @include('livewire.guru.outline.sidebar-tree-node', ['node' => $child, 'level' => $level + 1])
    @endforeach
</div>
