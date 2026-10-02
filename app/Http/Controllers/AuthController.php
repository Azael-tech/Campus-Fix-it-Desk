<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const GENDERS  = ['Male', 'Female', 'Prefer not to say'];
    public const SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];

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
        return view('auth.register', [
            'genders'  => self::GENDERS,
            'suffixes' => self::SUFFIXES,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'last_name'      => ['required', 'string', 'max:60'],
            'first_name'     => ['required', 'string', 'max:60'],
            'middle_initial' => ['nullable', 'string', 'size:1', 'alpha'],
            'gender'         => ['required', Rule::in(self::GENDERS)],
            'suffix'         => ['nullable', Rule::in(self::SUFFIXES)],
            'email'          => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'staff_code'     => ['nullable', 'string', 'max:100'],
        ]);

        $isStaff = false;

        if (filled($data['staff_code'] ?? null)) {
            $expected = (string) config('fixit.staff_code');

            if ($expected === '' || ! hash_equals($expected, $data['staff_code'])) {
                return back()
                    ->withErrors(['staff_code' => 'That staff code is not correct. Leave it empty if you are not maintenance staff.'])
                    ->onlyInput('last_name', 'first_name', 'middle_initial', 'gender', 'suffix', 'email');
            }

            $isStaff = true;
        }

        $initial = filled($data['middle_initial'] ?? null) ? Str::upper($data['middle_initial']) : null;

        // The "name" column still holds the full display name, e.g. "Maria D. Santos Jr."
        $fullName = collect([
            $data['first_name'],
            $initial ? $initial . '.' : null,
            $data['last_name'],
            $data['suffix'] ?? null,
        ])->filter()->implode(' ');

        $user = new User();
        $user->name           = $fullName;
        $user->last_name      = $data['last_name'];
        $user->first_name     = $data['first_name'];
        $user->middle_initial = $initial;
        $user->gender         = $data['gender'];
        $user->suffix         = $data['suffix'] ?? null;
        $user->email          = $data['email'];
        $user->password       = Hash::make($data['password']);
        $user->role           = $isStaff ? 'staff' : 'member';
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