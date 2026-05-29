<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Material;
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

        $titles = [
            'Konsep Energi dan Sumber Energi',
            'Masalah Energi Fosil',
            'Pengertian dan Jenis Energi Terbarukan',
            'Teknologi Energi Terbarukan Berbasis STEM',
            'Merancang Aksi Sederhana Energi Terbarukan',
        ];

        foreach ($titles as $index => $title) {
            $unit = LearningUnit::firstOrCreate(
                [
                    'module_id' => $module->id,
                    'slug' => Str::slug($title),
                ],
                [
                    'title' => $title,
                    'description' => 'Kegiatan belajar '.$index + 1,
                    'objectives' => 'Murid mengamati, bertanya, mencoba, menalar, dan menyimpulkan topik '.$title.'.',
                    'order' => $index + 1,
                ],
            );

            Material::firstOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'title' => 'Materi '.$title,
                ],
                [
                    'content' => 'Bacalah konsep utama '.$title.' lalu hubungkan dengan contoh energi di lingkungan sekolah.',
                    'material_type' => 'text',
                    'order' => 1,
                ],
            );

            foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan'] as $order => $phase) {
                Activity::firstOrCreate(
                    [
                        'learning_unit_id' => $unit->id,
                        'phase' => $phase,
                        'order' => $order + 1,
                    ],
                    [
                        'title' => Str::headline($phase),
                        'prompt' => 'Tuliskan hasil '.$phase.' berdasarkan materi dan lingkungan sekitar.',
                        'input_type' => $phase === 'ayo_mencoba' ? 'table' : 'essay',
                        'is_required' => true,
                    ],
                );
            }
        }

        $assessment = Assessment::firstOrCreate(
            [
                'module_id' => $module->id,
                'title' => 'Asesmen Formatif Energi',
            ],
            [
                'learning_unit_id' => $module->learningUnits()->first()?->id,
                'type' => 'formative',
                'description' => 'Asesmen awal untuk menguji pemahaman konsep energi terbarukan.',
                'kktp' => 75,
                'max_attempts' => 2,
                'is_published' => true,
                'order' => 1,
            ],
        );

        $question = Question::firstOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 1,
            ],
            [
                'question_text' => 'Sumber energi berikut yang termasuk energi terbarukan adalah ...',
                'question_type' => 'multiple_choice',
                'options' => ['A' => 'Batu bara', 'B' => 'Matahari', 'C' => 'Minyak bumi', 'D' => 'Gas alam'],
                'correct_answer' => ['B'],
                'weight' => 10,
            ],
        );

        $essay = Question::firstOrCreate(
            [
                'assessment_id' => $assessment->id,
                'order' => 2,
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

        $question->touch();
    }
}
