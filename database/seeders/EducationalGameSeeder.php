<?php

namespace Database\Seeders;

use App\Models\EducationalGame;
use App\Models\GameItem;
use Illuminate\Database\Seeder;

class EducationalGameSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->games() as $gameData) {
            $items = $gameData['items'];
            unset($gameData['items']);

            $game = EducationalGame::updateOrCreate(
                ['code' => $gameData['code']],
                $gameData,
            );

            foreach ($items as $itemData) {
                GameItem::updateOrCreate(
                    [
                        'educational_game_id' => $game->id,
                        'sort_order' => $itemData['sort_order'],
                    ],
                    array_merge($itemData, [
                        'educational_game_id' => $game->id,
                    ]),
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function games(): array
    {
        return [
            [
                'code' => 'puzzle_solar_flow',
                'slug' => 'puzzle-alur-panel-surya',
                'title' => 'Puzzle Alur Panel Surya',
                'type' => 'puzzle_order',
                'icon' => 'PLTS',
                'description' => 'Susun alur kerja PLTS dari panel surya sampai listrik dipakai di rumah.',
                'config' => ['allow_replay' => true, 'show_leaderboard' => false],
                'is_active' => true,
                'sort_order' => 1,
                'items' => [
                    [
                        'item_type' => 'component',
                        'prompt' => 'Urutkan komponen PLTS sesuai aliran energi listrik.',
                        'question_text' => 'Susun komponen dari sumber energi sampai beban listrik.',
                        'options' => [
                            'panel_surya' => 'Panel Surya',
                            'baterai' => 'Baterai Opsional',
                            'inverter' => 'Inverter',
                            'beban_listrik' => 'Rumah atau Beban Listrik',
                        ],
                        'correct_answer' => [
                            'accepted_orders' => [
                                ['panel_surya', 'inverter', 'beban_listrik'],
                                ['panel_surya', 'baterai', 'inverter', 'beban_listrik'],
                            ],
                        ],
                        'explanation' => 'Panel surya menghasilkan listrik DC, baterai dapat menyimpan energi, inverter mengubahnya menjadi AC, lalu listrik dipakai oleh beban.',
                        'score' => 100,
                        'sort_order' => 1,
                        'config' => [],
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'code' => 'quick_quiz_energy',
                'slug' => 'kuis-cepat-energi',
                'title' => 'Kuis Cepat Energi',
                'type' => 'timed_quiz',
                'icon' => 'KUIS',
                'description' => 'Jawab 10 pertanyaan energi terbarukan dengan batas waktu 10 detik per soal.',
                'config' => ['allow_replay' => true, 'time_limit_seconds' => 10],
                'is_active' => true,
                'sort_order' => 2,
                'items' => $this->quickQuizItems(),
            ],
            [
                'code' => 'earth_rescue_mission',
                'slug' => 'misi-penyelamatan-bumi',
                'title' => 'Misi Penyelamatan Bumi',
                'type' => 'decision_mission',
                'icon' => 'MISI',
                'description' => 'Pilih keputusan energi bersih dan lihat dampaknya terhadap skor keberlanjutan.',
                'config' => ['allow_replay' => true],
                'is_active' => true,
                'sort_order' => 3,
                'items' => $this->missionItems(),
            ],
            [
                'code' => 'image_guess_energy',
                'slug' => 'tebak-gambar-energi',
                'title' => 'Tebak Gambar Energi',
                'type' => 'image_guess',
                'icon' => 'IMG',
                'description' => 'Tebak jenis energi dari gambar sederhana. Petunjuk mengurangi skor.',
                'config' => ['allow_replay' => true, 'hint_penalty' => 5],
                'is_active' => true,
                'sort_order' => 4,
                'items' => $this->imageGuessItems(),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function quickQuizItems(): array
    {
        $questions = [
            ['Apa arti energi terbarukan?', ['A' => 'Energi yang cepat habis', 'B' => 'Energi yang dapat diperbarui proses alam', 'C' => 'Energi buatan pabrik', 'D' => 'Energi yang hanya dipakai sekali'], 'B'],
            ['Komponen yang mengubah cahaya matahari menjadi listrik adalah ...', ['A' => 'Panel surya', 'B' => 'Kompresor', 'C' => 'Boiler', 'D' => 'Karburator'], 'A'],
            ['Energi angin dimanfaatkan dengan alat utama berupa ...', ['A' => 'Turbin angin', 'B' => 'Baterai kering', 'C' => 'Pompa minyak', 'D' => 'Mesin diesel'], 'A'],
            ['Sumber energi fosil yang perlu dikurangi adalah ...', ['A' => 'Matahari', 'B' => 'Air', 'C' => 'Batu bara', 'D' => 'Angin'], 'C'],
            ['Mikrohidro memanfaatkan energi dari ...', ['A' => 'Gelombang radio', 'B' => 'Aliran air', 'C' => 'Asap pabrik', 'D' => 'Bensin'], 'B'],
            ['Biomassa berasal dari ...', ['A' => 'Bahan organik', 'B' => 'Batu bara murni', 'C' => 'Plastik baru', 'D' => 'Logam berat'], 'A'],
            ['Menghemat listrik membantu karena ...', ['A' => 'Konsumsi energi turun', 'B' => 'Emisi selalu naik', 'C' => 'Panel surya rusak', 'D' => 'Data tidak diperlukan'], 'A'],
            ['Inverter pada sistem PLTS berfungsi untuk ...', ['A' => 'Mengubah DC menjadi AC', 'B' => 'Membakar batu bara', 'C' => 'Mengukur curah hujan', 'D' => 'Menghasilkan angin'], 'A'],
            ['Contoh data proyek hemat energi adalah ...', ['A' => 'Waktu pemakaian lampu', 'B' => 'Warna sepatu', 'C' => 'Nama kantin', 'D' => 'Jenis musik'], 'A'],
            ['Energi panas bumi memanfaatkan ...', ['A' => 'Panas dari dalam bumi', 'B' => 'Asap kendaraan', 'C' => 'Bahan bakar bensin', 'D' => 'Lampu kelas'], 'A'],
        ];

        return collect($questions)
            ->map(fn (array $question, int $index): array => [
                'item_type' => 'question',
                'prompt' => 'Jawab sebelum waktu habis.',
                'question_text' => $question[0],
                'options' => $question[1],
                'correct_answer' => ['key' => $question[2]],
                'explanation' => 'Jawaban benar menguatkan konsep energi terbarukan.',
                'score' => 10,
                'time_limit_seconds' => 10,
                'sort_order' => $index + 1,
                'config' => [],
                'is_active' => true,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function missionItems(): array
    {
        $scenarios = [
            [
                'Sekolah ingin mengurangi tagihan listrik. Apa keputusan awal yang paling seimbang?',
                [
                    ['key' => 'A', 'text' => 'Menyalakan semua lampu agar kelas lebih terang', 'score_delta' => 0, 'feedback' => 'Konsumsi naik dan masalah energi tidak berkurang.'],
                    ['key' => 'B', 'text' => 'Audit pemakaian listrik lalu kampanye hemat energi', 'score_delta' => 25, 'feedback' => 'Keputusan baik karena dimulai dari data dan perubahan kebiasaan.'],
                    ['key' => 'C', 'text' => 'Kampanye hemat energi sambil rancang PLTS bertahap', 'score_delta' => 30, 'feedback' => 'Solusi paling seimbang karena konsumsi turun sambil menyiapkan energi bersih.'],
                ],
            ],
            [
                'Bengkel sekolah membutuhkan sumber energi untuk alat praktik ringan.',
                [
                    ['key' => 'A', 'text' => 'Memakai genset setiap hari', 'score_delta' => 5, 'feedback' => 'Genset membantu tetapi tetap menghasilkan emisi dan biaya bahan bakar.'],
                    ['key' => 'B', 'text' => 'Memasang panel surya kecil untuk perangkat rendah daya', 'score_delta' => 30, 'feedback' => 'Pilihan tepat untuk praktik STEM dan pengurangan emisi.'],
                    ['key' => 'C', 'text' => 'Membiarkan alat menyala setelah praktik', 'score_delta' => 0, 'feedback' => 'Pemborosan energi membuat dampak lingkungan bertambah.'],
                ],
            ],
            [
                'Kantin menghasilkan sisa organik setiap hari.',
                [
                    ['key' => 'A', 'text' => 'Membuang semua sisa tanpa pemilahan', 'score_delta' => 0, 'feedback' => 'Tanpa pemilahan, potensi biomassa tidak dimanfaatkan.'],
                    ['key' => 'B', 'text' => 'Mengolah sisa organik menjadi kompos atau biogas sederhana', 'score_delta' => 30, 'feedback' => 'Keputusan baik karena mengubah limbah organik menjadi manfaat.'],
                    ['key' => 'C', 'text' => 'Menyimpan sisa makanan di kelas', 'score_delta' => 5, 'feedback' => 'Penyimpanan tanpa pengolahan menimbulkan masalah kebersihan.'],
                ],
            ],
            [
                'Kelompokmu harus menyampaikan hasil proyek energi.',
                [
                    ['key' => 'A', 'text' => 'Menyajikan data, foto, hasil, dan saran perbaikan', 'score_delta' => 30, 'feedback' => 'Laporan kuat karena berbasis bukti dan refleksi.'],
                    ['key' => 'B', 'text' => 'Hanya menulis kesimpulan tanpa data', 'score_delta' => 10, 'feedback' => 'Kesimpulan perlu didukung data agar dapat dipercaya.'],
                    ['key' => 'C', 'text' => 'Tidak membagikan hasil proyek', 'score_delta' => 0, 'feedback' => 'Komunikasi hasil adalah bagian penting pembelajaran proyek.'],
                ],
            ],
        ];

        return collect($scenarios)
            ->map(fn (array $scenario, int $index): array => [
                'item_type' => 'scenario',
                'prompt' => 'Pilih keputusan terbaik untuk misi keberlanjutan.',
                'question_text' => $scenario[0],
                'options' => $scenario[1],
                'correct_answer' => null,
                'explanation' => 'Setiap keputusan memberi dampak berbeda pada keberlanjutan.',
                'score' => 30,
                'sort_order' => $index + 1,
                'config' => [],
                'is_active' => true,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function imageGuessItems(): array
    {
        $items = [
            ['SUN', 'Sumber energi dari cahaya matahari.', ['surya' => 'Energi surya', 'angin' => 'Energi angin', 'air' => 'Energi air'], 'surya', 'Panel surya memanfaatkan cahaya matahari.'],
            ['WIND', 'Sumber energi dari gerakan udara.', ['surya' => 'Energi surya', 'angin' => 'Energi angin', 'biomassa' => 'Biomassa'], 'angin', 'Turbin angin mengubah gerak udara menjadi listrik.'],
            ['WATER', 'Sumber energi dari aliran sungai atau air terjun.', ['air' => 'Energi air', 'fosil' => 'Energi fosil', 'panas_bumi' => 'Panas bumi'], 'air', 'Mikrohidro memakai aliran air.'],
            ['BIO', 'Sumber energi dari bahan organik.', ['biomassa' => 'Biomassa', 'surya' => 'Energi surya', 'angin' => 'Energi angin'], 'biomassa', 'Biomassa berasal dari sisa organik atau bahan hayati.'],
            ['GEO', 'Sumber energi dari panas dalam bumi.', ['panas_bumi' => 'Panas bumi', 'air' => 'Energi air', 'fosil' => 'Energi fosil'], 'panas_bumi', 'Panas bumi memanfaatkan energi termal dari dalam bumi.'],
        ];

        return collect($items)
            ->map(fn (array $item, int $index): array => [
                'item_type' => 'image_question',
                'prompt' => $item[1],
                'question_text' => 'Tebak jenis energi dari simbol ini.',
                'media_url' => $item[0],
                'options' => $item[2],
                'correct_answer' => ['key' => $item[3]],
                'explanation' => $item[4],
                'score' => 20,
                'sort_order' => $index + 1,
                'config' => ['hint' => $item[1], 'hint_penalty' => 5],
                'is_active' => true,
            ])
            ->all();
    }
}
