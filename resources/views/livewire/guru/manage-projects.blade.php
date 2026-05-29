<div class="space-y-4">
    <flux:heading size="xl">Proyek Murid</flux:heading>
    @forelse ($projects as $project)
        <flux:card wire:key="project-{{ $project->id }}"><div class="font-semibold">{{ $project->project_title }}</div><flux:text>{{ $project->user->name }} · {{ $project->module->title }} · {{ $project->status }}</flux:text></flux:card>
    @empty
        <flux:text>Belum ada proyek murid.</flux:text>
    @endforelse
</div>
