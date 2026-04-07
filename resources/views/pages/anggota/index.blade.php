@extends('layouts.app')
@section('title', 'Anggota')

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
                    Daftar Anggota
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                    Menampilkan data user beserta level akses akun.
                </p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Total: {{ $users->total() }} anggota
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <a href="{{ route('anggota.create') }}"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                Tambah Anggota
            </a>
        </div>
        @if ($isFounder)
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Sebagai founder, Anda bisa mengubah level user menjadi anggota, pengurus, atau founder langsung dari tabel di bawah.
        </p>
        @endif
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-5 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
        <form method="GET" action="{{ route('anggota.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari anggota</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}"
                    placeholder="Cari nama, nomor anggota, username, email, atau jabatan"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Filter status</label>
                <select id="status" name="status"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua status</option>
                    @foreach ($availableStatuses as $status)
                    <option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
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
            <div class="md:col-span-4 flex flex-wrap items-center gap-2">
                <button type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                    Terapkan Filter
                </button>
                <a href="{{ route('anggota.index') }}"
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
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">No. Anggota</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status Anggota</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Level</th>
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
                            {{ $user->anggota?->no_anggota ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ ucfirst($user->anggota?->status ?? '-') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleBadgeClasses[$user->role] ?? $roleBadgeClasses[User::ROLE_ANGGOTA] }}">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ optional($user->created_at)->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex min-w-[220px] flex-col gap-2">
                                <a href="{{ route('anggota.show', $user) }}"
                                    class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-700 px-4 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                                    Detail / Edit
                                </a>
                                @if ($isFounder)
                                @if (auth()->id() === $user->id)
                                <span class="text-xs text-gray-400 dark:text-gray-500">Role akun aktif tidak bisa diubah</span>
                                @else
                                <form method="POST" action="{{ route('anggota.update-role', $user) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                        @foreach ($availableRoles as $role)
                                        <option value="{{ $role }}" @selected($user->role === $role)>
                                            {{ $roleLabels[$role] ?? ucfirst($role) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                        class="inline-flex h-10 items-center justify-center rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition hover:bg-orange-600">
                                        Simpan
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada data anggota.
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