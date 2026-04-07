<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard')->with([
                'status' => 'Anda sudah login.',
                'status_type' => 'info',
            ]);
        }

        return view('pages.auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'unique' => ':attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
            'accepted' => ':attribute harus disetujui.',
            'password.min' => ':attribute minimal :min karakter.',
            'password.letters' => ':attribute harus mengandung minimal satu huruf.',
            'password.mixed' => ':attribute harus mengandung huruf besar dan huruf kecil.',
            'password.numbers' => ':attribute harus mengandung minimal satu angka.',
            'password.symbols' => ':attribute harus mengandung minimal satu simbol.',
            'password.uncompromised' => ':attribute pernah muncul dalam kebocoran data. Silakan gunakan :attribute yang lain.',
        ], [
            'nama_lengkap' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'password' => 'password',
            'terms' => 'persetujuan syarat dan ketentuan',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_ANGGOTA,
            ]);

            $user->profile()->create([
                'nama_lengkap' => $validated['nama_lengkap'],
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with([
            'status' => 'Registrasi berhasil.',
            'status_type' => 'success',
        ]);
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard')->with([
                'status' => 'Anda sudah login.',
                'status_type' => 'info',
            ]);
        }

        return view('pages.auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
        ], [
            'login' => 'username atau email',
            'password' => 'password',
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::query()
            ->where($loginField, $credentials['login'])
            ->first();

        if ($user instanceof User && ! (bool) $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Akun Anda sedang nonaktif. Silakan hubungi admin.',
            ]);
        }

        if ($user instanceof User && ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Username, email, atau password tidak sesuai.',
            ]);
        }

        if (! Auth::attempt([
            $loginField => $credentials['login'],
            'password' => $credentials['password'],
            'is_active' => 1,
        ])) {
            throw ValidationException::withMessages([
                'login' => 'Username, email, atau password tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with([
            'status' => 'Login berhasil.',
            'status_type' => 'success',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with([
            'status' => 'Anda berhasil logout.',
            'status_type' => 'success',
        ]);
    }
}
