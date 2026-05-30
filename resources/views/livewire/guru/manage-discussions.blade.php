<div class="space-y-4">
    <flux:heading size="xl">Diskusi dan Refleksi</flux:heading>
    
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif

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
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-semibold">{{ $discussion->title ?? 'Komentar diskusi' }}</div>
                    <flux:text>{{ $discussion->user->name }} - {{ $discussion->learningUnit->module->title }} - {{ $discussion->learningUnit->title }} - {{ $discussion->replies_count }} balasan</flux:text>
                    @if($discussion->is_pinned)
                        <flux:badge size="sm" color="blue" class="mt-1">Pinned</flux:badge>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" wire:click="togglePinned({{ $discussion->id }})">{{ $discussion->is_pinned ? 'Lepas Pin' : 'Pin' }}</flux:button>
                    <flux:button size="sm" variant="danger" wire:click="delete({{ $discussion->id }})">Hapus</flux:button>
                </div>
            </div>
            
            <p class="mt-4 text-sm whitespace-pre-wrap">{{ $discussion->body }}</p>

            <div class="mt-4 space-y-3">
                @foreach ($discussion->replies as $reply)
                    <div class="border-l-2 pl-3 py-1 text-sm bg-zinc-50 dark:bg-zinc-800/50 rounded-r" wire:key="discussion-reply-{{ $reply->id }}">
                        <div class="flex justify-between">
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $reply->user->name }}</div>
                            <button wire:click="delete({{ $reply->id }})" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>
            
            <form wire:submit="replyToDiscussion({{ $discussion->id }})" class="mt-4 space-y-3">
                <flux:field>
                    <flux:textarea wire:model="replyBodies.{{ $discussion->id }}" placeholder="Tulis balasan atau feedback..." />
                    <flux:error name="replyBodies.{{ $discussion->id }}" />
                </flux:field>
                <flux:button type="submit" size="sm" variant="primary">Kirim Balasan</flux:button>
            </form>
        </flux:card>
    @empty
        <flux:text>Belum ada diskusi.</flux:text>
    @endforelse
</div>
