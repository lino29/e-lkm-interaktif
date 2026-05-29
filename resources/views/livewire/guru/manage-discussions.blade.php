<div class="space-y-4">
    <flux:heading size="xl">Diskusi dan Refleksi</flux:heading>
    @forelse ($discussions as $discussion)
        <flux:card wire:key="discussion-{{ $discussion->id }}"><div class="font-semibold">{{ $discussion->title ?? 'Komentar diskusi' }}</div><flux:text>{{ $discussion->user->name }} · {{ $discussion->learningUnit->title }}</flux:text><p class="mt-2 text-sm">{{ $discussion->body }}</p></flux:card>
    @empty
        <flux:text>Belum ada diskusi.</flux:text>
    @endforelse
</div>
