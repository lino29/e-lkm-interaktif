@props([
    'section',
    'learningUnit',
    'activityStatuses' => [],
])

@if (! $section)
    <div class="rounded-lg border p-6">Outline belum tersedia.</div>
@else
    <div class="space-y-5 rounded-lg border bg-white p-6 shadow-sm dark:bg-zinc-900">
        <h2 class="text-xl font-bold">{{ $section->title }}</h2>

        @switch($section->section_type)
            @case('learning_objective')
                <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($section->content ?? $learningUnit->objectives)) !!}</div>
                @break

            @case('key_points')
                <x-learning.key-points-table :items="$section->content_json" />
                @break

            @case('material_group')
                <div class="space-y-3">
                    @foreach ($section->children as $child)
                        <button type="button" wire:click="openSection({{ $child->id }})" class="block w-full rounded-md border p-4 text-left hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
                @break

            @case('material_item')
                @php($material = $section->linkedModel())
                @if ($material)
                    <article class="prose max-w-none dark:prose-invert">{!! $material->content !!}</article>
                    <div class="mt-4 grid gap-4">
                        @foreach ($material->media as $media)
                            <x-learning.media-renderer
                                :type="$media->type"
                                :url="$media->url"
                                :file-path="$media->file_path"
                                :embed-code="$media->embed_code"
                                :title="$media->title"
                            />
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-zinc-500">Materi belum tersedia.</div>
                @endif
                @break

            @case('activity_group')
                <div class="space-y-3">
                    @foreach ($section->children as $child)
                        <x-learning.activity-section-card :section="$child" :activity-statuses="$activityStatuses" />
                    @endforeach
                </div>
                @break

            @case('activity_item')
                <x-learning.activity-section-card :section="$section" :activity-statuses="$activityStatuses" />
                @break

            @case('forum')
                <x-learning.forum-section-card :section="$section" :activity-statuses="$activityStatuses" />
                @break

            @case('assessment_group')
                <x-learning.assessment-section-card :section="$section" />
                @break

            @case('question_group')
                <x-learning.question-group-preview :section="$section" />
                @break

            @default
                <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($section->content)) !!}</div>
        @endswitch
    </div>
@endif
