<flux:field>
    <flux:label>Upload file</flux:label>
    <flux:input type="file" wire:model="file" :disabled="$answer?->status === 'reviewed'" />
    <flux:error name="file" />

    @if ($answer?->file_path)
        <div class="mt-2 text-sm">
            File tersimpan:
            <a href="{{ \Illuminate\Support\Facades\Storage::url($answer->file_path) }}" target="_blank" class="text-blue-600 underline">
                Lihat File
            </a>
        </div>
    @endif
</flux:field>
