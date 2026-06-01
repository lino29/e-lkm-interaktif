@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
    $statusData = $activity ? ($activityStatuses[$activity->id] ?? ['is_locked' => false]) : [];
    $isLocked = $statusData['is_locked'] ?? false;
@endphp

<div class="rounded-lg border p-4">
    <div class="font-semibold">Forum Diskusi/Refleksi</div>
    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Forum mengikuti activity engine fase forum_diskusi, sehingga jawaban dan progres tetap tercatat sebagai aktivitas belajar.
    </p>

    @if ($activity && ! $isLocked)
        <flux:button class="mt-4" size="sm" variant="primary" :href="route('murid.activities.show', $activity)" wire:navigate>
            Buka Forum
        </flux:button>
    @elseif ($activity)
        <flux:button class="mt-4" size="sm" disabled>Selesaikan aktivitas sebelumnya</flux:button>
    @else
        <div class="mt-3 text-sm text-zinc-500">Aktivitas forum belum tersedia.</div>
    @endif
</div>
