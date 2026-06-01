<div class="space-y-3">
    <details class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
        <summary class="cursor-pointer text-sm font-medium">Pengaturan Lanjutan</summary>
        <div class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
            Hanya untuk admin/developer. Pengaturan ini memengaruhi cara bagian terhubung ke data internal.
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>Slug</flux:label>
                <flux:input wire:model="form.slug" />
                <flux:error name="form.slug" />
            </flux:field>

            <flux:field>
                <flux:label>Parent</flux:label>
                <flux:select wire:model="form.parent_id">
                    <flux:select.option value="">Root</flux:select.option>
                    @foreach ($learningUnit->sections->reject(fn ($sectionOption) => $sectionOption->id === $selectedSection->id) as $sectionOption)
                        <flux:select.option value="{{ $sectionOption->id }}">{{ $sectionOption->title }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="form.parent_id" />
            </flux:field>

            <flux:field>
                <flux:label>Jenis Bagian</flux:label>
                <flux:select wire:model.live="form.section_type">
                    @foreach ($sectionTypes as $sectionType)
                        <flux:select.option value="{{ $sectionType }}">{{ $sectionTypeChoices[$sectionType] ?? $sectionType }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="form.section_type" />
            </flux:field>

            <flux:field>
                <flux:label>Tipe Editor</flux:label>
                <flux:select wire:model.live="form.editor_type">
                    @foreach ($editorTypes as $editorType)
                        <flux:select.option value="{{ $editorType }}">{{ $editorType }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="form.editor_type" />
            </flux:field>

            <flux:field>
                <flux:label>Urutan</flux:label>
                <flux:input type="number" min="1" wire:model="form.order" />
                <flux:error name="form.order" />
            </flux:field>

            <flux:field>
                <flux:label>Linked Model</flux:label>
                <flux:select wire:model="form.linked_model_type">
                    <flux:select.option value="">Tidak ada</flux:select.option>
                    @foreach ($linkableModels as $modelClass => $label)
                        <flux:select.option value="{{ $modelClass }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="form.linked_model_type" />
            </flux:field>

            <flux:field>
                <flux:label>Target ID</flux:label>
                <flux:input type="number" min="1" wire:model="form.linked_model_id" />
                <flux:error name="form.linked_model_id" />
            </flux:field>

            <flux:checkbox wire:model="form.is_locked" label="Kunci bagian" />
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>content_json</flux:label>
                <flux:textarea rows="8" wire:model="contentJsonText" />
                <flux:error name="contentJsonText" />
            </flux:field>

            <flux:field>
                <flux:label>settings</flux:label>
                <flux:textarea rows="8" wire:model="settingsText" />
                <flux:error name="settingsText" />
            </flux:field>
        </div>
    </details>

    <details class="rounded-lg border border-red-200 p-4 dark:border-red-900">
        <summary class="cursor-pointer text-sm font-medium text-red-700 dark:text-red-300">Zona Berbahaya</summary>
        <div class="mt-3 flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <div class="font-medium">Hapus Bagian</div>
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Bagian dan semua subbagian akan dihapus. Materi, aktivitas, dan asesmen asli tidak ikut terhapus.</div>
            </div>
            <flux:button type="button" variant="danger" wire:click="deleteSection({{ $selectedSection->id }})" wire:confirm="Hapus bagian ini beserta semua subbagian?">Hapus Bagian</flux:button>
        </div>
    </details>
</div>
