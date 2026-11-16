<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    /** @param array{name: string, username: string, email: string, password: string} $data */
    public function execute(array $data): User
    {
        // Role selalu member (default database); tidak pernah berasal dari input.
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return $user;
    }
}
