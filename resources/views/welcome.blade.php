@extends('layouts.app-guest')

@section('content')
@php
$theme = $landingPage['theme'];
$galleryItems = [
['title' => 'RAT Tahunan', 'caption' => 'Agenda evaluasi dan penyusunan target baru koperasi.', 'class' => 'from-slate-800 to-slate-600'],
['title' => 'Layanan Simpanan', 'caption' => 'Transaksi anggota dilayani lebih cepat dan transparan.', 'class' => 'from-cyan-700 to-sky-500'],
['title' => 'Usaha Anggota', 'caption' => 'Dukungan usaha produktif berbasis kebutuhan komunitas.', 'class' => 'from-emerald-700 to-emerald-500'],
['title' => 'Program Sosial', 'caption' => 'Kegiatan kebersamaan dan kontribusi untuk lingkungan.', 'class' => 'from-rose-700 to-orange-500'],
];
$featureCards = [
['title' => 'Simpanan Anggota', 'description' => 'Kelola jenis simpanan, transaksi, dan rekap saldo.', 'icon' => 'S'],
['title' => 'Pinjaman', 'description' => 'Persiapkan pengajuan, cicilan, dan status pembayaran.', 'icon' => 'P'],
['title' => 'Data Anggota', 'description' => 'Pantau profil anggota, status, dan nomor keanggotaan.', 'icon' => 'A'],
['title' => 'Laporan Koperasi', 'description' => 'Siapkan neraca, arus kas, dan distribusi SHU.', 'icon' => 'L'],
];
@endphp

<x-layout.header-guest :landing-page="$landingPage" />

<section class="relative overflow-hidden pt-16 {{ $theme['hero_gradient_class'] }}">
    <img src="{{ $landingPage['hero_image'] }}" alt="Hero Banner" class="absolute inset-0 h-full w-full object-cover opacity-50">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.18),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.12),transparent_30%)]"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-[420px] items-center py-10 sm:min-h-[480px] sm:py-12 lg:min-h-[540px] lg:py-14">
            <div class="max-w-4xl text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/75 sm:text-sm">Koperasi Digital</p>
                <h1 class="mt-4 whitespace-pre-line text-3xl font-bold leading-tight sm:text-4xl md:text-5xl lg:mt-4 lg:text-6xl">
                    {{ $landingPage['hero_title'] }}
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-white/85 sm:text-lg sm:leading-8 md:text-xl">
                    {{ $landingPage['hero_subtitle'] }}
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('login') }}" class="inline-flex h-12 items-center justify-center rounded-full px-6 text-sm font-semibold text-white transition {{ $theme['accent_bg_class'] }}">
                        Masuk ke Sistem
                    </a>
                    <a href="#tentang-kami" class="inline-flex h-12 items-center justify-center rounded-full border border-white/30 bg-white/10 px-6 text-sm font-semibold text-white transition hover:bg-white/20">
                        Lihat Profil Koperasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="px-4 py-5 sm:py-6 {{ $theme['surface_alt_class'] }}">
    <div class="mx-auto grid max-w-7xl gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-white px-5 py-5 shadow-sm ring-1 ring-black/5">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 sm:text-sm">Tema Aktif</p>
            <p class="mt-2 text-base font-semibold text-gray-900 sm:text-lg">{{ $theme['label'] }}</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-5 shadow-sm ring-1 ring-black/5">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 sm:text-sm">Akses Anggota</p>
            <p class="mt-2 text-base font-semibold text-gray-900 sm:text-lg">Simpan pinjam, transaksi, dan akun anggota</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-5 shadow-sm ring-1 ring-black/5">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 sm:text-sm">Operasional</p>
            <p class="mt-2 text-base font-semibold text-gray-900 sm:text-lg">Monitoring koperasi yang lebih rapi dan transparan</p>
        </div>
    </div>
</section>

