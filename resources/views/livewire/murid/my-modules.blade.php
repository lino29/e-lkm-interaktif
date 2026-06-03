<div class="space-y-6">
    <x-elkm.page-header title="Modul E-LKM" subtitle="Daftar modul pembelajaran yang dapat Anda akses">
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Modul E-LKM</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>
    <div class="grid gap-6 max-w-6xl mx-auto">
        @forelse ($modules as $module)
            <div class="card-elkm overflow-hidden flex flex-col md:flex-row shadow-sm" wire:key="my-module-{{ $module->id }}">
                
                <!-- Kiri: Info Modul -->
                <div class="p-6 md:w-1/3 bg-elkm-surface/50 border-b md:border-b-0 md:border-r border-elkm-line flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#e4f8ef] text-elkm-primary-2 text-xs font-bold shrink-0 border border-[#c7eadb] mb-4">
                            {{ $module->subject->name }}
                        </div>
                        <h2 class="text-2xl font-bold text-elkm-text leading-tight mb-2">{{ $module->title }}</h2>
                        <p class="text-sm text-elkm-muted mb-4">{{ $module->learningUnits->count() }} Kegiatan Belajar &bull; KKTP: {{ $module->kktp }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('murid.modules.show', $module) }}" wire:navigate class="btn-elkm btn-elkm-primary w-full justify-center">
                            Buka Halaman Modul &rarr;
                        </a>
                    </div>
                </div>

@inject('progress', 'App\Services\Learning\ProgressService')

                <!-- Kanan: Navigasi Cepat Materi & Asesmen -->
                <div class="p-6 md:w-2/3">
                    <h3 class="text-sm font-bold text-elkm-text uppercase tracking-wider mb-4 border-b border-elkm-line pb-2">Kegiatan Belajar (Materi)</h3>
                    
                    @if($module->learningUnits->count() > 0)
                        <div class="grid gap-2 sm:grid-cols-2 mb-6">
                            @foreach ($module->learningUnits as $unit)
                                @php($isLocked = !$progress->isLearningUnitUnlocked(auth()->user(), $unit))
                                
                                @if($isLocked)
                                    <div class="flex items-start gap-3 p-3 rounded-xl border border-elkm-line bg-gray-50 opacity-60 cursor-not-allowed group">
                                        <div class="flex items-center justify-center size-8 rounded-lg bg-gray-200 text-gray-500 font-bold text-sm shrink-0">
                                            <flux:icon.lock-closed class="size-4" />
                                        </div>
                                        <div class="text-sm font-semibold text-gray-500 leading-tight mt-1">
                                            {{ $unit->title }}
                                            <span class="text-[11px] text-orange-600 font-normal block mt-1">Terkunci - Selesaikan tahap sebelumnya</span>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('murid.learning-units.show', $unit) }}" wire:navigate class="flex items-start gap-3 p-3 rounded-xl border border-elkm-line bg-white hover:border-elkm-primary hover:shadow-sm transition group">
                                        <div class="flex items-center justify-center size-8 rounded-lg bg-elkm-surface text-elkm-primary font-bold text-sm shrink-0 group-hover:bg-elkm-primary group-hover:text-white transition">
                                            {{ $unit->order }}
                                        </div>
                                        <div class="text-sm font-semibold text-elkm-text group-hover:text-elkm-primary transition leading-tight mt-1">
                                            {{ $unit->title }}
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-elkm-muted mb-6">Belum ada kegiatan belajar.</div>
                    @endif

                    @if($module->assessments->count() > 0)
                        <h3 class="text-sm font-bold text-elkm-text uppercase tracking-wider mb-4 border-b border-elkm-line pb-2">Asesmen</h3>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($module->assessments as $assessment)
                                @php($isLocked = !$progress->isAssessmentUnlocked(auth()->user(), $assessment))
                                
                                @if($isLocked)
                                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed group">
                                        <div class="flex items-center justify-center size-8 rounded-lg bg-gray-200 text-gray-500 shadow-sm shrink-0">
                                            <flux:icon.lock-closed class="size-4" />
                                        </div>
                                        <div class="text-sm font-semibold text-gray-500 leading-tight">
                                            {{ $assessment->title }}
                                            <span class="text-[11px] text-orange-600 font-normal block mt-1">Terkunci</span>
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('murid.assessments.show', $assessment) }}" wire:navigate class="flex items-center gap-3 p-3 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 transition group">
                                        <div class="flex items-center justify-center size-8 rounded-lg bg-white text-blue-600 shadow-sm shrink-0">
                                            <flux:icon.document-text class="size-4" />
                                        </div>
                                        <div class="text-sm font-semibold text-blue-800 leading-tight">
                                            {{ $assessment->title }}
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <x-elkm.empty-state 
                icon="lucide-book-x" 
                title="Belum ada Modul" 
                message="Anda belum memiliki modul pembelajaran saat ini." 
            />
        @endforelse
    </div>
</div>
