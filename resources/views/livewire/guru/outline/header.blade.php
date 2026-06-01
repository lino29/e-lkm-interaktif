<div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
    <div>
        <flux:heading size="xl">Kelola Outline KB</flux:heading>
        <flux:text>{{ $learningUnit->title }} - {{ $learningUnit->module->title }}</flux:text>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:button type="button" variant="ghost" wire:click="previewAsStudent">Preview Murid</flux:button>
        <flux:button type="button" variant="ghost" wire:click="generateOitlineTemplate" wire:confirm="Sinkronkan template OITLINE tanpa menghapus konten custom?">Generate Template OITLINE</flux:button>
        <flux:button type="button" variant="primary" wire:click="openAddSectionModal">Tambah Bagian</flux:button>
    </div>
</div>
