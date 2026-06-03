<div class="space-y-6">
    <x-elkm.page-header 
        title="Dashboard Admin" 
        subtitle="Monitoring global sistem, role, kelas, dan status modul." 
        :actions="null" 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item>Dashboard Admin</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        @foreach ($stats as $label => $value)
            @php
                $icon = '📊';
                if (str_contains(strtolower($label), 'murid')) $icon = '🎓';
                if (str_contains(strtolower($label), 'guru')) $icon = '👨‍🏫';
                if (str_contains(strtolower($label), 'modul')) $icon = '📚';
                if (str_contains(strtolower($label), 'tuntas') || str_contains(strtolower($label), 'rata')) $icon = '✅';
            @endphp
            <x-elkm.stat-card :title="$label" :value="$value" :icon="$icon" wire:key="admin-stat-{{ $label }}" />
        @endforeach
    </div>

    <div class="grid md:grid-cols-[1.4fr_360px] gap-4 mt-4">
        <x-elkm.app-card title="Aktivitas Sistem Terbaru">
            <div class="overflow-x-auto mt-2">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#f7fbf9]">
                            <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Waktu</th>
                            <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">User</th>
                            <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Aktivitas</th>
                            <th class="text-left p-3 text-xs uppercase tracking-wider text-elkm-muted border-b border-elkm-line">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-3 text-sm border-b border-elkm-line">08:10</td>
                            <td class="p-3 text-sm border-b border-elkm-line">Guru Projek IPAS</td>
                            <td class="p-3 text-sm border-b border-elkm-line">Memperbarui Aktivitas KB1</td>
                            <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.status-pill color="green">Sukses</x-elkm.status-pill></td>
                        </tr>
                        <tr>
                            <td class="p-3 text-sm border-b border-elkm-line">08:22</td>
                            <td class="p-3 text-sm border-b border-elkm-line">Murid X TKJ</td>
                            <td class="p-3 text-sm border-b border-elkm-line">Submit Ayo Mencoba</td>
                            <td class="p-3 text-sm border-b border-elkm-line"><x-elkm.status-pill color="blue">Tersimpan</x-elkm.status-pill></td>
                        </tr>
                        <tr>
                            <td class="p-3 text-sm border-b-0">09:01</td>
                            <td class="p-3 text-sm border-b-0">Sistem</td>
                            <td class="p-3 text-sm border-b-0">Scoring Asesmen Formatif</td>
                            <td class="p-3 text-sm border-b-0"><x-elkm.status-pill color="yellow">Proses</x-elkm.status-pill></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-elkm.app-card>

        <x-elkm.app-card title="Quick Setup" description="Checklist awal sebelum modul digunakan murid.">
            <div class="grid gap-3 mt-4">
                <div class="grid grid-cols-[42px_1fr_auto] gap-3 items-center p-3.5 border border-elkm-line rounded-[18px] bg-white">
                    <div class="w-[42px] h-[42px] rounded-xl bg-[#e8f5ef] text-elkm-primary-2 grid place-items-center font-black">1</div>
                    <div>
                        <b class="text-sm">Role & Permission</b><br>
                        <small class="text-elkm-muted">Admin, Guru, Murid</small>
                    </div>
                    <x-elkm.status-pill color="green">OK</x-elkm.status-pill>
                </div>
                <div class="grid grid-cols-[42px_1fr_auto] gap-3 items-center p-3.5 border border-elkm-line rounded-[18px] bg-white">
                    <div class="w-[42px] h-[42px] rounded-xl bg-[#e8f5ef] text-elkm-primary-2 grid place-items-center font-black">2</div>
                    <div>
                        <b class="text-sm">Data Kelas</b><br>
                        <small class="text-elkm-muted">X TKJ / X ATPH</small>
                    </div>
                    <x-elkm.status-pill color="green">OK</x-elkm.status-pill>
                </div>
                <div class="grid grid-cols-[42px_1fr_auto] gap-3 items-center p-3.5 border border-elkm-line rounded-[18px] bg-white">
                    <div class="w-[42px] h-[42px] rounded-xl bg-[#e8f5ef] text-elkm-primary-2 grid place-items-center font-black">3</div>
                    <div>
                        <b class="text-sm">Publikasi Modul</b><br>
                        <small class="text-elkm-muted">Butuh review guru</small>
                    </div>
                    <x-elkm.status-pill color="yellow">Review</x-elkm.status-pill>
                </div>
            </div>
        </x-elkm.app-card>
    </div>
</div>
