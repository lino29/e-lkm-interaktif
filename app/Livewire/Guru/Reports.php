<?php

namespace App\Livewire\Guru;

use App\Models\AssessmentAttempt;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Services\Report\ReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Reports extends Component
{
    private const REPORT_SECTIONS = ['summary', 'assessments', 'progress', 'projects', 'discussions'];

    #[Url(as: 'bagian')]
    public string $activeSection = 'summary';

    public ?int $module_id = null;

    public string $attempt_status = '';

    public string $project_status = '';

    public string $search = '';

    public bool $showAttemptDetailModal = false;

    #[Locked]
    public ?int $selectedAttemptId = null;

    public function mount(): void
    {
        if (! in_array($this->activeSection, self::REPORT_SECTIONS, true)) {
            $this->activeSection = 'summary';
        }
    }

    public function showSection(string $section): void
    {
        if (! in_array($section, self::REPORT_SECTIONS, true)) {
            return;
        }

        if ($this->activeSection !== $section) {
            $this->reset('attempt_status', 'project_status', 'search');
        }

        $this->activeSection = $section;
        $this->closeAttemptDetail();
    }

    public function showAttemptDetail(int $attemptId): void
    {
        $attempt = $this->teacherAttemptQuery()
            ->with('assessment.module')
            ->whereKey($attemptId)
            ->first();

        if ($attempt === null) {
            $this->closeAttemptDetail();

            return;
        }

        Gate::authorize('view', $attempt->assessment);

        $this->selectedAttemptId = $attempt->id;
        $this->showAttemptDetailModal = true;
    }

    public function closeAttemptDetail(): void
    {
        $this->showAttemptDetailModal = false;
        $this->selectedAttemptId = null;
    }

    public function resetFilters(): void
    {
        $this->reset('module_id', 'attempt_status', 'project_status', 'search');
        $this->resetErrorBag();
    }

    public function exportExcel(ReportExportService $exportService)
    {
        $moduleId = $this->selectedModuleId();

        if ($moduleId === null) {
            $this->addError('module_id', 'Pilih modul yang Anda kelola terlebih dahulu.');

            return;
        }

        return $exportService->exportToExcel($moduleId);
    }

    public function exportPdf(ReportExportService $exportService)
    {
        $moduleId = $this->selectedModuleId();

        if ($moduleId === null) {
            $this->addError('module_id', 'Pilih modul yang Anda kelola terlebih dahulu.');

            return;
        }

        return $exportService->exportToPdf($moduleId);
    }

    public function render()
    {
        $teacherModuleIds = Module::where('created_by', auth()->id())->pluck('id');
        $moduleIds = $this->module_id && $teacherModuleIds->contains($this->module_id)
            ? collect([$this->module_id])
            : $teacherModuleIds;
        $search = trim($this->search);

        $attemptsQuery = AssessmentAttempt::with('student', 'assessment.module')
            ->whereHas('assessment', fn ($query) => $query->whereIn('module_id', $moduleIds));

        $tuntasCount = (clone $attemptsQuery)->where('status', 'tuntas')->count();
        $remedialCount = (clone $attemptsQuery)->where('status', 'remedial')->count();
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
            ->when($search !== '', fn ($query) => $query->where(function ($attemptQuery) use ($search) {
                $attemptQuery
                    ->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('assessment', fn ($assessmentQuery) => $assessmentQuery->where('title', 'like', '%'.$search.'%'));
            }));

        $filteredProjectsQuery = Project::with('user', 'module', 'rubricScores')
            ->whereIn('module_id', $moduleIds)
            ->when($this->project_status !== '', fn ($query) => $query->where('status', $this->project_status))
            ->when($search !== '', fn ($query) => $query->where(function ($projectQuery) use ($search) {
                $projectQuery
                    ->where('project_title', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
            }));

        $filteredDiscussionsQuery = Discussion::with('user', 'learningUnit.module', 'replies.user.roles')
            ->whereNull('parent_id')
            ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
            ->when($search !== '', fn ($query) => $query->where(function ($discussionQuery) use ($search) {
                $discussionQuery
                    ->where('body', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
            }));

        $filteredProgressQuery = Progress::with('user', 'module', 'learningUnit')
            ->whereIn('module_id', $moduleIds)
            ->when($search !== '', fn ($query) => $query->where(function ($progressQuery) use ($search) {
                $progressQuery
                    ->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('module', fn ($moduleQuery) => $moduleQuery->where('title', 'like', '%'.$search.'%'))
                    ->orWhereHas('learningUnit', fn ($unitQuery) => $unitQuery->where('title', 'like', '%'.$search.'%'));
            }));

        return view('livewire.guru.reports', [
            'modules' => Module::whereIn('id', $teacherModuleIds)->orderBy('title')->get(),
            'tuntasCount' => $tuntasCount,
            'remedialCount' => $remedialCount,
            'reviewedProjectCount' => $reviewedProjectCount,
            'reviewedProjectAverageScore' => $reviewedProjectAverageScore === null ? null : round((float) $reviewedProjectAverageScore, 2),
            'discussionCount' => Discussion::whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))->count(),
            'discussionThreadCount' => $discussionThreadCount,
            'respondedDiscussionCount' => $respondedDiscussionCount,
            'unrespondedDiscussionCount' => max(0, $discussionThreadCount - $respondedDiscussionCount),
            'averageParticipationScore' => $averageParticipationScore === null ? null : round((float) $averageParticipationScore, 2),
            'assessmentAverageScore' => $assessmentAverageScore === null ? null : round((float) $assessmentAverageScore, 2),
            'activeFilterCount' => collect([$this->module_id, $this->attempt_status, $this->project_status, $search])
                ->filter(fn ($value): bool => $value !== null && $value !== '')
                ->count(),
            'attempts' => $this->activeSection === 'assessments'
                ? $filteredAttemptsQuery->latest()->limit(20)->get()
                : collect(),
            'progressRecords' => $this->activeSection === 'progress'
                ? $filteredProgressQuery->latest()->limit(20)->get()
                : collect(),
            'projects' => $this->activeSection === 'projects'
                ? (clone $filteredProjectsQuery)->latest()->limit(20)->get()
                : collect(),
            'remedialAttempts' => $this->activeSection === 'progress'
                ? (clone $attemptsQuery)
                    ->where('status', 'remedial')
                    ->when($search !== '', fn ($query) => $query->where(function ($attemptQuery) use ($search) {
                        $attemptQuery
                            ->whereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', '%'.$search.'%'))
                            ->orWhereHas('assessment', fn ($assessmentQuery) => $assessmentQuery->where('title', 'like', '%'.$search.'%'));
                    }))
                    ->latest()
                    ->limit(20)
                    ->get()
                : collect(),
            'discussions' => $this->activeSection === 'discussions'
                ? $filteredDiscussionsQuery->latest()->limit(10)->get()
                : collect(),
            'discussionParticipation' => $this->activeSection === 'discussions'
                ? Discussion::query()
                    ->with('user')
                    ->select('user_id')
                    ->selectRaw('count(*) as total_discussions')
                    ->selectRaw('avg(participation_score) as average_participation_score')
                    ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
                    ->whereHas('user', fn ($query) => $query->role('murid'))
                    ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%')))
                    ->groupBy('user_id')
                    ->orderByDesc('total_discussions')
                    ->limit(10)
                    ->get()
                : collect(),
            'projectStatusSummary' => $this->activeSection === 'summary'
                ? Project::query()
                    ->select('status')
                    ->selectRaw('count(*) as total')
                    ->whereIn('module_id', $moduleIds)
                    ->groupBy('status')
                    ->orderBy('status')
                    ->get()
                : collect(),
            'selectedAttempt' => $this->showAttemptDetailModal
                ? $this->teacherAttemptQuery()
                    ->with('student', 'assessment.module', 'studentAnswers.question')
                    ->whereKey($this->selectedAttemptId)
                    ->first()
                : null,
        ]);
    }

    private function teacherAttemptQuery(): Builder
    {
        return AssessmentAttempt::query()
            ->whereHas('assessment.module', fn ($query) => $query->where('created_by', auth()->id()));
    }

    private function selectedModuleId(): ?int
    {
        if ($this->module_id === null) {
            return null;
        }

        return Module::query()
            ->whereKey($this->module_id)
            ->where('created_by', auth()->id())
            ->value('id');
    }
}
