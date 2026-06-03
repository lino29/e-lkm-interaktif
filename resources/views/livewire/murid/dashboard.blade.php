<div class="space-y-6">
    <x-elkm.page-header 
        title="Halo, {{ explode(' ', auth()->user()->name)[0] }} 👋" 
        subtitle="Selamat datang di Dashboard Ruang Murid." 
        :actions="null" 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item>Dashboard</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <div class="card-elkm !p-0 !border-0 overflow-hidden" style="background:linear-gradient(135deg,#0f8f5f,#1fb579);color:white;">
        <div class="grid md:grid-cols-[1.4fr_360px]">
            <div class="p-8 md:p-10">
                <x-elkm.status-pill color="yellow">Platform Aktif</x-elkm.status-pill>
                <h3 class="text-3xl md:text-4xl font-bold mt-4 mb-3">E-LKM Interaktif Energi Terbarukan</h3>
                <p class="text-white/80 leading-relaxed text-[15px] max-w-xl">
                    Selesaikan aktivitas bertahap, ikuti forum diskusi, lalu kerjakan asesmen formatif untuk mengukur pemahaman dan membuka kegiatan belajar berikutnya.
                </p>
                <a href="{{ route('murid.modules') }}" wire:navigate class="btn-elkm btn-elkm-dark mt-6">Mulai Belajar</a>
            </div>
            
            <div class="bg-white/15 p-8 md:p-10 flex flex-col justify-center border-l border-white/20">
                <h4 class="font-bold text-lg mb-2">Progress Global</h4>
                <strong class="text-5xl font-black block">
                    {{ count($moduleProgress) > 0 ? $moduleProgress[0]['percentage'] : 0 }}%
                </strong>
                <div class="my-4">
                    <x-elkm.progress-bar :percent="count($moduleProgress) > 0 ? $moduleProgress[0]['percentage'] : 0" alt="true" />
                </div>
                <p class="text-white/80 text-[14px]">
                    Pastikan menyelesaikan setiap aktivitas (Ayo Mengamati hingga Asesmen).
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4 mt-6">
        @foreach ($stats as $label => $value)
            @php
                $icon = '📊';
                if (str_contains(strtolower($label), 'modul')) $icon = '📚';
                if (str_contains(strtolower($label), 'asesmen') || str_contains(strtolower($label), 'soal')) $icon = '📝';
                if (str_contains(strtolower($label), 'aktivitas') || str_contains(strtolower($label), 'proyek')) $icon = '🚀';
                if (str_contains(strtolower($label), 'nilai') || str_contains(strtolower($label), 'rata')) $icon = '🏆';
            @endphp
            <x-elkm.stat-card :title="$label" :value="$value" :icon="$icon" wire:key="murid-stat-{{ $label }}" />
        @endforeach
    </div>

    <div class="grid md:grid-cols-[1.4fr_360px] gap-4 mt-4">
        <x-elkm.app-card title="Modul Saya">
            <div class="grid gap-3 mt-4">
                @forelse ($moduleProgress as $item)
                    <div wire:key="dashboard-module-progress-{{ $item['module']->id }}" class="grid grid-cols-[42px_1fr_auto] gap-3 items-center p-3.5 border border-elkm-line rounded-[18px] bg-white">
                        <div class="w-[42px] h-[42px] rounded-xl bg-[#e8f5ef] text-elkm-primary-2 grid place-items-center font-black">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <b class="text-sm block">{{ $item['module']->title }}</b>
                            <small class="text-elkm-muted block">{{ $item['module']->learningUnits->count() }} kegiatan belajar</small>
                        </div>
                        <div class="w-32 text-right">
                            <span class="text-xs font-bold text-elkm-primary-2 mb-1 inline-block">{{ $item['percentage'] }}%</span>
                            <x-elkm.progress-bar :percent="$item['percentage']" />
                        </div>
                    </div>
                @empty
                    <x-elkm.empty-state title="Belum ada modul aktif" description="Modul akan muncul saat guru telah menerbitkan modul." />
                @endforelse
            </div>
        </x-elkm.app-card>
        
        <x-elkm.app-card title="Tugas Berikutnya" description="Aktivitas yang belum selesai.">
            <div class="mt-4">
                <x-elkm.empty-state icon="🎉" title="Semua tuntas" description="Anda tidak memiliki tugas yang harus dikerjakan saat ini." />
            </div>
        </x-elkm.app-card>
    </div>
</div>
