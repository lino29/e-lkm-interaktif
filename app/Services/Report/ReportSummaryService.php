<?php

namespace App\Services\Report;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\ClassRoom;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Models\User;

class ReportSummaryService
{
    /**
     * @return array<string, mixed>
     */
    public function systemSummary(): array
    {
        return [
            'users_total' => User::count(),
            'users_by_role' => [
                'admin' => User::role('admin')->count(),
                'guru' => User::role('guru')->count(),
                'murid' => User::role('murid')->count(),
            ],
            'classes' => ClassRoom::count(),
            'modules' => Module::count(),
            'assessments' => Assessment::count(),
            'activities' => Activity::count(),
            'progress_records' => Progress::count(),
            'assessment_attempts' => AssessmentAttempt::count(),
            'remedial_attempts' => AssessmentAttempt::where('status', 'remedial')->count(),
            'activity_answers' => ActivityAnswer::count(),
            'discussions' => Discussion::count(),
            'projects' => Project::count(),
        ];
    }
}
