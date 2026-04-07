@extends('layout.app-guest')

@section('content')

<x-layout.header-guest />

{{-- Hero Slider --}}
<section class="relative h-[600px] overflow-hidden mt-16">
    <div x-data="{ 
        currentSlide: 0,
        slides: [
            { img: 'https://placehold.co/1920x600/4f46e5/ffffff?text=Slide+1', title: 'Solusi Keuangan Terpercaya' },
            { img: 'https://placehold.co/1920x600/7c3aed/ffffff?text=Slide+2', title: 'Melayani Dengan Hati' },
            { img: 'https://placehold.co/1920x600/2563eb/ffffff?text=Slide+3', title: 'Untuk Kesejahteraan Bersama' }
        ],
        autoplay: null,
        init() {
            this.autoplay = setInterval(() => {
                this.next();
            }, 5000);
        },
        next() {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        },
        prev() {
            this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        }
    }" class="relative h-full">
        {{-- Slides --}}
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="currentSlide === index"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="absolute inset-0 w-full h-full">
                <img :src="slide.img" :alt="slide.title" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                    <h2 x-text="slide.title" class="text-white text-5xl font-bold"></h2>
                </div>
            </div>
        </template>

        {{-- Navigation Buttons --}}
        <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-zinc-900 rounded-full p-3 transition z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-zinc-900 rounded-full p-3 transition z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        {{-- Indicators --}}
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
            <template x-for="(slide, index) in slides" :key="index">
                <button @click="currentSlide = index"
                    :class="currentSlide === index ? 'bg-white' : 'bg-white/50'"
                    class="w-3 h-3 rounded-full transition"></button>
            </template>
        </div>
    </div>
</section>

{{-- Jenis Usaha / Layanan --}}
<section class="py-16 px-4 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-center text-3xl font-bold text-zinc-900 dark:text-white mb-12">
            Jenis Usaha
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            {{-- Card 1 --}}
            <a href="/jenis-usaha/pinjaman" class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                        Pinjaman Uang
                    </h3>
                </div>
            </a>

            {{-- Card 2 --}}
            <a href="/jenis-usaha/motor" class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition">
                        Sepeda Motor
                    </h3>
                </div>
            </a>

            {{-- Card 3 --}}
            <a href="/jenis-usaha/elektronik" class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                        Barang Elektronik
                    </h3>
                </div>
            </a>

            {{-- Card 4 --}}
            <a href="/jenis-usaha/market" class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition">
                        Market
                    </h3>
                </div>
            </a>

            {{-- Card 5 --}}
            <a href="/jenis-usaha/emaggot" class="group block bg-white dark:bg-zinc-800 rounded-xl shadow-lg hover:shadow-xl transition overflow-hidden">
                <div class="aspect-square bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-bold text-zinc-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
                        eMaggot
                    </h3>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- Tentang Kami --}}
<section class="py-20 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Image --}}
            <div class="order-2 lg:order-1">
                <img src="https://placehold.co/600x400/4f46e5/ffffff?text=About+Us" alt="Tentang Kami" class="rounded-2xl shadow-2xl w-full">
            </div>

            {{-- Content --}}
            <div class="order-1 lg:order-2">
                <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white mb-6">
                    Tentang Kami
                </h2>
                <div class="space-y-4 text-zinc-600 dark:text-zinc-400 text-justify leading-relaxed">
                    <p>
                        Koperasi {{ config('app.name') }} merupakan koperasi karyawan yang dibentuk dengan tujuan untuk meningkatkan
                        kesejahteraan bagi para anggotanya. Anggota Koperasi {{ config('app.name') }} terdiri dari karyawan
                        aktif dan mitra kerja perusahaan.
                    </p>
                    <p>
                        Koperasi {{ config('app.name') }} telah ditetapkan dalam Keputusan Menteri Hukum Republik
                        Indonesia dengan nomor registrasi yang sah dan telah memiliki Sertifikat Nomor Induk Koperasi (NIK)
                        dari Kementerian Koperasi Republik Indonesia serta telah memiliki Perizinan
                        Berusaha Berbasis Risiko dengan Nomor Induk Berusaha dari Menteri Investasi dan Hillirisasi/
                        Kepala Badan Koordinasi Penanaman Modal Republik Indonesia.
                    </p>
                    <p>
                        Dengan komitmen penuh untuk memberikan pelayanan terbaik, kami terus berinovasi dalam menyediakan
                        berbagai produk dan layanan yang mendukung kesejahteraan anggota, mulai dari pinjaman, simpanan,
                        hingga berbagai usaha yang bermanfaat bagi seluruh anggota koperasi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Section --}}
