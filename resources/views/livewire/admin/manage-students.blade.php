<div class="space-y-4">
    <flux:heading size="xl">Data Murid</flux:heading>
    @foreach ($students as $student)
        <flux:card wire:key="student-{{ $student->id }}">
            <div class="font-semibold">{{ $student->name }}</div>
            <flux:text>NISN {{ $student->nisn ?? '-' }} - {{ $student->classRoom?->name ?? 'Belum punya kelas' }}</flux:text>
        </flux:card>
    @endforeach
</div>
