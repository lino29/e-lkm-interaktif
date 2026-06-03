<?php

namespace App\Livewire\Admin;

use App\Models\ClassRoom;
use App\Models\User;
use App\Services\Admin\UserCsvImportService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageUsers extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $nisn = '';

    public string $password = 'password';

    public string $role = 'murid';

    public ?int $class_room_id = null;

    public $csvFile;

    /**
     * @var array<int, string>
     */
    public array $importErrors = [];

    public ?string $importStatus = null;

    public function updatedRole(): void
    {
        if ($this->role !== 'murid') {
            $this->nisn = '';
            $this->class_room_id = null;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                Rule::requiredIf(fn (): bool => $this->role !== 'murid'),
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'nisn' => [
                Rule::requiredIf(fn (): bool => $this->role === 'murid'),
                'nullable',
                'digits:10',
                'unique:users,nisn',
            ],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'class_room_id' => [
                Rule::requiredIf(fn (): bool => $this->role === 'murid'),
                'nullable',
                Rule::exists('class_rooms', 'id'),
            ],
        ]);

        $user = User::create([
            'class_room_id' => $validated['role'] === 'murid' ? $validated['class_room_id'] : null,
            'nisn' => $validated['role'] === 'murid' ? $validated['nisn'] : null,
            'name' => $validated['name'],
            'email' => $validated['role'] === 'murid'
                ? $this->studentEmail($validated['nisn'])
                : $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($validated['role']);

        $this->reset(['name', 'email', 'nisn', 'class_room_id']);
        $this->password = 'password';
        $this->role = 'murid';
        session()->flash('status', 'Pengguna berhasil dibuat.');
    }

    public function importTeacherCsv(UserCsvImportService $importer): void
    {
        $this->importCsvForRole($importer, 'guru', 'import-teachers-modal');
    }

    public function importStudentCsv(UserCsvImportService $importer): void
    {
        $this->importCsvForRole($importer, 'murid', 'import-students-modal');
    }

    public function downloadTeacherCsvTemplate(): StreamedResponse
    {
        return $this->downloadCsvTemplate(
            'template-import-guru.csv',
            [
                ['name', 'email', 'password'],
                ['Guru Projek IPAS', 'guru.ipas@example.test', 'password123'],
            ],
        );
    }

    public function downloadStudentCsvTemplate(): StreamedResponse
    {
        return $this->downloadCsvTemplate(
            'template-import-murid.csv',
            [
                ['name', 'nisn', 'password', 'kelas'],
                ['Murid Energi', '1234567890', 'password123', 'X-TKJ-1'],
            ],
        );
    }

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'users' => User::with('classRoom', 'roles')->latest()->get(),
            'roles' => Role::pluck('name'),
            'classes' => ClassRoom::orderBy('name')->get(),
        ]);
    }

    private function importCsvForRole(UserCsvImportService $importer, string $role, string $modalName): void
    {
        $this->resetImportMessages();

        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $result = $importer->import($this->csvFile, $role);

        $this->reset('csvFile');
        $this->dispatch('close-modal', $modalName);

        if ($result['errors'] !== []) {
            $this->importErrors = $result['errors'];
            $this->importStatus = "Import selesai dengan {$result['created']} berhasil dan ".count($result['errors']).' gagal.';
            session()->flash('error', $this->importStatus);
        } else {
            $this->importStatus = "{$result['created']} pengguna berhasil diimport.";
            session()->flash('status', $this->importStatus);
        }
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function downloadCsvTemplate(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function resetImportMessages(): void
    {
        $this->importErrors = [];
        $this->importStatus = null;
    }

    private function studentEmail(string $nisn): string
    {
        return "{$nisn}@murid.elkm.local";
    }
}
