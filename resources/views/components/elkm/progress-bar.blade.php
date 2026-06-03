@props(['percent' => 0, 'alt' => false])

<div class="h-2.5 rounded-full overflow-hidden {{ $alt ? 'bg-[#fff3d7]' : 'bg-[#edf3f0]' }}">
    <i class="block h-full rounded-full" 
       style="width: {{ max(0, min(100, $percent)) }}%; background: {{ $alt ? 'linear-gradient(90deg, var(--color-elkm-accent), #f7cd70)' : 'linear-gradient(90deg, var(--color-elkm-primary), #20b47b)' }}">
    </i>
</div>
