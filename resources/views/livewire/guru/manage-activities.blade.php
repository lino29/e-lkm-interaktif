<div class="space-y-6">
    <x-elkm.page-header 
        title="Kelola Aktivitas" 
        subtitle="Bangun aktivitas Ayo Mengamati sampai Forum Diskusi/Refleksi." 
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Kelola Aktivitas</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($editingActivityId)
                <button type="button" class="btn-elkm btn-elkm-outline" wire:click="cancelEdit">Batal Edit</button>
            @endif
        </x-slot:actions>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid lg:grid-cols-2 gap-4">
        <x-elkm.app-card title="Activity Engine Schema Builder">
            <form wire:submit="save" class="grid gap-4 md:grid-cols-2 mt-4">
                <flux:field>
                    <flux:label>Kegiatan belajar</flux:label>
                    <flux:select wire:model="learning_unit_id">
                        <flux:select.option value="">Pilih kegiatan</flux:select.option>
                        @foreach ($learningUnits as $unit)
                            <flux:select.option value="{{ $unit->id }}">{{ $unit->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="learning_unit_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Judul aktivitas</flux:label>
                    <flux:input wire:model="title" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Fase E-LKM</flux:label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <flux:select wire:model.live="phase">
                                @foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'] as $phaseOption)
                                    <flux:select.option value="{{ $phaseOption }}">{{ \Illuminate\Support\Str::headline($phaseOption) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                        <button type="button" class="btn-elkm btn-elkm-outline !px-3" wire:click="applyTemplate">
                            Gunakan Template
                        </button>
                    </div>
                    <flux:error name="phase" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipe input murid</flux:label>
                    <flux:select wire:model="input_type">
                        @foreach (['short_text', 'essay', 'table', 'fields', 'file', 'discussion', 'project_form'] as $inputOption)
                            <flux:select.option value="{{ $inputOption }}">{{ \Illuminate\Support\Str::headline($inputOption) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="input_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Urutan</flux:label>
                    <flux:input type="number" min="1" wire:model="order" />
                    <flux:error name="order" />
                </flux:field>

                <div class="space-y-2 pt-8">
                    <flux:checkbox wire:model="is_required" label="Wajib dikerjakan murid" />
                    <flux:error name="is_required" />
                    
                    <flux:checkbox wire:model="requires_teacher_review" label="Wajib direview guru" />
                    <flux:error name="requires_teacher_review" />
                </div>

                <flux:field class="md:col-span-2">
                    <flux:label>Instruksi</flux:label>
                    <flux:textarea wire:model="prompt" rows="5" />
                    <flux:error name="prompt" />
                </flux:field>
                
                <flux:field class="md:col-span-2">
                    <flux:label>Answer Schema (JSON)</flux:label>
                    <flux:textarea wire:model="answer_schema" rows="6" placeholder='{"columns": [{"name": "alat", "label": "Nama Alat", "type": "text"}], "min_rows": 1, "allow_add": true}' class="font-mono text-sm" />
                    <flux:error name="answer_schema" />
                </flux:field>

                <flux:field>
                    <flux:label>Display Config (JSON)</flux:label>
                    <flux:textarea wire:model="display_config" rows="4" placeholder="{}" class="font-mono text-sm" />
                    <flux:error name="display_config" />
                </flux:field>

                <flux:field>
                    <flux:label>Validation Rules (JSON)</flux:label>
                    <flux:textarea wire:model="validation_rules" rows="4" placeholder="{}" class="font-mono text-sm" />
                    <flux:error name="validation_rules" />
                </flux:field>

                @if ($phase === 'ayo_mengamati')
                    <div
                        class="space-y-4 rounded-[18px] border border-[#c7eadb] bg-[#e4f8ef] p-4 md:col-span-2"
                        x-data="{ uploading: false, progress: 0, finished: false, failed: false }"
                        x-on:livewire-upload-start="uploading = true; finished = false; failed = false; progress = 0"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        x-on:livewire-upload-finish="uploading = false; finished = true; progress = 100"
                        x-on:livewire-upload-error="uploading = false; failed = true"
                        x-on:livewire-upload-cancel="uploading = false"
                    >
                        <div>
                            <flux:heading size="lg" class="text-elkm-primary-2">Media Ayo Mengamati</flux:heading>
                            <flux:text>Upload gambar atau video yang akan tampil pada halaman aktivitas murid.</flux:text>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:field>
                                <flux:label>Tipe media</flux:label>
                                <flux:select wire:model="mediaType">
                                    <flux:select.option value="image">Gambar</flux:select.option>
                                    <flux:select.option value="video">Video</flux:select.option>
                                    <flux:select.option value="youtube">YouTube</flux:select.option>
                                </flux:select>
                                <flux:error name="mediaType" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Judul media</flux:label>
                                <flux:input wire:model="mediaTitle" placeholder="Contoh: Video pengamatan panel surya" />
                                <flux:error name="mediaTitle" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Upload gambar/video</flux:label>
                                <flux:input type="file" wire:model="mediaFile" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime" />
                                <flux:error name="mediaFile" />
                                @if ($mediaPath)
                                    <flux:text>File tersimpan: {{ $mediaPath }}</flux:text>
                                @endif
                            </flux:field>

                            <flux:field>
                                <flux:label>URL YouTube</flux:label>
                                <flux:input wire:model="mediaUrl" placeholder="https://www.youtube.com/watch?v=..." />
                                <flux:error name="mediaUrl" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Caption</flux:label>
                            <flux:textarea wire:model="mediaCaption" rows="2" placeholder="Instruksi singkat atau sumber media." />
                            <flux:error name="mediaCaption" />
                        </flux:field>

                        <div x-show="uploading" class="rounded-xl border border-[#b3d7c5] bg-white p-3 text-sm text-elkm-primary shadow-sm">
                            <div class="mb-2 flex items-center gap-2 font-medium">
                                <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                                <span x-text="'Mengupload media... ' + progress + '%'"></span>
                            </div>
                            <x-elkm.progress-bar ::percent="progress" />
                        </div>

                        <div x-show="finished && ! uploading" class="rounded-xl border border-[#c7eadb] bg-white px-3 py-2 text-sm text-elkm-primary font-semibold">
                            Upload selesai. Klik Simpan Aktivitas untuk menyimpan media ke aktivitas.
                        </div>

                        <div x-show="failed" class="rounded-xl border border-elkm-danger bg-[#fff1f1] px-3 py-2 text-sm text-[#b83333]">
                            Upload gagal. Pastikan file berupa JPG, PNG, WebP, MP4, WebM, atau MOV dan maksimal 50MB.
                        </div>
                    </div>
                @endif

                <div class="md:col-span-2 mt-4">
                    <button type="submit" class="btn-elkm btn-elkm-primary">
                        {{ $editingActivityId ? 'Perbarui Aktivitas' : 'Simpan Aktivitas' }}
                    </button>
                </div>
            </form>
        </x-elkm.app-card>

        <x-elkm.app-card title="Daftar Aktivitas">
            <div class="grid gap-3 mt-4">
                @foreach ($activities as $activity)
                    <div wire:key="activity-{{ $activity->id }}" class="grid grid-cols-[42px_1fr_auto] gap-3 items-center p-3.5 border border-elkm-line rounded-[18px] bg-white">
                        <div class="w-[42px] h-[42px] rounded-xl bg-[#e8f5ef] text-elkm-primary-2 grid place-items-center font-black">{{ $activity->order }}</div>
                        <div>
                            <b class="text-[14px] leading-tight block">{{ $activity->title }}</b>
                            <small class="text-elkm-muted block mt-0.5">
                                {{ \Illuminate\Support\Str::headline($activity->phase) }} • 
                                <span class="text-elkm-primary-2 font-semibold">{{ $activity->input_type }}</span>
                            </small>
                            <small class="text-elkm-muted block">{{ $activity->learningUnit->title }}</small>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <button type="button" class="btn-elkm btn-elkm-soft !px-2.5 !py-1 !text-xs" wire:click="edit({{ $activity->id }})">Edit</button>
                            <button type="button" class="btn-elkm btn-elkm-danger !px-2.5 !py-1 !text-xs" wire:click="delete({{ $activity->id }})" wire:confirm="Hapus aktivitas ini?">Hapus</button>
                        </div>
                    </div>
                @endforeach
                
                @if($activities->isEmpty())
                    <x-elkm.empty-state title="Belum ada aktivitas" description="Silakan buat aktivitas baru dari form di samping." />
                @endif
            </div>
            
            <div class="mt-6 p-4 rounded-xl border border-elkm-line bg-[#fbfdfc]">
                <h4 class="font-bold text-sm mb-3">Schema Preview (Simulasi Render)</h4>
                @if($input_type)
                    @php
                        $mockSchema = is_array($answer_schema ?? []) 
                            ? ($answer_schema ?? []) 
                            : (json_decode($answer_schema ?? '{}', true) ?? []);
                        $mockSchema['input_type'] = $input_type;
                    @endphp
                    <x-elkm.activity-renderer :schema="$mockSchema" modelPrefix="preview" />
                @else
                    <p class="text-sm text-elkm-muted">Pilih tipe input murid untuk melihat preview render form.</p>
                @endif
            </div>
        </x-elkm.app-card>
    </div>
</div>