<section id="layanan-utama" class="px-4 py-14 sm:py-16 {{ $theme['surface_class'] }}">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">Layanan Utama</h2>
        <p class="mx-auto mt-3 mb-10 max-w-2xl text-center text-sm leading-7 text-gray-600 sm:mb-12">
            Halaman depan ini dipakai sebagai ringkasan cepat layanan koperasi yang paling sering diakses anggota dan pengurus.
        </p>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($featureCards as $card)
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 sm:p-6">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-bold text-white {{ $theme['accent_bg_class'] }}">
                    {{ $card['icon'] }}
                </span>
                <h3 class="mt-5 text-lg font-semibold text-gray-900">{{ $card['title'] }}</h3>
                <p class="mt-2 text-sm leading-7 text-gray-600">{{ $card['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section id="tentang-kami" class="px-4 py-16 sm:py-20">
    <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-12 lg:items-center">
            <div class="order-2 lg:order-1">
                <div class="relative overflow-hidden rounded-[28px] p-6 text-white shadow-2xl sm:rounded-[32px] sm:p-8 {{ $theme['hero_gradient_class'] }}">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.18),transparent_30%)]"></div>
                    <div class="relative">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/70 sm:text-sm">Tentang Koperasi</p>
                        <h3 class="mt-4 text-2xl font-semibold sm:text-3xl">{{ $landingPage['koperasi_name'] }}</h3>
                        <p class="mt-5 text-sm leading-7 text-white/85">{{ $landingPage['contact_address'] }}</p>
                        <div class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                                <p class="text-xs uppercase tracking-[0.2em] text-white/65">Email</p>
                                <p class="mt-2 break-all text-sm font-medium">{{ $landingPage['contact_email'] }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-sm">
                                <p class="text-xs uppercase tracking-[0.2em] text-white/65">Telepon</p>
                                <p class="mt-2 text-sm font-medium">{{ $landingPage['contact_phone'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <h2 class="mb-5 text-2xl font-bold text-zinc-900 sm:mb-6 sm:text-3xl md:text-4xl">
                    {{ $landingPage['about_title'] }}
                </h2>
                <div class="space-y-4 text-left leading-7 text-zinc-600 sm:text-justify">
                    @foreach (preg_split('/\r\n|\r|\n/', $landingPage['about_description']) as $paragraph)
                    @if (trim($paragraph) !== '')
                    <p>{{ $paragraph }}</p>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section id="sorotan-kegiatan" class="px-4 py-16 sm:py-20 {{ $theme['surface_alt_class'] }}">
    <div class="mx-auto max-w-7xl">
        <h2 class="mb-10 text-center text-2xl font-bold text-zinc-900 sm:mb-12 sm:text-3xl md:text-4xl">
            Sorotan Kegiatan
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($galleryItems as $index => $item)
            <div class="group overflow-hidden rounded-3xl bg-white shadow-lg transition hover:-translate-y-1 hover:shadow-2xl">
                <div class="flex min-h-[260px] items-end bg-gradient-to-br {{ $item['class'] }} p-5 text-white sm:min-h-[300px]">
                    <div>
                        <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-sm">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-4 text-xl font-semibold">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-white/80">{{ $item['caption'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<footer class="bg-gray-900 px-4 py-14 text-zinc-400 sm:py-16">
    <div class="mx-auto max-w-7xl">
        <div class="mb-10 grid grid-cols-1 gap-10 md:grid-cols-3 md:gap-12 md:mb-12">
            <div class="md:col-span-1">
                <div class="mb-4 flex items-center space-x-2">
                    <x-logo.app-logo class="h-10 w-10 {{ $theme['accent_text_class'] }}" />
                    <span class="text-xl font-bold text-white sm:text-2xl">{{ $landingPage['koperasi_name'] }}</span>
                </div>
                <p class="text-sm leading-7">
                    {{ $landingPage['hero_subtitle'] }}
                </p>
            </div>

            <div class="md:col-span-2">
                <h4 class="mb-4 text-lg font-semibold text-white">Hubungi Kami</h4>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3 text-sm leading-7">
                        <p class="font-medium text-white">Alamat</p>
                        <p>{{ $landingPage['contact_address'] }}</p>
                    </div>
                    <div class="space-y-3 text-sm leading-7">
                        <div>
                            <p class="font-medium text-white">Telepon</p>
                            <p>{{ $landingPage['contact_phone'] }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-white">Email</p>
                            <p class="break-all">{{ $landingPage['contact_email'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-start justify-between gap-4 border-t border-zinc-800 pt-8 text-sm md:flex-row md:items-center">
            <p>&copy; {{ date('Y') }} {{ $landingPage['koperasi_name'] }}. All rights reserved.</p>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <a href="{{ route('login') }}" class="transition hover:text-white">Masuk</a>
                <a href="{{ route('register') }}" class="transition hover:text-white">Daftar</a>
                <a href="{{ route('password.request') }}" class="transition hover:text-white">Bantuan Login</a>
            </div>
        </div>
    </div>
</footer>
@endsection