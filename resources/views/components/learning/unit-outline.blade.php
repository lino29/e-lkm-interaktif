@props([
    'sections',
    'activeSectionId' => null,
])

<div class="rounded-lg border bg-white p-4 shadow-sm dark:bg-zinc-900">
    <div class="mb-3 font-semibold">Outline Kegiatan Belajar</div>

    <nav class="space-y-1">
        @foreach ($sections->where('is_visible', true) as $section)
            <button
                type="button"
                wire:click="openSection({{ $section->id }})"
                class="w-full rounded-md px-3 py-2 text-left text-sm {{ $activeSectionId === $section->id ? 'bg-blue-600 text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
            >
                {{ $section->title }}
            </button>

            @if ($section->children->isNotEmpty())
                <div class="ml-4 space-y-1 border-l pl-3">
                    @foreach ($section->children->where('is_visible', true) as $child)
                        <button
                            type="button"
                            wire:click="openSection({{ $child->id }})"
                            class="w-full rounded-md px-3 py-2 text-left text-xs {{ $activeSectionId === $child->id ? 'bg-blue-100 font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-200' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                        >
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
</div>
