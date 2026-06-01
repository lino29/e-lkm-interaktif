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
    @php
        $questionGroupService = app(\App\Services\Assessment\QuestionGroupService::class);
        $groups = $assessment->questions->groupBy(fn ($question) => $question->question_group ?? $questionGroupService->groupForType($question->question_type));
    @endphp

    <form wire:submit="submit" class="space-y-6">
        @foreach (\App\Services\Assessment\QuestionGroupService::GROUP_LABELS as $groupKey => $groupLabel)
            @continue(! $groups->has($groupKey))

            <section class="space-y-4" wire:key="assessment-question-group-{{ $groupKey }}">
                <flux:heading>{{ $groupLabel }}</flux:heading>

                @foreach ($groups[$groupKey] as $question)
                    <flux:card wire:key="answer-question-{{ $question->id }}">
                        <div class="font-semibold">{{ $question->order }}. {{ $question->question_text }}</div>
                
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
                                    @php
                                        $matchingOptions = (array) $question->options;
                                        $matchingLeftItems = isset($matchingOptions['left']) ? array_combine($matchingOptions['left'], $matchingOptions['left']) : $matchingOptions;
                                        $matchingRightItems = $matchingOptions['right'] ?? array_values((array) $question->correct_answer);
                                    @endphp
                                    <div class="rounded-md bg-zinc-50 p-2 text-xs text-zinc-500 dark:bg-zinc-800">
                                        Pilihan pasangan: {{ collect($matchingRightItems)->join(', ') }}
                                    </div>
                                    @foreach ($matchingLeftItems as $key => $option)
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
            </section>
        @endforeach

        <flux:button type="submit" variant="primary" :disabled="$currentAttempt === null">Kirim dan Nilai Otomatis</flux:button>
    </form>
</div>
