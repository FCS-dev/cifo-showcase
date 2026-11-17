<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class TeacherRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'students.view', 'students.create', 'students.update', 'students.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'tags.view',
        ];

        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web'])
            ->syncPermissions(
                Permission::where('guard_name', 'web')
                    ->whereIn('name', $permissions)
                    ->get()
            );

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
