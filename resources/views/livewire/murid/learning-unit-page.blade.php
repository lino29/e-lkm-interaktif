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

    @if ($discussions->isNotEmpty())
        <section class="space-y-3">
            <flux:heading>Diskusi Terbaru</flux:heading>
            @foreach ($discussions as $discussion)
                <flux:card wire:key="unit-discussion-preview-{{ $discussion->id }}">
                    <div class="font-semibold">{{ $discussion->user->name }}</div>
                    <p class="mt-2 text-sm">{{ $discussion->body }}</p>
                    <div class="mt-4 space-y-2">
                        @foreach ($discussion->replies as $reply)
                            <div class="border-l-2 pl-3 text-sm" wire:key="unit-discussion-preview-reply-{{ $reply->id }}">
                                <div class="font-medium">{{ $reply->user->name }}</div>
                                <p>{{ $reply->body }}</p>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endforeach
        </section>
    @endif
</div>
