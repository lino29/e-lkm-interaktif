@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
    $statusData = $activity ? ($activityStatuses[$activity->id] ?? ['status' => 'belum_mulai', 'is_locked' => false]) : [];
    $status = $statusData['status'] ?? 'not_found';
    $isLocked = $statusData['is_locked'] ?? false;
@endphp

@if (! $activity)
    <div class="rounded-lg border p-4 text-sm text-zinc-500">Aktivitas belum tersedia.</div>
@else
    <div class="rounded-lg border p-4">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <div class="font-semibold">{{ $section->title }}</div>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ \Illuminate\Support\Str::limit($activity->prompt, 160) }}</div>
                <div class="mt-2 text-xs text-zinc-500">Status: {{ \Illuminate\Support\Str::headline($status) }}</div>
            </div>

            @if ($isLocked)
                <flux:button size="sm" disabled>Terkunci</flux:button>
            @else
                <flux:button size="sm" variant="primary" :href="route('murid.activities.show', $activity)" wire:navigate>
                    {{ $status === 'reviewed' ? 'Lihat Hasil' : 'Kerjakan' }}
                </flux:button>
            @endif
        </div>
    </div>
@endif
