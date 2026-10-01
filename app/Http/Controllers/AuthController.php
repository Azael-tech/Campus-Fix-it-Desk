<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const GENDERS  = ['Male', 'Female', 'Other', 'Prefer not to say'];
    public const SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];

    /* ---------- Log in ---------- */

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

    /* ---------- Sign up ---------- */

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
            'last_name'      => ['required', 'string', 'max:60', "regex:/^[\pL\s.'\-]+$/u"],
            'first_name'     => ['required', 'string', 'max:60', "regex:/^[\pL\s.'\-]+$/u"],
            'middle_initial' => ['nullable', 'string', 'size:1', 'regex:/^\pL$/u'],
            'gender'         => ['required', Rule::in(self::GENDERS)],
            'suffix'         => ['nullable', Rule::in(self::SUFFIXES)],
            'email'          => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'staff_code'     => ['nullable', 'string', 'max:100'],
        ], [
            'last_name.regex'      => 'Last name can only have letters, spaces, dots, apostrophes, and dashes.',
            'first_name.regex'     => 'First name can only have letters, spaces, dots, apostrophes, and dashes.',
            'middle_initial.size'  => 'Middle initial must be just one letter.',
            'middle_initial.regex' => 'Middle initial must be a letter.',
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

        $user = new User();
        $user->last_name      = $data['last_name'];
        $user->first_name     = $data['first_name'];
        $user->middle_initial = filled($data['middle_initial'] ?? null) ? strtoupper($data['middle_initial']) : null;
        $user->gender         = $data['gender'];
        $user->suffix         = $data['suffix'] ?? null;
        $user->name           = User::buildName($data['first_name'], $user->middle_initial, $data['last_name'], $user->suffix);
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

    /* ---------- Forgot password ---------- */

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = PasswordBroker::sendResetLink($request->only('email'));

        if ($status === PasswordBroker::RESET_THROTTLED) {
            return back()
                ->withErrors(['email' => 'Please wait a minute before asking for another link.'])
                ->onlyInput('email');
        }

        return back()->with('status', 'If that email has an account, we sent a link to reset the password.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => 'This reset link is not valid or has expired. Ask for a new one.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Your password was changed. You can log in now.');
    }

    /* ---------- Log out ---------- */

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