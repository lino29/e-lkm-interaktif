@props([
    'section',
    'learningUnit',
    'activityStatuses' => [],
])

@if (! $section || ! $section->is_visible)
    <div class="rounded-lg border p-6">Outline belum tersedia.</div>
@else
    <div class="space-y-5 rounded-lg border bg-white p-6 shadow-sm dark:bg-zinc-900">
        <h2 class="text-xl font-bold">{{ $section->title }}</h2>

        @switch($section->section_type)
            @case('learning_objective')
                @if ($section->content)
                    <div class="ck-content learning-content prose max-w-none dark:prose-invert">{!! $section->content !!}</div>
                @else
                    <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($learningUnit->objectives)) !!}</div>
                @endif
                @break

            @case('key_points')
                <x-learning.key-points-table :items="$section->content_json" />
                @break

            @case('material_group')
                <div class="space-y-3">
                    @foreach ($section->children->where('is_visible', true) as $child)
                        <button type="button" wire:click="openSection({{ $child->id }})" class="block w-full rounded-md border p-4 text-left hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
                @break

            @case('material_item')
                @php($material = $section->linkedModel())
                @if ($material)
                    <article class="ck-content learning-content prose max-w-none dark:prose-invert">{!! $material->content !!}</article>
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
                @elseif ($section->content)
                    <article class="ck-content learning-content prose max-w-none dark:prose-invert">{!! $section->content !!}</article>
                @else
                    <div class="text-sm text-zinc-500">Materi belum tersedia.</div>
                @endif
                @if ($section->media->isNotEmpty())
                    <div class="mt-4 grid gap-4">
                        @foreach ($section->media as $media)
                            <x-learning.media-renderer
                                :type="$media->type"
                                :url="$media->url"
                                :file-path="$media->file_path"
                                :embed-code="$media->embed_code"
                                :title="$media->title"
                            />
                        @endforeach
                    </div>
                @endif
                @break

            @case('activity_group')
                <div class="space-y-3">
                    @foreach ($section->children->where('is_visible', true) as $child)
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
                @if ($section->children->where('is_visible', true)->isNotEmpty())
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @foreach ($section->children->where('is_visible', true) as $child)
                            <x-learning.question-group-preview :section="$child" />
                        @endforeach
                    </div>
                @endif
                @break

            @case('question_group')
                <x-learning.question-group-preview :section="$section" />
                @break

            @case('media_gallery')
                <div class="grid gap-4">
                    @forelse ($section->media as $media)
                        <x-learning.media-renderer
                            :type="$media->type"
                            :url="$media->url"
                            :file-path="$media->file_path"
                            :embed-code="$media->embed_code"
                            :title="$media->title"
                        />
                    @empty
                        <flux:text>Media belum tersedia.</flux:text>
                    @endforelse
                </div>
                @break

            @case('custom_content')
                <div class="ck-content learning-content prose max-w-none dark:prose-invert">{!! $section->content !!}</div>
                @break

            @default
                <div class="prose max-w-none dark:prose-invert">{!! nl2br(e($section->content)) !!}</div>
        @endswitch
    </div>
@endif
