<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'E-LKM Interaktif') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-elkm-canvas flex items-center justify-center p-6 antialiased">
    <div class="w-full max-w-md">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <img src="{{ asset('logo.ico') }}" alt="Logo E-LKM" class="size-14 rounded-xl shadow-sm">
                <span class="text-xl font-bold text-elkm-text">E-LKM Interaktif</span>
            </a>
            <p class="mt-3 text-sm text-elkm-muted">Silakan masuk ke akun Anda</p>
            <p class="mt-1 text-xs font-semibold px-3 py-1 rounded-full bg-white border border-elkm-line inline-block text-elkm-primary-2 shadow-sm">
                Guru/Admin: Email &nbsp;|&nbsp; Murid: NISN (10 Angka)
            </p>
        </div>

        <!-- Login Card -->
        <div class="card-elkm p-8">
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-[#e4f8ef] text-elkm-primary-2 text-sm font-medium border border-[#c7eadb]">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 text-sm font-medium border border-red-200">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-semibold text-elkm-text mb-2">Email / NISN</label>
                    <input 
                        id="email" 
                        type="text" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        class="w-full rounded-xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 text-sm text-elkm-text outline-none transition focus:border-elkm-primary focus:bg-white"
                        placeholder="Masukkan Email atau NISN"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-semibold text-elkm-text">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-semibold text-elkm-primary hover:underline">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            required
                            class="w-full rounded-xl border border-elkm-line bg-elkm-surface/60 px-4 py-3 pr-12 text-sm text-elkm-text outline-none transition focus:border-elkm-primary focus:bg-white"
                            placeholder="••••••••"
                        >
                        <button 
                            type="button" 
                            onclick="const p = document.getElementById('password'); const isPass = p.type === 'password'; p.type = isPass ? 'text' : 'password'; this.querySelector('.eye-open').style.display = isPass ? 'block' : 'none'; this.querySelector('.eye-closed').style.display = isPass ? 'none' : 'block';"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-elkm-muted hover:text-elkm-primary focus:outline-none"
                        >
                            <svg class="eye-closed size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="eye-open size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input 
                        id="remember_me" 
                        type="checkbox" 
                        name="remember"
                        class="size-4 rounded border-elkm-line text-elkm-primary focus:ring-elkm-primary"
                    >
                    <label for="remember_me" class="ml-2 block text-sm text-elkm-muted">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="w-full btn-elkm btn-elkm-primary justify-center py-3 text-base mt-2">
                    Masuk ke Sistem
                </button>
            </form>
        </div>

        @if (Route::has('register'))
            <p class="mt-8 text-center text-sm text-elkm-muted">
                Belum punya akun? 
                <a href="{{ route('register') }}" wire:navigate class="font-semibold text-elkm-primary hover:underline">
                    Daftar di sini
                </a>
            </p>
        @endif
    </div>
</body>
</html>
