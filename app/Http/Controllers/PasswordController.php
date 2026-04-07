<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function request(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berupa alamat email yang valid.',
        ], [
            'email' => 'email',
        ]);

        $status = Password::sendResetLink($validated);

        return back()->with([
            'status' => $status === Password::RESET_LINK_SENT
                ? 'Link reset password berhasil dikirim. Silakan cek email Anda.'
                : __($status),
            'status_type' => $status === Password::RESET_LINK_SENT ? 'success' : 'info',
        ]);
    }

    public function reset(string $token, Request $request): View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
        ], [
            'token' => 'token reset password',
            'email' => 'email',
            'password' => 'password',
        ]);

        $status = Password::reset(
            $validated,
            function ($user) use ($validated) {
                $user->forceFill([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->with([
                'status' => __($status),
                'status_type' => 'info',
            ]);
        }

        return redirect()->route('login')->with([
            'status' => 'Password berhasil diperbarui. Silakan login dengan password baru.',
            'status_type' => 'success',
        ]);
    }
}
