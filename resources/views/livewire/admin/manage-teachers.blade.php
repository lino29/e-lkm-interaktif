<div class="space-y-4">
    <flux:heading size="xl">Data Guru</flux:heading>
    @foreach ($teachers as $teacher)
        <flux:card wire:key="teacher-{{ $teacher->id }}">
            <div class="font-semibold">{{ $teacher->name }}</div>
            <flux:text>{{ $teacher->email }} · {{ $teacher->modules_count }} modul</flux:text>
        </flux:card>
    @endforeach
</div>
