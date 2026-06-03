@props(['section'])

@php($assessment = $section->linkedModel())

@if (! $assessment)
    <div class="card-elkm p-4 text-sm text-elkm-muted">Asesmen belum tersedia.</div>
@else
    <div class="space-y-4">
@inject('progress', 'App\Services\Learning\ProgressService')

        <div class="card-elkm p-4">
            <div class="font-bold text-elkm-text">{{ $assessment->title }}</div>
            <div class="mt-1 text-sm text-elkm-muted">
                KKTP: {{ $assessment->kktp }} | Maksimal Percobaan: {{ $assessment->max_attempts }}
            </div>
            
            @php($isLocked = !$progress->isAssessmentUnlocked(auth()->user(), $assessment))
            
            @if($isLocked)
                <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-800">
                    <div class="font-semibold mb-1 flex items-center gap-1.5"><flux:icon.lock-closed class="size-4" /> Akses Terkunci</div>
                    Anda belum menyelesaikan aktivitas pembelajaran sebelumnya. Harap lengkapi semua tahap secara berurutan untuk membuka asesmen ini.
                </div>
                <div class="mt-3">
                    <button class="btn-elkm btn-elkm-outline opacity-50 cursor-not-allowed w-full md:w-auto" disabled>
                        Kerjakan Asesmen
                    </button>
                </div>
            @else
                <div class="mt-4">
                    <a href="{{ route('murid.assessments.show', $assessment) }}" wire:navigate class="btn-elkm btn-elkm-primary">
                        Kerjakan Asesmen
                    </a>
                </div>
            @endif
        </div>

        <div class="space-y-2">
            @foreach ($section->children as $child)
                <button
                    type="button"
                    wire:click="openSection({{ $child->id }})"
                    class="block w-full card-elkm soft px-4 py-3 text-left text-sm text-elkm-text hover:bg-white transition-colors"
                >
                    {{ $child->title }}
                </button>
            @endforeach
        </div>
    </div>
@endif
