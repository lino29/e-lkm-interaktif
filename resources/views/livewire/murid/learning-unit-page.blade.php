<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $learningUnit->title }}</flux:heading>
        <flux:text>{{ $learningUnit->module->title }}</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
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
