@if ($showMediaModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">Tambah Media</flux:heading>
                    <flux:text>Media ini akan tampil pada bagian yang sedang diedit.</flux:text>
                </div>
                <flux:button type="button" variant="ghost" wire:click="closeMediaModal">Tutup</flux:button>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Judul media</flux:label>
                    <flux:input wire:model="mediaTitle" />
                    <flux:error name="mediaTitle" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipe media</flux:label>
                    <flux:select wire:model="mediaType">
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

                <flux:field>
                    <flux:label>URL</flux:label>
                    <flux:input wire:model="mediaUrl" placeholder="https://..." />
                    <flux:error name="mediaUrl" />
                </flux:field>

                <flux:field>
                    <flux:label>Upload file</flux:label>
                    <flux:input type="file" wire:model="mediaFile" />
                    <flux:error name="mediaFile" />
                </flux:field>
            </div>

            <flux:field class="mt-4">
                <flux:label>Embed code</flux:label>
                <flux:textarea rows="3" wire:model="mediaEmbedCode" />
                <flux:error name="mediaEmbedCode" />
            </flux:field>

            <div class="mt-6 flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeMediaModal">Batal</flux:button>
                <flux:button type="button" variant="primary" wire:click="addMedia">Simpan Media</flux:button>
            </div>
        </div>
    </div>
@endif
