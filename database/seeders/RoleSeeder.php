<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage classes',
            'manage subjects',
            'manage modules',
            'manage assessments',
            'view learning',
            'submit activities',
            'submit assessments',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions($permissions);

        Role::firstOrCreate(['name' => 'guru'])
            ->syncPermissions([
                'manage modules',
                'manage assessments',
                'view reports',
            ]);

        Role::firstOrCreate(['name' => 'murid'])
            ->syncPermissions([
                'view learning',
                'submit activities',
                'submit assessments',
            ]);
    }
}
