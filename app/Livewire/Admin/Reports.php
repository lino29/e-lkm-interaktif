<?php

namespace App\Livewire\Admin;

use App\Models\AssessmentAttempt;
use App\Models\Module;
use App\Services\Report\ReportExportService;
use App\Services\Report\ReportSummaryService;
use Livewire\Component;

class Reports extends Component
{
    public ?int $module_id = null;

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
        return view('livewire.admin.reports', [
            'stats' => app(ReportSummaryService::class)->systemSummary(),
            'recentActivities' => AssessmentAttempt::with('student', 'assessment.module')
                ->latest('updated_at')
                ->limit(10)
                ->get(),
            'modules' => Module::all(),
        ]);
    }
}
