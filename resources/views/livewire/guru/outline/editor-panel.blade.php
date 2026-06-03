<main>
    @if ($selectedSection)
        <form
            wire:submit="saveSection"
            x-on:submit.capture="window.dispatchEvent(new CustomEvent('rich-editor:sync'))"
            class="space-y-5"
        >
            <flux:card class="space-y-5">
                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                    <div>
                        <flux:heading size="lg">{{ $form['title'] ?: 'Bagian' }}</flux:heading>
                        <flux:text>{{ $selectedSectionLabel }}</flux:text>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <flux:button type="button" size="sm" variant="ghost" wire:click="openAddSectionModal({{ $selectedSection->id }})">Tambah Subbagian</flux:button>
                        <flux:button type="button" size="sm" variant="ghost" wire:click="toggleVisibility({{ $selectedSection->id }})">{{ $selectedSection->is_visible ? 'Sembunyikan dari Murid' : 'Tampilkan untuk Murid' }}</flux:button>
                    </div>
                </div>

                @include($editorView)

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:checkbox wire:model="form.is_visible" label="Tampil untuk murid" />
                    <flux:checkbox wire:model="form.is_required" label="Wajib diselesaikan" />
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                </div>
            </flux:card>

            @include('livewire.guru.outline.media-section-card')
            @include('livewire.guru.outline.advanced-settings')
        </form>
    @else
        <flux:card>
            <flux:text>Pilih bagian dari struktur kegiatan belajar.</flux:text>
        </flux:card>
    @endif
</main>
