@props([
    'title' => 'E-LKM',
    'sidebarRole' => 'murid' // admin, guru, murid
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="canvas-shell bg-elkm-canvas min-h-screen text-elkm-text font-sans antialiased lg:flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Header -->
    <div class="lg:hidden flex items-center justify-between p-4 border-b border-elkm-line bg-white sticky top-0 z-20 shadow-sm">
        <div class="brand flex items-center gap-3">
            <div class="brand-mark w-10 h-10 rounded-xl grid place-items-center text-white font-black text-sm">EL</div>
            <div>
                <h1 class="text-sm leading-tight m-0 font-bold">E-LKM V2</h1>
                <p class="text-[10px] text-elkm-muted mt-0.5 m-0">Energi Terbarukan</p>
            </div>
        </div>
        <button @click="sidebarOpen = true" class="p-2 text-elkm-text focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity style="display: none;" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="canvas-sidebar fixed lg:sticky top-0 left-0 h-screen overflow-auto w-[280px] shrink-0 p-7 border-r border-elkm-line z-40 transition-transform duration-300 lg:translate-x-0 bg-white/95 backdrop-blur-xl lg:bg-transparent lg:backdrop-blur-none"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <button @click="sidebarOpen = false" class="lg:hidden absolute top-6 right-6 p-1 text-elkm-muted hover:text-elkm-text focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="brand flex items-center gap-3.5 mb-6">
            <div class="brand-mark w-14 h-14 rounded-[18px] grid place-items-center text-white font-black text-lg">EL</div>
            <div>
                <h1 class="text-[17px] leading-tight m-0 font-bold">E-LKM V2</h1>
                <p class="text-[12px] text-elkm-muted mt-1 m-0">Energi Terbarukan</p>
            </div>
        </div>

        <div class="section-label text-[11px] uppercase tracking-widest text-elkm-muted mt-6 mb-2.5 font-extrabold">Menu Utama</div>
        <nav class="screen-nav grid gap-2">
            @if($sidebarRole === 'admin')
                <x-elkm.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="🏠">Dashboard Admin</x-elkm.nav-link>
                <x-elkm.nav-link href="#" icon="👥">Pengguna</x-elkm.nav-link>
                <x-elkm.nav-link href="#" icon="🏫">Kelas</x-elkm.nav-link>
                <x-elkm.nav-link href="#" icon="📖">Mapel</x-elkm.nav-link>
                <x-elkm.nav-link href="#" icon="📋">Laporan</x-elkm.nav-link>
            @elseif($sidebarRole === 'guru')
                <x-elkm.nav-link href="{{ route('guru.dashboard') }}" :active="request()->routeIs('guru.dashboard')" icon="🏠">Dashboard Guru</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('guru.modules') }}" :active="request()->routeIs('guru.modules')" icon="📚">Modul E-LKM</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('guru.activities') }}" :active="request()->routeIs('guru.activities')" icon="📝">Kegiatan</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('guru.assessments') }}" :active="request()->routeIs('guru.assessments', 'guru.questions', 'guru.rubrics')" icon="📋">Asesmen</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('guru.reports') }}" :active="request()->routeIs('guru.reports')" icon="📈">Laporan</x-elkm.nav-link>
            @elseif($sidebarRole === 'murid')
                <x-elkm.nav-link href="{{ route('murid.dashboard') }}" :active="request()->routeIs('murid.dashboard')" icon="🏠">Dashboard Murid</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('murid.modules') }}" :active="request()->routeIs('murid.modules')" icon="📚">Modul Saya</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('murid.remedial') }}" :active="request()->routeIs('murid.remedial')" icon="🔄">Remedial</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('murid.project') }}" :active="request()->routeIs('murid.project')" icon="🚀">Proyek</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('murid.portfolio') }}" :active="request()->routeIs('murid.portfolio')" icon="🏆">Portofolio</x-elkm.nav-link>
                <x-elkm.nav-link href="{{ route('murid.scores') }}" :active="request()->routeIs('murid.scores')" icon="📊">Nilai Saya</x-elkm.nav-link>
            @endif
        </nav>
        
        <div class="mt-8">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left p-3 text-sm font-bold text-elkm-danger bg-[#fff1f1] rounded-2xl flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg grid place-items-center bg-white/50 text-sm">🚪</span>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <main class="canvas-main p-4 md:p-7 min-w-0 flex-1">
        {{ $slot }}
    </main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>
