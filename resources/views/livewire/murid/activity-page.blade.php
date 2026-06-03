<div class="space-y-6">
    <x-elkm.page-header 
        title="{{ $activity->title }}" 
        subtitle="{{ $activity->learningUnit->title }} - {{ \Illuminate\Support\Str::headline($activity->phase) }}" 
        :actions="null"
    >
        <x-slot:breadcrumbs>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('murid.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('murid.modules') }}">Modul</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('murid.learning-units.show', $activity->learning_unit_id) }}">{{ $activity->learningUnit->title }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $activity->title }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </x-slot:breadcrumbs>
    </x-elkm.page-header>

    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    @error('activity')
        <flux:callout variant="danger">{{ $message }}</flux:callout>
    @enderror

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="space-y-6">
            <x-elkm.app-card>
                <div class="prose prose-sm max-w-none text-elkm-text">
                    {{ $activity->prompt }}
                </div>
            </x-elkm.app-card>

            @if ($activityMedia['type'] || $activityMedia['url'] || $activityMedia['filePath'] || $activityMedia['embedCode'])
                <x-elkm.app-card 
                    class="{{ $activity->phase === 'ayo_mengamati' ? '!border-[#c7eadb] !bg-[#e4f8ef]' : '' }}"
                >
                    <h4 class="font-bold mb-4 {{ $activity->phase === 'ayo_mengamati' ? 'text-elkm-primary-2' : '' }}">
                        {{ $activity->phase === 'ayo_mengamati' ? 'Media Pengamatan / Media Pendukung' : 'Media Pendukung' }}
                    </h4>
                    <x-learning.media-renderer
                        :type="$activityMedia['type']"
                        :url="$activityMedia['url']"
                        :file-path="$activityMedia['filePath']"
                        :embed-code="$activityMedia['embedCode']"
                        :title="$activityMedia['title']"
                        :caption="$activityMedia['caption']"
                    />
                </x-elkm.app-card>
            @endif

            @if ($answer && $answer->status === 'reviewed')
                <x-elkm.app-card class="!border-elkm-primary-2 !bg-[#e4f8ef]">
                    <div class="flex items-center gap-2 mb-2 font-semibold text-elkm-primary-2">
                        <span>✅</span> Jawaban telah dinilai (Nilai: {{ $answer->score ?? '-' }})
                    </div>
                    @if ($answer->teacher_feedback)
                        <div class="text-[13px] text-elkm-text mt-3 pt-3 border-t border-[#c7eadb]">
                            <strong>Feedback Guru:</strong><br>
                            {{ $answer->teacher_feedback }}
                        </div>
                    @endif
                </x-elkm.app-card>
            @endif

            <x-elkm.app-card title="Lembar Kerja">
                <form wire:submit.prevent="submit" class="space-y-4">
                    @php
                        $schema = is_array($activity->answer_schema) 
                            ? $activity->answer_schema 
                            : (json_decode($activity->answer_schema ?? '{}', true) ?? []);
                        $schema['input_type'] = $activity->input_type;
                    @endphp
                    
                    <x-elkm.activity-renderer :schema="$schema" />

                    @if (! ($answer?->status === 'reviewed'))
                        <div class="flex gap-2 pt-6 mt-6 border-t border-elkm-line">
                            <button type="button" wire:click="saveDraft" class="btn-elkm btn-elkm-outline">Simpan Draft</button>
                            <button type="submit" wire:confirm="Yakin ingin mengirim? Jawaban yang disubmit akan dikunci." class="btn-elkm btn-elkm-primary">Kirim Jawaban</button>
                        </div>
                    @endif
                </form>
            </x-elkm.app-card>

            @if ($discussions->isNotEmpty())
                <x-elkm.app-card title="Diskusi dan Balasan">
                    <div class="space-y-4">
                        @foreach ($discussions as $discussion)
                            <div class="p-4 rounded-xl border border-elkm-line bg-[#fbfdfc]" wire:key="activity-discussion-{{ $discussion->id }}">
                                <div class="font-semibold text-sm">{{ $discussion->user->name }}</div>
                                <p class="mt-2 text-[13px] text-elkm-text">{{ $discussion->body }}</p>
                                @if($discussion->replies->count() > 0)
                                    <div class="mt-3 space-y-2">
                                        @foreach ($discussion->replies as $reply)
                                            <div class="border-l-[3px] border-elkm-primary-2 pl-3 py-1" wire:key="activity-discussion-reply-{{ $reply->id }}">
                                                <div class="font-medium text-xs">{{ $reply->user->name }}</div>
                                                <p class="text-[13px] mt-0.5 text-elkm-text">{{ $reply->body }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-elkm.app-card>
            @endif
        </div>

        <div class="space-y-4 hidden lg:block">
            <x-elkm.app-card title="Panduan Mengerjakan">
                <ul class="text-[13px] text-elkm-text space-y-2 list-disc pl-4 mt-2">
                    <li>Baca instruksi dengan teliti sebelum mulai.</li>
                    <li>Gunakan tombol <b>Simpan Draft</b> jika belum yakin atau ingin melanjutkan nanti.</li>
                    <li>Tombol <b>Kirim Jawaban</b> akan mengunci jawaban untuk diperiksa oleh guru.</li>
                    <li>Pastikan mengisi semua bagian yang wajib.</li>
                </ul>
            </x-elkm.app-card>
        </div>
    </div>
</div>
