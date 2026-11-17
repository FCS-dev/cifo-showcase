<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::role('Admin')->exists()) {
            return;
        }

        $email = config('admin.email');

        $user = User::withTrashed()->where('email', $email)->first();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            if ($user->activated_at === null) {
                $user->forceFill(['activated_at' => now()])->save();
            }

            $user->assignRole('Admin');

            return;
        }

        if (app()->environment('production') && $this->usesInsecurePassword()) {
            throw new RuntimeException(
                'ADMIN_PASSWORD is missing or still uses the default value. Set a secure password before seeding in production.'
            );
        }

        $user = User::create([
            'name' => config('admin.name'),
            'email' => $email,
            'password' => config('admin.password'),
            'locale' => 'es',
            'email_verified_at' => now(),
            'activated_at' => now(),
        ]);

        $user->assignRole('Admin');
    }

    private function usesInsecurePassword(): bool
    {
        $password = config('admin.password');

        return blank($password) || $password === 'admin123';
    }
}
