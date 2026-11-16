<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Actions\UpdateProfileAction;
use App\Features\Profile\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'profile' => $request->user()->only(['name', 'username', 'email', 'role']),
        ]);
    }

    public function update(ProfileUpdateRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->safe()->only(['name', 'username', 'email', 'password']));

        return to_route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
