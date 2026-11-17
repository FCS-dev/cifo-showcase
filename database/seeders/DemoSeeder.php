<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoSeeder cannot be executed in production.');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->syncPermissions(
            Permission::where('name', 'not like', 'users.%')
                ->where('name', 'not like', 'roles.%')
                ->where('name', 'not like', 'permissions.%')
                ->where('name', 'not like', 'institutional.%')
                ->get()
        );

        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(
            Permission::where('name', 'like', '%.view')
                ->where('name', 'not like', 'institutional.%')
                ->get()
        );

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            CourseSeeder::class,
            ProjectSeeder::class,
            ProjectMediaSeeder::class,
        ]);
    }
}
