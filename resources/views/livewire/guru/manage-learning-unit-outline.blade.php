<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Outline KB</flux:heading>
            <flux:text>{{ $learningUnit->title }} - {{ $learningUnit->module->title }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button type="button" variant="ghost" wire:click="previewAsStudent">Preview Murid</flux:button>
            <flux:button type="button" variant="ghost" wire:click="generateOitlineTemplate" wire:confirm="Sinkronkan template OITLINE tanpa menghapus konten custom?">Generate Template OITLINE</flux:button>
            <flux:button type="button" variant="primary" wire:click="createRootSection">Tambah Root</flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[360px_1fr]">
        <aside class="space-y-3">
            <flux:card class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="font-semibold">Tree Outline</div>
                    <flux:button type="button" size="sm" wire:click="createRootSection">Root</flux:button>
                </div>

                <div class="space-y-2">
                    @forelse ($tree as $node)
                        @include('livewire.guru.partials.outline-tree-node', ['node' => $node, 'level' => 0])
                    @empty
                        <flux:text>Belum ada section.</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </aside>

        <main>
            @if ($selectedSection)
                <form wire:submit="saveSection" class="space-y-5">
                    <flux:card class="space-y-5">
                        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                            <div>
                                <flux:heading size="lg">{{ $form['title'] ?: 'Section' }}</flux:heading>
                                <flux:text>{{ $form['section_type'] }} / {{ $form['editor_type'] }}</flux:text>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <flux:button type="button" size="sm" variant="ghost" wire:click="createChildSection({{ $selectedSection->id }})">Tambah Child</flux:button>
                                <flux:button type="button" size="sm" variant="ghost" wire:click="duplicateSection({{ $selectedSection->id }})">Duplicate</flux:button>
                                <flux:button type="button" size="sm" variant="ghost" wire:click="toggleVisibility({{ $selectedSection->id }})">{{ $selectedSection->is_visible ? 'Hide' : 'Show' }}</flux:button>
                                <flux:button type="button" size="sm" variant="danger" wire:click="deleteSection({{ $selectedSection->id }})" wire:confirm="Hapus section ini beserta child section?">Delete</flux:button>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:field>
                                <flux:label>Judul</flux:label>
                                <flux:input wire:model.live="form.title" />
                                <flux:error name="form.title" />
                            </flux:field>

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
                                <flux:label>Urutan</flux:label>
                                <flux:input type="number" min="1" wire:model="form.order" />
                                <flux:error name="form.order" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Section Type</flux:label>
                                <flux:select wire:model.live="form.section_type">
                                    @foreach ($sectionTypes as $sectionType)
                                        <flux:select.option value="{{ $sectionType }}">{{ $sectionType }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="form.section_type" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Editor Type</flux:label>
                                <flux:select wire:model.live="form.editor_type">
                                    @foreach ($editorTypes as $editorType)
                                        <flux:select.option value="{{ $editorType }}">{{ $editorType }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="form.editor_type" />
                            </flux:field>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <flux:checkbox wire:model="form.is_visible" label="Tampil untuk murid" />
                            <flux:checkbox wire:model="form.is_required" label="Wajib" />
                            <flux:checkbox wire:model="form.is_locked" label="Kunci section" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
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
                                <flux:label>Target</flux:label>
                                <flux:select wire:model="form.linked_model_id">
                                    <flux:select.option value="">Pilih target</flux:select.option>
                                    @foreach ($materials as $material)
                                        <flux:select.option value="{{ $material->id }}">Materi: {{ $material->title }}</flux:select.option>
                                    @endforeach
                                    @foreach ($activities as $activity)
                                        <flux:select.option value="{{ $activity->id }}">Aktivitas: {{ $activity->title }}</flux:select.option>
                                    @endforeach
                                    @foreach ($assessments as $assessment)
                                        <flux:select.option value="{{ $assessment->id }}">Asesmen: {{ $assessment->title }}</flux:select.option>
                                    @endforeach
                                    @foreach ($mediaItems as $media)
                                        <flux:select.option value="{{ $media->id }}">Media: {{ $media->title }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="form.linked_model_id" />
                            </flux:field>
                        </div>

                        @includeIf('livewire.guru.outline-editors.'.str_replace('_', '-', $form['editor_type']).'-editor')

                        <details class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                            <summary class="cursor-pointer text-sm font-medium">Advanced JSON</summary>
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

                        <div class="flex flex-wrap gap-2">
                            <flux:button type="submit" variant="primary">Simpan Section</flux:button>
                            <flux:button type="button" variant="ghost" wire:click="moveUp({{ $selectedSection->id }})">Naik</flux:button>
                            <flux:button type="button" variant="ghost" wire:click="moveDown({{ $selectedSection->id }})">Turun</flux:button>
                        </div>
                    </flux:card>

                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">Media Section</flux:heading>
                            <flux:text>Media tersimpan langsung ke section ini.</flux:text>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:field>
                                <flux:label>Judul media</flux:label>
                                <flux:input wire:model="mediaTitle" />
                                <flux:error name="mediaTitle" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Tipe media</flux:label>
                                <flux:select wire:model="mediaType">
                                    <flux:select.option value="image">image</flux:select.option>
                                    <flux:select.option value="video_file">video_file</flux:select.option>
                                    <flux:select.option value="youtube">youtube</flux:select.option>
                                    <flux:select.option value="simulation">simulation</flux:select.option>
                                    <flux:select.option value="file">file</flux:select.option>
                                    <flux:select.option value="link">link</flux:select.option>
                                    <flux:select.option value="embed">embed</flux:select.option>
                                </flux:select>
                                <flux:error name="mediaType" />
                            </flux:field>

                            <flux:field>
                                <flux:label>URL</flux:label>
                                <flux:input wire:model="mediaUrl" />
                                <flux:error name="mediaUrl" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Upload file</flux:label>
                                <flux:input type="file" wire:model="mediaFile" />
                                <flux:error name="mediaFile" />
                            </flux:field>
                        </div>

                        <flux:field>
                            <flux:label>Embed code</flux:label>
                            <flux:textarea rows="3" wire:model="mediaEmbedCode" />
                            <flux:error name="mediaEmbedCode" />
                        </flux:field>

                        <flux:button type="button" wire:click="addMedia">Tambah Media</flux:button>

                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($selectedSection->media as $media)
                                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800" wire:key="section-media-{{ $media->id }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium">{{ $media->title }}</div>
                                            <flux:text>{{ $media->type }}</flux:text>
                                        </div>
                                        <flux:button type="button" size="sm" variant="danger" wire:click="deleteMedia({{ $media->id }})" wire:confirm="Hapus media ini?">Hapus</flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                </form>
            @else
                <flux:card>
                    <flux:text>Pilih section untuk diedit.</flux:text>
                </flux:card>
            @endif
        </main>
    </div>
</div>
