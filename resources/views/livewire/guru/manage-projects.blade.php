<div class="space-y-4">
    <flux:heading size="xl">Proyek Murid</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    @if ($reviewingProjectId)
        <flux:card>
            <flux:heading>Nilai Proyek</flux:heading>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:field><flux:label>Skor</flux:label><flux:input type="number" min="0" max="100" step="0.01" wire:model="score" /></flux:field>
                <flux:field><flux:label>Feedback</flux:label><flux:textarea wire:model="feedback" /></flux:field>
            </div>
            <flux:button class="mt-4" variant="primary" wire:click="saveReview">Simpan Review</flux:button>
        </flux:card>
    @endif
    @forelse ($projects as $project)
        <flux:card wire:key="project-{{ $project->id }}">
            <div class="font-semibold">{{ $project->project_title }}</div>
            <flux:text>{{ $project->user->name }} - {{ $project->module->title }} - {{ $project->status }}</flux:text>
            <div class="mt-3 grid gap-2 text-sm md:grid-cols-2">
                <div><span class="font-medium">Masalah:</span> {{ $project->problem ?? '-' }}</div>
                <div><span class="font-medium">Tujuan:</span> {{ $project->objective ?? '-' }}</div>
                <div><span class="font-medium">Alat bahan:</span> {{ $project->tools_materials ?? '-' }}</div>
                <div><span class="font-medium">Data:</span> {{ $project->collected_data ?? '-' }}</div>
            </div>
            <flux:button class="mt-3" size="sm" wire:click="review({{ $project->id }})">Review</flux:button>
        </flux:card>
    @empty
        <flux:text>Belum ada proyek murid.</flux:text>
    @endforelse
</div>
