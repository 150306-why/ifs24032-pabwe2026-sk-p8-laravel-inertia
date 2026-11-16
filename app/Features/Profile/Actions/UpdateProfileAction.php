<?php

namespace App\Features\Profile\Actions;

use App\Models\User;

class UpdateProfileAction
{
    /** @param array{name: string, username: string, email: string, password?: ?string} $data */
    public function execute(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user;
    }
}
