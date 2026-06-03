<div class="space-y-6">
    <x-elkm.page-header 
        title="Portofolio Belajar" 
        subtitle="Riwayat aktivitas dan hasil belajar Anda." 
        :actions="null"
    />

    <div class="grid lg:grid-cols-[1fr_360px] gap-6">
        <section class="space-y-4">
            <x-elkm.app-card title="Jawaban Aktivitas">
                <div class="space-y-3 mt-4">
                    @forelse ($activityAnswers as $answer)
                        <div wire:key="portfolio-answer-{{ $answer->id }}" class="p-4 border border-elkm-line rounded-xl bg-white">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-sm m-0">{{ $answer->activity->title }}</h4>
                                <x-elkm.status-pill :color="$answer->status === 'reviewed' ? 'green' : 'blue'">
                                    {{ $answer->status }}
                                </x-elkm.status-pill>
                            </div>
                            <p class="text-xs text-elkm-muted mb-3">{{ $answer->activity->learningUnit->title }}</p>
                            
                            <div class="bg-[#fbfdfc] p-3 rounded-lg text-[13px] text-elkm-text border border-elkm-line">
                                @if($answer->activity->input_type === 'table')
                                    <span class="italic text-elkm-muted">[Data Tabel tersimpan]</span>
                                @else
                                    {{ Str::limit($answer->answer_text, 150) }}
                                @endif
                            </div>
                            
                            @if ($answer->status === 'reviewed')
                                <div class="mt-2 flex gap-4 text-xs">
                                    <span class="font-bold text-elkm-primary-2">Nilai: {{ $answer->score }}</span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <x-elkm.empty-state title="Belum ada aktivitas" description="Anda belum menyelesaikan aktivitas apapun." />
                    @endforelse
                </div>
            </x-elkm.app-card>
        </section>

        <section class="space-y-4">
            <x-elkm.app-card title="Proyek Akhir">
                <div class="space-y-3 mt-4">
                    @forelse ($projects as $project)
                        <div wire:key="portfolio-project-{{ $project->id }}" class="p-4 border border-elkm-line rounded-xl bg-white">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-sm m-0">{{ $project->project_title }}</h4>
                                <x-elkm.status-pill :color="$project->status === 'reviewed' ? 'green' : 'yellow'">
                                    {{ $project->status }}
                                </x-elkm.status-pill>
                            </div>
                            <p class="text-xs text-elkm-muted mb-3">{{ $project->module->title }}</p>
                            
                            @if ($project->conclusion)
                                <div class="bg-[#fbfdfc] p-3 rounded-lg text-[13px] text-elkm-text border border-elkm-line mb-3">
                                    <strong class="block text-xs mb-1">Kesimpulan:</strong>
                                    {{ Str::limit($project->conclusion, 100) }}
                                </div>
                            @endif

                            @if ($project->status === 'reviewed')
                                <div class="bg-[#e4f8ef] border border-[#c7eadb] p-3 rounded-lg text-[13px]">
                                    <strong class="text-elkm-primary-2 text-sm block mb-1">Nilai: {{ $project->score ?? '-' }}</strong>
                                    @if ($project->feedback)
                                        <p class="text-elkm-text mt-1">{{ $project->feedback }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <x-elkm.empty-state title="Belum ada proyek" description="Anda belum membuat atau mengirimkan proyek." />
                    @endforelse
                </div>
            </x-elkm.app-card>
        </section>
    </div>
</div>
