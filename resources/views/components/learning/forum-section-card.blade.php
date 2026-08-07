@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
@endphp

<div class="card-elkm p-4">
    <div class="font-bold text-elkm-text">Forum Diskusi/Refleksi</div>
    <p class="mt-2 text-sm text-elkm-muted">
        Forum mengikuti activity engine fase forum_diskusi, sehingga jawaban dan progres tetap tercatat sebagai aktivitas belajar.
    </p>

    @if ($activity)
        <div class="mt-4">
            <a href="{{ route('murid.activities.show', $activity) }}" wire:navigate class="btn-elkm btn-elkm-primary">
                Buka Forum
            </a>
        </div>
    @else
        <div class="mt-3 text-sm text-elkm-muted">Aktivitas forum belum tersedia.</div>
    @endif
</div>
