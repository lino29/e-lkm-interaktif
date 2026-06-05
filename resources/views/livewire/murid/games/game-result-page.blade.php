<div class="space-y-6">
    <x-elkm.page-header
        title="Hasil Game"
        subtitle="{{ $game->title }}"
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('murid.games.index') }}">Games</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Hasil</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <div class="text-sm text-elkm-muted">Skor</div>
            <div class="mt-1 text-3xl font-black text-elkm-text">{{ $attempt->score }}/{{ $attempt->max_score }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-elkm-muted">Persentase</div>
            <div class="mt-1 text-3xl font-black text-elkm-text">{{ $percentage }}%</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-elkm-muted">Durasi</div>
            <div class="mt-1 text-3xl font-black text-elkm-text">{{ $attempt->duration_seconds }}s</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-elkm-muted">Attempt</div>
            <div class="mt-1 text-3xl font-black text-elkm-text">{{ $attempt->attempt_number }}</div>
        </flux:card>
    </div>

    <flux:card class="space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading>Rincian Jawaban</flux:heading>
                <flux:text>{{ $attempt->answers->count() }} jawaban tersimpan pada attempt ini.</flux:text>
            </div>
            <flux:button href="{{ route('murid.games.index') }}" wire:navigate>Ke Games</flux:button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-elkm-line">
            <table class="min-w-full divide-y divide-elkm-line text-sm">
                <thead class="bg-elkm-surface text-left text-elkm-muted">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Skor</th>
                        <th class="px-4 py-3">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-elkm-line">
                    @foreach ($attempt->answers as $answer)
                        <tr wire:key="game-result-answer-{{ $answer->id }}">
                            <td class="px-4 py-3 font-medium text-elkm-text">{{ $answer->item->question_text ?? $answer->item->prompt }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $answer->is_correct ? 'green' : 'yellow' }}">{{ $answer->is_correct ? 'Benar/Optimal' : 'Perlu diperbaiki' }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">{{ $answer->score }}/{{ $answer->item->score }}</td>
                            <td class="px-4 py-3 text-elkm-muted">{{ $answer->feedback }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
</div>
