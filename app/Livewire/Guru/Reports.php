<?php

namespace App\Livewire\Guru;

use App\Models\AssessmentAttempt;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Services\Report\ReportExportService;
use Livewire\Component;

class Reports extends Component
{
    public ?int $module_id = null;

    public string $attempt_status = '';

    public string $project_status = '';

    public string $search = '';

    public function exportExcel(ReportExportService $exportService)
    {
        if (! $this->module_id) {
            $this->addError('module_id', 'Pilih modul terlebih dahulu.');

            return;
        }

        return $exportService->exportToExcel($this->module_id);
    }

    public function exportPdf(ReportExportService $exportService)
    {
        if (! $this->module_id) {
            $this->addError('module_id', 'Pilih modul terlebih dahulu.');

            return;
        }

        return $exportService->exportToPdf($this->module_id);
    }

    public function render()
    {
        $teacherModuleIds = Module::where('created_by', auth()->id())->pluck('id');
        $moduleIds = $this->module_id && $teacherModuleIds->contains($this->module_id)
            ? collect([$this->module_id])
            : $teacherModuleIds;

        $attemptsQuery = AssessmentAttempt::with('student', 'assessment.module')
            ->whereHas('assessment', fn ($query) => $query->whereIn('module_id', $moduleIds));

        $tuntasCount = (clone $attemptsQuery)->where('status', 'tuntas')->count();
        $remedialCount = (clone $attemptsQuery)->where('status', 'remedial')->count();
        $submittedProjectCount = Project::whereIn('module_id', $moduleIds)->where('status', 'submitted')->count();
        $reviewedProjectCount = Project::whereIn('module_id', $moduleIds)->where('status', 'reviewed')->count();
        $reviewedProjectAverageScore = Project::whereIn('module_id', $moduleIds)
            ->where('status', 'reviewed')
            ->whereNotNull('score')
            ->avg('score');
        $discussionThreadsQuery = Discussion::query()
            ->whereNull('parent_id')
            ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds));
        $respondedDiscussionCount = (clone $discussionThreadsQuery)
            ->whereHas('replies', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->role('guru')))
            ->count();
        $discussionThreadCount = (clone $discussionThreadsQuery)->count();
        $averageParticipationScore = (clone $discussionThreadsQuery)
            ->whereNotNull('participation_score')
            ->avg('participation_score');
        $assessmentAverageScore = (clone $attemptsQuery)
            ->whereNotNull('submitted_at')
            ->avg('total_score');

        $filteredAttemptsQuery = (clone $attemptsQuery)
            ->when($this->attempt_status !== '', fn ($query) => $query->where('status', $this->attempt_status))
            ->when($this->search !== '', fn ($query) => $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', '%'.$this->search.'%')));

        $filteredProjectsQuery = Project::with('user', 'module', 'rubricScores')
            ->whereIn('module_id', $moduleIds)
            ->when($this->project_status !== '', fn ($query) => $query->where('status', $this->project_status))
            ->when($this->search !== '', fn ($query) => $query->where(function ($projectQuery) {
                $projectQuery
                    ->where('project_title', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$this->search.'%'));
            }));

        $filteredDiscussionsQuery = Discussion::with('user', 'learningUnit.module', 'replies.user')
            ->whereNull('parent_id')
            ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
            ->when($this->search !== '', fn ($query) => $query->where(function ($discussionQuery) {
                $discussionQuery
                    ->where('body', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$this->search.'%'));
            }));

        return view('livewire.guru.reports', [
            'modules' => Module::whereIn('id', $teacherModuleIds)->orderBy('title')->get(),
            'tuntasCount' => $tuntasCount,
            'remedialCount' => $remedialCount,
            'submittedProjectCount' => $submittedProjectCount,
            'reviewedProjectCount' => $reviewedProjectCount,
            'reviewedProjectAverageScore' => $reviewedProjectAverageScore === null ? null : round((float) $reviewedProjectAverageScore, 2),
            'discussionCount' => Discussion::whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))->count(),
            'discussionThreadCount' => $discussionThreadCount,
            'respondedDiscussionCount' => $respondedDiscussionCount,
            'unrespondedDiscussionCount' => max(0, $discussionThreadCount - $respondedDiscussionCount),
            'averageParticipationScore' => $averageParticipationScore === null ? null : round((float) $averageParticipationScore, 2),
            'assessmentAverageScore' => $assessmentAverageScore === null ? null : round((float) $assessmentAverageScore, 2),
            'attempts' => $filteredAttemptsQuery
                ->latest()
                ->limit(20)
                ->get(),
            'progressRecords' => Progress::with('user', 'module', 'learningUnit')
                ->whereIn('module_id', $moduleIds)
                ->latest()
                ->limit(20)
                ->get(),
            'projects' => (clone $filteredProjectsQuery)->latest()->limit(20)->get(),
            'remedialAttempts' => (clone $attemptsQuery)
                ->where('status', 'remedial')
                ->latest()
                ->limit(20)
                ->get(),
            'discussions' => $filteredDiscussionsQuery->latest()->limit(10)->get(),
            'discussionParticipation' => Discussion::query()
                ->with('user')
                ->select('user_id')
                ->selectRaw('count(*) as total_discussions')
                ->selectRaw('avg(participation_score) as average_participation_score')
                ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->whereHas('user', fn ($query) => $query->role('murid'))
                ->groupBy('user_id')
                ->orderByDesc('total_discussions')
                ->limit(10)
                ->get(),
            'projectStatusSummary' => Project::query()
                ->select('status')
                ->selectRaw('count(*) as total')
                ->whereIn('module_id', $moduleIds)
                ->groupBy('status')
                ->orderBy('status')
                ->get(),
        ]);
    }
}
