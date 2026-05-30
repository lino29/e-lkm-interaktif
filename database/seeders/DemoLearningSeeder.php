<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Glossary;
use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Media;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Reference;
use App\Models\Rubric;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoLearningSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::role('guru')->first();

        if (! $teacher) {
            return;
        }

        $subject = Subject::firstOrCreate(
            ['code' => 'IPAS-X'],
            [
                'name' => 'Projek IPAS Kelas X',
                'description' => 'Mata pelajaran Projek IPAS untuk modul energi terbarukan.',
            ],
        );

        $module = Module::firstOrCreate(
            ['slug' => 'energi-terbarukan'],
            [
                'subject_id' => $subject->id,
                'created_by' => $teacher->id,
                'title' => 'E-LKM Energi Terbarukan',
                'introduction' => 'Modul digital untuk memahami konsep energi, masalah energi fosil, dan rancangan aksi energi terbarukan.',
                'learning_objectives' => 'Murid mampu menjelaskan sumber energi, membandingkan energi fosil dan terbarukan, serta merancang aksi sederhana.',
                'status' => 'published',
                'kktp' => 75,
                'max_attempts' => 2,
            ],
        );

        $learningUnits = [
            [
                'title' => 'KB1 Konsep Energi dan Sumber Energi',
                'objectives' => 'Murid mampu menjelaskan konsep energi, mengidentifikasi sumber energi di lingkungan sekitar, dan membedakan sumber energi berdasarkan ketersediaannya.',
                'content' => 'Energi adalah kemampuan untuk melakukan usaha atau menyebabkan perubahan. Dalam kehidupan sehari-hari, energi muncul sebagai cahaya, panas, gerak, listrik, dan bunyi. Sumber energi dapat berasal dari matahari, angin, air, biomassa, bahan bakar fosil, dan panas bumi. Energi tidak hilang, tetapi berubah bentuk, misalnya listrik berubah menjadi cahaya pada lampu atau gerak pada kipas. Pada kegiatan ini murid mengamati penggunaan energi di sekolah lalu mengelompokkan sumber energi berdasarkan contoh nyata, bentuk perubahan energi, manfaat, dan dampaknya.',
                'question' => 'Contoh sumber energi yang tersedia terus-menerus dari alam adalah ...',
                'options' => ['A' => 'Batu bara', 'B' => 'Matahari', 'C' => 'Minyak bumi', 'D' => 'Gas alam'],
                'answer' => 'B',
                'true_false' => 'Matahari termasuk sumber energi yang dapat diperbarui oleh proses alam.',
                'short_question' => 'Sebutkan satu sumber energi terbarukan yang mudah ditemukan di Indonesia.',
                'keywords' => ['matahari', 'air', 'angin'],
                'essay_question' => 'Jelaskan perbedaan energi terbarukan dan energi tidak terbarukan dengan contoh.',
                'reference_answer' => 'Energi terbarukan berasal dari proses alam yang dapat diperbarui seperti matahari, air, dan angin, sedangkan energi tidak terbarukan seperti batu bara dan minyak bumi jumlahnya terbatas.',
            ],
            [
                'title' => 'KB2 Masalah Energi Fosil',
                'objectives' => 'Murid mampu menjelaskan dampak penggunaan energi fosil terhadap lingkungan, kesehatan, dan keberlanjutan sumber daya.',
                'content' => 'Energi fosil seperti batu bara, minyak bumi, dan gas alam terbentuk dalam waktu sangat lama sehingga jumlahnya terbatas. Pembakaran energi fosil menghasilkan emisi gas rumah kaca dan polutan udara. Dampaknya dapat terlihat pada peningkatan suhu bumi, kualitas udara yang menurun, gangguan kesehatan pernapasan, dan biaya energi yang semakin tidak stabil. Murid diajak membaca masalah energi fosil dari aktivitas harian, seperti kendaraan, listrik, dan proses industri, lalu menilai kebiasaan yang bisa dikurangi.',
                'question' => 'Masalah utama dari pembakaran energi fosil adalah ...',
                'options' => ['A' => 'Tidak menghasilkan panas', 'B' => 'Menghasilkan emisi', 'C' => 'Selalu tersedia cepat', 'D' => 'Tidak dapat digunakan'],
                'answer' => 'B',
                'true_false' => 'Pembakaran energi fosil dapat menghasilkan emisi gas rumah kaca.',
                'short_question' => 'Sebutkan satu dampak penggunaan energi fosil bagi lingkungan.',
                'keywords' => ['emisi', 'polusi', 'pemanasan global'],
                'essay_question' => 'Jelaskan mengapa ketergantungan pada energi fosil perlu dikurangi.',
                'reference_answer' => 'Ketergantungan pada energi fosil perlu dikurangi karena sumbernya terbatas dan pembakarannya menghasilkan emisi serta polusi yang memperburuk pemanasan global dan kualitas udara.',
            ],
            [
                'title' => 'KB3 Pengertian dan Jenis Energi Terbarukan',
                'objectives' => 'Murid mampu mendefinisikan energi terbarukan dan membandingkan contoh energi surya, angin, air, biomassa, dan panas bumi.',
                'content' => 'Energi terbarukan adalah energi yang berasal dari proses alam yang terus berlangsung dan dapat diperbarui dalam skala waktu manusia. Contohnya energi surya dari matahari, energi angin dari pergerakan udara, energi air dari aliran sungai, biomassa dari bahan organik, dan panas bumi dari energi termal bumi. Setiap jenis energi memiliki kelebihan, keterbatasan, dan syarat lokasi. Karena itu murid perlu membandingkan potensi lingkungan sekitar sebelum memilih teknologi atau aksi yang paling tepat.',
                'question' => 'Energi terbarukan disebut berkelanjutan karena ...',
                'options' => ['A' => 'Dapat diperbarui oleh proses alam', 'B' => 'Hanya ada di tambang', 'C' => 'Tidak membutuhkan teknologi', 'D' => 'Selalu berbentuk bahan bakar cair'],
                'answer' => 'A',
                'true_false' => 'Energi angin, air, biomassa, panas bumi, dan surya termasuk energi terbarukan.',
                'short_question' => 'Tuliskan dua jenis energi terbarukan.',
                'keywords' => ['surya', 'angin', 'air', 'biomassa', 'panas bumi'],
                'essay_question' => 'Jelaskan alasan energi terbarukan dinilai lebih berkelanjutan daripada energi fosil.',
                'reference_answer' => 'Energi terbarukan dinilai lebih berkelanjutan karena sumbernya berasal dari proses alam yang terus berlangsung dan emisinya lebih rendah dibandingkan pembakaran energi fosil.',
            ],
            [
                'title' => 'KB4 Teknologi Energi Terbarukan Berbasis STEM',
                'objectives' => 'Murid mampu menghubungkan konsep sains, teknologi, rekayasa, dan matematika pada panel surya, turbin angin, mikrohidro, dan biodigester sederhana.',
                'content' => 'Teknologi energi terbarukan memanfaatkan prinsip STEM. Panel surya mengubah cahaya menjadi listrik, turbin angin mengubah energi gerak udara menjadi putaran generator, mikrohidro memakai aliran air, dan biodigester menghasilkan biogas dari bahan organik. Sains membantu memahami sumber energi, teknologi menyediakan alat, rekayasa mengatur rancangan, dan matematika dipakai untuk menghitung kebutuhan daya, biaya, serta efisiensi. Murid menganalisis cara kerja teknologi dan faktor yang memengaruhi keberhasilannya.',
                'question' => 'Komponen teknologi yang mengubah cahaya matahari menjadi listrik adalah ...',
                'options' => ['A' => 'Panel surya', 'B' => 'Kompresor', 'C' => 'Boiler batu bara', 'D' => 'Karburator'],
                'answer' => 'A',
                'true_false' => 'Panel surya mengubah energi cahaya matahari menjadi energi listrik.',
                'short_question' => 'Sebutkan satu teknologi energi terbarukan berbasis STEM.',
                'keywords' => ['panel surya', 'turbin angin', 'mikrohidro', 'biodigester'],
                'essay_question' => 'Jelaskan contoh penerapan konsep STEM pada teknologi energi terbarukan.',
                'reference_answer' => 'Konsep STEM tampak pada panel surya yang memakai sains cahaya, teknologi sel surya, rekayasa pemasangan, dan matematika untuk menghitung kebutuhan energi serta efisiensi.',
            ],
            [
                'title' => 'KB5 Merancang Aksi Sederhana Energi Terbarukan',
                'objectives' => 'Murid mampu merancang aksi sederhana hemat energi atau pemanfaatan energi terbarukan di sekolah berdasarkan masalah yang ditemukan.',
                'content' => 'Aksi sederhana energi terbarukan dimulai dari menemukan masalah nyata, menetapkan tujuan, memilih alat dan bahan, menyusun langkah kerja, mengumpulkan data, lalu menyimpulkan hasilnya. Contoh aksi adalah audit penggunaan listrik kelas, kampanye lampu hemat energi, briket biomassa, atau model sederhana panel surya untuk mengisi perangkat kecil. Rancangan proyek perlu mempertimbangkan keselamatan kerja, ketersediaan alat, pembagian tugas, bukti foto atau video, dan cara mengukur dampak sebelum disimpulkan.',
                'question' => 'Langkah awal merancang proyek energi terbarukan adalah ...',
                'options' => ['A' => 'Menentukan nilai akhir', 'B' => 'Menemukan masalah nyata', 'C' => 'Menghapus data', 'D' => 'Langsung membuat laporan'],
                'answer' => 'B',
                'true_false' => 'Rancangan aksi energi terbarukan sebaiknya dimulai dari masalah nyata di lingkungan sekitar.',
                'short_question' => 'Sebutkan satu data yang perlu dikumpulkan saat membuat proyek hemat energi.',
                'keywords' => ['penggunaan listrik', 'waktu pemakaian', 'daya listrik', 'kebiasaan'],
                'essay_question' => 'Jelaskan tahapan singkat merancang aksi sederhana energi terbarukan di sekolah.',
                'reference_answer' => 'Tahapan aksi dimulai dari menemukan masalah, menetapkan tujuan, memilih alat dan bahan, menyusun langkah kerja, mengumpulkan data, mengevaluasi hasil, dan menarik kesimpulan.',
            ],
        ];

        foreach ($learningUnits as $index => $data) {
            $order = $index + 1;
            $unit = LearningUnit::firstOrCreate(
                [
                    'module_id' => $module->id,
                    'slug' => Str::slug($data['title']),
                ],
                [
                    'title' => $data['title'],
                    'description' => 'Kegiatan belajar '.$order.' pada modul energi terbarukan.',
                    'objectives' => $data['objectives'],
                    'order' => $order,
                ],
            );

            $material = Material::firstOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'title' => 'Materi '.$data['title'],
                ],
                [
                    'content' => $data['content'],
                    'material_type' => 'text',
                    'order' => 1,
                ],
            );

            $media = $this->mediaDescriptorFor($order, $data['title']);

            Media::updateOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'order' => 1,
                ],
                [
                    'material_id' => $material->id,
                    'title' => $media['title'],
                    'type' => 'image',
                    'url' => null,
                    'file_path' => $media['file_path'],
                    'embed_code' => $media['caption'],
                ],
            );

            // Activities are now seeded by RenewableEnergyActivitySeeder

            $assessment = Assessment::firstOrCreate(
                [
                    'module_id' => $module->id,
                    'learning_unit_id' => $unit->id,
                    'title' => 'Asesmen Formatif '.$data['title'],
                ],
                [
                    'type' => 'formative',
                    'description' => 'Asesmen formatif untuk '.$data['title'].'.',
                    'kktp' => 75,
                    'max_attempts' => 2,
                    'is_published' => true,
                    'order' => $order,
                ],
            );

            $this->seedFormativeQuestions($assessment, $data);
        }

        $this->seedModuleExtras($module);
        $this->seedFinalAssessment($module);

        $essayAssessment = Assessment::firstOrCreate(
            [
                'module_id' => $module->id,
                'title' => 'Asesmen Uraian Energi Terbarukan',
            ],
            [
                'learning_unit_id' => $module->learningUnits()->orderBy('order')->first()?->id,
                'type' => 'formative',
                'description' => 'Asesmen uraian untuk menguji penjelasan konsep energi terbarukan.',
                'kktp' => 75,
                'max_attempts' => 2,
                'is_published' => true,
                'order' => 99,
            ],
        );

        $essay = Question::firstOrCreate(
            [
                'assessment_id' => $essayAssessment->id,
                'order' => 1,
            ],
            [
                'question_text' => 'Jelaskan mengapa energi surya termasuk energi terbarukan.',
                'question_type' => 'essay',
                'reference_answer' => 'Energi surya berasal dari matahari, tersedia terus-menerus, dan dapat dimanfaatkan tanpa menghabiskan bahan bakar fosil.',
                'weight' => 10,
            ],
        );

        QuestionKeyword::firstOrCreate(['question_id' => $essay->id, 'keyword' => 'matahari'], ['weight' => 2]);
        QuestionKeyword::firstOrCreate(['question_id' => $essay->id, 'keyword' => 'terus menerus'], ['weight' => 1]);
        QuestionKeyword::firstOrCreate(['question_id' => $essay->id, 'keyword' => 'bahan bakar fosil'], ['weight' => 1]);
        Rubric::firstOrCreate(['question_id' => $essay->id, 'criterion' => 'Ketepatan konsep'], ['score' => 80]);
    }

    /**
     * @return array{title: string, file_path: string, caption: string}
     */
    private function mediaDescriptorFor(int $order, string $title): array
    {
        return [
            'title' => 'Ilustrasi '.$title,
            'file_path' => 'demo/media/energi-terbarukan/kb'.$order.'-'.Str::slug($title).'.png',
            'caption' => 'Caption: media demo untuk mengamati konsep utama '.$title.' pada konteks sekolah dan rumah.',
        ];
    }

    private function seedModuleExtras(Module $module): void
    {
        foreach ([
            ['Energi', 'Kemampuan untuk melakukan usaha atau menyebabkan perubahan pada benda atau sistem.'],
            ['Energi terbarukan', 'Energi dari proses alam yang dapat diperbarui dalam skala waktu manusia, seperti surya, angin, air, biomassa, dan panas bumi.'],
            ['Energi fosil', 'Energi dari batu bara, minyak bumi, dan gas alam yang terbentuk sangat lama dan menghasilkan emisi saat dibakar.'],
            ['STEM', 'Pendekatan belajar yang menghubungkan sains, teknologi, rekayasa, dan matematika untuk menyelesaikan masalah.'],
            ['KKTP', 'Kriteria Ketercapaian Tujuan Pembelajaran yang menjadi batas ketuntasan asesmen.'],
        ] as $index => [$term, $definition]) {
            Glossary::updateOrCreate(
                ['module_id' => $module->id, 'term' => $term],
                ['definition' => $definition, 'order' => $index + 1],
            );
        }

        foreach ([
            ['Energi Terbarukan: Konsep dan Pemanfaatannya', 'Kementerian ESDM', 'Bahan ajar energi terbarukan', 2023],
            ['Projek IPAS SMK Kelas X', 'Kemdikbudristek', 'Capaian pembelajaran Projek IPAS', 2022],
            ['Renewable Energy Education Toolkit', 'IRENA', 'Referensi pembelajaran energi bersih', 2021],
        ] as $index => [$title, $author, $source, $year]) {
            Reference::updateOrCreate(
                ['module_id' => $module->id, 'title' => $title],
                ['author' => $author, 'source' => $source, 'year' => $year, 'order' => $index + 1],
            );
        }
    }

    private function seedFinalAssessment(Module $module): void
    {
        $assessment = Assessment::updateOrCreate(
            [
                'module_id' => $module->id,
                'learning_unit_id' => null,
                'title' => 'Asesmen Akhir Modul Energi Terbarukan',
            ],
            [
                'type' => 'final',
                'description' => 'Asesmen akhir untuk mengukur pemahaman utuh tentang konsep energi, energi fosil, energi terbarukan, teknologi STEM, dan rancangan aksi sederhana.',
                'kktp' => 75,
                'max_attempts' => 2,
                'is_published' => true,
                'order' => 100,
            ],
        );

        $this->seedFormativeQuestions($assessment, [
            'question' => 'Tujuan utama transisi dari energi fosil ke energi terbarukan adalah ...',
            'options' => ['A' => 'Meningkatkan emisi', 'B' => 'Mengurangi dampak lingkungan dan menjaga keberlanjutan energi', 'C' => 'Menghapus kebutuhan data', 'D' => 'Menghindari teknologi'],
            'answer' => 'B',
            'true_false' => 'Rancangan aksi energi terbarukan perlu didasarkan pada masalah nyata dan data pengamatan.',
            'short_question' => 'Sebutkan satu teknologi energi terbarukan dan sumber energi yang dimanfaatkannya.',
            'keywords' => ['panel surya', 'matahari', 'turbin angin', 'angin', 'mikrohidro', 'air', 'biomassa'],
            'essay_question' => 'Jelaskan hubungan antara masalah energi fosil, energi terbarukan, dan rancangan aksi sederhana di sekolah.',
            'reference_answer' => 'Energi fosil berdampak pada emisi dan keterbatasan sumber daya. Energi terbarukan seperti surya, angin, air, biomassa, dan panas bumi dapat menjadi alternatif. Rancangan aksi sederhana di sekolah perlu dimulai dari masalah nyata, data pengamatan, tujuan, alat bahan, langkah kerja, dan evaluasi hasil.',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function activityPromptsFor(int $order): array
    {
        return match ($order) {
            1 => [
                'ayo_mengamati' => 'Amati tiga penggunaan energi di kelas atau rumah. Catat bentuk energi awal, bentuk energi akhir, dan alat yang mengubah energi tersebut.',
                'ayo_bertanya' => 'Susun dua pertanyaan tentang asal sumber energi yang kamu amati dan alasan sumber tersebut termasuk terbarukan atau tidak terbarukan.',
                'ayo_mencoba' => 'Buat tabel dengan kolom alat, sumber energi, bentuk perubahan energi, manfaat, dan catatan penggunaan harian.',
                'ayo_menalar' => 'Bandingkan hasil pengamatanmu. Jelaskan pola perubahan energi yang paling sering muncul dan alasan sumber energinya dipilih.',
                'ayo_menyimpulkan' => 'Rumuskan kesimpulan tentang konsep energi dan contoh sumber energi yang paling dekat dengan kehidupan sehari-hari.',
                'forum_diskusi' => 'Bagikan satu contoh penggunaan energi di sekitarmu, lalu tanggapi contoh teman dengan menyebutkan sumber energinya.',
            ],
            2 => [
                'ayo_mengamati' => 'Amati berita, foto, atau kondisi sekitar tentang penggunaan bahan bakar fosil. Catat dampak yang terlihat pada udara, biaya, atau kesehatan.',
                'ayo_bertanya' => 'Tulis dua pertanyaan penyebab dan dampak ketergantungan energi fosil yang perlu dijawab melalui diskusi kelas.',
                'ayo_mencoba' => 'Buat tabel dengan kolom sumber fosil, contoh penggunaan, emisi atau polutan, dampak lingkungan, dan alternatif pengurangan.',
                'ayo_menalar' => 'Jelaskan hubungan antara pembakaran energi fosil, emisi gas rumah kaca, dan kebutuhan transisi energi.',
                'ayo_menyimpulkan' => 'Simpulkan alasan ilmiah dan sosial mengapa penggunaan energi fosil perlu dikurangi secara bertahap.',
                'forum_diskusi' => 'Diskusikan kebiasaan di sekolah yang masih bergantung pada energi fosil dan usulkan satu perubahan yang realistis.',
            ],
            3 => [
                'ayo_mengamati' => 'Amati potensi energi surya, angin, air, biomassa, atau panas bumi di lingkungan sekitar. Catat potensi yang paling mungkin dimanfaatkan.',
                'ayo_bertanya' => 'Buat dua pertanyaan tentang kelebihan, keterbatasan, dan syarat lokasi untuk salah satu jenis energi terbarukan.',
                'ayo_mencoba' => 'Buat tabel perbandingan dengan kolom jenis energi, sumber alam, contoh teknologi, kelebihan, keterbatasan, dan peluang di daerahmu.',
                'ayo_menalar' => 'Analisis jenis energi terbarukan yang paling sesuai untuk sekolahmu dengan mempertimbangkan sumber daya dan kebutuhan energi.',
                'ayo_menyimpulkan' => 'Tuliskan kesimpulan tentang pengertian energi terbarukan dan jenis yang paling relevan untuk lingkunganmu.',
                'forum_diskusi' => 'Pilih satu jenis energi terbarukan yang menurutmu paling mungkin diterapkan di sekolah dan beri alasan.',
            ],
            4 => [
                'ayo_mengamati' => 'Amati contoh panel surya, turbin angin, mikrohidro, atau biodigester melalui media yang tersedia. Catat komponen utama dan fungsi tiap komponen.',
                'ayo_bertanya' => 'Susun dua pertanyaan tentang cara kerja teknologi energi terbarukan dan faktor yang memengaruhi efisiensinya.',
                'ayo_mencoba' => 'Buat tabel STEM dengan kolom teknologi, konsep sains, komponen teknologi, prinsip rekayasa, data matematika, dan contoh penerapan.',
                'ayo_menalar' => 'Jelaskan bagaimana sains, teknologi, rekayasa, dan matematika saling berhubungan dalam satu teknologi yang kamu pilih.',
                'ayo_menyimpulkan' => 'Simpulkan manfaat pendekatan STEM dalam merancang atau memilih teknologi energi terbarukan.',
                'forum_diskusi' => 'Diskusikan teknologi energi terbarukan yang paling cocok untuk sekolah beserta kendala teknisnya.',
            ],
            default => [
                'ayo_mengamati' => 'Amati masalah penggunaan energi di kelas, bengkel, kantin, atau rumah. Catat bukti masalah dan siapa yang terdampak.',
                'ayo_bertanya' => 'Tulis dua pertanyaan proyek tentang penyebab masalah energi dan data yang harus dikumpulkan sebelum merancang aksi.',
                'ayo_mencoba' => 'Buat tabel rancangan proyek dengan kolom masalah, tujuan, alat dan bahan, langkah kerja, data yang dikumpulkan, hasil yang diharapkan, dan risiko keselamatan.',
                'ayo_menalar' => 'Nilai kelayakan rancangan aksimu berdasarkan data, waktu, alat, keselamatan, dan dampak yang mungkin dicapai.',
                'ayo_menyimpulkan' => 'Rangkum rancangan aksi sederhana energi terbarukan atau hemat energi yang paling realistis untuk dilaksanakan.',
                'forum_diskusi' => 'Presentasikan rancangan aksi kelompokmu dan beri masukan terhadap rancangan kelompok lain secara spesifik.',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedFormativeQuestions(Assessment $assessment, array $data): void
    {
        $contextTitle = $assessment->learningUnit?->title ?? $assessment->module->title;

        Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 1,
            ],
            [
                'question_text' => $data['question'],
                'question_type' => 'multiple_choice',
                'options' => $data['options'],
                'correct_answer' => [$data['answer']],
                'weight' => 10,
            ],
        );

        Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 2,
            ],
            [
                'question_text' => $data['true_false'],
                'question_type' => 'true_false',
                'options' => ['Benar' => true, 'Salah' => false],
                'correct_answer' => [true],
                'weight' => 10,
            ],
        );

        $shortAnswer = Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 3,
            ],
            [
                'question_text' => $data['short_question'],
                'question_type' => 'short_answer',
                'correct_answer' => $data['keywords'],
                'weight' => 10,
            ],
        );

        $essay = Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 4,
            ],
            [
                'question_text' => $data['essay_question'],
                'question_type' => 'essay',
                'reference_answer' => $data['reference_answer'],
                'weight' => 20,
            ],
        );

        foreach ($data['keywords'] as $keyword) {
            $this->seedKeyword($shortAnswer, $keyword, 1);
            $this->seedKeyword($essay, $keyword, 1);
        }

        Rubric::updateOrCreate(
            ['question_id' => $essay->id, 'criterion' => 'Ketepatan konsep'],
            [
                'level' => 'baik',
                'description' => 'Jawaban memuat konsep utama, contoh, dan hubungan sebab-akibat yang sesuai.',
                'score' => 85,
            ],
        );

        Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 5,
            ],
            [
                'question_text' => 'Pilih dua konsep yang paling sesuai dengan '.$contextTitle.'.',
                'question_type' => 'complex_multiple_choice',
                'options' => [
                    'A' => $data['keywords'][0],
                    'B' => $data['keywords'][1] ?? $data['keywords'][0],
                    'C' => 'batu bara',
                    'D' => 'jawaban tanpa data',
                ],
                'correct_answer' => ['A', 'B'],
                'weight' => 10,
            ],
        );

        Question::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 6,
            ],
            [
                'question_text' => 'Jodohkan konsep energi berikut dengan karakteristik yang tepat.',
                'question_type' => 'matching',
                'options' => [
                    'A' => 'Energi terbarukan',
                    'B' => 'Energi fosil',
                ],
                'correct_answer' => [
                    'A' => 'Diperbarui proses alam',
                    'B' => 'Sumber terbatas',
                ],
                'weight' => 10,
            ],
        );
    }

    private function seedKeyword(Question $question, string $keyword, int $weight): void
    {
        QuestionKeyword::updateOrCreate(
            ['question_id' => $question->id, 'keyword' => $keyword],
            ['normalized_keyword' => Str::lower($keyword), 'weight' => $weight],
        );
    }
}
