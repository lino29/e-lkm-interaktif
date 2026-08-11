<?php

namespace App\Console\Commands;

use App\Services\Assessment\ImportedQuestionRepairService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('questions:repair-imported-options {--apply : Simpan perubahan ke database} {--module= : Batasi ke ID modul tertentu}')]
#[Description('Periksa dan perbaiki struktur soal lama hasil import Excel tanpa menghapus soal atau jawaban murid')]
class RepairImportedQuestionOptions extends Command
{
    public function handle(ImportedQuestionRepairService $repairService): int
    {
        $moduleId = $this->option('module');
        if ($moduleId !== null && filter_var($moduleId, FILTER_VALIDATE_INT) === false) {
            $this->error('Opsi --module harus berupa ID numerik.');

            return self::INVALID;
        }

        $apply = (bool) $this->option('apply');
        $result = $repairService->repair($apply, $moduleId !== null ? (int) $moduleId : null);

        $this->table(['Mode', 'Diperiksa', 'Perlu/diperbaiki', 'Sudah benar', 'Duplikat', 'Error'], [[
            $apply ? 'APPLY' : 'DRY RUN',
            $result['scanned'],
            $result['repaired'],
            $result['unchanged'],
            $result['duplicates'],
            count($result['errors']),
        ]]);

        foreach ($result['errors'] as $error) {
            $this->warn($error);
        }

        if (! $apply && $result['repaired'] > 0) {
            $this->info('Tidak ada data yang diubah. Jalankan kembali dengan --apply setelah hasil dry-run diperiksa.');
        }

        if ($result['duplicates'] > 0) {
            $this->warn('Duplikat tidak dihapus otomatis agar jawaban murid tetap aman.');
        }

        return $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
