<div class="space-y-6">
    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
        <div>
            <flux:breadcrumbs class="mb-3">
                <flux:breadcrumbs.item href="{{ route('guru.dashboard') }}">Dashboard</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Remedial</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">Daftar Remedial</flux:heading>
            <flux:text>Pantau daftar murid yang sedang dalam masa remedial untuk asesmen di modul Anda.</flux:text>
        </div>
    </div>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-300">
                <thead class="border-b border-zinc-200 bg-zinc-50/50 text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama Murid</th>
                        <th class="px-4 py-3 font-medium">Modul & Asesmen</th>
                        <th class="px-4 py-3 font-medium text-center">Nilai Terakhir</th>
                        <th class="px-4 py-3 font-medium text-center">KKTP</th>
                        <th class="px-4 py-3 font-medium text-center">Percobaan</th>
                        <th class="px-4 py-3 font-medium text-right">Tanggal Submit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($attempts as $attempt)
                        <tr>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                {{ $attempt->student->name }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $attempt->assessment->title }}</div>
                                <div class="text-xs text-zinc-500">{{ $attempt->assessment->module->title }}{{ $attempt->assessment->learningUnit ? ' - ' . $attempt->assessment->learningUnit->title : '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-red-600 dark:text-red-400">{{ $attempt->total_score }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $attempt->assessment->kktp }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $attempt->attempts_used }} / {{ $attempt->assessment->max_attempts }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ $attempt->submitted_at?->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                Tidak ada murid yang sedang remedial saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if ($attempts->hasPages())
            <div class="mt-4">
                {{ $attempts->links() }}
            </div>
        @endif
    </flux:card>
</div>