<section id="harga" class="py-20 px-4 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                Semua fitur hebat hanya mulai dari Rp6.900/bulan
            </h2>
            <p class="text-xl text-zinc-600 dark:text-zinc-400">
                Mulai dari payroll, absensi, reimbursement, kelola PPh 21, kelola BPJS hingga ESS semua ada.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Paket GRATIS --}}
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border-2 border-zinc-200 dark:border-zinc-700">
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">GRATIS</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">Rp 0</span>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-2">unlimited karyawan</p>
                </div>
                <a href="{{ route('register') }}" class="block w-full py-3 px-4 bg-zinc-900 dark:bg-zinc-700 text-white text-center rounded-lg font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-600 transition mb-6">
                    Coba Gratis
                </a>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Absensi Android & iOS</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Validasi Absensi GPS</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Manajemen Karyawan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Cuti & Izin</span>
                    </li>
                </ul>
            </div>

            {{-- Paket PRO --}}
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border-2 border-zinc-200 dark:border-zinc-700">
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">PRO</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">Rp 6.900</span>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-2">/bulan/karyawan</p>
                </div>
                <a href="{{ route('register') }}" class="block w-full py-3 px-4 bg-zinc-900 dark:bg-zinc-700 text-white text-center rounded-lg font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-600 transition mb-6">
                    Coba Gratis
                </a>
                <p class="text-sm font-semibold text-zinc-900 dark:text-white mb-3">Semua fitur GRATIS, plus:</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Payroll Karyawan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Hitung Lembur & THR Otomatis</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Jadwal & Shift Kerja</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Kasbon Karyawan</span>
                    </li>
                </ul>
            </div>

            {{-- Paket ELITE --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-2xl p-8 border-2 border-transparent relative transform scale-105">
                <div class="absolute top-0 right-0 bg-yellow-400 text-zinc-900 text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-xl">
                    Popular
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">ELITE</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-white">Rp 14.900</span>
                    <p class="text-indigo-100 mt-2">/bulan/karyawan</p>
                </div>
                <a href="{{ route('register') }}" class="block w-full py-3 px-4 bg-white text-indigo-600 text-center rounded-lg font-semibold hover:bg-indigo-50 transition mb-6">
                    Coba Gratis
                </a>
                <p class="text-sm font-semibold text-white mb-3">Semua fitur PRO, plus:</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Hitung pajak PPh 21 & BPJS Otomatis</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Statistik Payroll & HRIS</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Approval Bertingkat</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Reimbursement</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-white">Face Recognition</span>
                    </li>
                </ul>
            </div>

            {{-- Paket CHAMPION --}}
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg p-8 border-2 border-zinc-200 dark:border-zinc-700">
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">CHAMPION</h3>
                <div class="mb-6">
                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">Rp 19.900</span>
                    <p class="text-zinc-600 dark:text-zinc-400 mt-2">/bulan/karyawan</p>
                </div>
                <a href="{{ route('register') }}" class="block w-full py-3 px-4 bg-zinc-900 dark:bg-zinc-700 text-white text-center rounded-lg font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-600 transition mb-6">
                    Coba Gratis
                </a>
                <p class="text-sm font-semibold text-zinc-900 dark:text-white mb-3">Semua fitur ELITE, plus:</p>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Key Performance Indicators (KPI)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Rekrutmen Karyawan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Petty Cash Karyawan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-zinc-600 dark:text-zinc-400">Liveness Detection</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials --}}
<section id="testimoni" class="py-20 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                Mereka yang sudah menggunakan {{ config('app.name') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">JD</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">John Doe</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">HR Manager, PT Example</p>
                    </div>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400">
                    "Karyawan pada suka menggunakan {{ config('app.name') }}, karena mau pengajuan reimbursement atau klaim lembur jadi tidak repot lagi. Sistemnya mudah digunakan!"
                </p>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-8 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-green-600 dark:text-green-400">SA</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-zinc-900 dark:text-white">Sarah Andini</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Owner, Toko Retail</p>
                    </div>
                </div>
                <p class="text-zinc-600 dark:text-zinc-400">
                    "Mengurus payroll di akhir bulan bisa sangat melelahkan. Tapi semenjak menggunakan {{ config('app.name') }}, cukup klik-klik saja langsung beres!"
                </p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="py-20 px-4 bg-gray-50 dark:bg-zinc-900">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white mb-4">
                Pertanyaan yang Sering Ditanyakan
            </h2>
        </div>

        <div class="space-y-4">
            {{-- FAQ Item 1 --}}
            <details class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <summary class="cursor-pointer p-6 font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                    Apakah {{ config('app.name') }} memiliki paket gratis?
                </summary>
                <div class="px-6 pb-6 text-zinc-600 dark:text-zinc-400">
                    Ya, kami menyediakan paket gratis dengan fitur dasar seperti absensi online, manajemen karyawan, dan cuti/izin untuk unlimited karyawan.
                </div>
            </details>

            {{-- FAQ Item 2 --}}
            <details class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <summary class="cursor-pointer p-6 font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                    Apa yang dibutuhkan untuk menggunakan {{ config('app.name') }}?
                </summary>
                <div class="px-6 pb-6 text-zinc-600 dark:text-zinc-400">
                    Anda hanya membutuhkan koneksi internet dan perangkat (smartphone/tablet/komputer). Aplikasi kami tersedia di Web, Android, dan iOS.
                </div>
            </details>

            {{-- FAQ Item 3 --}}
            <details class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <summary class="cursor-pointer p-6 font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                    Bagaimana perhitungan biaya {{ config('app.name') }}?
                </summary>
                <div class="px-6 pb-6 text-zinc-600 dark:text-zinc-400">
                    Biaya dihitung per karyawan per bulan. Anda hanya membayar untuk karyawan aktif yang menggunakan sistem. Tidak ada biaya tersembunyi!
                </div>
            </details>

            {{-- FAQ Item 4 --}}
            <details class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <summary class="cursor-pointer p-6 font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                    Amankah menggunakan {{ config('app.name') }}?
                </summary>
                <div class="px-6 pb-6 text-zinc-600 dark:text-zinc-400">
                    Sangat aman! Data Anda dienkripsi dengan standar internasional dan disimpan di server yang aman. Kami juga melakukan backup rutin untuk memastikan data Anda tetap tersimpan.
                </div>
            </details>

            {{-- FAQ Item 5 --}}
            <details class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <summary class="cursor-pointer p-6 font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                    Apakah {{ config('app.name') }} cocok untuk bisnis kecil?
                </summary>
                <div class="px-6 pb-6 text-zinc-600 dark:text-zinc-400">
                    Sangat cocok! Kami memiliki paket khusus untuk berbagai ukuran bisnis, mulai dari paket gratis untuk startup hingga paket enterprise untuk perusahaan besar.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA Final --}}
