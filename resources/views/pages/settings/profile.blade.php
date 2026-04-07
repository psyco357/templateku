@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$currentPhoto = $user->profile?->foto_profil;
$currentPhotoUrl = asset('images/user/owner.png');

if (filled($currentPhoto)) {
if (Str::startsWith($currentPhoto, ['http://', 'https://'])) {
$currentPhotoUrl = $currentPhoto;
} elseif (Str::startsWith($currentPhoto, ['/storage/', 'storage/', '/images/', 'images/'])) {
$currentPhotoUrl = asset(ltrim($currentPhoto, '/'));
} else {
$currentPhotoUrl = Storage::disk('public')->url($currentPhoto);
}
}
@endphp

@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
                    Profile Saya
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                    Kelola identitas akun yang dipakai untuk login dan data profil dasar anggota.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-500/15 dark:text-orange-300">
                    {{ ucfirst($user->role) }}
                </span>
                @if ($user->anggota?->no_anggota)
                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    No. Anggota {{ $user->anggota->no_anggota }}
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Form Profile</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perubahan di sini akan langsung memperbarui akun login dan tabel profile Anda.</p>

            <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div class="md:col-span-2">
                    <label for="foto_profil" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto Profil</label>
                    <div class="flex flex-col gap-4 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700 md:flex-row md:items-center">
                        <div class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                            <img src="{{ $currentPhotoUrl }}" alt="{{ $user->profile?->nama_lengkap ?? $user->username }}" class="h-full w-full object-cover">
                        </div>
                        <div class="flex-1">
                            <input type="file" id="foto_profil" name="foto_profil" accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-orange-500 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-orange-600 dark:text-gray-300">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Format: JPG, PNG, atau WEBP. Maksimal 2 MB. Foto baru akan langsung dipakai di dropdown setelah profil disimpan.</p>
                        </div>
                    </div>
                    @error('foto_profil')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="nama_lengkap" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->profile?->nama_lengkap ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('nama_lengkap')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('username')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_hp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->profile?->no_hp ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('no_hp')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="tempat_lahir" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $user->profile?->tempat_lahir ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('tempat_lahir')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="tanggal_lahir" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->profile?->tanggal_lahir ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('tanggal_lahir')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('alamat', $user->profile?->alamat ?? '') }}</textarea>
                    @error('alamat')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="bio" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                    <textarea id="bio" name="bio" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('bio', $user->profile?->bio ?? '') }}</textarea>
                    @error('bio')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-orange-500 px-5 text-sm font-medium text-white transition hover:bg-orange-600">
                        Simpan Profile
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ringkasan Akun</h4>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Level User</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ ucfirst($user->role) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Status Akun</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">No. Anggota</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->anggota?->no_anggota ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Terdaftar</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ optional($user->created_at)->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Keamanan</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jika Anda perlu mengganti sandi akun, gunakan halaman password.</p>
                <a href="{{ route('settings.password.edit') }}"
                    class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-lg bg-slate-700 px-4 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                    Buka Pengaturan Password
                </a>
            </div>
        </div>
    </div>
</div>
@endsection