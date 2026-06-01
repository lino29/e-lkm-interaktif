<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Outline KB</flux:heading>
            <flux:text>{{ $learningUnit->title }} - {{ $learningUnit->module->title }}</flux:text>
        </div>
        <flux:button type="button" variant="primary" wire:click="regenerate">Generate Outline OITLINE</flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="space-y-4">
        @foreach ($rootSections as $section)
            <flux:card wire:key="outline-section-{{ $section->id }}" class="space-y-4">
                @include('livewire.guru.partials.outline-section-form', ['section' => $section])

                @if ($section->children->isNotEmpty())
                    <div class="ml-4 space-y-3 border-l pl-4">
                        @foreach ($section->children as $child)
                            <div class="rounded-lg border p-4" wire:key="outline-child-section-{{ $child->id }}">
                                @include('livewire.guru.partials.outline-section-form', ['section' => $child])
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        @endforeach
    </div>
</div>
