@props([
    'sections',
    'activeSectionId'    => null,
    'activityStatuses'   => [],
    'assessmentStatuses' => [],
])

@php
/**
 * Resolve the status icon for a given section node.
 * Returns one of: 'done', 'draft', 'locked', 'open'
 */
function sectionStatus(
    $section,
    array $activityStatuses,
    array $assessmentStatuses
): string {
    $type       = $section->section_type ?? '';
    $modelType  = $section->linked_model_type ?? '';
    $modelId    = $section->linked_model_id;

    // Activity-backed sections
    if (
        in_array($type, ['activity_item', 'forum'], true)
        && str_contains($modelType, 'Activity')
        && $modelId
    ) {
        $info = $activityStatuses[$modelId] ?? null;
        if (! $info) { return 'open'; }
        if ($info['is_locked'])                                         return 'locked';
        if (in_array($info['status'], ['submitted', 'reviewed'], true)) return 'done';
        if ($info['status'] === 'draft')                                return 'draft';
        return 'open';
    }

    // Assessment-backed sections
    if (
        in_array($type, ['assessment_group'], true)
        && str_contains($modelType, 'Assessment')
        && $modelId
    ) {
        $info = $assessmentStatuses[$modelId] ?? null;
        if (! $info) { return 'open'; }
        if ($info['is_locked'])                   return 'locked';
        if ($info['status'] === 'tuntas')         return 'done';
        if ($info['status'] === 'remedial')       return 'draft';
        if ($info['status'] !== 'belum_mulai')    return 'draft';
        return 'open';
    }

    return 'none'; // sections like material, key_points: no icon needed
}
@endphp

<div class="card-elkm p-4">
    <div class="mb-4 text-[11px] font-extrabold uppercase tracking-widest text-elkm-muted">Outline Kegiatan Belajar</div>

    <nav class="space-y-1">
        @foreach ($sections->where('is_visible', true) as $section)
            @php
                $isActive  = $activeSectionId === $section->id;
                $status    = sectionStatus($section, $activityStatuses, $assessmentStatuses);
            @endphp

            <button
                type="button"
                wire:click="openSection({{ $section->id }})"
                class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold transition-colors flex items-center gap-2
                    {{ $isActive ? 'bg-elkm-primary text-white shadow-[0_8px_20px_rgba(15,143,95,0.22)]' : 'text-elkm-text hover:bg-elkm-surface-2' }}"
            >
                {{-- Status icon for parent sections that are directly tied to activities/assessments --}}
                @if ($status === 'done')
                    <span class="shrink-0 size-4 flex items-center justify-center rounded-full bg-green-500 text-white text-[9px]">✓</span>
                @elseif ($status === 'draft')
                    <span class="shrink-0 size-4 flex items-center justify-center rounded-full bg-yellow-400 text-white text-[9px]">●</span>
                @elseif ($status === 'locked')
                    <span class="shrink-0 size-4 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M8 1a3.5 3.5 0 0 0-3.5 3.5V6H4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-.5V4.5A3.5 3.5 0 0 0 8 1Zm2 5V4.5a2 2 0 1 0-4 0V6h4Z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @endif
                <span class="truncate">{{ $section->title }}</span>
            </button>

            @if ($section->children->isNotEmpty())
                <div class="ml-4 space-y-0.5 border-l-2 border-elkm-line pl-3 my-1">
                    @foreach ($section->children->where('is_visible', true) as $child)
                        @php
                            $childActive = $activeSectionId === $child->id;
                            $childStatus = sectionStatus($child, $activityStatuses, $assessmentStatuses);
                        @endphp

                        <button
                            type="button"
                            wire:click="openSection({{ $child->id }})"
                            class="w-full rounded-lg px-3 py-2 text-left text-[13px] font-semibold transition-colors flex items-center gap-2
                                {{ $childActive ? 'bg-[#e4f8ef] text-elkm-primary-2' : 'text-elkm-muted hover:bg-elkm-surface-2 hover:text-elkm-text' }}"
                        >
                            @if ($childStatus === 'done')
                                <span class="shrink-0 size-3.5 flex items-center justify-center rounded-full bg-green-500 text-white text-[8px]">✓</span>
                            @elseif ($childStatus === 'draft')
                                <span class="shrink-0 size-3.5 flex items-center justify-center rounded-full bg-yellow-400 text-white text-[8px]">●</span>
                            @elseif ($childStatus === 'locked')
                                <span class="shrink-0 size-3.5 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                        <path fill-rule="evenodd" d="M8 1a3.5 3.5 0 0 0-3.5 3.5V6H4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-.5V4.5A3.5 3.5 0 0 0 8 1Zm2 5V4.5a2 2 0 1 0-4 0V6h4Z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @elseif ($childStatus === 'open')
                                <span class="shrink-0 size-3.5 rounded-full border-2 border-gray-300"></span>
                            @endif
                            <span class="truncate">{{ $child->title }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
</div>

