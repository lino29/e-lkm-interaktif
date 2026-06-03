<div class="space-y-6">
    <x-elkm.page-header 
        title="Kelola Modul" 
        subtitle="Susun pendahuluan, tujuan, cover, KKTP, dan status publikasi modul E-LKM." 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Kelola Modul</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($editingModuleId)
                <button type="button" class="btn-elkm btn-elkm-outline" wire:click="cancelEdit">Batal Edit</button>
            @endif
        </x-slot:actions>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid lg:grid-cols-[minmax(0,1.4fr)_360px] gap-4">
        <x-elkm.app-card title="Informasi Modul" description="Metadata modul, tujuan, struktur KB, materi, media, glosarium, dan referensi.">
            <form wire:submit="save" class="grid gap-4 md:grid-cols-2 mt-4">
                <flux:field>
                    <flux:label>Mata pelajaran</flux:label>
                    <flux:select wire:model="subject_id">
                        <flux:select.option value="">Pilih mata pelajaran</flux:select.option>
                        @foreach ($subjects as $subject)
                            <flux:select.option value="{{ $subject->id }}">{{ $subject->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="subject_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Judul modul</flux:label>
                    <flux:input wire:model="title" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="draft">Draft</flux:select.option>
                        <flux:select.option value="published">Published</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <flux:field>
                    <flux:label>Cover modul</flux:label>
                    <flux:input type="file" wire:model="cover" accept="image/*" />
                    <flux:error name="cover" />
                    @if ($existingCoverPath)
                        <flux:description>Cover saat ini: {{ basename($existingCoverPath) }}</flux:description>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>KKTP</flux:label>
                    <flux:input type="number" min="0" max="100" wire:model="kktp" />
                    <flux:error name="kktp" />
                </flux:field>

                <flux:field>
                    <flux:label>Maks percobaan asesmen</flux:label>
                    <flux:input type="number" min="1" max="10" wire:model="max_attempts" />
                    <flux:error name="max_attempts" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Pendahuluan</flux:label>
                    <flux:textarea wire:model="introduction" rows="5" />
                    <flux:error name="introduction" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Tujuan pembelajaran</flux:label>
                    <flux:textarea wire:model="learning_objectives" rows="5" />
                    <flux:error name="learning_objectives" />
                </flux:field>

                <div class="md:col-span-2 mt-2">
                    <button type="submit" class="btn-elkm btn-elkm-primary">
                        {{ $editingModuleId ? 'Perbarui Modul' : 'Simpan Modul' }}
                    </button>
                </div>
            </form>
        </x-elkm.app-card>

        <div class="space-y-4">
            @foreach ($modules as $module)
                <x-elkm.app-card class="flex flex-col h-full justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <h4 class="font-bold text-[15px] m-0">{{ $module->title }}</h4>
                            <x-elkm.status-pill :color="$module->status === 'published' ? 'green' : 'yellow'">
                                {{ $module->status }}
                            </x-elkm.status-pill>
                        </div>
                        <p class="text-[13px] text-elkm-muted m-0 leading-relaxed">
                            {{ $module->subject->name }}. KKTP {{ $module->kktp }}. 
                            <br>{{ $module->learning_units_count }} kegiatan, {{ $module->assessments_count }} asesmen.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('guru.modules.show', $module) }}" wire:navigate class="btn-elkm btn-elkm-outline !py-1.5 !px-3 !text-[12px]">Detail</a>
                        <a href="{{ route('guru.modules.outline', $module) }}" wire:navigate class="btn-elkm btn-elkm-soft !py-1.5 !px-3 !text-[12px]">Outline</a>
                        <button type="button" wire:click="edit({{ $module->id }})" class="btn-elkm btn-elkm-outline !py-1.5 !px-3 !text-[12px]">Edit</button>
                        <button type="button" wire:click="toggleStatus({{ $module->id }})" class="btn-elkm btn-elkm-outline !py-1.5 !px-3 !text-[12px]">
                            {{ $module->status === 'published' ? 'Draft' : 'Publish' }}
                        </button>
                        <button type="button" wire:click="delete({{ $module->id }})" wire:confirm="Hapus modul beserta kegiatan, materi, aktivitas, dan asesmennya?" class="btn-elkm btn-elkm-danger !py-1.5 !px-3 !text-[12px]">Hapus</button>
                    </div>
                </x-elkm.app-card>
            @endforeach
            
            @if($modules->isEmpty())
                <x-elkm.empty-state title="Belum ada modul" description="Silakan buat modul baru melalui form." />
            @endif
        </div>
    </div>
</div>
