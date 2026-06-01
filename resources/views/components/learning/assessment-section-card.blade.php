@props(['section'])

@php($assessment = $section->linkedModel())

@if (! $assessment)
    <div class="rounded-lg border p-4 text-sm text-zinc-500">Asesmen belum tersedia.</div>
@else
    <div class="space-y-4">
        <div class="rounded-lg border p-4">
            <div class="font-semibold">{{ $assessment->title }}</div>
            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                KKTP: {{ $assessment->kktp }} | Maksimal Percobaan: {{ $assessment->max_attempts }}
            </div>
            <flux:button class="mt-4" size="sm" variant="primary" :href="route('murid.assessments.show', $assessment)" wire:navigate>
                Kerjakan Asesmen
            </flux:button>
        </div>

        <div class="space-y-2">
            @foreach ($section->children as $child)
                <button
                    type="button"
                    wire:click="openSection({{ $child->id }})"
                    class="block w-full rounded-md border px-4 py-3 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800"
                >
                    {{ $child->title }}
                </button>
            @endforeach
        </div>
    </div>
@endif
