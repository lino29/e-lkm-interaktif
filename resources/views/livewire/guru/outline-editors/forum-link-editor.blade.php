<div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
    <div class="text-sm font-medium">Forum Diskusi/Refleksi</div>
    @php($forum = $activities->firstWhere('phase', 'forum_diskusi'))
    <flux:text>{{ $forum ? 'Forum tersedia: '.$forum->title : 'Aktivitas forum_diskusi belum tersedia.' }}</flux:text>
</div>
