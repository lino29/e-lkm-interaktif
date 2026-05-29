<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $assessment->title }}</flux:heading>
        <flux:text>KKTP {{ $assessment->kktp }} · Maks {{ $assessment->max_attempts }} percobaan</flux:text>
    </div>
    @if (session('status')) <flux:callout>{{ session('status') }}</flux:callout> @endif
    @if ($latestAttempt)
        <flux:card>
            <div class="font-semibold">Attempt terakhir: {{ $latestAttempt->total_score }}/{{ $latestAttempt->max_score }}</div>
            <flux:text>Status {{ $latestAttempt->status }} · {{ $latestAttempt->feedback }}</flux:text>
        </flux:card>
    @endif
    <form wire:submit="submit" class="space-y-4">
        @foreach ($assessment->questions as $question)
            <flux:card wire:key="answer-question-{{ $question->id }}">
                <div class="font-semibold">{{ $loop->iteration }}. {{ $question->question_text }}</div>
                @if (is_array($question->options) && $question->options !== [])
                    <div class="mt-2 grid gap-1 text-sm">
                        @foreach ($question->options as $key => $option)
                            <div>{{ $key }}. {{ $option }}</div>
                        @endforeach
                    </div>
                @endif
                <flux:field class="mt-3">
                    <flux:label>Jawaban</flux:label>
                    <flux:textarea wire:model="answers.{{ $question->id }}" placeholder="Untuk multi/jodohkan boleh isi JSON atau pisahkan koma" />
                </flux:field>
            </flux:card>
        @endforeach
        <flux:button type="submit" variant="primary">Kirim dan Nilai Otomatis</flux:button>
    </form>
</div>
