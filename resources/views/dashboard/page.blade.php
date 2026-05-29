<x-layouts::app :title="$title ?? __('Dashboard')">
    @livewire($livewireComponent, request()->route()->parameters())
</x-layouts::app>
