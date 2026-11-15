<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SiteImage;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class InstitutionalSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(SiteImage::SLOTS) as $key) {
            SiteImage::firstOrCreate(['key' => $key]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['institutional.view', 'institutional.update'];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'Admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
