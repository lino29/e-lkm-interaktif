<div class="space-y-6">
    <flux:heading size="xl">Proyek Saya</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    
    @php
        $isDisabled = $currentProject && $currentProject->status === 'reviewed';
    @endphp

    @if ($isDisabled)
        <flux:callout variant="warning">Proyek ini sudah dinilai oleh guru dan tidak dapat diubah lagi.</flux:callout>
    @endif

    <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
        <flux:field><flux:label>Modul</flux:label><flux:select wire:model.live="module_id"><flux:select.option value="">Pilih</flux:select.option>@foreach ($modules as $module)<flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>@endforeach</flux:select></flux:field>
        <flux:field><flux:label>Judul proyek</flux:label><flux:input wire:model="project_title" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Masalah</flux:label><flux:textarea wire:model="problem" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Tujuan</flux:label><flux:textarea wire:model="objective" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Alat dan bahan</flux:label><flux:textarea wire:model="tools_materials" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Langkah kerja</flux:label><flux:textarea wire:model="procedure" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Data yang dikumpulkan</flux:label><flux:textarea wire:model="collected_data" :disabled="$isDisabled" /></flux:field>
        <flux:field><flux:label>Hasil yang diharapkan</flux:label><flux:textarea wire:model="expected_result" :disabled="$isDisabled" /></flux:field>
        <flux:field class="md:col-span-2"><flux:label>Kesimpulan</flux:label><flux:textarea wire:model="conclusion" :disabled="$isDisabled" /></flux:field>
        <flux:field class="md:col-span-2">
            <flux:label>File bukti</flux:label>
            <input type="file" wire:model="file" class="block w-full text-sm mt-1" {{ $isDisabled ? 'disabled' : '' }} />
            @if ($existing_file_path)
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    File saat ini: <a href="#" wire:click.prevent="downloadExistingFile" class="text-indigo-600 hover:underline">Unduh File</a>
                </div>
            @endif
        </flux:field>
        
        @if (!$isDisabled)
            <div class="flex items-center gap-3 md:col-span-2 mt-2">
                <flux:button type="button" wire:click="save('draft')">Simpan Draft</flux:button>
                <flux:button type="submit" variant="primary">Kirim Proyek</flux:button>
            </div>
        @endif
    </form>

    @foreach ($projects as $project)
        <flux:card wire:key="my-project-{{ $project->id }}">
            <div class="font-semibold">{{ $project->project_title }}</div>
            <flux:text>{{ $project->module->title }} - {{ $project->status }} - Nilai {{ $project->score ?? '-' }}</flux:text>
            @if ($project->feedback)
                <p class="mt-2 text-sm">{{ $project->feedback }}</p>
            @endif
        </flux:card>
    @endforeach
</div>
