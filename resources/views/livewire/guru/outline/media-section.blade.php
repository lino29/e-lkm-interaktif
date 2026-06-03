@if ($showMediaModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
         x-data="{ 
             uploading: false, 
             progress: 0, 
             finished: false, 
             failed: false,
             mediaType: @entangle('mediaType')
         }"
         x-on:livewire-upload-start="uploading = true; finished = false; failed = false; progress = 0"
         x-on:livewire-upload-finish="uploading = false; finished = true"
         x-on:livewire-upload-error="uploading = false; failed = true"
         x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Tambah Media</flux:heading>
                    <flux:text>Media ini akan tampil pada bagian yang sedang diedit.</flux:text>
                </div>
                <flux:button type="button" variant="ghost" wire:click="closeMediaModal" x-bind:disabled="uploading">Tutup</flux:button>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Judul media</flux:label>
                    <flux:input wire:model="mediaTitle" />
                    <flux:error name="mediaTitle" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipe media</flux:label>
                    <flux:select wire:model.live="mediaType">
                        <flux:select.option value="image">Gambar</flux:select.option>
                        <flux:select.option value="video_file">Video file</flux:select.option>
                        <flux:select.option value="youtube">YouTube</flux:select.option>
                        <flux:select.option value="simulation">Simulasi</flux:select.option>
                        <flux:select.option value="file">File</flux:select.option>
                        <flux:select.option value="link">Link</flux:select.option>
                        <flux:select.option value="embed">Embed</flux:select.option>
                    </flux:select>
                    <flux:error name="mediaType" />
                </flux:field>

                <flux:field x-show="!['image', 'video_file', 'file'].includes(mediaType)">
                    <flux:label>URL</flux:label>
                    <flux:input wire:model="mediaUrl" placeholder="https://..." />
                    <flux:error name="mediaUrl" />
                </flux:field>

                <div class="space-y-3" x-show="['image', 'video_file', 'file'].includes(mediaType)">
                    <flux:field>
                        <flux:label>Upload file</flux:label>
                        <flux:input type="file" wire:model="mediaFile" />
                        <flux:error name="mediaFile" />
                    </flux:field>

                    <!-- Upload Progress UI -->
                    <div x-show="uploading" class="rounded-xl border border-[#b3d7c5] bg-[#fbfdfc] p-3 text-sm text-elkm-primary shadow-sm" x-cloak>
                        <div class="mb-2 flex items-center gap-2 font-medium">
                            <span class="size-4 animate-spin rounded-full border-2 border-elkm-primary border-t-transparent"></span>
                            <span x-text="'Mengupload media... ' + progress + '%'"></span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-elkm-primary transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    <div x-show="failed" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-600 shadow-sm" x-cloak>
                        <div class="flex items-center gap-2 font-medium">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Gagal mengupload media. Coba lagi atau periksa ukuran file.</span>
                        </div>
                    </div>

                    <div x-show="finished && !failed" class="rounded-xl border border-[#c7eadb] bg-[#e4f8ef] p-3 text-sm text-elkm-primary-2 shadow-sm" x-cloak>
                        <div class="flex items-center gap-2 font-medium">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Media berhasil diunggah dan siap disimpan.</span>
                        </div>
                    </div>
                </div>
            </div>

            <flux:field class="mt-4" x-show="mediaType === 'embed'">
                <flux:label>Embed code</flux:label>
                <flux:textarea rows="3" wire:model="mediaEmbedCode" />
                <flux:error name="mediaEmbedCode" />
            </flux:field>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeMediaModal" x-bind:disabled="uploading">Batal</flux:button>
                <flux:button type="button" variant="primary" wire:click="addMedia" x-bind:disabled="uploading">Simpan Media</flux:button>
            </div>
        </div>
    </div>
@endif
