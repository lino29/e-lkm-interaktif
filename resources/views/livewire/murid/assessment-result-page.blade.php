<div class="space-y-6 max-w-4xl mx-auto mt-8">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="text-center space-y-4">
        <h1 class="text-3xl font-bold text-elkm-text">Hasil Asesmen</h1>
        <p class="text-lg text-elkm-muted">{{ $assessment->title }}</p>
    </div>

    <div class="bg-white rounded-3xl border border-elkm-line p-8 shadow-sm text-center mt-6">
        <div class="mb-6">
            <div class="text-sm font-semibold text-elkm-muted mb-2">Nilai Anda</div>
            <div class="text-6xl font-black {{ $latestAttempt->status === 'tuntas' ? 'text-elkm-primary' : 'text-elkm-danger' }}">
                {{ round($latestAttempt->total_score) }}
            </div>
            <div class="text-sm text-elkm-muted mt-2">
                KKTP: {{ $assessment->kktp }}
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-semibold text-sm {{ $latestAttempt->status === 'tuntas' ? 'bg-[#e4f8ef] text-elkm-primary-2' : 'bg-red-50 text-red-600' }}">
            @if($latestAttempt->status === 'tuntas')
                <flux:icon.check-circle class="w-5 h-5" />
                <span>Tuntas! Anda telah menguasai materi ini.</span>
            @else
                <flux:icon.x-circle class="w-5 h-5" />
                <span>Belum Tuntas. Anda perlu melakukan remedial atau mengulang asesmen.</span>
            @endif
        </div>

        <div class="mt-8 flex items-center justify-center gap-4 border-t border-elkm-line pt-8">
            <a href="{{ route('murid.assessments.review', $assessment->id) }}" wire:navigate class="btn-elkm btn-elkm-outline">
                Review Hasil
            </a>
            
            @if ($canRetake)
                <a href="{{ route('murid.assessments.show', $assessment->id) }}" wire:navigate class="btn-elkm btn-elkm-primary">
                    Kerjakan Ulang
                </a>
            @endif
        </div>

        <div class="mt-6 text-xs text-elkm-muted">
            Percobaan {{ $attemptCount }} dari maksimal {{ $assessment->max_attempts }} kali
        </div>
    </div>
    
    <div class="text-center mt-6">
        @if ($assessment->learningUnit)
            <a href="{{ route('murid.learning-units.show', $assessment->learning_unit_id) }}" wire:navigate class="text-sm text-elkm-primary hover:underline font-semibold">
                &larr; Kembali ke Kegiatan Belajar
            </a>
        @else
            <a href="{{ route('murid.modules.show', $assessment->module_id) }}" wire:navigate class="text-sm text-elkm-primary hover:underline font-semibold">
                &larr; Kembali ke Modul
            </a>
        @endif
    </div>
</div>
