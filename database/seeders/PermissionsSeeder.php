<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $entities = ['categories', 'courses', 'projects', 'students', 'tags', 'teachers', 'users'];
        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete',
            'dashboard.view',
            'users.assign-roles',
            'institutional.view', 'institutional.update',
        ];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permissions[] = "{$entity}.{$action}";
            }
        }

        foreach (array_unique($permissions) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
