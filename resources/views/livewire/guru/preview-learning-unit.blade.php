<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Preview Murid: {{ $learningUnit->title }}</flux:heading>
            <flux:text>{{ $learningUnit->module->title }}</flux:text>
        </div>
        <flux:button :href="route(auth()->user()->hasRole('admin') ? 'admin.learning-units.outline' : 'guru.learning-units.outline', $learningUnit)" wire:navigate>Kembali ke Outline</flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
        <aside>
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
