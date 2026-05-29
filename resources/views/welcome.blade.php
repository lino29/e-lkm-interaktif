<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'E-LKM Interaktif') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
        <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-6 py-6 sm:px-8 lg:px-10">
            <header class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-lg bg-emerald-600 text-sm font-bold text-white">EL</span>
                    <span class="text-sm font-semibold">E-LKM Interaktif</span>
                </a>

                <nav class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-800 transition hover:bg-white dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-900">
                            Masuk
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </nav>
            </header>

            <section class="grid flex-1 items-center gap-10 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:py-20">
                <div class="max-w-3xl">
                    <p class="mb-4 inline-flex rounded-md bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                        Projek IPAS SMK Kelas X
                    </p>

                    <h1 class="max-w-4xl text-4xl font-semibold leading-tight text-zinc-950 dark:text-white sm:text-5xl lg:text-6xl">
                        E-LKM Interaktif Energi Terbarukan
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-7 text-zinc-600 dark:text-zinc-300 sm:text-lg">
                        Platform pembelajaran berbasis web untuk modul digital, aktivitas interaktif, asesmen otomatis, remedial, portofolio proyek, dan laporan hasil belajar.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                Masuk ke Sistem
                            </a>
                            <a href="#fitur" class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-5 py-3 text-sm font-semibold text-zinc-800 transition hover:bg-white dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-900">
                                Lihat Fitur
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Alur belajar</p>
                            <h2 class="text-xl font-semibold">Energi Terbarukan</h2>
                        </div>
                        <span class="rounded-md bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-200">MVP Aktif</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ([
                            ['Kegiatan Belajar', 'Mengamati, bertanya, mencoba, menalar, dan menyimpulkan.'],
                            ['Asesmen Otomatis', 'Pilihan ganda, benar/salah, menjodohkan, isian, dan uraian.'],
                            ['Remedial & Progress', 'Status tuntas atau remedial berdasarkan KKTP.'],
                            ['Portofolio Proyek', 'Dokumentasi proyek energi terbarukan murid.'],
                        ] as [$title, $description])
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                                <h3 class="font-medium">{{ $title }}</h3>
                                <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="fitur" class="grid gap-4 border-t border-zinc-200 py-8 dark:border-zinc-800 md:grid-cols-3">
                <div>
                    <h2 class="text-lg font-semibold">Admin</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Kelola pengguna, guru, murid, kelas, mata pelajaran, role, dan laporan sistem.</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Guru</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Susun modul, kegiatan belajar, materi, aktivitas, asesmen, rubrik, dan laporan belajar.</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Murid</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Buka modul, isi aktivitas, kerjakan asesmen, ikuti remedial, dan kumpulkan portofolio proyek.</p>
                </div>
            </section>
        </main>
    </body>
</html>
