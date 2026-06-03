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
        <aside class="space-y-3">
            <x-learning.unit-outline
                :sections="$learningUnit->rootSections"
                :active-section-id="$activeSectionId"
            />
        </aside>

        <main>
            <x-learning.unit-section-renderer
                :section="$this->activeSection"
                :learning-unit="$learningUnit"
                :activity-statuses="$activityStatuses"
            />
        </main>
    </div>
</div>
