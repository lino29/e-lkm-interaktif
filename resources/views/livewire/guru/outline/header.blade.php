<x-elkm.page-header 
    title="Kelola Outline KB" 
    subtitle="{{ $learningUnit->title }} - {{ $learningUnit->module->title }}" 
>
    <x-slot:breadcrumbs>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('guru.learning-units') }}">Kegiatan Belajar</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Outline</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:breadcrumbs>
    <x-slot:actions>
        <flux:button type="button" variant="ghost" wire:click="previewAsStudent">Preview Murid</flux:button>
        <flux:button type="button" variant="ghost" wire:click="generateOitlineTemplate" wire:confirm="Sinkronkan template OITLINE tanpa menghapus konten custom?">Generate Template OITLINE</flux:button>
        <flux:button type="button" variant="primary" wire:click="openAddSectionModal">Tambah Bagian</flux:button>
    </x-slot:actions>
</x-elkm.page-header>
