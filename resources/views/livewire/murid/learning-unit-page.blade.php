<div class="space-y-6">
    <x-elkm.page-header 
        title="{{ $learningUnit->title }}" 
        subtitle="{{ $learningUnit->module->title }}" 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('murid.modules') }}">Modul</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $learningUnit->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[280px_1fr]">
        <aside class="space-y-3 order-2 lg:order-1">
            <div class="hidden lg:block">
                <x-learning.unit-outline
                    :sections="$learningUnit->rootSections"
                    :active-section-id="$activeSectionId"
                />
            </div>
        </aside>

        <main class="order-1 lg:order-2 space-y-6">
            <div class="block lg:hidden">
                <flux:select wire:model.live="activeSectionId" label="Struktur Kegiatan Belajar">
                    @foreach($this->flatVisibleSections as $section)
                        <flux:select.option value="{{ $section->id }}">
                            {!! str_repeat('&nbsp;&nbsp;&nbsp;', $section->parent_id ? 1 : 0) !!}{{ $section->title }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <x-learning.unit-section-renderer
                :section="$this->activeSection"
                :learning-unit="$learningUnit"
                :activity-statuses="$activityStatuses"
            />

            <div class="flex items-center justify-between border-t border-zinc-200 pt-6 mt-8 dark:border-zinc-700">
                <div>
                    @if($this->previousSection)
                        <flux:button variant="ghost" wire:click="openSection({{ $this->previousSection->id }})" icon="chevron-left">
                            <div class="flex flex-col text-left">
                                <span class="text-xs font-normal text-zinc-500">Sebelumnya</span>
                                <span>{{ Str::limit($this->previousSection->title, 25) }}</span>
                            </div>
                        </flux:button>
                    @endif
                </div>
                <div>
                    @if($this->nextSection)
                        <flux:button variant="ghost" wire:click="openSection({{ $this->nextSection->id }})" icon-trailing="chevron-right">
                            <div class="flex flex-col text-right">
                                <span class="text-xs font-normal text-zinc-500">Selanjutnya</span>
                                <span>{{ Str::limit($this->nextSection->title, 25) }}</span>
                            </div>
                        </flux:button>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>
