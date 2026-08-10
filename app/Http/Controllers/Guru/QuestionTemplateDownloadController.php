<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionTemplateDownloadController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        $templatePath = base_path('docs/contoh_template_import.xlsx');

        abort_unless(file_exists($templatePath), 404, 'Template tidak ditemukan.');

        return response()->download($templatePath, 'template-import-soal.xlsx');
    }
}
