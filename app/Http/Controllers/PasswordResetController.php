<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /** Page where the person types their email */
    public function showRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /** Email the reset link */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['email' => 'We could not send the email right now. Please try again in a few minutes.'])
                ->onlyInput('email');
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withErrors(['email' => 'Please wait a minute before asking for another link.'])
                ->onlyInput('email');
        }

        // Same message whether or not the email has an account, so strangers cannot check who is registered
        return back()->with('success', 'If that email has an account, a reset link is on its way. Check your inbox and spam folder.');
    }

    /** Page opened from the email link */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Save the new password */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', 'Your password was changed. You can log in with it now.');
        }

        return back()
            ->withErrors(['email' => 'This reset link is invalid or has expired. Please ask for a new one.'])
            ->onlyInput('email');
    }
}