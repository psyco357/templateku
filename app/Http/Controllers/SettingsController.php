<?php

namespace App\Http\Controllers;

use App\Helpers\LandingPageHelper;
use App\Models\Koperasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function editProfile(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->load('profile', 'anggota');

        return view('pages.settings.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'unique' => ':attribute sudah digunakan.',
        ], [
            'nama_lengkap' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'no_hp' => 'nomor HP',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'alamat' => 'alamat',
            'bio' => 'bio',
        ]);

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        $user->profile()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp' => $validated['no_hp'] ?? null,
            'tempat_lahir' => $validated['tempat_lahir'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()->route('settings.profile.edit')->with([
            'status' => 'Profil berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function editPassword(): View
    {
        return view('pages.settings.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'required' => ':attribute wajib diisi.',
            'current_password' => 'Password saat ini tidak sesuai.',
            'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
        ], [
            'current_password' => 'password saat ini',
            'password' => 'password baru',
        ]);

        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => null,
        ])->save();

        Auth::logoutOtherDevices($validated['password']);

        return redirect()->route('settings.password.edit')->with([
            'status' => 'Password berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    public function editAppearance(): View
    {
        $koperasi = $this->getPrimaryKoperasi();

        return view('pages.settings.landingpage.index', [
            'koperasi' => $koperasi,
            'themeOptions' => LandingPageHelper::presets(),
            'landingPage' => LandingPageHelper::build($koperasi),
        ]);
    }

    public function updateAppearance(Request $request): RedirectResponse
    {
        $koperasi = $this->getPrimaryKoperasi();

        $validated = $request->validate([
            'landing_theme' => ['required', Rule::in(array_keys(LandingPageHelper::presets()))],
            'landing_hero_title' => ['required', 'string', 'max:255'],
            'landing_hero_subtitle' => ['required', 'string', 'max:255'],
            'landing_hero_image' => ['nullable', 'url', 'max:2048'],
            'landing_about_title' => ['required', 'string', 'max:255'],
            'landing_about_description' => ['required', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute tidak boleh lebih dari :max karakter.',
            'url' => ':attribute harus berupa URL yang valid.',
            'in' => ':attribute yang dipilih tidak valid.',
        ], [
            'landing_theme' => 'tema landing page',
            'landing_hero_title' => 'judul hero',
            'landing_hero_subtitle' => 'subjudul hero',
            'landing_hero_image' => 'URL gambar hero',
            'landing_about_title' => 'judul profil koperasi',
            'landing_about_description' => 'deskripsi profil koperasi',
        ]);

        $koperasi->update($validated);

        return redirect()->route('settings.appearance')->with([
            'status' => 'Tema landing page berhasil diperbarui.',
            'status_type' => 'success',
        ]);
    }

    protected function getPrimaryKoperasi(): Koperasi
    {
        return Koperasi::query()->firstOrFail();
    }
}
