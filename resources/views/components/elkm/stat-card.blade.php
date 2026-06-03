@props(['title', 'value', 'icon' => ''])

<div class="card-elkm flex justify-between items-start gap-3 p-4.5">
    <div>
        <span class="text-[13px] font-semibold text-elkm-muted">{{ $title }}</span>
        <strong class="block text-3xl tracking-tight mt-2.5">{{ $value }}</strong>
    </div>
    @if($icon)
        <div class="w-11 h-11 rounded-[15px] grid place-items-center bg-[#eaf6f1] text-elkm-primary text-xl">
            {{ $icon }}
        </div>
    @endif
</div>
