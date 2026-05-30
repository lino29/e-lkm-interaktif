<div class="space-y-4">
    <flux:heading size="xl">Diskusi dan Refleksi</flux:heading>
    <div class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Filter Modul</flux:label>
            <flux:select wire:model.live="module_id">
                <flux:select.option value="">Semua modul</flux:select.option>
                @foreach ($modules as $module)
                    <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
        <flux:field>
            <flux:label>Filter Kegiatan Belajar</flux:label>
            <flux:select wire:model.live="learning_unit_id">
                <flux:select.option value="">Semua kegiatan</flux:select.option>
                @foreach ($learningUnits as $learningUnit)
                    <flux:select.option value="{{ $learningUnit->id }}">{{ $learningUnit->title }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
    </div>
    @forelse ($discussions as $discussion)
        <flux:card wire:key="discussion-{{ $discussion->id }}">
            <div class="font-semibold">{{ $discussion->title ?? 'Komentar diskusi' }}</div>
            <flux:text>{{ $discussion->user->name }} - {{ $discussion->learningUnit->module->title }} - {{ $discussion->learningUnit->title }} - {{ $discussion->replies_count }} balasan</flux:text>
            <p class="mt-2 text-sm">{{ $discussion->body }}</p>
            <div class="mt-3 flex gap-2">
                <flux:button size="sm" wire:click="togglePinned({{ $discussion->id }})">{{ $discussion->is_pinned ? 'Lepas Pin' : 'Pin' }}</flux:button>
                <flux:button size="sm" variant="danger" wire:click="delete({{ $discussion->id }})">Hapus</flux:button>
            </div>
        </flux:card>
    @empty
        <flux:text>Belum ada diskusi.</flux:text>
    @endforelse
</div>
