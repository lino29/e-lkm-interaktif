<?php

namespace App\Services\Learning;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Progress;
use App\Models\User;

class ProgressService
{
    public function markStarted(User $student, Module $module, ?LearningUnit $learningUnit = null): Progress
    {
        $progress = Progress::firstOrNew([
            'user_id' => $student->id,
            'module_id' => $module->id,
            'learning_unit_id' => $learningUnit?->id,
            'assessment_id' => null,
        ]);

        if ($progress->status !== 'tuntas') {
            $progress->status = $progress->status ?: 'sedang_dikerjakan';
            $progress->started_at ??= now();
            $progress->save();
        }

        return $progress;
    }

    public function recordAssessment(User $student, Assessment $assessment, float $score, string $status): Progress
    {
        return Progress::updateOrCreate(
            [
                'user_id' => $student->id,
                'module_id' => $assessment->module_id,
                'learning_unit_id' => $assessment->learning_unit_id,
                'assessment_id' => $assessment->id,
            ],
            [
                'status' => $status,
                'score' => $score,
                'completed_at' => $status === 'tuntas' ? now() : null,
            ],
        );
    }

    public function isLearningUnitUnlocked(User $student, LearningUnit $learningUnit): bool
    {
        $previousLearningUnits = LearningUnit::where('module_id', $learningUnit->module_id)
            ->where('order', '<', $learningUnit->order)
            ->orderBy('order')
            ->get();

        if ($previousLearningUnits->isEmpty()) {
            return true;
        }

        return $previousLearningUnits->every(fn (LearningUnit $previousLearningUnit): bool => $this->isLearningUnitComplete($student, $previousLearningUnit));
    }

    public function isActivityUnlocked(User $student, Activity $activity): bool
    {
        if ($student->hasAnyRole(['admin', 'guru'])) {
            return true;
        }

        $activity->loadMissing('learningUnit.module');

        if (! $this->isLearningUnitUnlocked($student, $activity->learningUnit)) {
            return false;
        }

        $previousRequiredActivityIds = $activity->learningUnit
            ->activities()
            ->where('order', '<', $activity->order)
            ->where('is_required', true)
            ->pluck('id');

        if ($previousRequiredActivityIds->isEmpty()) {
            return true;
        }

        $submittedCount = ActivityAnswer::where('user_id', $student->id)
            ->whereIn('activity_id', $previousRequiredActivityIds)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->distinct('activity_id')
            ->count('activity_id');

        return $submittedCount === $previousRequiredActivityIds->count();
    }

    public function isAssessmentUnlocked(User $student, Assessment $assessment): bool
    {
        if ($student->hasAnyRole(['admin', 'guru'])) {
            return true;
        }

        if (! $assessment->learningUnit) {
            return true;
        }

        if (! $this->isLearningUnitUnlocked($student, $assessment->learningUnit)) {
            return false;
        }

        $requiredActivityIds = $assessment->learningUnit
            ->activities()
            ->where('is_required', true)
            ->pluck('id');

        if ($requiredActivityIds->isEmpty()) {
            return true;
        }

        $submittedCount = ActivityAnswer::where('user_id', $student->id)
            ->whereIn('activity_id', $requiredActivityIds)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->distinct('activity_id')
            ->count('activity_id');

        return $submittedCount === $requiredActivityIds->count();
    }

    public function isLearningUnitComplete(User $student, LearningUnit $learningUnit): bool
    {
        $requiredActivityIds = $learningUnit->activities()
            ->where('is_required', true)
            ->pluck('id');

        if ($requiredActivityIds->isNotEmpty()) {
            $submittedActivityCount = ActivityAnswer::where('user_id', $student->id)
                ->whereIn('activity_id', $requiredActivityIds)
                ->whereIn('status', ['submitted', 'reviewed'])
                ->distinct('activity_id')
                ->count('activity_id');

            if ($submittedActivityCount < $requiredActivityIds->count()) {
                return false;
            }
        }

        $publishedAssessments = $learningUnit->assessments()
            ->where('is_published', true)
            ->get();

        foreach ($publishedAssessments as $assessment) {
            $latestAttempt = AssessmentAttempt::where('assessment_id', $assessment->id)
                ->where('student_id', $student->id)
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->first();

            if ($latestAttempt?->status !== 'tuntas') {
                return false;
            }
        }

        return true;
    }

    public function refreshLearningUnitProgress(User $student, LearningUnit $learningUnit): Progress
    {
        $status = 'sedang_dikerjakan';

        if ($this->isLearningUnitComplete($student, $learningUnit)) {
            $status = 'tuntas';
        } elseif ($this->hasRemedialAssessment($student, $learningUnit)) {
            $status = 'remedial';
        }

        return Progress::updateOrCreate(
            [
                'user_id' => $student->id,
                'module_id' => $learningUnit->module_id,
                'learning_unit_id' => $learningUnit->id,
                'assessment_id' => null,
            ],
            [
                'status' => $status,
                'completed_at' => $status === 'tuntas' ? now() : null,
            ],
        );
    }

    public function moduleCompletionPercentage(User $student, Module $module): int
    {
        $learningUnits = $module->learningUnits()->get();

        if ($learningUnits->isEmpty()) {
            return 0;
        }

        $completedCount = $learningUnits
            ->filter(fn (LearningUnit $learningUnit): bool => $this->isLearningUnitComplete($student, $learningUnit))
            ->count();

        return (int) round(($completedCount / $learningUnits->count()) * 100);
    }

    private function hasRemedialAssessment(User $student, LearningUnit $learningUnit): bool
    {
        $assessmentIds = $learningUnit->assessments()
            ->where('is_published', true)
            ->pluck('id');

        if ($assessmentIds->isEmpty()) {
            return false;
        }

        return AssessmentAttempt::whereIn('assessment_id', $assessmentIds)
            ->where('student_id', $student->id)
            ->where('status', 'remedial')
            ->whereNotNull('submitted_at')
            ->exists();
    }
}
