<?php

namespace App\Services\Learning;

class ActivityTemplateService
{
    /**
     * Get the default template configuration for a specific phase and learning unit order.
     */
    public function templateFor(string $phase, ?int $learningUnitOrder = null): array
    {
        $methodName = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $phase))).'Template';

        if (method_exists($this, $methodName)) {
            return $this->$methodName($learningUnitOrder);
        }

        return $this->getDefaultTemplate($phase);
    }

    private function getAyoMengamatiTemplate(?int $kb): array
    {
        $prompt = match ($kb) {
            1 => 'Lakukan observasi penggunaan energi di kelas/sekolah Anda. Tuliskan apa saja yang Anda temukan.',
            2 => 'Lakukan pengamatan kendaraan masuk sekolah selama 10 menit. Catat jenis dan perkiraan jumlah bahan bakar yang digunakan.',
            3 => 'Amatilah video/gambar tentang panel surya, PLTA, turbin angin, biogas, dan briket biomassa. Apa pendapat Anda?',
            4 => 'Amatilah video atau simulasi panel surya / kompor surya. Catat bagian-bagian pentingnya.',
            5 => 'Amatilah masalah energi yang ada di lingkungan sekolah. Tuliskan permasalahan utama yang Anda temukan.',
            default => 'Tuliskan hasil pengamatan Anda pada kolom di bawah ini.'
        };

        return [
            'input_type' => 'essay',
            'title' => 'Ayo Mengamati',
            'prompt' => $prompt,
            'answer_schema' => null,
            'display_config' => null,
            'validation_rules' => ['required' => true, 'min_words' => 10],
            'requires_teacher_review' => false,
        ];
    }

    private function getAyoBertanyaTemplate(?int $kb): array
    {
        $prompt = match ($kb) {
            1 => 'Tuliskan pertanyaan tentang sumber energi alternatif untuk menghemat listrik sekolah.',
            2 => 'Tuliskan pertanyaan mengenai akibat ketergantungan bahan bakar fosil.',
            3 => 'Tanya: Mengapa alasan daerah berbeda membutuhkan energi terbarukan yang berbeda?',
            4 => 'Tanya: Mengapa warna hitam lebih cepat menyerap panas dibanding warna putih?',
            5 => 'Tuliskan masalah energi yang paling dekat dengan kehidupan Anda sehari-hari.',
            default => 'Tuliskan pertanyaan yang muncul di benak Anda terkait materi ini.'
        };

        return [
            'input_type' => 'short_text',
            'title' => 'Ayo Bertanya',
            'prompt' => $prompt,
            'answer_schema' => null,
            'display_config' => null,
            'validation_rules' => ['required' => true],
            'requires_teacher_review' => false,
        ];
    }

    private function getAyoMencobaTemplate(?int $kb): array
    {
        if ($kb === 1) {
            return [
                'input_type' => 'table',
                'title' => 'Ayo Mencoba: Alat dan Energi',
                'prompt' => 'Lengkapi tabel 10 alat dengan kolom alat, energi masuk, energi keluar, dan sumber energi.',
                'answer_schema' => [
                    'columns' => [
                        ['name' => 'alat', 'label' => 'Nama Alat', 'type' => 'text', 'required' => true],
                        ['name' => 'energi_masuk', 'label' => 'Energi Masuk', 'type' => 'text', 'required' => true],
                        ['name' => 'energi_keluar', 'label' => 'Energi Keluar', 'type' => 'text', 'required' => true],
                        ['name' => 'sumber_energi', 'label' => 'Sumber Energi', 'type' => 'text', 'required' => true],
                    ],
                    'min_rows' => 10,
                    'allow_add' => true,
                    'allow_delete' => true,
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ];
        }

        if ($kb === 2) {
            return [
                'input_type' => 'essay',
                'title' => 'Ayo Mencoba: Peta Masalah',
                'prompt' => 'Uraikan peta masalah energi fosil berdasarkan hasil analisis Anda.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true, 'min_words' => 20],
                'requires_teacher_review' => false,
            ];
        }

        if ($kb === 3) {
            return [
                'input_type' => 'table',
                'title' => 'Ayo Mencoba: Potensi Energi Terbarukan',
                'prompt' => 'Isilah tabel kondisi lingkungan, energi terbarukan yang cocok, serta alasannya.',
                'answer_schema' => [
                    'columns' => [
                        ['name' => 'kondisi', 'label' => 'Kondisi Lingkungan', 'type' => 'text', 'required' => true],
                        ['name' => 'energi_cocok', 'label' => 'Energi yang Cocok', 'type' => 'text', 'required' => true],
                        ['name' => 'alasan', 'label' => 'Alasan', 'type' => 'textarea', 'required' => true],
                    ],
                    'min_rows' => 3,
                    'allow_add' => true,
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ];
        }

        if ($kb === 4) {
            return [
                'input_type' => 'table',
                'title' => 'Ayo Mencoba: Suhu dan Penyerapan Panas',
                'prompt' => 'Lakukan percobaan warna dan penyerapan panas. Catat suhu awal dan akhir.',
                'answer_schema' => [
                    'columns' => [
                        ['name' => 'warna', 'label' => 'Warna Material', 'type' => 'text', 'required' => true],
                        ['name' => 'waktu', 'label' => 'Waktu (menit)', 'type' => 'number', 'required' => true],
                        ['name' => 'suhu_awal', 'label' => 'Suhu Awal (°C)', 'type' => 'number', 'required' => true],
                        ['name' => 'suhu_akhir', 'label' => 'Suhu Akhir (°C)', 'type' => 'number', 'required' => true],
                        ['name' => 'perubahan_suhu', 'label' => 'Perubahan Suhu', 'type' => 'computed', 'formula' => 'suhu_akhir - suhu_awal', 'required' => false],
                    ],
                    'min_rows' => 2,
                    'allow_add' => true,
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ];
        }

        if ($kb === 5) {
            return [
                'input_type' => 'project_form',
                'title' => 'Ayo Mencoba: Rancangan Aksi',
                'prompt' => 'Buatlah rancangan aksi sederhana mengenai energi terbarukan.',
                'answer_schema' => [
                    'fields' => [
                        ['name' => 'project_type', 'label' => 'Tipe Proyek / Nama Aksi', 'type' => 'text', 'required' => true],
                        ['name' => 'problem', 'label' => 'Masalah yang Diangkat', 'type' => 'textarea', 'required' => true],
                        ['name' => 'objective', 'label' => 'Tujuan Proyek', 'type' => 'textarea', 'required' => true],
                        ['name' => 'tools_materials', 'label' => 'Alat dan Bahan', 'type' => 'textarea', 'required' => true],
                        ['name' => 'procedure', 'label' => 'Langkah Kerja', 'type' => 'textarea', 'required' => true],
                        ['name' => 'expected_result', 'label' => 'Hasil yang Diharapkan', 'type' => 'textarea', 'required' => true],
                    ],
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ];
        }

        return $this->getDefaultTemplate('ayo_mencoba');
    }

    private function getAyoMenalarTemplate(?int $kb): array
    {
        $prompt = match ($kb) {
            1 => 'Berikan alasan mengapa listrik harus dihemat walaupun kita tidak melihat asap polusinya.',
            2 => 'Berikan alasan mengapa transisi energi fosil harus dilakukan secara bertahap.',
            3 => 'Sebutkan faktor-faktor yang perlu dipertimbangkan saat akan memasang panel surya di sekolah.',
            4 => 'Jelaskan prinsip penyerapan panas dalam desain kompor surya.',
            5 => 'Jelaskan kelayakan proyek Anda dan apakah ada risiko keselamatan yang perlu diperhatikan.',
            default => 'Jelaskan alasan atau hubungan sebab-akibat dari hasil percobaan Anda.'
        };

        return [
            'input_type' => 'essay',
            'title' => 'Ayo Menalar',
            'prompt' => $prompt,
            'answer_schema' => null,
            'display_config' => null,
            'validation_rules' => ['required' => true],
            'requires_teacher_review' => true,
        ];
    }

    private function getAyoMenyimpulkanTemplate(?int $kb): array
    {
        $prompt = match ($kb) {
            1 => 'Tuliskan kesimpulan tentang hubungan energi, perubahan energi, dan sumber energi.',
            2 => 'Berikan tiga alasan utama mengapa penggunaan energi fosil perlu dikurangi.',
            3 => 'Berdasarkan tabel Anda, energi terbarukan mana yang paling sesuai untuk lingkungan sekolah? Mengapa?',
            4 => 'Tuliskan kesimpulan Anda berdasarkan data perubahan suhu yang diperoleh.',
            5 => 'Tuliskan kesimpulan proyek Anda berdasarkan data yang akan/telah didapat.',
            default => 'Tuliskan kesimpulan akhir berdasarkan seluruh aktivitas di atas.'
        };

        return [
            'input_type' => 'essay',
            'title' => 'Ayo Menyimpulkan',
            'prompt' => $prompt,
            'answer_schema' => null,
            'display_config' => null,
            'validation_rules' => ['required' => true],
            'requires_teacher_review' => true,
        ];
    }

    private function getForumDiskusiTemplate(?int $kb): array
    {
        $prompt = match ($kb) {
            1 => 'Diskusikan: Aktivitas apa di sekolah yang paling banyak menggunakan energi listrik?',
            2 => 'Bagikan ide nyata untuk mengurangi energi fosil di sekolah Anda.',
            3 => 'Bagaimana pendapat Anda mengenai briket biomassa sebagai solusi energi rumah tangga?',
            4 => 'Diskusikan bagaimana teknologi sederhana dapat membantu daerah yang sulit akses energi.',
            5 => 'Presentasikan secara singkat rancangan proyek kelompok Anda, dan berikan komentar kepada minimal 2 kelompok lain.',
            default => 'Tuliskan pandangan Anda di forum ini, dan berikan tanggapan untuk teman yang lain.'
        };

        $rules = ['required' => true];
        if ($kb === 5) {
            $rules['reply_required'] = true;
            $rules['min_replies'] = 2;
        }

        return [
            'input_type' => 'discussion',
            'title' => 'Forum Diskusi/Refleksi',
            'prompt' => $prompt,
            'answer_schema' => null,
            'display_config' => null,
            'validation_rules' => $rules,
            'requires_teacher_review' => false,
        ];
    }

    private function getDefaultTemplate(string $phase): array
    {
        return match ($phase) {
            'ayo_mencoba' => [
                'input_type' => 'table',
                'title' => 'Ayo Mencoba',
                'prompt' => 'Lengkapi tabel percobaan di bawah ini.',
                'answer_schema' => [
                    'columns' => [
                        ['name' => 'alat', 'label' => 'Nama Alat/Bahan', 'type' => 'text'],
                        ['name' => 'hasil', 'label' => 'Hasil/Pengamatan', 'type' => 'text'],
                    ],
                    'min_rows' => 3,
                    'allow_add' => true,
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ],
            default => [
                'input_type' => 'short_text',
                'title' => 'Aktivitas Umum',
                'prompt' => 'Isikan jawaban Anda.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => null,
                'requires_teacher_review' => false,
            ],
        };
    }

    /**
     * Check if a given JSON string is valid for schema structure.
     */
    public function isValidSchema(?string $json): bool
    {
        if (blank($json)) {
            return true;
        }

        json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $decoded = json_decode($json, true);
        if (is_array($decoded) && isset($decoded['columns'])) {
            return is_array($decoded['columns']);
        }

        return true;
    }
}
