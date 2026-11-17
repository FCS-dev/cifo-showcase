<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            AdminRoleSeeder::class,
            AdminUserSeeder::class,
            InstitutionalSeeder::class,
            CategorySeeder::class,
            CategoryContentSeeder::class,
        ]);
    }
}
