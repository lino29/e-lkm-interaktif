<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Material;
use App\Models\Media;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
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
                'content' => 'Energi adalah kemampuan untuk melakukan usaha atau menyebabkan perubahan. Dalam kehidupan sehari-hari, energi muncul sebagai cahaya, panas, gerak, listrik, dan bunyi. Sumber energi dapat berasal dari matahari, angin, air, biomassa, bahan bakar fosil, dan panas bumi. Pada kegiatan ini murid mengamati penggunaan energi di sekolah lalu mengelompokkan sumber energi berdasarkan contoh nyata.',
                'question' => 'Contoh sumber energi yang tersedia terus-menerus dari alam adalah ...',
                'options' => ['A' => 'Batu bara', 'B' => 'Matahari', 'C' => 'Minyak bumi', 'D' => 'Gas alam'],
                'answer' => 'B',
            ],
            [
                'title' => 'KB2 Masalah Energi Fosil',
                'objectives' => 'Murid mampu menjelaskan dampak penggunaan energi fosil terhadap lingkungan, kesehatan, dan keberlanjutan sumber daya.',
                'content' => 'Energi fosil seperti batu bara, minyak bumi, dan gas alam terbentuk dalam waktu sangat lama sehingga jumlahnya terbatas. Pembakaran energi fosil menghasilkan emisi gas rumah kaca dan polutan udara. Dampaknya dapat terlihat pada peningkatan suhu bumi, kualitas udara yang menurun, dan biaya energi yang semakin tidak stabil.',
                'question' => 'Masalah utama dari pembakaran energi fosil adalah ...',
                'options' => ['A' => 'Tidak menghasilkan panas', 'B' => 'Menghasilkan emisi', 'C' => 'Selalu tersedia cepat', 'D' => 'Tidak dapat digunakan'],
                'answer' => 'B',
            ],
            [
                'title' => 'KB3 Pengertian dan Jenis Energi Terbarukan',
                'objectives' => 'Murid mampu mendefinisikan energi terbarukan dan membandingkan contoh energi surya, angin, air, biomassa, dan panas bumi.',
                'content' => 'Energi terbarukan adalah energi yang berasal dari proses alam yang terus berlangsung dan dapat diperbarui dalam skala waktu manusia. Contohnya energi surya dari matahari, energi angin dari pergerakan udara, energi air dari aliran sungai, biomassa dari bahan organik, dan panas bumi dari energi termal bumi.',
                'question' => 'Energi terbarukan disebut berkelanjutan karena ...',
                'options' => ['A' => 'Dapat diperbarui oleh proses alam', 'B' => 'Hanya ada di tambang', 'C' => 'Tidak membutuhkan teknologi', 'D' => 'Selalu berbentuk bahan bakar cair'],
                'answer' => 'A',
            ],
            [
                'title' => 'KB4 Teknologi Energi Terbarukan Berbasis STEM',
                'objectives' => 'Murid mampu menghubungkan konsep sains, teknologi, rekayasa, dan matematika pada panel surya, turbin angin, mikrohidro, dan biodigester sederhana.',
                'content' => 'Teknologi energi terbarukan memanfaatkan prinsip STEM. Panel surya mengubah cahaya menjadi listrik, turbin angin mengubah energi gerak udara menjadi putaran generator, mikrohidro memakai aliran air, dan biodigester menghasilkan biogas dari bahan organik. Murid menganalisis cara kerja teknologi dan faktor yang memengaruhi efisiensinya.',
                'question' => 'Komponen teknologi yang mengubah cahaya matahari menjadi listrik adalah ...',
                'options' => ['A' => 'Panel surya', 'B' => 'Kompresor', 'C' => 'Boiler batu bara', 'D' => 'Karburator'],
                'answer' => 'A',
            ],
            [
                'title' => 'KB5 Merancang Aksi Sederhana Energi Terbarukan',
                'objectives' => 'Murid mampu merancang aksi sederhana hemat energi atau pemanfaatan energi terbarukan di sekolah berdasarkan masalah yang ditemukan.',
                'content' => 'Aksi sederhana energi terbarukan dimulai dari menemukan masalah nyata, menetapkan tujuan, memilih alat dan bahan, menyusun langkah kerja, mengumpulkan data, lalu menyimpulkan hasilnya. Contoh aksi adalah audit penggunaan listrik kelas, kampanye lampu hemat energi, atau model sederhana panel surya untuk mengisi perangkat kecil.',
                'question' => 'Langkah awal merancang proyek energi terbarukan adalah ...',
                'options' => ['A' => 'Menentukan nilai akhir', 'B' => 'Menemukan masalah nyata', 'C' => 'Menghapus data', 'D' => 'Langsung membuat laporan'],
                'answer' => 'B',
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

            Media::firstOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'title' => 'Media ilustrasi '.$data['title'],
                ],
                [
                    'material_id' => $material->id,
                    'type' => 'link',
                    'url' => 'https://example.com/media/'.Str::slug($data['title']),
                    'order' => 1,
                ],
            );

            foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'] as $activityOrder => $phase) {
                Activity::firstOrCreate(
                    [
                        'learning_unit_id' => $unit->id,
                        'phase' => $phase,
                        'order' => $activityOrder + 1,
                    ],
                    [
                        'title' => Str::headline($phase),
                        'prompt' => $phase === 'forum_diskusi'
                            ? 'Diskusikan temuan dan pertanyaan utama dari kegiatan belajar ini.'
                            : 'Tuliskan hasil '.$phase.' berdasarkan materi dan lingkungan sekitar.',
                        'input_type' => $phase === 'forum_diskusi' ? 'discussion' : ($phase === 'ayo_mencoba' ? 'table' : 'essay'),
                        'is_required' => true,
                    ],
                );
            }

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

            Question::firstOrCreate(
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
        }

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
}
