@props(['title' => '', 'description' => '', 'soft' => false])

<div {{ $attributes->merge(['class' => 'card-elkm ' . ($soft ? 'soft' : '') . ' p-4.5']) }}>
    @if($title)
        <h4 class="m-0 mb-2 text-[17px] tracking-tight font-bold">{{ $title }}</h4>
    @endif
    @if($description)
        <p class="m-0 text-elkm-muted leading-relaxed">{{ $description }}</p>
    @endif
    
    <div class="{{ ($title || $description) ? 'mt-4' : '' }}">
        {{ $slot }}
    </div>
</div>
