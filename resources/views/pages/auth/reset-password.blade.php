@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="register-shell relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900 lg:h-screen lg:p-0">
    <div class="register-stage min-h-screen w-full dark:bg-gray-900 lg:h-full">
        <div class="register-image-panel bg-brand-950 overflow-hidden h-64 lg:fixed lg:inset-y-0 lg:right-0 lg:block lg:h-screen lg:w-1/2 dark:bg-white/5">
            <img src="{{ asset('images/guest/jakarta-indonesia.jpg') }}" alt="Jakarta" class="block h-full w-full object-cover">
        </div>
        <div class="register-form-panel relative z-10 w-full bg-white dark:bg-gray-900 lg:fixed lg:inset-y-0 lg:left-0 lg:h-screen lg:w-1/2 lg:overflow-y-auto duration-300 ease-linear no-scrollbar">
            <div class="mx-auto w-full max-w-md pt-20 pb-10 px-6">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">Reset Password</h1>
                    <p class="text-zinc-600 dark:text-zinc-400">Masukkan password baru untuk akun Anda.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Email
                        </label>
                        <input type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                            placeholder="Masukkan email Anda"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Password Baru
                        </label>
                        <input type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password baru"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            placeholder="Ulangi password baru"
                            class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>

                    <button type="submit"
                        class="w-full py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.01] transition-all duration-200">
                        Simpan Password Baru
                    </button>
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
            title: 'Reset gagal',
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