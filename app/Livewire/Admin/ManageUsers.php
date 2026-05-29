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
    public string $name = '';

    public string $email = '';

    public string $password = 'password';

    public string $role = 'murid';

    public ?int $class_room_id = null;

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

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'users' => User::with('classRoom', 'roles')->latest()->get(),
            'roles' => Role::pluck('name'),
            'classes' => ClassRoom::orderBy('name')->get(),
        ]);
    }
}
