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
    <div class="card-elkm p-4 text-sm text-elkm-muted">Aktivitas belum tersedia.</div>
@else
    <div class="card-elkm p-4">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <div class="font-bold text-elkm-text">{{ $section->title }}</div>
                <div class="mt-1 text-sm text-elkm-muted">{{ \Illuminate\Support\Str::limit($activity->prompt, 160) }}</div>
                <div class="mt-2 text-xs text-elkm-muted">Status: {{ \Illuminate\Support\Str::headline($status) }}</div>
            </div>

            @if ($isLocked)
                <button class="btn-elkm btn-elkm-outline opacity-50 cursor-not-allowed" disabled>Terkunci</button>
            @else
                <a href="{{ route('murid.activities.show', $activity) }}" wire:navigate class="btn-elkm btn-elkm-primary">
                    {{ $status === 'reviewed' ? 'Lihat Hasil' : 'Kerjakan' }}
                </a>
            @endif
        </div>
    </div>
@endif
