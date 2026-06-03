@props(['icon' => '📄', 'title' => 'Tidak ada data', 'description' => '', 'action' => null])

<div class="card-elkm flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-16 h-16 bg-[#f7fbf9] rounded-2xl flex items-center justify-center text-3xl mb-4 border border-elkm-line text-elkm-muted">
        {{ $icon }}
    </div>
    <h3 class="text-lg font-bold tracking-tight mb-1">{{ $title }}</h3>
    @if($description)
        <p class="text-elkm-muted max-w-md mx-auto">{{ $description }}</p>
    @endif
    @if($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endif
</div>
