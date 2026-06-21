<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordAction
{
    public function execute(string $phone, string $newPassword): void
    {
        $user = User::where('phone', $phone)->firstOrFail();
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        // revoke all tokens to force re-login on all devices
        $user->tokens()->delete();
    }
}
