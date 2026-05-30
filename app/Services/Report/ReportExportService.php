<?php

namespace App\Services\Report;

use App\Exports\ReportExport;
use App\Models\Module;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportService
{
    /**
     * Data format target:
     * - Class/Module overview
     * - Student list with progress (tuntas/remedial)
     * - Formative assessment scores per KB
     * - Final assessment score
     * - Project score (total + per criteria)
     * - Forum participation score
     */
    public function getExportData(int $moduleId): Collection
    {
        $module = Module::with('learningUnits')->findOrFail($moduleId);

        $students = User::role('murid')
            ->whereHas('progressRecords', fn ($q) => $q->where('module_id', $moduleId))
            ->with([
                'progressRecords' => fn ($q) => $q->where('module_id', $moduleId)->whereNull('learning_unit_id'),
                'assessmentAttempts' => fn ($q) => $q->whereHas('assessment', fn ($q2) => $q2->where('module_id', $moduleId)),
                'assessmentAttempts.assessment',
                'projects' => fn ($q) => $q->where('module_id', $moduleId),
                'projects.rubricScores',
                'discussions' => fn ($q) => $q->whereHas('learningUnit', fn ($q2) => $q2->where('module_id', $moduleId)),
            ])
            ->get();

        return $students->map(function ($student) use ($module) {
            $progress = $student->progressRecords->first();

            $formativeScores = [];
            foreach ($module->learningUnits as $unit) {
                $attempt = $student->assessmentAttempts
                    ->where('assessment.learning_unit_id', $unit->id)
                    ->where('assessment.type', 'formative')
                    ->sortByDesc('created_at')
                    ->first();
                $formativeScores[$unit->id] = $attempt ? $attempt->score : 0;
            }

            $finalAttempt = $student->assessmentAttempts
                ->where('assessment.type', 'final')
                ->sortByDesc('created_at')
                ->first();

            $project = $student->projects->first();

            $forumScores = $student->discussions->whereNotNull('participation_score')->pluck('participation_score');
            $forumAvg = $forumScores->count() > 0 ? $forumScores->avg() : 0;

            return [
                'name' => $student->name,
                'email' => $student->email,
                'status' => $progress ? $progress->status : 'Belum Mulai',
                'formative_scores' => $formativeScores,
                'final_score' => $finalAttempt ? $finalAttempt->score : 0,
                'project_score' => $project ? $project->score : 0,
                'project_rubric' => $project ? $project->rubricScores->pluck('score', 'criterion_key')->all() : [],
                'forum_score' => round((float) $forumAvg, 2),
            ];
        });
    }

    public function exportToExcel(int $moduleId)
    {
        $module = Module::with('learningUnits')->findOrFail($moduleId);
        $data = $this->getExportData($moduleId);

        return Excel::download(
            new ReportExport($module, $data),
            'Laporan_E-LKM_'.Str::slug($module->title).'.xlsx'
        );
    }

    public function exportToPdf(int $moduleId)
    {
        $module = Module::with('learningUnits')->findOrFail($moduleId);
        $data = $this->getExportData($moduleId);

        $pdf = Pdf::loadView('exports.report-pdf', [
            'module' => $module,
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_E-LKM_'.Str::slug($module->title).'.pdf');
    }
}
