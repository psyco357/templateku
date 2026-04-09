@extends('layouts.app')
@section('title', 'Tambah Anggota')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-8">
        <a href="{{ route('anggota.index') }}" class="text-sm font-medium text-orange-500 hover:text-orange-600">
            Kembali ke daftar anggota
        </a>
        <h3 class="mt-2 font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
            Tambah Anggota Baru
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
            Form ini akan membuat data baru di tabel users, profiles, dan anggota sekaligus sesuai struktur database yang sudah ada.
        </p>
    </div>

    <form method="POST" action="{{ route('anggota.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" id="auto_generate_no_anggota" name="auto_generate_no_anggota" value="{{ old('auto_generate_no_anggota', '1') }}">

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Data Login</h4>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('username')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="username_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="email_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input type="password" id="password" name="password" minlength="8"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="password_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <p id="password_confirmation_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Profil Anggota</h4>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="nama_lengkap" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('nama_lengkap')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="nama_lengkap_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="no_hp" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('no_hp')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="no_hp_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="tempat_lahir" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" maxlength="255"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('tempat_lahir')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="tempat_lahir_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="tanggal_lahir" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('tanggal_lahir')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="alamat" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('alamat') }}</textarea>
                    @error('alamat')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="bio" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                    <textarea id="bio" name="bio" rows="3" maxlength="255"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('bio') }}</textarea>
                    @error('bio')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="bio_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Data Keanggotaan</h4>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="no_anggota" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Anggota</label>
                    <div class="flex gap-2">
                        <input type="text" id="no_anggota" name="no_anggota" value="{{ old('no_anggota', $suggestedMemberNumber) }}" maxlength="255"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <button type="button" id="regenerate-member-number" data-url="{{ route('anggota.generate-member-number') }}"
                            class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-orange-500 px-4 text-sm font-medium text-orange-500 transition hover:bg-orange-50 dark:hover:bg-orange-500/10">
                            Generate Ulang
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Nomor anggota digenerate otomatis. Anda masih bisa mengubahnya bila perlu.</p>
                    @error('no_anggota')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="no_anggota_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="jabatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                    <select id="jabatan" name="jabatan"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Pilih jabatan</option>
                        <option value="pengurus" @selected(old('jabatan')==='pengurus' )>Pengurus</option>
                        <option value="anggota" @selected(old('jabatan')==='anggota' )>Anggota</option>
                    </select>
                    @error('jabatan')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status_keanggotaan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status Keanggotaan</label>
                    <select id="status_keanggotaan" name="status_keanggotaan" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @foreach ($availableStatuses as $status)
                        <option value="{{ $status }}" @selected(old('status_keanggotaan', \App\Models\AnggotaModel::STATUS_AKTIF)===$status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status_keanggotaan')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    <p id="status_keanggotaan_client_error" class="mt-1 hidden text-sm text-red-500"></p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="inline-flex h-11 items-center justify-center rounded-lg bg-orange-500 px-5 text-sm font-medium text-white transition hover:bg-orange-600">
                Simpan Anggota
            </button>
            <a href="{{ route('anggota.index') }}"
                class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.Swal.fire({
            icon: 'error',
            title: 'Tambah anggota gagal',
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
        const form = document.querySelector("form[action='{{ route('anggota.store') }}']");
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const fullNameInput = document.getElementById('nama_lengkap');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const phoneInput = document.getElementById('no_hp');
        const birthPlaceInput = document.getElementById('tempat_lahir');
        const bioInput = document.getElementById('bio');
        const statusInput = document.getElementById('status_keanggotaan');

        const errorElements = {
            username: document.getElementById('username_client_error'),
            email: document.getElementById('email_client_error'),
            namaLengkap: document.getElementById('nama_lengkap_client_error'),
            password: document.getElementById('password_client_error'),
            passwordConfirmation: document.getElementById('password_confirmation_client_error'),
            noHp: document.getElementById('no_hp_client_error'),
            tempatLahir: document.getElementById('tempat_lahir_client_error'),
            bio: document.getElementById('bio_client_error'),
            noAnggota: document.getElementById('no_anggota_client_error'),
            status: document.getElementById('status_keanggotaan_client_error'),
        };

        const alphaDashPattern = /^[A-Za-z0-9_-]+$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const showFieldError = (input, messageElement, message) => {
            if (!input || !messageElement) {
                return;
            }

            input.classList.remove('border-gray-300', 'dark:border-gray-700');
            input.classList.add('border-red-500');
            messageElement.textContent = message;
            messageElement.classList.remove('hidden');
        };

        const clearFieldError = (input, messageElement) => {
            if (!input || !messageElement) {
                return;
            }

            input.classList.remove('border-red-500');
            input.classList.add('border-gray-300', 'dark:border-gray-700');
            messageElement.textContent = '';
            messageElement.classList.add('hidden');
        };

        const validateMaxLength = (input, label, maxLength, messageElement) => {
            if (!input || !messageElement) {
                return true;
            }

            const value = input.value.trim();

            if (value !== '' && value.length > maxLength) {
                showFieldError(input, messageElement, `${label} tidak boleh lebih dari ${maxLength} karakter.`);
                return false;
            }

            clearFieldError(input, messageElement);
            return true;
        };

        const validateUsername = () => {
            if (!usernameInput || !errorElements.username) {
                return true;
            }

            const value = usernameInput.value.trim();

            if (value === '') {
                showFieldError(usernameInput, errorElements.username, 'Username wajib diisi.');
                return false;
            }

            if (value.length > 255) {
                showFieldError(usernameInput, errorElements.username, 'Username tidak boleh lebih dari 255 karakter.');
                return false;
            }

            if (!alphaDashPattern.test(value)) {
                showFieldError(usernameInput, errorElements.username, 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.');
                return false;
            }

            clearFieldError(usernameInput, errorElements.username);
            return true;
        };

        const validateEmail = () => {
            if (!emailInput || !errorElements.email) {
                return true;
            }

            const value = emailInput.value.trim();

            if (value === '') {
                showFieldError(emailInput, errorElements.email, 'Email wajib diisi.');
                return false;
            }

            if (value.length > 255) {
                showFieldError(emailInput, errorElements.email, 'Email tidak boleh lebih dari 255 karakter.');
                return false;
            }

            if (!emailPattern.test(value)) {
                showFieldError(emailInput, errorElements.email, 'Email harus berupa alamat email yang valid.');
                return false;
            }

            clearFieldError(emailInput, errorElements.email);
            return true;
        };

        const validateFullName = () => {
            if (!fullNameInput || !errorElements.namaLengkap) {
                return true;
            }

            const value = fullNameInput.value.trim();

            if (value === '') {
                showFieldError(fullNameInput, errorElements.namaLengkap, 'Nama lengkap wajib diisi.');
                return false;
            }

            if (value.length > 255) {
                showFieldError(fullNameInput, errorElements.namaLengkap, 'Nama lengkap tidak boleh lebih dari 255 karakter.');
                return false;
            }

            clearFieldError(fullNameInput, errorElements.namaLengkap);
            return true;
        };

        const validatePassword = () => {
            if (!passwordInput || !errorElements.password) {
                return true;
            }

            const value = passwordInput.value.trim();

            if (value === '') {
                showFieldError(passwordInput, errorElements.password, 'Password wajib diisi.');
                return false;
            }

            if (value.length < 8) {
                showFieldError(passwordInput, errorElements.password, 'Password minimal 8 karakter.');
                return false;
            }

            clearFieldError(passwordInput, errorElements.password);
            return true;
        };

        const validatePasswordConfirmation = () => {
            if (!passwordConfirmationInput || !errorElements.passwordConfirmation || !passwordInput) {
                return true;
            }

            const confirmationValue = passwordConfirmationInput.value.trim();

            if (confirmationValue === '') {
                showFieldError(passwordConfirmationInput, errorElements.passwordConfirmation, 'Konfirmasi password wajib diisi.');
                return false;
            }

            if (confirmationValue !== passwordInput.value) {
                showFieldError(passwordConfirmationInput, errorElements.passwordConfirmation, 'Konfirmasi password tidak sesuai.');
                return false;
            }

            clearFieldError(passwordConfirmationInput, errorElements.passwordConfirmation);
            return true;
        };

        const validateStatus = () => {
            if (!statusInput || !errorElements.status) {
                return true;
            }

            if (statusInput.value.trim() === '') {
                showFieldError(statusInput, errorElements.status, 'Status keanggotaan wajib dipilih.');
                return false;
            }

            clearFieldError(statusInput, errorElements.status);
            return true;
        };

        const validatePhone = () => validateMaxLength(phoneInput, 'Nomor HP', 255, errorElements.noHp);
        const validateBirthPlace = () => validateMaxLength(birthPlaceInput, 'Tempat lahir', 255, errorElements.tempatLahir);
        const validateBio = () => validateMaxLength(bioInput, 'Bio', 255, errorElements.bio);
        const validateMemberNumber = () => validateMaxLength(memberNumberInput, 'Nomor anggota', 255, errorElements.noAnggota);

        const fieldValidators = [{
                input: usernameInput,
                event: 'blur',
                validate: validateUsername,
                error: errorElements.username
            },
            {
                input: emailInput,
                event: 'blur',
                validate: validateEmail,
                error: errorElements.email
            },
            {
                input: fullNameInput,
                event: 'blur',
                validate: validateFullName,
                error: errorElements.namaLengkap
            },
            {
                input: passwordInput,
                event: 'blur',
                validate: validatePassword,
                error: errorElements.password
            },
            {
                input: passwordConfirmationInput,
                event: 'blur',
                validate: validatePasswordConfirmation,
                error: errorElements.passwordConfirmation
            },
            {
                input: phoneInput,
                event: 'blur',
                validate: validatePhone,
                error: errorElements.noHp
            },
            {
                input: birthPlaceInput,
                event: 'blur',
                validate: validateBirthPlace,
                error: errorElements.tempatLahir
            },
            {
                input: bioInput,
                event: 'blur',
                validate: validateBio,
                error: errorElements.bio
            },
            {
                input: memberNumberInput,
                event: 'blur',
                validate: validateMemberNumber,
                error: errorElements.noAnggota
            },
            {
                input: statusInput,
                event: 'change',
                validate: validateStatus,
                error: errorElements.status
            },
        ];

        fieldValidators.forEach(({
            input,
            event,
            validate,
            error
        }) => {
            input?.addEventListener(event, validate);

            input?.addEventListener('input', () => {
                if (!error?.classList.contains('hidden')) {
                    validate();
                }
            });
        });

        passwordInput?.addEventListener('input', () => {
            if (passwordConfirmationInput?.value) {
                validatePasswordConfirmation();
            }
        });

        form?.addEventListener('submit', (event) => {
            const validators = [
                validateUsername,
                validateEmail,
                validateFullName,
                validatePassword,
                validatePasswordConfirmation,
                validatePhone,
                validateBirthPlace,
                validateBio,
                validateMemberNumber,
                validateStatus,
            ];

            const isValid = validators.every((validator) => validator());

            if (!isValid) {
                event.preventDefault();
            }
        });

        memberNumberInput?.addEventListener('input', () => {
            autoGenerateInput.value = '0';
            if (!errorElements.noAnggota?.classList.contains('hidden')) {
                validateMemberNumber();
            }
        });

        if (!regenerateButton || !memberNumberInput || !autoGenerateInput) {
            return true;
        }

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