<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\Learning\LearningUnitOutlineService;
use Illuminate\Database\Seeder;

class RenewableEnergyOutlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $module = Module::where('slug', 'energi-terbarukan')->first();

        if (! $module) {
            return;
        }

        $outlineService = app(LearningUnitOutlineService::class);

        $module->learningUnits()
            ->with(['materials', 'activities', 'assessments.questions'])
            ->orderBy('order')
            ->get()
            ->each(fn ($unit) => $outlineService->ensureDefaultOutline($unit));
    }
}
