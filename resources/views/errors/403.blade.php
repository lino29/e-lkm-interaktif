<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Akses Ditolak - {{ config('app.name', 'E-LKM Interaktif') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-elkm-canvas flex items-center justify-center p-6 antialiased">
    <div class="w-full max-w-md text-center">
        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-8xl font-black text-elkm-primary/10 tracking-tighter">403</h1>
        </div>

        <!-- Card -->
        <div class="card-elkm p-8 relative overflow-hidden shadow-xl shadow-elkm-primary/5">
            <!-- Decorative Icon -->
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 border border-red-100 mb-6 shadow-sm">
                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-elkm-text mb-3">Akses Ditolak!</h2>
            <p class="text-elkm-muted text-sm mb-8 leading-relaxed">
                Mohon maaf, peran <strong>(Role)</strong> akun Anda saat ini tidak memiliki izin yang cukup untuk melihat atau memodifikasi halaman ini.
            </p>

            <div class="flex flex-col gap-3">
                <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='/'" class="w-full btn-elkm btn-elkm-primary justify-center py-3 text-base">
                    &larr; Kembali ke Sebelumnya
                </button>
                <a href="{{ url('/') }}" class="w-full btn-elkm btn-elkm-outline justify-center py-3 text-sm">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
        
        <p class="mt-8 text-xs text-elkm-muted font-medium uppercase tracking-wider">
            &copy; {{ date('Y') }} E-LKM Interaktif
        </p>
    </div>
</body>
</html>
