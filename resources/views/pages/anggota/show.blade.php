@extends('layouts.app')
@section('title', 'Detail Anggota')

@section('content')
@php
use App\Models\User;

$authenticatedUser = auth()->user();
$isFounder = $authenticatedUser instanceof User && $authenticatedUser->hasRole(User::ROLE_FOUNDER);
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
                <a href="{{ route('anggota.index') }}" class="text-sm font-medium text-orange-500 hover:text-orange-600">
                    Kembali ke daftar anggota
                </a>
                <h3 class="mt-2 font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
                    Detail Anggota
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                    Ubah data profil dan keanggotaan anggota yang sudah terdaftar tanpa mengubah struktur data yang sudah dipakai sistem.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleBadgeClasses[$user->role] ?? $roleBadgeClasses[User::ROLE_ANGGOTA] }}">
                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                </span>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ $user->anggota?->status ? ucfirst($user->anggota->status) : 'Belum ada status' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Form Detail Anggota</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data pada form ini akan memperbarui tabel profiles dan anggota untuk user yang dipilih.</p>

            <form method="POST" action="{{ route('anggota.update', $user) }}" class="mt-6 grid gap-5 md:grid-cols-2">
                @csrf
                @method('PATCH')
                <input type="hidden" id="auto_generate_no_anggota" name="auto_generate_no_anggota" value="{{ old('auto_generate_no_anggota', '0') }}">

                <div class="md:col-span-2">
                    <label for="nama_lengkap" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->profile?->nama_lengkap ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('nama_lengkap')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_anggota" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Anggota</label>
                    <div class="flex gap-2">
                        <input type="text" id="no_anggota" name="no_anggota" value="{{ old('no_anggota', $user->anggota?->no_anggota ?? '') }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <button type="button" id="regenerate-member-number" data-url="{{ route('anggota.generate-member-number') }}"
                            class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-orange-500 px-4 text-sm font-medium text-orange-500 transition hover:bg-orange-50 dark:hover:bg-orange-500/10">
                            Generate Ulang
                        </button>
                    </div>
                    @error('no_anggota')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="jabatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->anggota?->jabatan ?? '') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('jabatan')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status_keanggotaan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Keanggotaan</label>
                    <select id="status_keanggotaan" name="status_keanggotaan"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @foreach ($availableStatuses as $status)
                        <option value="{{ $status }}" @selected(old('status_keanggotaan', $user->anggota?->status ?? \App\Models\AnggotaModel::STATUS_AKTIF) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status_keanggotaan')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
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
                        Simpan Perubahan Anggota
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ringkasan Akun</h4>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Username</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->username }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Level User</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ optional($user->created_at)->format('d M Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @if ($isFounder)
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Kelola Akses</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jika perlu mengubah username, email, status akun, atau reset password, gunakan halaman detail akun.</p>
                <a href="{{ route('accounts.show', $user) }}"
                    class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-lg bg-slate-700 px-4 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                    Buka Detail Akun
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.Swal.fire({
            icon: 'error',
            title: 'Perubahan anggota gagal',
            html: `
                        <ul class="list-disc pl-5 text-left space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ e($error) }}</li>
                            @endforeach
                        </ul>
                    `,
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#f97316',
        });
    });
</script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const regenerateButton = document.getElementById('regenerate-member-number');
        const memberNumberInput = document.getElementById('no_anggota');
        const autoGenerateInput = document.getElementById('auto_generate_no_anggota');

        if (!regenerateButton || !memberNumberInput || !autoGenerateInput) {
            return;
        }

        memberNumberInput.addEventListener('input', () => {
            autoGenerateInput.value = '0';
        });

        regenerateButton.addEventListener('click', async () => {
            regenerateButton.disabled = true;

            try {
                const response = await fetch(regenerateButton.dataset.url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal membuat nomor anggota baru.');
                }

                const data = await response.json();
                memberNumberInput.value = data.no_anggota ?? '';
                autoGenerateInput.value = '1';
            } catch (error) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Generate nomor gagal',
                    text: error.message,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f97316',
                });
            } finally {
                regenerateButton.disabled = false;
            }
        });
    });
</script>
@endpush