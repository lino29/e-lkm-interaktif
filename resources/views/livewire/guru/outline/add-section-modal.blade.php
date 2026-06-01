@if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ $selectedParentId ? 'Tambah Subbagian' : 'Tambah Bagian' }}</flux:heading>
                    <flux:text>Pilih jenis bagian bahan ajar yang ingin ditambahkan.</flux:text>
                </div>
                <flux:button type="button" variant="ghost" wire:click="closeAddSectionModal">Tutup</flux:button>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2">
                @foreach ($sectionTypeChoices as $sectionType => $label)
                    <label class="cursor-pointer rounded-lg border border-zinc-200 p-4 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800">
                        <div class="flex items-start gap-3">
                            <input type="radio" wire:model="newSectionType" value="{{ $sectionType }}" class="mt-1">
                            <div>
                                <div class="font-medium">{{ $label }}</div>
                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    @switch($sectionType)
                                        @case('learning_objective')
                                            Rumusan tujuan pembelajaran untuk kegiatan ini.
                                            @break
                                        @case('key_points')
                                            Tabel konsep, fakta, prosedur, dan metakognitif.
                                            @break
                                        @case('material_group')
                                            Kelompok besar untuk kumpulan materi.
                                            @break
                                        @case('material_item')
                                            Submateri dengan isi dan media pendukung.
                                            @break
                                        @case('activity_group')
                                            Kelompok aktivitas pembelajaran.
                                            @break
                                        @case('activity_item')
                                            Kartu aktivitas yang terhubung ke tugas murid.
                                            @break
                                        @case('forum')
                                            Pertanyaan diskusi atau refleksi murid.
                                            @break
                                        @case('assessment_group')
                                            Bagian asesmen formatif dan ringkasan soal.
                                            @break
                                        @case('media_gallery')
                                            Galeri gambar, video, file, atau link.
                                            @break
                                        @default
                                            Konten bebas untuk kebutuhan tambahan guru.
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeAddSectionModal">Batal</flux:button>
                <flux:button type="button" variant="primary" wire:click="createSectionFromModal">Tambahkan</flux:button>
            </div>
        </div>
    </div>
@endif
