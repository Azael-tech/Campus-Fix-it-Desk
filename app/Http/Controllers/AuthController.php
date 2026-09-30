<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'That email and password do not match. Check them and try again.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('reports.index'))
            ->with('success', 'Welcome back, ' . $request->user()->name . '!');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'staff_code' => ['nullable', 'string', 'max:100'],
        ]);

        $isStaff = false;

        if (filled($data['staff_code'] ?? null)) {
            $expected = (string) config('fixit.staff_code');

            if ($expected === '' || ! hash_equals($expected, $data['staff_code'])) {
                return back()
                    ->withErrors(['staff_code' => 'That staff code is not correct. Leave it empty if you are not maintenance staff.'])
                    ->onlyInput('name', 'email');
            }

            $isStaff = true;
        }

        $user = new User();
        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role     = $isStaff ? 'staff' : 'member';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('reports.index')
            ->with('success', $isStaff
                ? 'Your staff account is ready. You can now update and manage reports.'
                : 'Your account is ready. Your name and email will be filled in when you report a problem.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('reports.index')
            ->with('success', 'You have been logged out.');
    }
}
