@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
    $statusData = $activity ? ($activityStatuses[$activity->id] ?? ['status' => 'belum_mulai']) : [];
    $status = $statusData['status'] ?? 'not_found';
@endphp

@if (! $activity)
    <div class="card-elkm p-4 text-sm text-elkm-muted">Aktivitas belum tersedia.</div>
@else
    <div class="card-elkm p-4">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <div class="font-bold text-elkm-text">{{ $section->title }}</div>
                <div class="mt-1 text-sm text-elkm-muted">{{ \Illuminate\Support\Str::limit($activity->prompt, 160) }}</div>
                <div class="mt-2 text-xs text-elkm-muted">Status: {{ \Illuminate\Support\Str::headline($status) }}</div>
            </div>

            <a href="{{ route('murid.activities.show', $activity) }}" wire:navigate class="btn-elkm btn-elkm-primary mt-4 md:mt-0">
                {{ $status === 'reviewed' ? 'Lihat Hasil' : 'Kerjakan' }}
            </a>
        </div>
    </div>
@endif
