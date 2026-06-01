<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Outline Modul</flux:heading>
            <flux:text>{{ $module->title }}</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button type="button" variant="ghost" wire:click="generateTemplate" wire:confirm="Sinkronkan template tanpa menimpa konten yang sudah diisi?">Generate Template</flux:button>
            <flux:button type="button" :href="route('guru.modules.show', $module)" wire:navigate>Kembali</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <flux:card class="space-y-4">
            <div>
                <flux:heading size="lg">Editor Section Modul</flux:heading>
                <flux:text>Kelola pendahuluan dan penutup modul dengan editor rich text yang sama seperti Outline KB.</flux:text>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Tipe</flux:label>
                        <flux:select wire:model="section_type">
                            <flux:select.option value="introduction">Pendahuluan</flux:select.option>
                            <flux:select.option value="closing">Penutup</flux:select.option>
                        </flux:select>
                        <flux:error name="section_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Urutan</flux:label>
                        <flux:input type="number" min="1" wire:model="order" />
                        <flux:error name="order" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Judul</flux:label>
                    <flux:input wire:model.live="title" />
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input wire:model="slug" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:checkbox wire:model="is_visible" label="Tampil untuk murid" />

                <flux:field>
                    <flux:label>Konten</flux:label>
                    <x-forms.rich-editor wire:model.live="content" id="module-section-editor" />
                    <flux:error name="content" />
                </flux:field>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingSectionId ? 'Simpan Perubahan' : 'Tambah Section' }}</flux:button>
                    @if ($editingSectionId)
                        <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                    @endif
                </div>
            </form>
        </flux:card>

        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Daftar Section Modul</flux:heading>
                <flux:text>Pilih section untuk mengedit konten, urutan, dan visibilitas.</flux:text>
            </div>

            @foreach (['introduction' => 'Pendahuluan', 'closing' => 'Penutup'] as $type => $label)
                <flux:card class="space-y-3">
                    <flux:heading size="lg">{{ $label }}</flux:heading>

                    @foreach ($sections->where('section_type', $type) as $section)
                        <div @class([
                            'rounded-lg border p-4 transition',
                            'border-blue-300 bg-blue-50/60 dark:border-blue-700 dark:bg-blue-950/20' => $editingSectionId === $section->id,
                            'border-zinc-200 dark:border-zinc-800' => $editingSectionId !== $section->id,
                        ]) wire:key="module-outline-section-{{ $section->id }}">
                            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                                <div class="space-y-1">
                                    <div class="font-semibold">{{ $section->order }}. {{ $section->title }}</div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:text>{{ $section->slug }}</flux:text>
                                        <flux:badge size="sm" color="{{ $section->is_visible ? 'green' : 'zinc' }}">
                                            {{ $section->is_visible ? 'Tampil' : 'Disembunyikan' }}
                                        </flux:badge>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" variant="ghost" wire:click="edit({{ $section->id }})">Edit</flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="move({{ $section->id }}, 'up')">Naik</flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="move({{ $section->id }}, 'down')">Turun</flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="toggleVisibility({{ $section->id }})">{{ $section->is_visible ? 'Hide' : 'Show' }}</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete({{ $section->id }})" wire:confirm="Hapus section modul ini?">Hapus</flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </flux:card>
            @endforeach
        </div>
    </div>
</div>
