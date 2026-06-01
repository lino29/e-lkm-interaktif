<?php

namespace App\Services\Learning;

use App\Models\Module;
use App\Models\ModuleSection;

class ModuleOutlineService
{
    /**
     * @return array<int, array{section_type: string, title: string, slug: string, content: string, order: int}>
     */
    public function defaultSections(Module $module): array
    {
        return [
            [
                'section_type' => 'introduction',
                'title' => 'Prakata',
                'slug' => 'prakata',
                'content' => 'Puji syukur, modul E-LKM Interaktif Energi Terbarukan ini disusun untuk membantu murid SMK kelas X memahami konsep energi, masalah energi fosil, energi terbarukan, teknologi berbasis STEM, dan rancangan aksi sederhana melalui kegiatan belajar berurutan.',
                'order' => 1,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Daftar Isi',
                'slug' => 'daftar-isi',
                'content' => $module->learningUnits()->orderBy('order')->pluck('title')->map(fn (string $title, int $index): string => ($index + 1).'. '.$title)->join("\n"),
                'order' => 2,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Deskripsi Singkat',
                'slug' => 'deskripsi-singkat',
                'content' => $module->introduction ?: 'Modul ini membahas energi dalam kehidupan sehari-hari, dampak energi fosil, potensi energi terbarukan, teknologi berbasis STEM, dan proyek aksi sederhana.',
                'order' => 3,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Capaian Pembelajaran',
                'slug' => 'capaian-pembelajaran',
                'content' => 'Murid mampu menganalisis penggunaan energi, dampak penggunaan energi fosil, alternatif energi terbarukan, dan merancang solusi sederhana berbasis data untuk konteks sekolah atau lingkungan sekitar.',
                'order' => 4,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Tujuan Pembelajaran',
                'slug' => 'tujuan-pembelajaran',
                'content' => $module->learning_objectives ?: 'Murid mampu menjelaskan konsep energi, membandingkan energi fosil dan terbarukan, serta merancang aksi sederhana.',
                'order' => 5,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Relevansi',
                'slug' => 'relevansi',
                'content' => 'Materi relevan dengan kehidupan murid karena penggunaan listrik, bahan bakar, dan pilihan teknologi energi memengaruhi biaya, lingkungan, kesehatan, serta keberlanjutan sumber daya.',
                'order' => 6,
            ],
            [
                'section_type' => 'introduction',
                'title' => 'Petunjuk Belajar',
                'slug' => 'petunjuk-belajar',
                'content' => 'Pelajari tujuan pembelajaran terlebih dahulu, baca materi secara berurutan, kerjakan aktivitas Ayo Mengamati sampai Forum Diskusi, selesaikan asesmen formatif, lalu lanjutkan kegiatan belajar berikutnya setelah tuntas.',
                'order' => 7,
            ],
            [
                'section_type' => 'closing',
                'title' => 'Rangkuman',
                'slug' => 'rangkuman',
                'content' => 'Energi diperlukan dalam setiap aktivitas. Energi fosil masih banyak dipakai tetapi menimbulkan emisi dan keterbatasan sumber daya. Energi terbarukan seperti surya, angin, air, biomassa, dan panas bumi dapat menjadi alternatif. Teknologi energi terbarukan membutuhkan pendekatan STEM dan dapat diwujudkan melalui proyek sederhana berbasis masalah nyata.',
                'order' => 90,
            ],
            [
                'section_type' => 'closing',
                'title' => 'Daftar Istilah',
                'slug' => 'daftar-istilah',
                'content' => 'Lihat glosarium modul untuk istilah penting seperti energi, energi fosil, energi terbarukan, STEM, dan KKTP.',
                'order' => 91,
            ],
            [
                'section_type' => 'closing',
                'title' => 'Daftar Pustaka',
                'slug' => 'daftar-pustaka',
                'content' => 'Lihat referensi modul untuk sumber belajar dan bahan ajar yang digunakan.',
                'order' => 92,
            ],
        ];
    }

    public function ensureDefaultSections(Module $module): void
    {
        foreach ($this->defaultSections($module) as $section) {
            ModuleSection::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'section_type' => $section['section_type'],
                    'slug' => $section['slug'],
                ],
                [
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'order' => $section['order'],
                    'is_visible' => true,
                ],
            );
        }
    }
}
