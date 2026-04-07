@extends('layouts.app')
@section('title', 'Detail Akun')

@section('content')
@php
use App\Models\User;

$authenticatedUser = auth()->user();
$isSelf = $authenticatedUser instanceof User && $authenticatedUser->id === $user->id;
$roleLabels = [
User::ROLE_ANGGOTA => 'Anggota',
User::ROLE_PENGURUS => 'Pengurus',
User::ROLE_FOUNDER => 'Founder',
];
$roleBadgeClasses = [
User::ROLE_ANGGOTA => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
User::ROLE_PENGURUS => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
User::ROLE_FOUNDER => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
];
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="{{ route('accounts.index') }}" class="text-sm font-medium text-orange-500 hover:text-orange-600">
                    Kembali ke daftar akun
                </a>
                <h3 class="mt-2 font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
                    Detail Akun
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                    Kelola data akun, level akses, status login, dan kirim reset password untuk user yang dipilih.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleBadgeClasses[$user->role] ?? $roleBadgeClasses[User::ROLE_ANGGOTA] }}">
                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                </span>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Akun</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perubahan di bawah ini akan dipakai untuk akun user yang dipilih di seluruh sistem.</p>

            <form method="POST" action="{{ route('accounts.update', $user) }}" class="mt-6 grid gap-5 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div class="md:col-span-2">
                    <label for="nama_lengkap" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->profile?->nama_lengkap ?? '') }}"
                        placeholder="Masukkan nama lengkap"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('nama_lengkap')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                        placeholder="Masukkan username"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('username')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                        placeholder="Masukkan email"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Level User</label>
                    <select id="role" name="role"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @foreach ($availableRoles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                            {{ $roleLabels[$role] ?? ucfirst($role) }}
                        </option>
                        @endforeach
                    </select>
                    @if ($isSelf)
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Role akun yang sedang Anda pakai tidak bisa diubah ke level lain dari halaman ini.</p>
                    @endif
                    @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-slate-700 px-5 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                        Simpan Detail Akun
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ringkasan</h4>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nama</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->profile?->nama_lengkap ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Username</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->username }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ optional($user->created_at)->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Aksi Akun</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gunakan aksi berikut untuk membantu user saat akun bermasalah.</p>

                <div class="mt-5 space-y-3">
                    @if ($isSelf)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900/40 dark:text-gray-400">
                        Akun yang sedang Anda gunakan tidak bisa dinonaktifkan dari halaman ini.
                    </div>
                    @else
                    <form method="POST" action="{{ route('accounts.toggle-status', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg px-4 text-sm font-medium text-white transition {{ $user->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                            {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </button>
                    </form>
                    @endif

                    <form method="POST" action="{{ route('accounts.reset-password', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                            Kirim Link Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection