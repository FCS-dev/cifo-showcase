<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkService
{
    /**
     * Generate a one-time password reset link for the given user.
     *
     * @return array{url: string, expires_at: Carbon}
     */
    public function createFor(User $user): array
    {
        $broker = config('fortify.passwords');
        $minutes = (int) config("auth.passwords.{$broker}.expire", 60);

        $token = Password::broker($broker)->createToken($user);

        return [
            'url' => route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]),
            'expires_at' => now()->addMinutes($minutes),
        ];
    }
}
