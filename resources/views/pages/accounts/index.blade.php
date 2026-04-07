@extends('layouts.app')
@section('title', 'Akun')

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
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
                    Daftar Akun
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                    Cari akun lalu buka detailnya untuk mengubah nama, username, email, role, status akun, dan reset password.
                </p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Total: {{ $users->total() }} akun
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Halaman ini dipakai untuk pencarian dan pemilihan akun. Perubahan data akun dilakukan dari halaman detail akun yang dipilih.
        </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-5 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
        <form method="GET" action="{{ route('accounts.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari akun</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                    placeholder="Cari nama, username, atau email"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Filter level</label>
                <select id="role" name="role"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua level</option>
                    @foreach ($availableRoles as $role)
                    <option value="{{ $role }}" @selected($filters['role']===$role)>{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Filter status</label>
                <select id="status" name="status"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua status</option>
                    <option value="active" @selected($filters['status']==='active' )>Aktif</option>
                    <option value="inactive" @selected($filters['status']==='inactive' )>Nonaktif</option>
                </select>
            </div>
            <div class="md:col-span-4 flex flex-wrap items-center gap-2">
                <button type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                    Terapkan Filter
                </button>
                <a href="{{ route('accounts.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Level</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($users as $user)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $user->profile?->nama_lengkap ?? $user->username }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->username }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->email }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleBadgeClasses[$user->role] ?? $roleBadgeClasses[User::ROLE_ANGGOTA] }}">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ optional($user->created_at)->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <a href="{{ route('accounts.show', $user) }}"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                                Detail Akun
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada data akun.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection