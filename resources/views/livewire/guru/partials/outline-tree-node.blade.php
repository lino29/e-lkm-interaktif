<div class="space-y-2" style="margin-left: {{ $level * 14 }}px">
    <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800 {{ $selectedSection?->id === $node['id'] ? 'bg-blue-50 dark:bg-blue-950/30' : '' }}">
        <div class="flex items-start justify-between gap-3">
            <button type="button" class="min-w-0 text-left" wire:click="selectSection({{ $node['id'] }})">
                <div class="truncate text-sm font-medium">{{ $node['title'] }}</div>
                <div class="mt-1 flex flex-wrap gap-1">
                    <flux:badge size="sm">{{ $node['section_type'] }}</flux:badge>
                    @unless ($node['is_visible'])
                        <flux:badge size="sm" color="zinc">hidden</flux:badge>
                    @endunless
                    @if ($node['is_required'])
                        <flux:badge size="sm" color="amber">required</flux:badge>
                    @endif
                    @if ($node['is_locked'])
                        <flux:badge size="sm" color="red">locked</flux:badge>
                    @endif
                </div>
            </button>

            <div class="flex shrink-0 flex-wrap justify-end gap-1">
                <flux:button type="button" size="sm" variant="ghost" wire:click="createChildSection({{ $node['id'] }})">Child</flux:button>
                <flux:button type="button" size="sm" variant="ghost" wire:click="moveUp({{ $node['id'] }})">Up</flux:button>
                <flux:button type="button" size="sm" variant="ghost" wire:click="moveDown({{ $node['id'] }})">Down</flux:button>
                <flux:button type="button" size="sm" variant="ghost" wire:click="duplicateSection({{ $node['id'] }})">Copy</flux:button>
                <flux:button type="button" size="sm" variant="ghost" wire:click="toggleVisibility({{ $node['id'] }})">{{ $node['is_visible'] ? 'Hide' : 'Show' }}</flux:button>
            </div>
        </div>
    </div>

    @foreach ($node['children'] as $child)
        @include('livewire.guru.partials.outline-tree-node', ['node' => $child, 'level' => $level + 1])
    @endforeach
</div>
