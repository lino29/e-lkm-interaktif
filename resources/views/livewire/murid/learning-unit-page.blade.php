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
            @php
                $statusData = $activityStatuses[$activity->id] ?? ['status' => 'belum_mulai', 'is_locked' => true];
                $isLocked = $statusData['is_locked'];
                $status = $statusData['status'];
            @endphp
            <flux:card wire:key="unit-activity-{{ $activity->id }}">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ $activity->title }}</div>
                        <flux:text>{{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text>
                        <div class="mt-1">
                            @if($status === 'submitted')
                                <flux:badge color="blue" size="sm">Menunggu Review</flux:badge>
                            @elseif($status === 'reviewed')
                                <flux:badge color="green" size="sm">Telah Dinilai</flux:badge>
                            @elseif($status === 'draft')
                                <flux:badge color="zinc" size="sm">Draft</flux:badge>
                            @elseif(!$isLocked)
                                <flux:badge color="zinc" size="sm">Belum Mulai</flux:badge>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($isLocked)
                            <flux:button size="sm" disabled>Terkunci</flux:button>
                        @elseif($status === 'reviewed')
                            <flux:button size="sm" variant="ghost" :href="route('murid.activities.show', $activity)" wire:navigate>Lihat Hasil</flux:button>
                        @else
                            <flux:button size="sm" variant="primary" :href="route('murid.activities.show', $activity)" wire:navigate>Kerjakan</flux:button>
                        @endif
                    </div>
                </div>
            </flux:card>
        @endforeach
    </section>
    <section class="space-y-3">
        <flux:heading>Asesmen Formatif</flux:heading>
        @foreach ($learningUnit->assessments as $assessment)
            @php
                $allActivitiesDone = collect($activityStatuses)->every(function($s, $activityId) use ($learningUnit) {
                    $activity = $learningUnit->activities->firstWhere('id', $activityId);
                    if (!$activity || !$activity->is_required) return true;
                    return in_array($s['status'], ['submitted', 'reviewed']);
                });
            @endphp
            <flux:card wire:key="unit-assessment-{{ $assessment->id }}">
                <div class="font-semibold">{{ $assessment->title }}</div>
                <flux:text>KKTP {{ $assessment->kktp }} - Maks {{ $assessment->max_attempts }} percobaan</flux:text>
                @if($allActivitiesDone)
                    <flux:button class="mt-3" size="sm" :href="route('murid.assessments.show', $assessment)" wire:navigate>Kerjakan Asesmen</flux:button>
                @else
                    <flux:button class="mt-3" size="sm" disabled>Selesaikan Semua Aktivitas Wajib</flux:button>
                @endif
            </flux:card>
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
