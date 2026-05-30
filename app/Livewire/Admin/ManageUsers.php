<?php

namespace App\Livewire\Admin;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use \Livewire\WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $password = 'password';

    public string $role = 'murid';

    public ?int $class_room_id = null;

    public $csvFile;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'class_room_id' => ['nullable', Rule::exists('class_rooms', 'id')],
        ]);

        $user = User::create([
            'class_room_id' => $validated['role'] === 'murid' ? $validated['class_room_id'] : null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($validated['role']);

        $this->reset(['name', 'email', 'class_room_id']);
        $this->password = 'password';
        $this->role = 'murid';
        session()->flash('status', 'Pengguna berhasil dibuat.');
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $this->csvFile->getRealPath();
        $handle = fopen($file, 'r');
        if ($handle === false) {
            session()->flash('error', 'Gagal membaca file CSV.');

            return;
        }

        $header = fgetcsv($handle, 1000, ',');
        // Convert header to lowercase
        $header = array_map('strtolower', $header);

        $successCount = 0;
        $errors = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($header) !== count($data)) {
                continue; // Skip invalid rows
            }

            $row = array_combine($header, $data);

            if (! isset($row['name']) || ! isset($row['email']) || ! isset($row['password']) || ! isset($row['role'])) {
                $errors[] = 'Baris tidak valid, pastikan kolom name, email, password, role ada.';

                continue;
            }

            try {
                // If user exists, skip (or update, but skip is safer for simple import)
                $user = User::where('email', $row['email'])->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole($row['role']);
                    $successCount++;
                } else {
                    $errors[] = "Email {$row['email']} sudah ada.";
                }
            } catch (\Exception $e) {
                $errors[] = "Gagal memproses {$row['email']}: ".$e->getMessage();
            }
        }
        fclose($handle);

        $this->reset('csvFile');
        $this->dispatch('close-modal', 'import-users-modal');

        if (count($errors) > 0) {
            session()->flash('error', "Import selesai dengan $successCount berhasil. ".count($errors).' gagal.');
        } else {
            session()->flash('status', "$successCount pengguna berhasil diimport.");
        }
    }

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'users' => User::with('classRoom', 'roles')->latest()->get(),
            'roles' => Role::pluck('name'),
            'classes' => ClassRoom::orderBy('name')->get(),
        ]);
    }
}
