@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="register-shell relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900 lg:h-screen lg:p-0">
    <div class="register-stage min-h-screen w-full dark:bg-gray-900 lg:h-full">
        <div class="register-image-panel bg-brand-950 overflow-hidden h-64 lg:fixed lg:inset-y-0 lg:right-0 lg:block lg:h-screen lg:w-1/2 dark:bg-white/5">
            <img src="{{ asset('images/guest/jakarta-indonesia.jpg') }}" alt="Jakarta" class="block h-full w-full object-cover">
        </div>
        <div class="register-form-panel relative z-10 w-full bg-white dark:bg-gray-900 lg:fixed lg:inset-y-0 lg:left-0 lg:h-screen lg:w-1/2 lg:overflow-y-auto duration-300 ease-linear no-scrollbar">
            <div class="mx-auto w-full max-w-md pt-20 pb-10 px-6">

                {{-- Logo --}}
                <div class="flex justify-center mb-8">
                    <a href="/" class="flex items-center space-x-3">
                        <x-logo.app-logo class="h-16 w-16 text-orange-500" />
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-orange-500 uppercase tracking-wide">Koperasi</span>
                            <span class="text-lg font-bold text-orange-500 -mt-0.5">{{ config('app.name', 'SUKA RESIK') }}</span>
                        </div>
                    </a>
                </div>

                {{-- Header --}}
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Login</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">Masuk untuk melanjutkan</p>
                </div>

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5">
                    @csrf

                    {{-- Username/Email --}}
                    <div>
                        <label for="login" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Username atau Email
                        </label>
                        <input type="text"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            placeholder="Masukkan username atau email"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Password
                        </label>
                        <input type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="w-full py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-200">
                        Log in
                    </button>

                    {{-- Links --}}
                    <div class="flex flex-col space-y-2 pt-2 text-sm text-center">
                        <p class="text-zinc-600 dark:text-zinc-400">
                            Belum terdaftar?
                            <a href="{{ route('register') }}" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">Daftar Disini</a>
                        </p>
                        <a href="{{ route('password.request') }}" class="text-orange-600 dark:text-orange-400 hover:underline">
                            Lupa password?
                        </a>
                    </div>
                </form>
            </div>
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
            title: 'Login gagal',
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

@endpush