<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create the demo Editor user
        $editUser = User::firstOrCreate(
            ['email' => 'editor@cifo.com'],
            [
                'name' => 'Editor',
                'password' => 'editor123',
                'locale' => 'ca',
            ]
        );
        $editUser->assignRole('Editor');

        // Create demo Viewer users only once
        if (User::role('Viewer')->doesntExist()) {
            User::factory()->count(10)->create()->each(function (User $user) {
                $user->assignRole('Viewer');
            });
        }
    }
}
