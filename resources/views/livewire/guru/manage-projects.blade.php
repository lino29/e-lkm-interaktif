<div class="space-y-4">
    <flux:heading size="xl">Proyek Murid</flux:heading>
    @if (session('status')) <flux:callout variant="success">{{ session('status') }}</flux:callout> @endif
    @if (session('error')) <flux:callout variant="danger">{{ session('error') }}</flux:callout> @endif

    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        <flux:select wire:model.live="filterModule" placeholder="Semua Modul">
            <flux:select.option value="">Semua Modul</flux:select.option>
            @foreach($modules as $module)
                <flux:select.option value="{{ $module->id }}">{{ $module->title }}</flux:select.option>
            @endforeach
        </flux:select>
        
        <flux:select wire:model.live="filterStatus" placeholder="Semua Status">
            <flux:select.option value="">Semua Status</flux:select.option>
            <flux:select.option value="draft">Draft</flux:select.option>
            <flux:select.option value="submitted">Submitted</flux:select.option>
            <flux:select.option value="reviewed">Reviewed</flux:select.option>
        </flux:select>
    </div>

    @if ($reviewingProjectId)
        <flux:card>
            <flux:heading>Nilai Proyek</flux:heading>
            <div class="mt-3 rounded-lg border border-zinc-200 p-3 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                <div class="font-medium text-zinc-800 dark:text-zinc-100">Rubrik proyek ringkas</div>
                <div class="mt-2 grid gap-2 md:grid-cols-2">
                    @foreach ($projectRubric as $criterion)
                        <div wire:key="project-rubric-{{ \Illuminate\Support\Str::slug($criterion) }}">- {{ $criterion }}</div>
                    @endforeach
                </div>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:field><flux:label>Skor</flux:label><flux:input type="number" min="0" max="100" step="0.01" wire:model="score" /></flux:field>
                <flux:field><flux:label>Feedback</flux:label><flux:textarea wire:model="feedback" /></flux:field>
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button variant="primary" wire:click="saveReview">Simpan Review</flux:button>
                <flux:button wire:click="$set('reviewingProjectId', null)">Batal</flux:button>
            </div>
        </flux:card>
    @endif

    <div class="grid gap-4">
        @forelse ($projects as $project)
            <flux:card wire:key="project-{{ $project->id }}">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-semibold">{{ $project->project_title }}</div>
                        <flux:text>{{ $project->user->name }} - {{ $project->module->title }}</flux:text>
                        <flux:badge size="sm" class="mt-1" color="{{ $project->status === 'reviewed' ? 'green' : ($project->status === 'submitted' ? 'blue' : 'zinc') }}">
                            {{ ucfirst($project->status) }}
                        </flux:badge>
                    </div>
                    <div class="flex gap-2">
                        @if($project->file_path)
                            <flux:button size="sm" icon="arrow-down-tray" wire:click="downloadFile({{ $project->id }})">Unduh File</flux:button>
                        @endif
                        <flux:button size="sm" wire:click="review({{ $project->id }})">Review</flux:button>
                    </div>
                </div>
                
                <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Masalah:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1">{{ $project->problem ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Tujuan:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1">{{ $project->objective ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Alat dan Bahan:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $project->tools_materials ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Prosedur:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $project->procedure ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Data Hasil:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $project->collected_data ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Hasil yang Diharapkan:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $project->expected_result ?? '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-medium block text-zinc-700 dark:text-zinc-300">Kesimpulan:</span> 
                        <div class="text-zinc-600 dark:text-zinc-400 mt-1 whitespace-pre-wrap">{{ $project->conclusion ?? '-' }}</div>
                    </div>
                </div>

                @if($project->score !== null)
                    <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="font-medium">Nilai Diberikan: {{ $project->score }}</div>
                        <div class="text-sm mt-1">Feedback: {{ $project->feedback ?? '-' }}</div>
                    </div>
                @endif
            </flux:card>
        @empty
            <flux:text>Belum ada proyek murid.</flux:text>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
