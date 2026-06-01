<div class="space-y-6">
    @include('livewire.guru.outline.header')

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_1fr]">
        @include('livewire.guru.outline.sidebar-tree')
        @include('livewire.guru.outline.editor-panel')
    </div>

    @include('livewire.guru.outline.add-section-modal')
    @include('livewire.guru.outline.media-section')
</div>
