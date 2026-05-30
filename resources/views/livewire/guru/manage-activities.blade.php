<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="xl">Kelola Aktivitas</flux:heading>
            <flux:text>Bangun aktivitas Ayo Mengamati sampai Forum Diskusi/Refleksi.</flux:text>
        </div>
        @if ($editingActivityId)
            <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal Edit</flux:button>
        @endif
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
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
            <flux:select wire:model.live="phase">
                @foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'] as $phaseOption)
                    <flux:select.option value="{{ $phaseOption }}">{{ \Illuminate\Support\Str::headline($phaseOption) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="phase" />
        </flux:field>

        <flux:field>
            <flux:label>Tipe input murid</flux:label>
            <flux:select wire:model="input_type">
                @foreach (['short_text', 'essay', 'table', 'file', 'discussion'] as $inputOption)
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

        <flux:field>
            <flux:checkbox wire:model="is_required" label="Wajib dikerjakan murid" />
            <flux:error name="is_required" />
        </flux:field>
        
        <flux:field>
            <flux:checkbox wire:model="requires_teacher_review" label="Wajib direview guru" />
            <flux:error name="requires_teacher_review" />
        </flux:field>

        <flux:field class="md:col-span-2">
            <flux:label>Instruksi</flux:label>
            <flux:textarea wire:model="prompt" rows="5" />
            <flux:error name="prompt" />
        </flux:field>
        
        <flux:field class="md:col-span-2">
            <flux:label>Answer Schema (JSON)</flux:label>
            <flux:textarea wire:model="answer_schema" rows="6" placeholder='{"columns": [{"name": "alat", "label": "Nama Alat", "type": "text"}], "min_rows": 1, "allow_add": true}' />
            <flux:error name="answer_schema" />
        </flux:field>

        <flux:field>
            <flux:label>Display Config (JSON)</flux:label>
            <flux:textarea wire:model="display_config" rows="4" placeholder="{}" />
            <flux:error name="display_config" />
        </flux:field>

        <flux:field>
            <flux:label>Validation Rules (JSON)</flux:label>
            <flux:textarea wire:model="validation_rules" rows="4" placeholder="{}" />
            <flux:error name="validation_rules" />
        </flux:field>

        <div class="md:col-span-2">
            <flux:button type="submit" variant="primary">
                {{ $editingActivityId ? 'Perbarui Aktivitas' : 'Simpan Aktivitas' }}
            </flux:button>
        </div>
    </form>

    <div class="space-y-3">
        @foreach ($activities as $activity)
            <flux:card wire:key="activity-{{ $activity->id }}" class="space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="font-semibold">{{ $activity->order }}. {{ $activity->title }}</div>
                        <flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }}. {{ \Illuminate\Support\Str::headline($activity->input_type) }}. {{ $activity->learningUnit->title }}</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="edit({{ $activity->id }})">Edit</flux:button>
                        <flux:button size="sm" variant="danger" wire:click="delete({{ $activity->id }})" wire:confirm="Hapus aktivitas ini?">Hapus</flux:button>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
