<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\AuthenticateUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use App\Features\Auth\Requests\LoginRequest;
use App\Features\Auth\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request, AuthenticateUserAction $action): RedirectResponse
    {
        $action->execute(
            $request->validated('username'),
            $request->validated('password'),
            $request->boolean('remember'),
            (string) $request->ip(),
        );

        $request->session()->regenerate();

        return redirect()->intended($request->user()->homeUrl());
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request, RegisterUserAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated());

        $request->session()->regenerate();

        return redirect($user->homeUrl())->with('success', 'Selamat datang di BlogVerse Pro!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