<section class="py-20 px-4 bg-gradient-to-r from-indigo-600 to-purple-600">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Kelola payroll, HR, dan absensi menggunakan {{ config('app.name') }}
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="flex items-center justify-center space-x-3 text-white">
                <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">Menghemat pengeluaran HR 37%</span>
            </div>
            <div class="flex items-center justify-center space-x-3 text-white">
                <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">Hemat 80% waktu tim HR</span>
            </div>
            <div class="flex items-center justify-center space-x-3 text-white">
                <svg class="w-6 h-6 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">Produktivitas karyawan meningkat 94%</span>
            </div>
        </div>
        <a href="{{ route('register') }}" class="inline-block px-8 py-4 text-lg font-semibold text-indigo-600 bg-white rounded-lg hover:bg-gray-50 transition shadow-lg">
            Coba Gratis 14 Hari
        </a>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-zinc-900 dark:bg-black text-zinc-400 py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            {{-- Column 1 --}}
            <div>
                <div class="flex items-center space-x-2 mb-4">

                    <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
                </div>
                <p class="text-sm">
                    Software payroll dan absensi terlengkap untuk mengelola karyawan dengan mudah.
                </p>
            </div>

            {{-- Column 2 --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Tentang Kami</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-white transition">Tentang</a></li>
                    <li><a href="#" class="hover:text-white transition">Blog</a></li>
                    <li><a href="#" class="hover:text-white transition">Karir</a></li>
                    <li><a href="#" class="hover:text-white transition">Keamanan</a></li>
                </ul>
            </div>

            {{-- Column 3 --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Layanan</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-white transition">Penggajian & THR</a></li>
                    <li><a href="#" class="hover:text-white transition">Kelola PPh 21</a></li>
                    <li><a href="#" class="hover:text-white transition">Kelola BPJS</a></li>
                    <li><a href="#" class="hover:text-white transition">Absensi Online</a></li>
                </ul>
            </div>

            {{-- Column 4 --}}
            <div>
                <h4 class="text-white font-semibold mb-4">Kontak</h4>
                <ul class="space-y-2 text-sm">
                    <li>Email: hello@{{ strtolower(config('app.name')) }}.com</li>
                    <li>Telp: 0895 4006 30000</li>
                    <li class="pt-4">
                        <div class="flex space-x-4">
                            <a href="#" class="hover:text-white transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="#" class="hover:text-white transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg>
                            </a>
                            <a href="#" class="hover:text-white transition">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                                </svg>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-zinc-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-white transition">Kebijakan Privasi</a>
            </div>
        </div>
    </div>
</footer>

@endsection