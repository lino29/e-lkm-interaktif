<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ManageClasses;
use App\Livewire\Admin\ManageStudents;
use App\Livewire\Admin\ManageSubjects;
use App\Livewire\Admin\ManageTeachers;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Guru\Dashboard as GuruDashboard;
use App\Livewire\Guru\ManageActivities;
use App\Livewire\Guru\ManageAssessments;
use App\Livewire\Guru\ManageDiscussions;
use App\Livewire\Guru\ManageLearningUnitOutline;
use App\Livewire\Guru\ManageLearningUnits;
use App\Livewire\Guru\ManageMaterials;
use App\Livewire\Guru\ManageModules;
use App\Livewire\Guru\ManageProjects;
use App\Livewire\Guru\ManageQuestions;
use App\Livewire\Guru\ManageRubrics;
use App\Livewire\Guru\ModuleDetail as GuruModuleDetail;
use App\Livewire\Guru\Reports as GuruReports;
use App\Livewire\Guru\ReviewActivityAnswers;
use App\Livewire\Murid\ActivityPage;
use App\Livewire\Murid\AssessmentPage;
use App\Livewire\Murid\Dashboard as MuridDashboard;
use App\Livewire\Murid\LearningUnitPage;
use App\Livewire\Murid\ModuleDetail as MuridModuleDetail;
use App\Livewire\Murid\MyModules;
use App\Livewire\Murid\MyProject;
use App\Livewire\Murid\MyScores;
use App\Livewire\Murid\Portfolio;
use App\Livewire\Murid\RemedialPage;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('guru')) {
            return redirect()->route('guru.dashboard');
        }

        if ($user->hasRole('murid')) {
            return redirect()->route('murid.dashboard');
        }

        abort(403);
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('dashboard', 'dashboard.page', ['livewireComponent' => AdminDashboard::class, 'title' => 'Dashboard Admin'])->name('dashboard');
        Route::view('users', 'dashboard.page', ['livewireComponent' => ManageUsers::class, 'title' => 'Kelola Pengguna'])->name('users');
        Route::view('teachers', 'dashboard.page', ['livewireComponent' => ManageTeachers::class, 'title' => 'Kelola Guru'])->name('teachers');
        Route::view('students', 'dashboard.page', ['livewireComponent' => ManageStudents::class, 'title' => 'Kelola Murid'])->name('students');
        Route::view('classes', 'dashboard.page', ['livewireComponent' => ManageClasses::class, 'title' => 'Kelola Kelas'])->name('classes');
        Route::view('subjects', 'dashboard.page', ['livewireComponent' => ManageSubjects::class, 'title' => 'Kelola Mata Pelajaran'])->name('subjects');
        Route::view('reports', 'dashboard.page', ['livewireComponent' => AdminReports::class, 'title' => 'Laporan Sistem'])->name('reports');
    });

    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::view('dashboard', 'dashboard.page', ['livewireComponent' => GuruDashboard::class, 'title' => 'Dashboard Guru'])->name('dashboard');
        Route::view('modules', 'dashboard.page', ['livewireComponent' => ManageModules::class, 'title' => 'Kelola Modul'])->name('modules');
        Route::view('modules/{module}', 'dashboard.page', ['livewireComponent' => GuruModuleDetail::class, 'title' => 'Detail Modul'])->name('modules.show');
        Route::view('learning-units', 'dashboard.page', ['livewireComponent' => ManageLearningUnits::class, 'title' => 'Kelola Kegiatan Belajar'])->name('learning-units');
        Route::view('learning-units/{learningUnit}/outline', 'dashboard.page', ['livewireComponent' => ManageLearningUnitOutline::class, 'title' => 'Kelola Outline KB'])->name('learning-units.outline');
        Route::view('materials', 'dashboard.page', ['livewireComponent' => ManageMaterials::class, 'title' => 'Kelola Materi'])->name('materials');
        Route::view('activities', 'dashboard.page', ['livewireComponent' => ManageActivities::class, 'title' => 'Kelola Aktivitas'])->name('activities');
        Route::view('discussions', 'dashboard.page', ['livewireComponent' => ManageDiscussions::class, 'title' => 'Kelola Diskusi'])->name('discussions');
        Route::view('assessments', 'dashboard.page', ['livewireComponent' => ManageAssessments::class, 'title' => 'Kelola Asesmen'])->name('assessments');
        Route::view('questions', 'dashboard.page', ['livewireComponent' => ManageQuestions::class, 'title' => 'Kelola Soal'])->name('questions');
        Route::view('rubrics', 'dashboard.page', ['livewireComponent' => ManageRubrics::class, 'title' => 'Kelola Rubrik'])->name('rubrics');
        Route::view('projects', 'dashboard.page', ['livewireComponent' => ManageProjects::class, 'title' => 'Kelola Proyek'])->name('projects');
        Route::view('reports', 'dashboard.page', ['livewireComponent' => GuruReports::class, 'title' => 'Laporan Guru'])->name('reports');
        Route::view('activity-reviews', 'dashboard.page', ['livewireComponent' => ReviewActivityAnswers::class, 'title' => 'Review Jawaban Aktivitas'])->name('activity-reviews');
    });

    Route::middleware('role:murid')->prefix('murid')->name('murid.')->group(function () {
        Route::view('dashboard', 'dashboard.page', ['livewireComponent' => MuridDashboard::class, 'title' => 'Dashboard Murid'])->name('dashboard');
        Route::view('modules', 'dashboard.page', ['livewireComponent' => MyModules::class, 'title' => 'Modul Saya'])->name('modules');
        Route::view('modules/{module}', 'dashboard.page', ['livewireComponent' => MuridModuleDetail::class, 'title' => 'Detail Modul'])->name('modules.show');
        Route::view('learning-units/{learningUnit}', 'dashboard.page', ['livewireComponent' => LearningUnitPage::class, 'title' => 'Kegiatan Belajar'])->name('learning-units.show');
        Route::view('activities/{activity}', 'dashboard.page', ['livewireComponent' => ActivityPage::class, 'title' => 'Aktivitas'])->name('activities.show');
        Route::view('assessments/{assessment}', 'dashboard.page', ['livewireComponent' => AssessmentPage::class, 'title' => 'Asesmen'])->name('assessments.show');
        Route::view('remedial', 'dashboard.page', ['livewireComponent' => RemedialPage::class, 'title' => 'Remedial'])->name('remedial');
        Route::view('project', 'dashboard.page', ['livewireComponent' => MyProject::class, 'title' => 'Proyek Saya'])->name('project');
        Route::view('scores', 'dashboard.page', ['livewireComponent' => MyScores::class, 'title' => 'Nilai Saya'])->name('scores');
        Route::view('portfolio', 'dashboard.page', ['livewireComponent' => Portfolio::class, 'title' => 'Portofolio'])->name('portfolio');
    });
});

require __DIR__.'/settings.php';
