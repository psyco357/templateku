@extends('layouts.app')

@section('title', 'Password')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-8">
        <h3 class="font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
            Password Saya
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
            Ubah password akun aktif dengan memasukkan password saat ini terlebih dahulu.
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Form Ganti Password</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sesi perangkat lain akan dikeluarkan setelah password diperbarui.</p>

            <form method="POST" action="{{ route('settings.password.update') }}" class="mt-6 grid gap-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('current_password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                    <input type="password" id="password" name="password"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>

                <div>
                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-orange-500 px-5 text-sm font-medium text-white transition hover:bg-orange-600">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Aturan Password</h4>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li>Password baru harus memenuhi aturan default Laravel.</li>
                    <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol bila perlu.</li>
                    <li>Jangan gunakan password lama yang mudah ditebak ulang.</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Profile</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kembali ke halaman profile jika Anda perlu memperbarui nama, email, atau biodata.</p>
                <a href="{{ route('settings.profile.edit') }}"
                    class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-lg bg-slate-700 px-4 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
                    Buka Profile Saya
                </a>
            </div>
        </div>
    </div>
</div>
@endsection