<?php

namespace App\Services\Admin;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserCsvImportService
{
    /**
     * @return array{created: int, errors: array<int, string>}
     */
    public function import(UploadedFile $file, string $role): array
    {
        if (! in_array($role, ['guru', 'murid'], true)) {
            return [
                'created' => 0,
                'errors' => ['Jenis import tidak valid. Pilih import guru atau murid.'],
            ];
        }

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [
                'created' => 0,
                'errors' => ['Gagal membaca file CSV.'],
            ];
        }

        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);

            return [
                'created' => 0,
                'errors' => ['File CSV kosong.'],
            ];
        }

        $header = array_map(fn (?string $column): string => strtolower(trim((string) $column)), $header);
        $requiredColumns = $this->requiredColumnsForRole($role);
        $missingColumns = array_values(array_diff($requiredColumns, $header));

        if ($missingColumns !== []) {
            fclose($handle);

            return [
                'created' => 0,
                'errors' => ['Header CSV tidak lengkap. Kolom wajib: '.implode(', ', $requiredColumns).'.'],
            ];
        }

        $created = 0;
        $errors = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($data)) {
                continue;
            }

            if (count($header) !== count($data)) {
                $errors[] = "Baris {$rowNumber}: jumlah kolom tidak sesuai header.";

                continue;
            }

            $row = array_map(fn (?string $value): string => trim((string) $value), array_combine($header, $data));
            $validator = Validator::make($row, $this->rulesForRole($role), [], [
                'name' => 'nama',
                'email' => 'email',
                'password' => 'password',
                'nisn' => 'NISN',
                'kelas' => 'kelas',
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $classRoom = $role === 'murid' ? $this->findClassRoom($row['kelas']) : null;
            if ($role === 'murid' && $classRoom === null) {
                $errors[] = "Baris {$rowNumber}: kelas {$row['kelas']} tidak ditemukan.";

                continue;
            }

            $user = User::create([
                'class_room_id' => $classRoom?->id,
                'nisn' => $role === 'murid' ? $row['nisn'] : null,
                'name' => $row['name'],
                'email' => $role === 'murid' ? $this->studentEmail($row['nisn']) : $row['email'],
                'password' => Hash::make($row['password']),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
            $created++;
        }

        fclose($handle);

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requiredColumnsForRole(string $role): array
    {
        return $role === 'murid'
            ? ['name', 'nisn', 'password', 'kelas']
            : ['name', 'email', 'password'];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rulesForRole(string $role): array
    {
        if ($role === 'murid') {
            return [
                'name' => ['required', 'string', 'max:255'],
                'nisn' => ['required', 'digits:10', Rule::unique('users', 'nisn')],
                'password' => ['required', 'string', 'min:8'],
                'kelas' => ['required', 'string'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    private function findClassRoom(string $classNameOrCode): ?ClassRoom
    {
        return ClassRoom::query()
            ->where('name', $classNameOrCode)
            ->orWhere('code', $classNameOrCode)
            ->first();
    }

    private function studentEmail(string $nisn): string
    {
        return "{$nisn}@murid.elkm.local";
    }

    /**
     * @param  array<int, string|null>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        return collect($data)->every(fn (?string $value): bool => trim((string) $value) === '');
    }
}
