<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ $activity->title }}</flux:heading>
        <flux:text>{{ $activity->learningUnit->title }} - {{ \Illuminate\Support\Str::headline($activity->phase) }}</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    @error('activity')
        <flux:callout variant="danger">{{ $message }}</flux:callout>
    @enderror

    <flux:card>
        <p class="text-sm leading-6">{{ $activity->prompt }}</p>
    </flux:card>

    @if ($answer && $answer->status === 'reviewed')
        <flux:card class="border-green-500 bg-zinc-50 dark:bg-zinc-900">
            <div class="mb-2 font-semibold text-green-600">Jawaban telah dinilai (Nilai: {{ $answer->score ?? '-' }})</div>
            @if ($answer->teacher_feedback)
                <div class="text-sm">
                    <strong>Feedback Guru:</strong><br>
                    {{ $answer->teacher_feedback }}
                </div>
            @endif
        </flux:card>
    @endif

    <form class="space-y-4">
        @switch($currentActivity->input_type)
            @case('short_text')
                @include('livewire.murid.activities.inputs.short-text')
                @break

            @case('essay')
                @include('livewire.murid.activities.inputs.essay')
                @break

            @case('table')
                @include('livewire.murid.activities.inputs.table')
                @break

            @case('fields')
                @include('livewire.murid.activities.inputs.fields')
                @break

            @case('file')
                @include('livewire.murid.activities.inputs.file')
                @break

            @case('discussion')
                @include('livewire.murid.activities.inputs.discussion')
                @break

            @case('project_form')
                @include('livewire.murid.activities.inputs.project-form')
                @break

            @default
                @include('livewire.murid.activities.inputs.essay')
        @endswitch

        @if (! ($answer?->status === 'reviewed'))
            <div class="flex gap-2">
                <flux:button type="button" wire:click="saveDraft">Simpan Draft</flux:button>
                <flux:button type="button" variant="primary" wire:click="submit" wire:confirm="Yakin ingin mengirim? Jawaban yang disubmit akan dikunci.">Kirim Jawaban</flux:button>
            </div>
        @endif
    </form>
</div>
