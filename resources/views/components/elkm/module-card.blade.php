@props(['title', 'description', 'status' => 'Aktif', 'statusColor' => 'green', 'progress' => null, 'locked' => false])

<div class="card-elkm p-4.5 {{ $locked ? 'opacity-60 grayscale' : '' }}">
    <x-elkm.status-pill :color="$statusColor">{{ $status }}</x-elkm.status-pill>
    
    <h4 class="m-0 mt-3 text-[17px] tracking-tight font-bold">{{ $title }}</h4>
    <p class="m-0 text-elkm-muted leading-relaxed mt-1">{{ $description }}</p>
    
    @if($progress !== null)
        <div class="mt-3.5">
            <x-elkm.progress-bar :percent="$progress" />
        </div>
    @endif
    
    <div class="mt-4">
        {{ $slot }}
    </div>
</div>
