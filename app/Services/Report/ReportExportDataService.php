<?php

namespace App\Services\Report;

use App\Models\Assessment;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportExportDataService
{
    /**
     * Returns structured export-ready data for a given module.
     * This service is package-agnostic: it only returns arrays/collections.
     * PDF/Excel packages can consume the output at a later stage.
     *
     * @return array{module_summary: array<string, mixed>, students: Collection}
     */
    public function getModuleExportData(int $moduleId): array
    {
        $module = Module::with('learningUnits')->findOrFail($moduleId);

        $students = User::role('murid')
            ->whereHas('progressRecords', fn ($q) => $q->where('module_id', $moduleId))
            ->with([
                'progressRecords' => fn ($q) => $q->where('module_id', $moduleId),
                'assessmentAttempts' => fn ($q) => $q->whereHas('assessment', fn ($q2) => $q2->where('module_id', $moduleId)),
                'assessmentAttempts.assessment',
                'projects' => fn ($q) => $q->where('module_id', $moduleId),
                'projects.rubricScores',
                'discussions' => fn ($q) => $q->whereHas('learningUnit', fn ($q2) => $q2->where('module_id', $moduleId)),
            ])
            ->get();

        $studentRows = $students->map(function ($student) use ($module) {
            $moduleProgress = $student->progressRecords
                ->whereNull('learning_unit_id')
                ->first();

            $formativeScores = [];
            foreach ($module->learningUnits as $unit) {
                $attempt = $student->assessmentAttempts
                    ->where('assessment.learning_unit_id', $unit->id)
                    ->where('assessment.type', 'formative')
                    ->sortByDesc('created_at')
                    ->first();
                $formativeScores[$unit->id] = [
                    'unit_title' => $unit->title,
                    'score' => $attempt?->total_score ?? 0,
                    'max_score' => $attempt?->max_score ?? 0,
                    'status' => $attempt?->status ?? 'belum_dikerjakan',
                ];
            }

            $finalAttempt = $student->assessmentAttempts
                ->where('assessment.type', 'final')
                ->sortByDesc('created_at')
                ->first();

            $project = $student->projects->first();

            $forumScores = $student->discussions
                ->whereNotNull('participation_score')
                ->pluck('participation_score');

            return [
                'name' => $student->name,
                'email' => $student->email,
                'module_status' => $moduleProgress?->status ?? 'belum_mulai',
                'formative_scores' => $formativeScores,
                'final_assessment' => [
                    'score' => $finalAttempt?->total_score ?? 0,
                    'max_score' => $finalAttempt?->max_score ?? 0,
                    'status' => $finalAttempt?->status ?? 'belum_dikerjakan',
                ],
                'project' => [
                    'title' => $project?->project_title,
                    'status' => $project?->status ?? 'belum_submit',
                    'score' => $project?->score ?? 0,
                    'rubric_scores' => $project
                        ? $project->rubricScores->map(fn ($rs) => [
                            'criterion' => $rs->criterion,
                            'score' => $rs->score,
                            'max_score' => $rs->max_score,
                        ])->all()
                        : [],
                ],
                'forum' => [
                    'total_discussions' => $student->discussions->count(),
                    'average_participation_score' => $forumScores->count() > 0
                        ? round((float) $forumScores->avg(), 2)
                        : null,
                ],
            ];
        });

        $moduleSummary = [
            'module_title' => $module->title,
            'total_students' => $students->count(),
            'total_learning_units' => $module->learningUnits->count(),
            'total_assessments' => Assessment::where('module_id', $moduleId)->count(),
            'total_projects' => Project::where('module_id', $moduleId)->count(),
            'reviewed_projects' => Project::where('module_id', $moduleId)->where('status', 'reviewed')->count(),
            'average_project_score' => round(
                (float) Project::where('module_id', $moduleId)
                    ->where('status', 'reviewed')
                    ->whereNotNull('score')
                    ->avg('score'),
                2
            ),
            'total_discussions' => Discussion::whereHas('learningUnit', fn ($q) => $q->where('module_id', $moduleId))->count(),
            'total_progress_records' => Progress::where('module_id', $moduleId)->count(),
        ];

        return [
            'module_summary' => $moduleSummary,
            'students' => $studentRows,
        ];
    }
}
