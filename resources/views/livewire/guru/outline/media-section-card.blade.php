<flux:card class="space-y-4">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
        <div>
            <flux:heading size="lg">Media Pendukung</flux:heading>
            <flux:text>Tambahkan gambar, video, file, atau link yang membantu murid memahami bagian ini.</flux:text>
        </div>
        <flux:button type="button" wire:click="openMediaModal">Tambah Media</flux:button>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        @forelse ($selectedSection->media as $media)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-800" wire:key="section-media-{{ $media->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-medium">{{ $media->title }}</div>
                        <flux:text>{{ \Illuminate\Support\Str::headline($media->type) }}</flux:text>
                    </div>
                    <flux:button type="button" size="sm" variant="danger" wire:click="deleteMedia({{ $media->id }})" wire:confirm="Hapus media ini?">Hapus</flux:button>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                Belum ada media pendukung.
            </div>
        @endforelse
    </div>
</flux:card>
