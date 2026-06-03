@php
    $role = 'murid';
    if(auth()->check()) {
        if(auth()->user()->hasRole('admin')) $role = 'admin';
        elseif(auth()->user()->hasRole('guru')) $role = 'guru';
    }
@endphp

<x-elkm.app-shell :title="$title ?? __('Dashboard')" :sidebarRole="$role">
    @livewire($livewireComponent, request()->route()->parameters())
</x-elkm.app-shell>
