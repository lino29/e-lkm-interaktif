@props(['color' => 'gray'])

@php
$colorClasses = [
    'green' => 'bg-[#e4f8ef] text-[#0a6c48]',
    'yellow' => 'bg-[#fff4d8] text-[#946411]',
    'blue' => 'bg-[#e8f2ff] text-[#1c64b7]',
    'red' => 'bg-[#fff0f0] text-[#bd3838]',
    'gray' => 'bg-[#eef2f1] text-[#66766f]',
    'purple' => 'bg-[#f1edff] text-[#5b47bd]',
][$color] ?? 'bg-[#eef2f1] text-[#66766f]';
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-extrabold {{ $colorClasses }}">
    {{ $slot }}
</span>
