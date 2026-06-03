<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $classRoom = ClassRoom::firstOrCreate(
            ['code' => 'X-TKJ-1'],
            [
                'name' => 'X TKJ 1',
                'description' => 'Kelas demo untuk pengembangan E-LKM.',
            ],
        );

        $teacher = User::firstOrCreate(
            ['email' => 'guru@elkm.test'],
            [
                'name' => 'Guru Projek IPAS',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $teacher->assignRole('guru');

        $student = User::firstOrCreate(
            ['email' => 'murid@elkm.test'],
            [
                'class_room_id' => $classRoom->id,
                'nisn' => '1234567890',
                'name' => 'Murid Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        if ($student->nisn === null) {
            $student->forceFill(['nisn' => '1234567890'])->save();
        }
        $student->assignRole('murid');
    }
}
