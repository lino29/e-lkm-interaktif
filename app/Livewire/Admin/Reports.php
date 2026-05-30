<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentAttempt;
use App\Services\Report\ReportSummaryService;
use Livewire\Component;

class Reports extends Component
{
    public function render()
    {
        return view('livewire.admin.reports', [
            'stats' => app(ReportSummaryService::class)->systemSummary(),
            'recentActivities' => AssessmentAttempt::with('student', 'assessment.module')
                ->latest('updated_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
