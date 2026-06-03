@props([
    'sections',
    'activeSectionId' => null,
])

<div class="card-elkm p-4">
    <div class="mb-4 text-[11px] font-extrabold uppercase tracking-widest text-elkm-muted">Outline Kegiatan Belajar</div>

    <nav class="space-y-1">
        @foreach ($sections->where('is_visible', true) as $section)
            <button
                type="button"
                wire:click="openSection({{ $section->id }})"
                class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold transition-colors {{ $activeSectionId === $section->id ? 'bg-elkm-primary text-white shadow-[0_8px_20px_rgba(15,143,95,0.22)]' : 'text-elkm-text hover:bg-elkm-surface-2' }}"
            >
                {{ $section->title }}
            </button>

            @if ($section->children->isNotEmpty())
                <div class="ml-4 space-y-1 border-l-2 border-elkm-line pl-3 my-1">
                    @foreach ($section->children->where('is_visible', true) as $child)
                        <button
                            type="button"
                            wire:click="openSection({{ $child->id }})"
                            class="w-full rounded-lg px-3 py-2 text-left text-[13px] font-semibold transition-colors {{ $activeSectionId === $child->id ? 'bg-[#e4f8ef] text-elkm-primary-2' : 'text-elkm-muted hover:bg-elkm-surface-2 hover:text-elkm-text' }}"
                        >
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
</div>
