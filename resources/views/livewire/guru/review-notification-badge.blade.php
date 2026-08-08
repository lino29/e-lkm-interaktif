<span>
    @if ($unreadCount > 0)
        <span class="ml-auto inline-flex min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-black leading-none text-amber-950" title="{{ $unreadCount }} jawaban KB baru">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</span>
