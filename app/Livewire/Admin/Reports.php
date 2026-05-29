<?php

namespace App\Livewire\Admin;

use App\Services\Report\ReportSummaryService;
use Livewire\Component;

class Reports extends Component
{
    public function render()
    {
        return view('livewire.admin.reports', [
            'stats' => app(ReportSummaryService::class)->systemSummary(),
        ]);
    }
}
