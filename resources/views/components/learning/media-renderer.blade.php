@props(['type', 'url' => null, 'filePath' => null, 'embedCode' => null, 'title' => 'Media'])

@php
    use Illuminate\Support\Facades\Storage;
    use App\Services\Learning\MediaHelper;

    $mediaUrl = $filePath ? Storage::disk('public')->url($filePath) : $url;
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
                <video controls class="w-full max-w-3xl rounded border border-slate-200">
                    <source src="{{ $mediaUrl }}" type="video/mp4">
                    Browser Anda tidak mendukung tag video.
                </video>
            @else
                <div class="text-slate-400 italic text-sm">Video tidak tersedia.</div>
            @endif

        @elseif($type === 'youtube')
            @php
                $embedUrl = MediaHelper::getYoutubeEmbedUrl($url);
            @endphp
            @if($embedUrl)
                <div class="relative w-full pb-[56.25%] h-0">
                    <iframe 
                        src="{{ $embedUrl }}" 
                        class="absolute top-0 left-0 w-full h-full rounded"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
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
</div>
