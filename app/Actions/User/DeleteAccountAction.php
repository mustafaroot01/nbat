<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteAccountAction
{
    public function execute(User $user): void
    {
        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Delete user's tokens
        $user->tokens()->delete();

        // Let the DB handle cascading deletes for plants and other relations if configured
        // Otherwise, manually delete related records here.
        
        $user->delete();
    }
}
