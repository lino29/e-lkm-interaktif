<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $assessment->title }}</flux:heading>
        <flux:text>KKTP {{ $assessment->kktp }} - Maks {{ $assessment->max_attempts }} percobaan</flux:text>
    </div>
    @if (session('status')) <flux:callout>{{ session('status') }}</flux:callout> @endif
    @if ($latestAttempt)
        <flux:card>
            <div class="font-semibold">Attempt terakhir: {{ $latestAttempt->total_score }}/{{ $latestAttempt->max_score }}</div>
            <flux:text>Status {{ $latestAttempt->status }} - {{ $latestAttempt->feedback }}</flux:text>
        </flux:card>
    @endif
    @if ($currentAttempt === null)
        <flux:callout>Batas percobaan asesmen sudah tercapai atau asesmen sudah tuntas.</flux:callout>
    @endif
    <form wire:submit="submit" class="space-y-4">
        @foreach ($assessment->questions as $question)
            <flux:card wire:key="answer-question-{{ $question->id }}">
                <div class="font-semibold">{{ $loop->iteration }}. {{ $question->question_text }}</div>
                
                <div class="mt-4">
                    @if ($question->question_type === 'multiple_choice')
                        <flux:radio.group wire:model="answers.{{ $question->id }}">
                            @foreach ($question->options as $key => $option)
                                <flux:radio value="{{ $key }}" label="{{ $key }}. {{ $option }}" />
                            @endforeach
                        </flux:radio.group>
                    
                    @elseif ($question->question_type === 'complex_multiple_choice')
                        <flux:checkbox.group wire:model="answers.{{ $question->id }}">
                            @foreach ($question->options as $key => $option)
                                <flux:checkbox value="{{ $key }}" label="{{ $key }}. {{ $option }}" />
                            @endforeach
                        </flux:checkbox.group>

                    @elseif ($question->question_type === 'true_false')
                        <flux:radio.group wire:model="answers.{{ $question->id }}">
                            <flux:radio value="true" label="Benar" />
                            <flux:radio value="false" label="Salah" />
                        </flux:radio.group>
                    
                    @elseif ($question->question_type === 'matching')
                        <div class="space-y-2">
                            <flux:text class="mb-2 text-sm text-zinc-500">Ketikkan pasangan jawaban yang benar (misal jika A cocok dengan 1, ketikkan 1 pada isian A)</flux:text>
                            @foreach ($question->options as $key => $option)
                                <flux:field>
                                    <flux:label>{{ $key }}. {{ $option }}</flux:label>
                                    <flux:input wire:model="answers.{{ $question->id }}.{{ $key }}" placeholder="Jawaban untuk {{ $key }}" />
                                </flux:field>
                            @endforeach
                        </div>
                    
                    @else
                        <flux:field>
                            <flux:textarea wire:model="answers.{{ $question->id }}" placeholder="Ketik jawaban di sini..." />
                        </flux:field>
                    @endif
                </div>
            </flux:card>
        @endforeach
        <flux:button type="submit" variant="primary" :disabled="$currentAttempt === null">Kirim dan Nilai Otomatis</flux:button>
    </form>
</div>
