@props(['type', 'url' => null, 'filePath' => null, 'embedCode' => null, 'title' => 'Media', 'caption' => null])

@php
    use Illuminate\Support\Facades\Storage;
    use App\Services\Learning\MediaHelper;

    // Gunakan Storage::disk()->url() agar fleksibel jika di hosting menggunakan folder /uploads/
    $mediaUrl = $filePath ? Storage::disk('public')->url($filePath) : $url;
    // Pastikan URL absolute jika belum (opsional, karena url() biasanya sudah absolute tergantung APP_URL)
    if ($filePath && !str_starts_with($mediaUrl, 'http')) {
        $mediaUrl = asset($mediaUrl);
    }
    
    $mediaPathForMime = parse_url((string) $mediaUrl, PHP_URL_PATH) ?: '';
    $videoMime = match (strtolower(pathinfo($mediaPathForMime, PATHINFO_EXTENSION))) {
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        default => 'video/mp4',
    };
@endphp

<div class="w-full bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm my-4">
    @if($title)
        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
            <h4 class="text-sm font-medium text-slate-700">{{ $title }}</h4>
        </div>
    @endif
    
    <div class="p-4 flex justify-center bg-slate-50/50">
        @if($type === 'image')
            @if($mediaUrl)
                <img src="{{ $mediaUrl }}" alt="{{ $title }}" class="max-w-full h-auto rounded border border-slate-200 object-contain max-h-[500px]" loading="lazy">
            @else
                <div class="text-slate-400 italic text-sm">Gambar tidak tersedia.</div>
            @endif

        @elseif($type === 'video')
            @if($mediaUrl)
                <video 
                    controls 
                    playsinline 
                    data-plyr-player 
                    class="w-full max-w-3xl rounded border border-slate-200"
                    x-data
                    x-init="
                        if (window.Plyr && !$el._plyr) {
                            $el._plyr = new window.Plyr($el, {
                                ratio: '16:9',
                                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
                            });
                        } else if (window.initPlyrPlayers) {
                            setTimeout(() => window.initPlyrPlayers(), 100);
                        }
                    "
                >
                    <source src="{{ $mediaUrl }}" type="{{ $videoMime }}">
                    Browser Anda tidak mendukung tag video.
                </video>
            @else
                <div class="text-slate-400 italic text-sm">Video tidak tersedia.</div>
            @endif

        @elseif($type === 'youtube')
            @php
                $videoId = MediaHelper::getYoutubeVideoId($url);
            @endphp
            @if($videoId)
                <div class="w-full max-w-3xl" x-data>
                    <div 
                        data-plyr-player 
                        data-plyr-provider="youtube" 
                        data-plyr-embed-id="{{ $videoId }}"
                        x-init="
                            if (window.Plyr && !$el._plyr) {
                                $el._plyr = new window.Plyr($el, {
                                    ratio: '16:9',
                                    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
                                });
                            } else if (window.initPlyrPlayers) {
                                setTimeout(() => window.initPlyrPlayers(), 100);
                            }
                        "
                    ></div>
                </div>
            @else
                <div class="text-slate-400 italic text-sm">Link YouTube tidak valid.</div>
            @endif

        @elseif($type === 'simulation' || $type === 'embed')
            @if($embedCode)
                <div class="w-full overflow-hidden flex justify-center">
                    {!! MediaHelper::sanitizeEmbedCode($embedCode) !!}
                </div>
            @elseif($url)
                <div class="text-center p-4">
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Buka Simulasi di Tab Baru
                    </a>
                </div>
            @else
                <div class="text-slate-400 italic text-sm">Simulasi tidak tersedia.</div>
            @endif

        @elseif($type === 'file')
            @if($mediaUrl)
                <div class="text-center p-4">
                    <a href="{{ $mediaUrl }}" target="_blank" download class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download File
                    </a>
                </div>
            @else
                <div class="text-slate-400 italic text-sm">File tidak tersedia.</div>
            @endif

        @elseif($type === 'link')
            @if($url)
                <div class="text-center p-4">
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                        Buka Tautan <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            @else
                <div class="text-slate-400 italic text-sm">Tautan tidak tersedia.</div>
            @endif
        @else
            <div class="text-slate-400 italic text-sm">Tipe media tidak dikenali.</div>
        @endif
    </div>

    @if ($caption)
        <div class="border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
            {{ $caption }}
        </div>
    @endif
</div>
