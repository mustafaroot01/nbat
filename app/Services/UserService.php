<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function getPaginated()
    {
        return $this->userRepository->getPaginated();
    }

    public function updateProfile($user, array $data)
    {
        return $this->userRepository->update($user, $data);
    }

    public function updatePhoto($user, $photo)
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        $path = $photo->store('profiles', 'public');
        return $this->userRepository->update($user, ['profile_photo' => $path]);
    }

    public function changePassword($user, string $newPassword)
    {
        return $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);
    }

    public function toggleActive($user)
    {
        return $this->userRepository->update($user, ['is_active' => !$user->is_active]);
    }
}
