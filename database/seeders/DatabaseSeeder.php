<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder is always production-safe. Demo data lives in DemoSeeder
     * and must be executed explicitly:
     *
     *     php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
    }
}
