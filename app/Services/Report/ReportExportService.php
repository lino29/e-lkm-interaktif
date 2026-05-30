<?php

namespace App\Services\Report;

use App\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportService
{
    public function exportToExcel(int $moduleId)
    {
        $exportData = app(ReportExportDataService::class)->getModuleExportData($moduleId);
        $moduleSummary = $exportData['module_summary'];
        $students = $exportData['students'];

        return Excel::download(
            new ReportExport($moduleSummary, $students),
            'Laporan_E-LKM_'.Str::slug($moduleSummary['module_title']).'.xlsx'
        );
    }

    public function exportToPdf(int $moduleId)
    {
        $exportData = app(ReportExportDataService::class)->getModuleExportData($moduleId);
        $moduleSummary = $exportData['module_summary'];
        $students = $exportData['students'];

        $pdf = Pdf::loadView('exports.report-pdf', [
            'module' => $moduleSummary,
            'data' => $students,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_E-LKM_'.Str::slug($moduleSummary['module_title']).'.pdf');
    }
}
