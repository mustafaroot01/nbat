<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getPaginated()
    {
        return User::with('governorate')->latest()->paginate(15);
    }

    public function findById(int $id)
    {
        return User::findOrFail($id);
    }

    public function update(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user)
    {
        $user->delete();
        return true;
    }
}
