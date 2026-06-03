@props(['active' => false, 'icon' => ''])

@php
$classes = $active
            ? 'w-full flex items-center gap-2.5 text-left rounded-2xl p-3 bg-elkm-primary text-white shadow-[0_12px_25px_rgba(15,143,95,.24)] font-bold transition-all'
            : 'w-full flex items-center gap-2.5 text-left rounded-2xl p-3 bg-transparent text-elkm-text font-bold hover:bg-[#edf7f3] hover:text-elkm-primary-2 transition-all';
            
$iconClasses = $active
            ? 'w-7 h-7 rounded-lg grid place-items-center bg-white/20 text-sm'
            : 'w-7 h-7 rounded-lg grid place-items-center bg-elkm-primary/10 text-sm';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="{{ $iconClasses }}">{{ $icon }}</span>
    @endif
    {{ $slot }}
</a>
