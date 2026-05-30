<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $learningUnit->title }}</flux:heading>
        <flux:text>{{ $learningUnit->module->title }}</flux:text>
    </div>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    @if ($learningUnit->objectives)
        <flux:callout>{{ $learningUnit->objectives }}</flux:callout>
    @endif
    <section class="space-y-3">
        <flux:heading>Materi</flux:heading>
        @foreach ($learningUnit->materials as $material)
            <flux:card wire:key="unit-material-{{ $material->id }}"><div class="font-semibold">{{ $material->title }}</div><p class="mt-2 text-sm leading-6">{{ $material->content }}</p></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Media</flux:heading>
        @forelse ($learningUnit->media as $media)
            <flux:card wire:key="unit-media-{{ $media->id }}"><div class="font-semibold">{{ $media->title }}</div><flux:text>{{ $media->type }} - {{ $media->url ?? $media->file_path ?? 'Placeholder tersedia' }}</flux:text></flux:card>
        @empty
            <flux:text>Belum ada media untuk kegiatan ini.</flux:text>
        @endforelse
    </section>
    <section class="space-y-3">
        <flux:heading>Aktivitas Interaktif</flux:heading>
        @foreach ($learningUnit->activities as $activity)
            <flux:card wire:key="unit-activity-{{ $activity->id }}"><div class="font-semibold">{{ $activity->title }}</div><flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text><flux:button class="mt-3" size="sm" :href="route('murid.activities.show', $activity)" wire:navigate>Kerjakan</flux:button></flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Asesmen Formatif</flux:heading>
        @foreach ($learningUnit->assessments as $assessment)
            <flux:card wire:key="unit-assessment-{{ $assessment->id }}"><div class="font-semibold">{{ $assessment->title }}</div><flux:text>KKTP {{ $assessment->kktp }} - Maks {{ $assessment->max_attempts }} percobaan</flux:text><flux:button class="mt-3" size="sm" :href="route('murid.assessments.show', $assessment)" wire:navigate>Kerjakan Asesmen</flux:button></flux:card>
        @endforeach
    </section>
    <section class="space-y-4">
        <flux:heading>Forum Diskusi</flux:heading>
        <flux:card>
            <form wire:submit="submitDiscussion" class="space-y-3">
                <flux:field>
                    <flux:label>Komentar utama</flux:label>
                    <flux:textarea wire:model="discussionBody" />
                    <flux:error name="discussionBody" />
                </flux:field>
                <flux:button type="submit" variant="primary">Kirim Komentar</flux:button>
            </form>
        </flux:card>
        @forelse ($discussions as $discussion)
            <flux:card wire:key="unit-discussion-{{ $discussion->id }}">
                <div class="font-semibold">{{ $discussion->user->name }}</div>
                <p class="mt-2 text-sm">{{ $discussion->body }}</p>
                <div class="mt-4 space-y-2">
                    @foreach ($discussion->replies as $reply)
                        <div class="border-l-2 pl-3 text-sm" wire:key="unit-discussion-reply-{{ $reply->id }}">
                            <div class="font-medium">{{ $reply->user->name }}</div>
                            <p>{{ $reply->body }}</p>
                        </div>
                    @endforeach
                </div>
                <form wire:submit="replyToDiscussion({{ $discussion->id }})" class="mt-4 space-y-3">
                    <flux:field>
                        <flux:label>Balasan</flux:label>
                        <flux:textarea wire:model="replyBodies.{{ $discussion->id }}" />
                        <flux:error name="replyBodies.{{ $discussion->id }}" />
                    </flux:field>
                    <flux:button type="submit" size="sm">Balas</flux:button>
                </form>
            </flux:card>
        @empty
            <flux:text>Belum ada diskusi pada kegiatan ini.</flux:text>
        @endforelse
    </section>
</div>
