<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $learningUnit->title }}</flux:heading>
        <flux:text>{{ $learningUnit->module->title }}</flux:text>
    </div>
    @if ($learningUnit->objectives)
        <flux:callout>{{ $learningUnit->objectives }}</flux:callout>
    @endif
    <section class="space-y-3">
        <flux:heading>Materi</flux:heading>
        @foreach ($learningUnit->materials as $material)
            <flux:card wire:key="unit-material-{{ $material->id }}"><div class="font-semibold">{{ $material->title }}</div><p class="mt-2 text-sm leading-6">{{ $material->content }}</p></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Aktivitas Interaktif</flux:heading>
        @foreach ($learningUnit->activities as $activity)
            <flux:card wire:key="unit-activity-{{ $activity->id }}"><div class="font-semibold">{{ $activity->title }}</div><flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text><flux:button class="mt-3" size="sm" :href="route('murid.activities.show', $activity)" wire:navigate>Kerjakan</flux:button></flux:card>
        @endforeach
    </section>
</div>
