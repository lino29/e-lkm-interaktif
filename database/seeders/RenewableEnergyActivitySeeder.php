<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Module;
use App\Services\Learning\ActivityTemplateService;
use Illuminate\Database\Seeder;

class RenewableEnergyActivitySeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'energi-terbarukan')->first();

        if (! $module) {
            return;
        }

        $learningUnits = $module->learningUnits()->orderBy('order')->get();
        $templateService = app(ActivityTemplateService::class);

        foreach ($learningUnits as $unit) {
            $order = $unit->order;

            foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan', 'forum_diskusi'] as $activityOrder => $phase) {
                $template = $templateService->templateFor($phase, $order);

                Activity::updateOrCreate(
                    [
                        'learning_unit_id' => $unit->id,
                        'phase' => $phase,
                    ],
                    [
                        'title' => $template['title'],
                        'prompt' => $template['prompt'],
                        'input_type' => $template['input_type'],
                        'order' => $activityOrder + 1,
                        'is_required' => true,
                        'answer_schema' => $template['answer_schema'],
                        'display_config' => $template['display_config'],
                        'validation_rules' => $template['validation_rules'],
                        'requires_teacher_review' => $template['requires_teacher_review'],
                    ]
                );
            }
        }
    }
}
