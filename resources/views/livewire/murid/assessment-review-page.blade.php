<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Review Hasil: {{ $assessment->title }}</flux:heading>
            <flux:text>Menampilkan jawaban Anda (tanpa membocorkan kunci jawaban)</flux:text>
        </div>
        
        <a href="{{ route('murid.assessments.result', $assessment->id) }}" wire:navigate class="btn-elkm btn-elkm-soft">
            &larr; Kembali ke Hasil
        </a>
    </div>

    <div class="space-y-6">
        @foreach ($questions as $question)
            @php
                $studentAnswer = $this->getStudentAnswer($question->id);
                
                // Determine if answer is correct based on score vs max_score. 
                // A question might have partial scores. We check if score > 0 for correct/partial
                // and score == max_score for fully correct. But since we don't know max_score directly
                // without $scoringService, we can just use the score. If score > 0 it's partially/fully correct.
                // Assuming max score for multiple choice is 100 or something similar, or > 0 is correct.
                $isCorrect = $studentAnswer && $studentAnswer->score > 0;
                $isPartiallyCorrect = $studentAnswer && $studentAnswer->score > 0 && $studentAnswer->score < 100; // rough heuristic if needed, or just > 0
                
                $options = is_array($question->options ?? null) ? $question->options : [];
                $optionText = fn ($option) => is_scalar($option) ? (string) $option : '';
                
                // Get the raw answer given by student
                $rawAnswer = $studentAnswer ? ($studentAnswer->answer_json ?? $studentAnswer->answer_text) : null;
            @endphp

            <div class="rounded-3xl border border-elkm-line bg-white p-5 shadow-sm md:p-6" wire:key="review-question-{{ $question->id }}">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="text-base font-bold leading-relaxed text-elkm-text">
                        {{ $question->order }}. {{ $question->question_text }}
                    </div>
                    
                    @if ($studentAnswer)
                        @if ($isCorrect)
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e4f8ef] text-elkm-primary-2 text-xs font-bold shrink-0 border border-[#c7eadb]">
                                <flux:icon.check-circle class="w-4 h-4" />
                                Benar ({{ round($studentAnswer->score) }})
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-bold shrink-0 border border-red-200">
                                <flux:icon.x-circle class="w-4 h-4" />
                                Salah
                            </div>
                        @endif
                    @else
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold shrink-0 border border-gray-200">
                            Kosong
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    @if ($question->question_type === 'multiple_choice')
                        <div class="grid gap-3">
                            @foreach ($options as $key => $option)
                                @php
                                    $isSelected = (string)$rawAnswer === (string)$key;
                                    $bgClass = 'bg-elkm-surface/60 border-elkm-line text-elkm-text';
                                    if ($isSelected) {
                                        $bgClass = $isCorrect ? 'bg-[#e4f8ef] border-elkm-primary-2 text-elkm-primary-2 font-bold' : 'bg-red-50 border-red-300 text-red-700 font-bold';
                                    }
                                @endphp
                                <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm {{ $bgClass }}">
                                    <div class="mt-0.5 size-4 shrink-0 rounded-full border {{ $isSelected ? ($isCorrect ? 'bg-elkm-primary-2 border-elkm-primary-2' : 'bg-red-500 border-red-500') : 'border-gray-300' }}"></div>
                                    <span>{{ $key }}. {{ $optionText($option) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($question->question_type === 'complex_multiple_choice')
                        @php
                            $selectedArray = is_array($rawAnswer) ? $rawAnswer : [];
                        @endphp
                        <div class="grid gap-3">
                            @foreach ($options as $key => $option)
                                @php
                                    $isSelected = in_array((string)$key, array_map('strval', $selectedArray), true);
                                    $bgClass = 'bg-elkm-surface/60 border-elkm-line text-elkm-text';
                                    if ($isSelected) {
                                        // For complex multiple choice, we only know if the overall question is correct, not individual checkboxes
                                        $bgClass = 'bg-blue-50 border-blue-300 text-blue-700 font-bold';
                                    }
                                @endphp
                                <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm {{ $bgClass }}">
                                    <div class="mt-0.5 size-4 shrink-0 rounded border {{ $isSelected ? 'bg-blue-500 border-blue-500' : 'border-gray-300' }}">
                                        @if($isSelected)
                                            <svg class="w-4 h-4 text-white p-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        @endif
                                    </div>
                                    <span>{{ $key }}. {{ $optionText($option) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($question->question_type === 'true_false')
                        @php
                            $trueFalseOptions = $options ?: ['True' => 'Benar', 'False' => 'Salah'];
                        @endphp
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($trueFalseOptions as $key => $option)
                                @php
                                    $isSelected = (string)$rawAnswer === (string)$key;
                                    $bgClass = 'bg-elkm-surface/60 border-elkm-line text-elkm-text';
                                    if ($isSelected) {
                                        $bgClass = $isCorrect ? 'bg-[#e4f8ef] border-elkm-primary-2 text-elkm-primary-2 font-bold' : 'bg-red-50 border-red-300 text-red-700 font-bold';
                                    }
                                @endphp
                                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold {{ $bgClass }}">
                                    <div class="size-4 shrink-0 rounded-full border {{ $isSelected ? ($isCorrect ? 'bg-elkm-primary-2 border-elkm-primary-2' : 'bg-red-500 border-red-500') : 'border-gray-300' }}"></div>
                                    <span>{{ $optionText($option) ?: \Illuminate\Support\Str::headline($key) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($question->question_type === 'matching')
                        @php
                            $hasStructuredMatchingOptions = isset($options['left'], $options['right']) && is_array($options['left']) && is_array($options['right']);
                            $leftOptions = $hasStructuredMatchingOptions ? $options['left'] : array_keys($options);
                            
                            $selectedArray = is_array($rawAnswer) ? $rawAnswer : [];
                        @endphp
                        
                        <div class="grid gap-3">
                            @foreach ($leftOptions as $leftKey => $leftValue)
                                @php
                                    $answerKey = $hasStructuredMatchingOptions ? $leftKey : $leftValue;
                                    $studentSelection = $selectedArray[$answerKey] ?? 'Belum dijawab';
                                @endphp
                                <div class="grid gap-3 rounded-2xl border border-elkm-line bg-elkm-surface/60 p-4 md:grid-cols-[1fr_16rem] md:items-center">
                                    <div class="text-sm font-semibold text-elkm-text">
                                        {{ $hasStructuredMatchingOptions ? "{$leftKey}. {$leftValue}" : $leftValue }}
                                    </div>
                                    <div class="w-full rounded-xl border border-blue-300 bg-blue-50 px-3 py-2 text-sm text-blue-700 font-bold">
                                        Jawaban: {{ $studentSelection }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-blue-300 bg-blue-50 p-4 text-sm text-blue-700 font-medium">
                            <div class="text-xs text-blue-500 mb-1 uppercase tracking-wider font-bold">Jawaban Anda:</div>
                            {!! nl2br(e($rawAnswer ?: 'Belum dijawab')) !!}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
