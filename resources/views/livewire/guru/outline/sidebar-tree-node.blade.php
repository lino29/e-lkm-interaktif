<div class="space-y-2" x-data="{ expanded: true }" wire:key="tree-node-{{ $node['id'] }}">
    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800 transition-colors {{ $selectedSection?->id === $node['id'] ? 'bg-elkm-surface-2 ring-1 ring-elkm-primary border-elkm-primary/30 dark:bg-zinc-800 dark:ring-elkm-primary dark:border-elkm-primary/50' : 'bg-white hover:border-zinc-300 dark:bg-zinc-900 dark:hover:border-zinc-700' }}">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2 flex-1 min-w-0">
                @if (count($node['children']) > 0)
                    <button type="button" @click="expanded = !expanded" class="mt-0.5 shrink-0 text-zinc-400 hover:text-zinc-600 transition-transform duration-200" :class="{'rotate-90': expanded}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @elseif ($level > 0)
                    <div class="size-4 shrink-0 border-l-2 border-b-2 border-zinc-200 dark:border-zinc-700 rounded-bl-sm mt-1 -ml-1"></div>
                @endif
                
                <button type="button" class="min-w-0 text-left flex-1" wire:click="selectSection({{ $node['id'] }})">
                    <div class="truncate text-sm font-medium {{ $selectedSection?->id === $node['id'] ? 'text-elkm-primary' : 'text-zinc-700 dark:text-zinc-200' }}">{{ $node['title'] }}</div>
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
            </div>

            <flux:dropdown>
                <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" class="-my-1 -mr-2" />
                <flux:navmenu>
                    <flux:navmenu.item wire:click="openAddSectionModal({{ $node['id'] }})">Tambah Subbagian</flux:navmenu.item>
                    <flux:navmenu.item wire:click="duplicateSection({{ $node['id'] }})">Duplikasi Bagian</flux:navmenu.item>
                    <flux:navmenu.item wire:click="toggleVisibility({{ $node['id'] }})">{{ $node['is_visible'] ? 'Sembunyikan' : 'Tampilkan' }}</flux:navmenu.item>
                    <flux:menu.separator />
                    <flux:navmenu.item wire:click="moveUp({{ $node['id'] }})">Pindah Atas</flux:navmenu.item>
                    <flux:navmenu.item wire:click="moveDown({{ $node['id'] }})">Pindah Bawah</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
        </div>
    </div>

    @if (count($node['children']) > 0)
        <div class="pl-4 border-l-2 border-zinc-100 dark:border-zinc-800 ml-2 space-y-2" x-show="expanded" x-collapse x-cloak>
            @foreach ($node['children'] as $child)
                @include('livewire.guru.outline.sidebar-tree-node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
