<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            DemoUserSeeder::class,
            EducationalGameSeeder::class,
            DemoLearningSeeder::class,
            RenewableEnergyMaterialSeeder::class,
            RenewableEnergyActivitySeeder::class,
            RenewableEnergyAssessmentSeeder::class,
            ModuleSectionSeeder::class,
            RenewableEnergyOutlineSeeder::class,
        ]);
    }
}
