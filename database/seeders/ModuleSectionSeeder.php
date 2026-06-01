<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\Learning\ModuleOutlineService;
use Illuminate\Database\Seeder;

class ModuleSectionSeeder extends Seeder
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

        app(ModuleOutlineService::class)->ensureDefaultSections($module);
    }
}
