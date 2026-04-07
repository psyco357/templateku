@extends('layouts.auth')

@section('title', 'Registrasi')

@section('content')

<style>
    @media (min-width: 1024px) {
        .register-shell {
            position: fixed;
            inset: 0;
            overflow: hidden;
            padding: 0;
        }

        .register-stage {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        .register-form-panel {
            position: fixed;
            inset: 0 auto 0 0;
            width: 50%;
            height: 100vh;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .register-form-panel::-webkit-scrollbar {
            display: none;
        }

        .register-image-panel {
            position: fixed;
            inset: 0 0 0 auto;
            display: block;
            width: 50%;
            height: 100vh;
            overflow: hidden;
        }
    }
</style>

<div class="register-shell relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900 lg:h-screen lg:p-0">
    <div class="register-stage min-h-screen w-full dark:bg-gray-900 lg:h-full">
        <!-- Form -->
        <div class="register-form-panel relative z-10 w-full bg-white dark:bg-gray-900 lg:fixed lg:inset-y-0 lg:left-0 lg:h-screen lg:w-1/2 lg:overflow-y-auto duration-300 ease-linear no-scrollbar">

            <div class="mx-auto w-full max-w-md pt-20 pb-10 px-6">

                <div class="mb-5 sm:mb-8">
                    <div class="flex items-center justify-center">
                        <a href="/" class="flex items-center space-x-2">
                            <x-logo.app-logo class="h-12 w-15 text-indigo-600 dark:text-indigo-500" />
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-orange-500 dark:text-orange-400 uppercase tracking-wide">Koperasi</span>
                                <span class="text-lg font-bold text-orange-500 dark:text-orange-400 -mt-1">{{ config('app.name') }}</span>
                            </div>
                        </a>
                    </div>

                    <div class="flex items-center justify-center">
                        <h3 class="font-bold text-zinc-900 dark:text-white mb-2">Registrasi</h3>
                    </div>
                    <div class="flex items-center justify-center">
                        <p class="text-zinc-900 dark:text-white">Sebagai Anggota Koperasi {{ config('app.name') }}</p>
                    </div>


                </div>
                <div>
                    <!-- Info Box -->
                    <div class="flex items-center gap-3 bg-gray-100 
                text-gray-700 px-4 py-3 
                rounded-xl mb-6 dark:bg-gray-800 dark:text-gray-300">

                        <div class="w-6 h-6 flex items-center 
                    justify-center 
                    bg-orange-500 
                    rounded-full text-sm text-white flex-shrink-0">
                            <i class="fas fa-info"></i>
                        </div>

                        <p class="text-sm">
                            Lengkapi formulir di bawah ini untuk mendaftar sebagai anggota Koperasi
                        </p>

                    </div>
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <!-- Full Name -->
                                <div class="sm:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Nama Lengkap<span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" id="nama_lengkap" name="nama_lengkap"
                                        value="{{ old('nama_lengkap') }}"
                                        placeholder="Masukkan nama lengkap"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                    @error('nama_lengkap')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <!-- Username -->
                                <div class="sm:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Username<span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" id="username" name="username"
                                        value="{{ old('username') }}"
                                        placeholder="Masukkan username"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                    @error('username')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Email -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Email<span class="text-error-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Password -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Password<span class="text-error-500">*</span>
                                </label>
                                <div x-data="{ showPassword: false }" class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="Masukkan password"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                    <span @click="showPassword = !showPassword"
                                        class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                        <i x-show="!showPassword" class="fa-regular fa-eye text-base"></i>
                                        <i x-show="showPassword" class="fa-regular fa-eye-slash text-base"></i>
                                    </span>
                                </div>
                                @error('password')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Konfirmasi Password<span class="text-error-500">*</span>
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                            </div>
                            <!-- Checkbox -->
                            <div>
                                <div x-data="{ checkboxToggle: @js((bool) old('terms')) }">
                                    <label for="checkboxLabelOne"
                                        class="flex cursor-pointer items-start text-sm font-normal text-gray-700 select-none dark:text-gray-400">
                                        <div class="relative">
                                            <input type="checkbox" id="checkboxLabelOne" name="terms" value="1" class="sr-only" @change="checkboxToggle = !checkboxToggle" {{ old('terms') ? 'checked' : '' }} />
                                            <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                                                    'bg-transparent border-gray-300 dark:border-gray-700'"
                                                class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                    <i class="fa-solid fa-check text-[10px] text-white"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="inline-block font-normal text-gray-500 dark:text-gray-400">
                                            By creating an account means you agree to the
                                            <span class="text-gray-800 dark:text-white/90">
                                                Terms and Conditions,
                                            </span>
                                            and our
                                            <span class="text-gray-800 dark:text-white">
                                                Privacy Policy
                                            </span>
                                        </p>
                                    </label>
                                </div>
                                @error('terms')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Button -->
                            <div>
                                <button type="submit"
                                    class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                    Daftar
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-5">
                        <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                            Already have an account?
                            <a href="/login" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="register-image-panel bg-brand-950 overflow-hidden h-64 lg:fixed lg:inset-y-0 lg:right-0 lg:block lg:h-screen lg:w-1/2 dark:bg-white/5">
            <img src="{{ asset('images/guest/jakarta-indonesia.jpg') }}" alt="Jakarta" class="block h-full w-full object-cover">
        </div>
        <!-- Toggler -->
        <div class="fixed right-6 bottom-6 z-50">
            <button
                class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors"
                @click.prevent="$store.theme.toggle()">
                <i class="fa-solid fa-sun hidden text-xl dark:block"></i>
                <i class="fa-solid fa-moon text-xl dark:hidden"></i>
            </button>
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
            title: 'Registrasi gagal',
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