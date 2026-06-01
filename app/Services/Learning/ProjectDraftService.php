<?php

namespace App\Services\Learning;

use App\Models\ActivityAnswer;
use App\Models\Project;

class ProjectDraftService
{
    public function syncFromActivityAnswer(ActivityAnswer $answer): void
    {
        $answer->loadMissing('activity.learningUnit');

        if ($answer->activity->input_type !== 'project_form') {
            return;
        }

        $json = $answer->answer_json ?? [];
        $data = $json[0] ?? $json;
        $status = $answer->status === 'submitted' ? 'submitted' : 'draft';

        $project = Project::where('user_id', $answer->user_id)
            ->where('module_id', $answer->activity->learningUnit->module_id)
            ->where('learning_unit_id', $answer->activity->learning_unit_id)
            ->first();

        if ($project && in_array($project->status, ['approved', 'reviewed'])) {
            return;
        }

        Project::updateOrCreate(
            [
                'user_id' => $answer->user_id,
                'module_id' => $answer->activity->learningUnit->module_id,
                'learning_unit_id' => $answer->activity->learning_unit_id,
            ],
            [
                'project_title' => $data['project_title'] ?? $data['project_type'] ?? 'Rancangan Proyek Energi Terbarukan',
                'project_type' => $data['project_type'] ?? null,
                'problem' => $data['problem'] ?? null,
                'objective' => $data['objective'] ?? null,
                'tools_materials' => $data['tools_materials'] ?? null,
                'procedure' => $data['procedure'] ?? null,
                'collected_data' => $data['collected_data'] ?? null,
                'data_to_collect' => $data['data_to_collect'] ?? null,
                'expected_result' => $data['expected_result'] ?? null,
                'conclusion' => $data['conclusion'] ?? null,
                'status' => $status,
            ],
        );
    }
}
