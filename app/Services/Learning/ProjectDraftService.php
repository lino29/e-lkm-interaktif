<?php

namespace App\Services\Learning;

use App\Models\ActivityAnswer;
use App\Models\Project;

class ProjectDraftService
{
    public function syncFromActivityAnswer(ActivityAnswer $answer): void
    {
        if ($answer->activity->input_type !== 'project_form') {
            return;
        }

        $json = $answer->answer_json ?? [];
        // Typically, fields are stored in the first array item for forms mapped to table components
        $data = $json[0] ?? $json;

        $project = Project::where('learning_unit_id', $answer->activity->learning_unit_id)
            ->where('student_id', $answer->user_id)
            ->first();

        $projectData = [
            'module_id' => $answer->activity->learningUnit->module_id,
            'learning_unit_id' => $answer->activity->learning_unit_id,
            'student_id' => $answer->user_id,
            'project_title' => $data['project_type'] ?? 'Proyek KB5',
            'project_type' => $data['project_type'] ?? 'Lainnya',
            'problem' => $data['problem'] ?? null,
            'objective' => $data['objective'] ?? null,
            'tools_materials' => $data['tools_materials'] ?? null,
            'procedure' => $data['procedure'] ?? null,
            'expected_result' => $data['expected_result'] ?? null,
            'data_to_collect' => $data['data_to_collect'] ?? null, // optional if column exists
        ];

        // Ensure status reflects the answer's status. If activity answer is submitted, project is submitted.
        $status = $answer->status === 'submitted' ? 'submitted' : 'draft';

        if ($project) {
            // Only update if not already approved or reviewed
            if (! in_array($project->status, ['approved', 'reviewed'])) {
                $project->update(array_merge($projectData, ['status' => $status]));
            }
        } else {
            Project::create(array_merge($projectData, ['status' => $status]));
        }
    }
}
