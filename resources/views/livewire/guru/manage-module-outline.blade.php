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
        <flux:card>
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
                    <x-forms.rich-editor wire:model="content" id="module-section-editor" />
                    <flux:error name="content" />
                </flux:field>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingSectionId ? 'Update Section' : 'Tambah Section' }}</flux:button>
                    @if ($editingSectionId)
                        <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                    @endif
                </div>
            </form>
        </flux:card>

        <div class="space-y-4">
            @foreach (['introduction' => 'Pendahuluan', 'closing' => 'Penutup'] as $type => $label)
                <flux:card class="space-y-3">
                    <flux:heading size="lg">{{ $label }}</flux:heading>

                    @foreach ($sections->where('section_type', $type) as $section)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800" wire:key="module-outline-section-{{ $section->id }}">
                            <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                                <div>
                                    <div class="font-semibold">{{ $section->order }}. {{ $section->title }}</div>
                                    <flux:text>{{ $section->slug }} {{ $section->is_visible ? '' : '- hidden' }}</flux:text>
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
