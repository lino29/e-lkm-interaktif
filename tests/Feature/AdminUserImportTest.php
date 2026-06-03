<?php

use App\Livewire\Admin\ManageUsers;
use App\Models\ClassRoom;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin imports teachers from teacher csv schema', function () {
    $file = csvUpload('teachers.csv', "name,email,password\nGuru Energi,guru-energi@example.test,password123\n");

    Livewire::actingAs($this->admin)
        ->test(ManageUsers::class)
        ->set('csvFile', $file)
        ->call('importTeacherCsv')
        ->assertHasNoErrors();

    $teacher = User::where('email', 'guru-energi@example.test')->firstOrFail();

    expect($teacher->hasRole('guru'))->toBeTrue()
        ->and($teacher->nisn)->toBeNull()
        ->and(Hash::check('password123', $teacher->password))->toBeTrue();
});

test('admin can download teacher and student csv templates', function () {
    Livewire::actingAs($this->admin)
        ->test(ManageUsers::class)
        ->call('downloadTeacherCsvTemplate')
        ->assertFileDownloaded('template-import-guru.csv');

    Livewire::actingAs($this->admin)
        ->test(ManageUsers::class)
        ->call('downloadStudentCsvTemplate')
        ->assertFileDownloaded('template-import-murid.csv');
});

test('admin imports students from student csv schema with nisn and class', function () {
    $classRoom = ClassRoom::create([
        'name' => 'X TKJ 1',
        'code' => 'X-TKJ-1',
    ]);
    $file = csvUpload('students.csv', "name,nisn,password,kelas\nMurid Energi,1234567891,password123,X-TKJ-1\n");

    Livewire::actingAs($this->admin)
        ->test(ManageUsers::class)
        ->set('csvFile', $file)
        ->call('importStudentCsv')
        ->assertHasNoErrors();

    $student = User::where('nisn', '1234567891')->firstOrFail();

    expect($student->hasRole('murid'))->toBeTrue()
        ->and($student->email)->toBe('1234567891@murid.elkm.local')
        ->and($student->class_room_id)->toBe($classRoom->id)
        ->and(Hash::check('password123', $student->password))->toBeTrue();
});

test('student import rejects rows without valid nisn and class', function () {
    $file = csvUpload('students-invalid.csv', "name,nisn,password,kelas\nMurid Salah,123,password123,\n");

    Livewire::actingAs($this->admin)
        ->test(ManageUsers::class)
        ->set('csvFile', $file)
        ->call('importStudentCsv')
        ->assertHasNoErrors()
        ->assertSet('importStatus', 'Import selesai dengan 0 berhasil dan 1 gagal.');

    expect(User::where('name', 'Murid Salah')->exists())->toBeFalse();
});

function csvUpload(string $name, string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $content);
}
