@props(['section'])

@php($assessment = $section->linkedModel())

@if (! $assessment)
    <div class="card-elkm p-4 text-sm text-elkm-muted">Asesmen belum tersedia.</div>
@else
    <div class="space-y-4">
        <div class="card-elkm p-4">
            <div class="font-bold text-elkm-text">{{ $assessment->title }}</div>
            <div class="mt-1 text-sm text-elkm-muted">
                KKTP: {{ $assessment->kktp }} | Maksimal Percobaan: {{ $assessment->max_attempts }}
            </div>
            <div class="mt-4">
                <a href="{{ route('murid.assessments.show', $assessment) }}" wire:navigate class="btn-elkm btn-elkm-primary">
                    Kerjakan Asesmen
                </a>
            </div>
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
