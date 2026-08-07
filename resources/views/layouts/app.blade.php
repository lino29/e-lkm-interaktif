@php
    $sidebarRole = match (true) {
        auth()->user()->hasRole('admin') => 'admin',
        auth()->user()->hasRole('guru') => 'guru',
        default => 'murid',
    };
@endphp

<x-elkm.app-shell :title="$title ?? __('E-LKM')" :sidebarRole="$sidebarRole">
    {{ $slot }}
</x-elkm.app-shell>
