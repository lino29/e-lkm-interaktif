<?php

namespace App\Services\Report;

use App\Models\ActivityAnswer;
use App\Models\AssessmentAttempt;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;

class ReportSummaryService
{
    /**
     * @return array<string, int>
     */
    public function systemSummary(): array
    {
        return [
            'users' => User::count(),
            'modules' => Module::count(),
            'progress_records' => Progress::count(),
            'assessment_attempts' => AssessmentAttempt::count(),
            'activity_answers' => ActivityAnswer::count(),
            'projects' => Project::count(),
        ];
    }
}
