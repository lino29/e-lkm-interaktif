<div class="space-y-6">
    <x-elkm.page-header 
        title="Dashboard Guru" 
        subtitle="Kelola konten, aktivitas, asesmen, proyek, dan progres belajar murid." 
        :actions="null" 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item>Dashboard</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <flux:input wire:model.live="search" placeholder="Cari modul, aktivitas..." icon="magnifying-glass" class="min-w-[260px]" />
        </x-slot:actions>
    </x-elkm.page-header>

    <div class="grid gap-4 md:grid-cols-3">
        <x-elkm.app-card title="E-LKM Energi Terbarukan">
            <x-slot:title>
                <x-elkm.status-pill color="green">Published</x-elkm.status-pill>
                <h4 class="mt-3 text-[17px] font-bold">E-LKM Energi Terbarukan</h4>
            </x-slot:title>
            <p class="text-elkm-muted">5 kegiatan belajar, 30 aktivitas, 5 asesmen formatif, 1 proyek akhir.</p>
            <div class="mt-3.5">
                <x-elkm.progress-bar :percent="84" />
            </div>
        </x-elkm.app-card>

        <x-elkm.app-card>
            <x-slot:title>
                <x-elkm.status-pill color="yellow">Perlu Review</x-elkm.status-pill>
                <h4 class="mt-3 text-[17px] font-bold">Jawaban Uraian</h4>
            </x-slot:title>
            <p class="text-elkm-muted">18 jawaban menalar dan menyimpulkan menunggu validasi guru.</p>
            <a href="{{ route('guru.activity-reviews') }}" class="btn-elkm btn-elkm-warning mt-3.5 block text-center">Review Sekarang</a>
        </x-elkm.app-card>

        <x-elkm.app-card>
            <x-slot:title>
                <x-elkm.status-pill color="blue">Laporan</x-elkm.status-pill>
                <h4 class="mt-3 text-[17px] font-bold">Rekap Kelas X</h4>
            </x-slot:title>
            <p class="text-elkm-muted">Export PDF/Excel nilai, progres, remedial, forum, dan proyek murid.</p>
            <a href="{{ route('guru.reports') }}" class="btn-elkm btn-elkm-soft mt-3.5 block text-center">Buka Laporan</a>
        </x-elkm.app-card>
    </div>

    <div class="grid gap-4 md:grid-cols-4 mt-6">
        @foreach ($stats as $label => $value)
            @php
                $icon = '📊';
                if (str_contains(strtolower($label), 'modul')) $icon = '📚';
                if (str_contains(strtolower($label), 'murid')) $icon = '🎓';
                if (str_contains(strtolower($label), 'aktivitas')) $icon = '🧩';
                if (str_contains(strtolower($label), 'soal')) $icon = '📝';
            @endphp
            <x-elkm.stat-card :title="$label" :value="$value" :icon="$icon" wire:key="guru-stat-{{ $label }}" />
        @endforeach
    </div>

    <x-elkm.app-card title="Ringkasan Progress Kegiatan Belajar" class="mt-6">
        <div class="overflow-x-auto mt-2">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#f7fbf9]">
                        <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">KB</th>
                        <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Judul</th>
                        <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Aktivitas</th>
                        <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Ketuntasan</th>
                        <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-3 text-sm border-b border-elkm-line">KB1</td>
                        <td class="p-3 text-sm border-b border-elkm-line font-semibold">Deteksi Potensi Energi</td>
                        <td class="p-3 text-sm border-b border-elkm-line">6/6</td>
                        <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.progress-bar :percent="92" /></td>
                        <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.status-pill color="green">Aktif</x-elkm.status-pill></td>
                    </tr>
                    <tr>
                        <td class="p-3 text-sm border-b border-elkm-line">KB2</td>
                        <td class="p-3 text-sm border-b border-elkm-line font-semibold">Masalah Energi Fosil</td>
                        <td class="p-3 text-sm border-b border-elkm-line">6/6</td>
                        <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.progress-bar :percent="70" /></td>
                        <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.status-pill color="green">Aktif</x-elkm.status-pill></td>
                    </tr>
                    <tr>
                        <td class="p-3 text-sm border-b-0">KB3</td>
                        <td class="p-3 text-sm border-b-0 font-semibold">Energi Terbarukan</td>
                        <td class="p-3 text-sm border-b-0">0/6</td>
                        <td class="p-3 text-sm border-b-0"><x-elkm.progress-bar :percent="0" /></td>
                        <td class="p-3 text-sm border-b-0"><x-elkm.status-pill color="yellow">Draft</x-elkm.status-pill></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-elkm.app-card>
</div>
