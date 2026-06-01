<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Rubric;
use App\Services\Assessment\QuestionGroupService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RenewableEnergyAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'energi-terbarukan')->first();

        if (! $module) {
            return;
        }

        foreach ($module->learningUnits()->orderBy('order')->get() as $unit) {
            $assessment = Assessment::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'learning_unit_id' => $unit->id,
                    'title' => 'Asesmen Formatif '.$unit->title,
                ],
                [
                    'type' => 'formative',
                    'description' => 'Asesmen formatif berbasis OITLINE untuk '.$unit->title.'.',
                    'kktp' => 75,
                    'max_attempts' => 2,
                    'is_published' => true,
                    'order' => $unit->order,
                ],
            );

            $this->seedQuestions($assessment, $this->questionDataFor($unit->order));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function seedQuestions(Assessment $assessment, array $questions): void
    {
        $questionGroups = app(QuestionGroupService::class);

        foreach ($questions as $order => $data) {
            $question = Question::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'order' => $order + 1,
                ],
                [
                    'question_text' => $data['question_text'],
                    'question_type' => $data['question_type'],
                    'question_group' => $questionGroups->groupForType($data['question_type']),
                    'options' => $data['options'] ?? null,
                    'correct_answer' => $data['correct_answer'] ?? null,
                    'reference_answer' => $data['reference_answer'] ?? null,
                    'weight' => $data['weight'] ?? 10,
                ],
            );

            foreach ($data['keywords'] ?? [] as $keyword) {
                QuestionKeyword::updateOrCreate(
                    ['question_id' => $question->id, 'keyword' => $keyword],
                    ['normalized_keyword' => Str::lower($keyword), 'weight' => 1],
                );
            }

            if ($question->question_type === 'essay') {
                Rubric::updateOrCreate(
                    ['question_id' => $question->id, 'criterion' => 'Ketepatan konsep'],
                    [
                        'level' => 'baik',
                        'description' => 'Jawaban menjelaskan konsep, contoh, dan alasan sesuai konteks OITLINE.',
                        'score' => 85,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questionDataFor(int $order): array
    {
        $context = match ($order) {
            1 => ['energi', 'perubahan energi', 'sumber energi', 'Matahari', 'Lampu mengubah energi listrik menjadi cahaya.'],
            2 => ['energi fosil', 'emisi', 'polusi', 'Batu bara', 'Pembakaran fosil menghasilkan gas rumah kaca.'],
            3 => ['energi terbarukan', 'surya', 'biomassa', 'Air', 'Sumber terbarukan dapat diperbarui proses alam.'],
            4 => ['STEM', 'panel surya', 'efisiensi', 'Panel surya', 'STEM menghubungkan sains, teknologi, rekayasa, dan matematika.'],
            5 => ['proyek energi', 'data pengamatan', 'keselamatan kerja', 'Audit energi kelas', 'Proyek dimulai dari masalah nyata dan data.'],
            default => ['energi', 'data', 'solusi', 'Matahari', 'Energi perlu digunakan secara bijak.'],
        };

        return [
            [
                'question_text' => 'Konsep utama yang paling sesuai dengan topik ini adalah ...',
                'question_type' => 'multiple_choice',
                'options' => ['A' => $context[0], 'B' => 'jawaban acak', 'C' => 'tanpa data', 'D' => 'bukan energi'],
                'correct_answer' => ['A'],
            ],
            [
                'question_text' => 'Contoh yang paling dekat dengan pembahasan kegiatan belajar ini adalah ...',
                'question_type' => 'multiple_choice',
                'options' => ['A' => 'Mengabaikan pengamatan', 'B' => $context[3], 'C' => 'Menghapus data', 'D' => 'Tidak menyimpulkan'],
                'correct_answer' => ['B'],
            ],
            [
                'question_text' => 'Pilih dua istilah yang relevan dengan kegiatan belajar ini.',
                'question_type' => 'complex_multiple_choice',
                'options' => ['A' => $context[0], 'B' => $context[1], 'C' => 'plutonium fiksi', 'D' => 'data dihapus'],
                'correct_answer' => ['A', 'B'],
            ],
            [
                'question_text' => 'Pilih dua hal yang perlu diperhatikan dalam analisis energi.',
                'question_type' => 'complex_multiple_choice',
                'options' => ['A' => $context[2], 'B' => 'bukti pengamatan', 'C' => 'menebak tanpa data', 'D' => 'mengabaikan keselamatan'],
                'correct_answer' => ['A', 'B'],
            ],
            [
                'question_text' => $context[4],
                'question_type' => 'true_false',
                'options' => ['Benar' => true, 'Salah' => false],
                'correct_answer' => [true],
            ],
            [
                'question_text' => 'Analisis energi tidak perlu menggunakan data pengamatan.',
                'question_type' => 'true_false',
                'options' => ['Benar' => true, 'Salah' => false],
                'correct_answer' => [false],
            ],
            [
                'question_text' => 'Tuliskan satu kata kunci penting dari kegiatan belajar ini.',
                'question_type' => 'short_answer',
                'correct_answer' => [$context[0], $context[1], $context[2]],
                'keywords' => [$context[0], $context[1], $context[2]],
            ],
            [
                'question_text' => 'Jelaskan hubungan topik ini dengan kehidupan sehari-hari.',
                'question_type' => 'essay',
                'reference_answer' => 'Topik ini berhubungan dengan kehidupan sehari-hari karena penggunaan energi, data pengamatan, dan pilihan solusi memengaruhi lingkungan, biaya, kesehatan, serta kebiasaan di sekolah atau rumah.',
                'keywords' => [$context[0], $context[1], $context[2]],
                'weight' => 20,
            ],
            [
                'question_text' => 'Jodohkan konsep dengan keterangan yang tepat.',
                'question_type' => 'matching',
                'options' => ['left' => ['Konsep', 'Data'], 'right' => ['Ide utama', 'Bukti pengamatan']],
                'correct_answer' => ['Konsep' => 'Ide utama', 'Data' => 'Bukti pengamatan'],
            ],
            [
                'question_text' => 'Jodohkan tindakan dengan tujuannya.',
                'question_type' => 'matching',
                'options' => ['left' => ['Mengamati', 'Menyimpulkan'], 'right' => ['Mengumpulkan bukti', 'Merumuskan hasil']],
                'correct_answer' => ['Mengamati' => 'Mengumpulkan bukti', 'Menyimpulkan' => 'Merumuskan hasil'],
            ],
        ];
    }
}
