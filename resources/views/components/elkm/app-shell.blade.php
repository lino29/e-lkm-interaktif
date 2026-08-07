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
        <button
            type="button"
            @click="sidebarOpen = true"
            :aria-expanded="sidebarOpen"
            aria-controls="app-sidebar"
            aria-label="Buka menu navigasi"
            class="rounded-lg p-2 text-elkm-text transition hover:bg-elkm-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" x-transition.opacity style="display: none;" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    <!-- Sidebar -->
    <aside id="app-sidebar" class="canvas-sidebar fixed lg:sticky top-0 left-0 h-screen w-[280px] shrink-0 overflow-hidden p-4 lg:p-7 border-r border-elkm-line z-40 transition-transform duration-300 lg:translate-x-0 bg-white/95 backdrop-blur-xl lg:bg-transparent lg:backdrop-blur-none"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <button type="button" @click="sidebarOpen = false" aria-label="Tutup menu navigasi" class="lg:hidden absolute top-6 right-6 rounded-lg p-1 text-elkm-muted transition hover:bg-elkm-surface hover:text-elkm-text focus-visible:outline-2 focus-visible:outline-elkm-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="h-full overflow-y-auto overscroll-contain pb-[258px] pr-1 lg:pb-[270px]" wire:navigate:scroll>
            <div class="brand mb-6 flex items-center gap-3.5">
                <div class="brand-mark grid h-14 w-14 place-items-center rounded-[18px] text-lg font-black text-white">EL</div>
                <div>
                    <h1 class="m-0 text-[17px] font-bold leading-tight">E-LKM V2</h1>
                    <p class="m-0 mt-1 text-[12px] text-elkm-muted">Energi Terbarukan</p>
                </div>
            </div>

            <div class="section-label mb-2.5 mt-6 text-[11px] font-extrabold uppercase tracking-widest text-elkm-muted">Menu Utama</div>
            <nav class="screen-nav grid gap-2" aria-label="Menu utama">
                @if($sidebarRole === 'admin')
                    <x-elkm.nav-link href="{{ route('admin.games') }}" :active="request()->routeIs('admin.games')" icon="GM">Games</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="🏠">Dashboard Admin</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('admin.users') }}" :active="request()->routeIs('admin.users', 'admin.teachers', 'admin.students')" icon="👥">Pengguna</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('admin.classes') }}" :active="request()->routeIs('admin.classes')" icon="🏫">Kelas</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('admin.subjects') }}" :active="request()->routeIs('admin.subjects')" icon="📖">Mapel</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('admin.reports') }}" :active="request()->routeIs('admin.reports')" icon="📋">Laporan</x-elkm.nav-link>
                @elseif($sidebarRole === 'guru')
                    <x-elkm.nav-link href="{{ route('guru.games.manage') }}" :active="request()->routeIs('guru.games.*')" icon="GM">Kelola Games</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.dashboard') }}" :active="request()->routeIs('guru.dashboard')" icon="🏠">Dashboard Guru</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.modules') }}" :active="request()->routeIs('guru.modules')" icon="📚">Modul E-LKM</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.learning-units') }}" :active="request()->routeIs('guru.learning-units', 'guru.activities')" icon="📝">Kegiatan</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.assessments') }}" :active="request()->routeIs('guru.assessments', 'guru.questions', 'guru.rubrics')" icon="📋">Asesmen</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.remedials') }}" :active="request()->routeIs('guru.remedials')" icon="🔄">Remedial</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('guru.reports') }}" :active="request()->routeIs('guru.reports')" icon="📈">Laporan</x-elkm.nav-link>
                @elseif($sidebarRole === 'murid')
                    <x-elkm.nav-link href="{{ route('murid.games.index') }}" :active="request()->routeIs('murid.games.*')" icon="GM">Games</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.dashboard') }}" :active="request()->routeIs('murid.dashboard')" icon="🏠">Dashboard Murid</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.modules') }}" :active="request()->routeIs('murid.modules')" icon="📚">Modul Saya</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.remedial') }}" :active="request()->routeIs('murid.remedial')" icon="🔄">Remedial</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.project') }}" :active="request()->routeIs('murid.project')" icon="🚀">Proyek</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.portfolio') }}" :active="request()->routeIs('murid.portfolio')" icon="🏆">Portofolio</x-elkm.nav-link>
                    <x-elkm.nav-link href="{{ route('murid.scores') }}" :active="request()->routeIs('murid.scores')" icon="📊">Nilai Saya</x-elkm.nav-link>
                @endif
            </nav>
        </div>
        
        <div data-test="account-widget" class="absolute inset-x-4 bottom-4 max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-3xl border border-elkm-line bg-white/95 p-3 shadow-[0_16px_45px_rgba(16,35,28,.14)] backdrop-blur-xl lg:inset-x-7 lg:bottom-6">
            <div class="section-label mb-3 text-[11px] font-extrabold uppercase tracking-widest text-elkm-muted">Akun</div>
            <div class="mb-3 flex items-center gap-3 rounded-2xl bg-white/70 p-3">
                <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-elkm-primary text-sm font-black text-white" aria-hidden="true">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-bold text-elkm-text">{{ auth()->user()->name }}</div>
                    <div class="truncate text-xs text-elkm-muted">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="grid gap-2">
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    @class([
                        'flex w-full items-center gap-2 rounded-2xl border px-3 py-2.5 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-primary',
                        'border-elkm-primary bg-elkm-primary text-white shadow-[0_10px_22px_rgba(15,143,95,.2)]' => request()->routeIs('profile.edit', 'security.edit', 'appearance.edit'),
                        'border-elkm-line bg-white text-elkm-text hover:border-elkm-primary hover:text-elkm-primary' => ! request()->routeIs('profile.edit', 'security.edit', 'appearance.edit'),
                    ])
                >
                    <span @class([
                        'grid size-7 place-items-center rounded-lg text-xs',
                        'bg-white/20 text-white' => request()->routeIs('profile.edit', 'security.edit', 'appearance.edit'),
                        'bg-elkm-primary/10 text-elkm-primary' => ! request()->routeIs('profile.edit', 'security.edit', 'appearance.edit'),
                    ]) aria-hidden="true">PR</span>
                    Lihat Profil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" data-test="logout-button" class="flex w-full items-center gap-2 rounded-2xl bg-[#fff1f1] px-3 py-2.5 text-left text-sm font-bold text-elkm-danger transition hover:bg-red-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-elkm-danger">
                        <span class="grid size-7 place-items-center rounded-lg bg-white/70 text-xs" aria-hidden="true">OUT</span>
                        Logout
                    </button>
                </form>
            </div>
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
