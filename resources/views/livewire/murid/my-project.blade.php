<div class="space-y-6">
    <x-elkm.page-header 
        title="Proyek Saya" 
        subtitle="Susun dan kumpulkan laporan akhir proyek pembelajaran E-LKM." 
        :actions="null"
    />
    
    @if (session('status')) 
        <flux:callout variant="success">{{ session('status') }}</flux:callout> 
    @endif
    
    @php
        $isDisabled = $currentProject && $currentProject->status === 'reviewed';
    @endphp

    @if ($isDisabled)
        <flux:callout variant="warning">Proyek ini sudah dinilai oleh guru dan tidak dapat diubah lagi.</flux:callout>
    @endif

    <div class="grid lg:grid-cols-[1.4fr_360px] gap-4">
        <x-elkm.app-card title="Formulir Proyek">
            <form wire:submit="save" class="grid gap-4 md:grid-cols-2 mt-4">
                <flux:field>
                    <flux:label>Modul</flux:label>
                    <flux:select wire:model.live="module_id">
                        <flux:select.option value="">Pilih</flux:select.option>
                        @foreach ($modules as $module)
                            <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Judul proyek</flux:label>
                    <flux:input wire:model="project_title" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Masalah</flux:label>
                    <flux:textarea wire:model="problem" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Tujuan</flux:label>
                    <flux:textarea wire:model="objective" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Alat dan bahan</flux:label>
                    <flux:textarea wire:model="tools_materials" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Langkah kerja</flux:label>
                    <flux:textarea wire:model="procedure" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Data yang dikumpulkan</flux:label>
                    <flux:textarea wire:model="collected_data" :disabled="$isDisabled" />
                </flux:field>

                <flux:field>
                    <flux:label>Hasil yang diharapkan</flux:label>
                    <flux:textarea wire:model="expected_result" :disabled="$isDisabled" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Kesimpulan</flux:label>
                    <flux:textarea wire:model="conclusion" :disabled="$isDisabled" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>File bukti</flux:label>
                    <input type="file" wire:model="file" class="block w-full text-sm mt-1" {{ $isDisabled ? 'disabled' : '' }} />
                    @if ($existing_file_path)
                        <div class="mt-2 text-sm text-elkm-muted">
                            File saat ini: <a href="#" wire:click.prevent="downloadExistingFile" class="text-elkm-primary-2 hover:underline">Unduh File</a>
                        </div>
                    @endif
                </flux:field>
                
                @if (!$isDisabled)
                    <div class="flex items-center gap-3 md:col-span-2 mt-4 pt-4 border-t border-elkm-line">
                        <button type="button" wire:click="save('draft')" class="btn-elkm btn-elkm-outline">Simpan Draft</button>
                        <button type="submit" class="btn-elkm btn-elkm-primary">Kirim Proyek</button>
                    </div>
                @endif
            </form>
        </x-elkm.app-card>

        <x-elkm.app-card title="Riwayat Proyek">
            <div class="grid gap-3 mt-4">
                @forelse ($projects as $project)
                    <div wire:key="my-project-{{ $project->id }}" class="p-4 border border-elkm-line rounded-xl bg-white">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-sm m-0">{{ $project->project_title }}</h4>
                            <x-elkm.status-pill :color="$project->status === 'reviewed' ? 'green' : 'yellow'">
                                {{ $project->status }}
                            </x-elkm.status-pill>
                        </div>
                        <p class="text-xs text-elkm-muted m-0">Modul: {{ $project->module->title }}</p>
                        
                        @if ($project->status === 'reviewed')
                            <div class="mt-3 bg-[#f7fbf9] border border-[#c7eadb] rounded-lg p-3">
                                <p class="text-xs font-bold text-elkm-primary-2 mb-1">Nilai: {{ $project->score ?? '-' }}</p>
                                @if ($project->feedback)
                                    <p class="text-xs text-elkm-text">{{ $project->feedback }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <x-elkm.empty-state title="Belum ada proyek" description="Anda belum mengirimkan proyek apapun." />
                @endforelse
            </div>
        </x-elkm.app-card>
    </div>
</div>
