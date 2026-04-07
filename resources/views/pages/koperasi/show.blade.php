@extends('layouts.app')

@section('title', 'Master Koperasi')

@php
$statusOptions = [
'aktif' => 'Aktif',
'nonaktif' => 'Nonaktif',
];
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Master Data Koperasi</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola identitas koperasi dan generate periode buku tahunan berdasarkan tanggal tutup buku Hijriah.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Profil Koperasi</h2>
            <p class="mt-1 text-sm text-slate-500">Data ini dipakai sebagai identitas utama koperasi dan pengaturan tutup buku.</p>

            <form action="{{ route('koperasi.update') }}" method="POST" class="mt-6 grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')

                <div>
                    <label for="kode_koperasi" class="mb-2 block text-sm font-medium text-slate-700">Kode koperasi</label>
                    <input
                        type="text"
                        id="kode_koperasi"
                        name="kode_koperasi"
                        value="{{ old('kode_koperasi', $koperasi->kode_koperasi) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('kode_koperasi')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_koperasi" class="mb-2 block text-sm font-medium text-slate-700">Nama koperasi</label>
                    <input
                        type="text"
                        id="nama_koperasi"
                        name="nama_koperasi"
                        value="{{ old('nama_koperasi', $koperasi->nama_koperasi) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('nama_koperasi')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nomor_badan_hukum" class="mb-2 block text-sm font-medium text-slate-700">Nomor badan hukum</label>
                    <input
                        type="text"
                        id="nomor_badan_hukum"
                        name="nomor_badan_hukum"
                        value="{{ old('nomor_badan_hukum', $koperasi->nomor_badan_hukum) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('nomor_badan_hukum')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_berdiri" class="mb-2 block text-sm font-medium text-slate-700">Tanggal berdiri</label>
                    <input
                        type="date"
                        id="tanggal_berdiri"
                        name="tanggal_berdiri"
                        value="{{ old('tanggal_berdiri', optional($koperasi->tanggal_berdiri)->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('tanggal_berdiri')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $koperasi->email) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('email')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telepon" class="mb-2 block text-sm font-medium text-slate-700">Telepon</label>
                    <input
                        type="text"
                        id="telepon"
                        name="telepon"
                        value="{{ old('telepon', $koperasi->telepon) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('telepon')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ketua" class="mb-2 block text-sm font-medium text-slate-700">Ketua</label>
                    <input
                        type="text"
                        id="ketua"
                        name="ketua"
                        value="{{ old('ketua', $koperasi->ketua) }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('ketua')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $koperasi->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="alamat" class="mb-2 block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="4"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('alamat', $koperasi->alamat) }}</textarea>
                    @error('alamat')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="siklus_tutup_buku" class="mb-2 block text-sm font-medium text-slate-700">Siklus tutup buku</label>
                    <input type="hidden" name="siklus_tutup_buku" value="tahunan">
                    <input
                        type="text"
                        id="siklus_tutup_buku"
                        value="Tahunan"
                        disabled
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    @error('siklus_tutup_buku')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tutup_buku_bulan_hijriah" class="mb-2 block text-sm font-medium text-slate-700">Bulan Hijriah</label>
                        <select
                            id="tutup_buku_bulan_hijriah"
                            name="tutup_buku_bulan_hijriah"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @foreach ($hijriMonths as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" @selected((int) old('tutup_buku_bulan_hijriah', $koperasi->tutup_buku_bulan_hijriah) === $monthNumber)>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                        @error('tutup_buku_bulan_hijriah')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tutup_buku_hari_hijriah" class="mb-2 block text-sm font-medium text-slate-700">Hari Hijriah</label>
                        <input
                            type="number"
                            id="tutup_buku_hari_hijriah"
                            name="tutup_buku_hari_hijriah"
                            min="1"
                            max="30"
                            value="{{ old('tutup_buku_hari_hijriah', $koperasi->tutup_buku_hari_hijriah) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('tutup_buku_hari_hijriah')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                        Simpan master koperasi
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Generate Periode Buku</h2>
                <p class="mt-1 text-sm text-slate-500">Periode buku tahunan akan mengikuti tutup buku pada {{ $koperasi->tutup_buku_hari_hijriah }} {{ $hijriMonths[$koperasi->tutup_buku_bulan_hijriah] }} setiap tahun Hijriah.</p>

                <form action="{{ route('koperasi.periode-buku.generate') }}" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="start_hijri_year" class="mb-2 block text-sm font-medium text-slate-700">Tahun Hijriah awal</label>
                        <input
                            type="number"
                            id="start_hijri_year"
                            name="start_hijri_year"
                            min="1300"
                            value="{{ old('start_hijri_year', $defaultHijriYearRange['start']) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('start_hijri_year')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_hijri_year" class="mb-2 block text-sm font-medium text-slate-700">Tahun Hijriah akhir</label>
                        <input
                            type="number"
                            id="end_hijri_year"
                            name="end_hijri_year"
                            min="1300"
                            value="{{ old('end_hijri_year', $defaultHijriYearRange['end']) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('end_hijri_year')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">
                        Generate periode buku
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Periode Buku Tersimpan</h2>
                        <p class="mt-1 text-sm text-slate-500">Riwayat periode buku yang sudah dibuat otomatis.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-wide text-slate-600">
                        {{ $periodeBuku->count() }} periode
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($periodeBuku as $periode)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Tahun buku {{ $periode->tahun_buku }} H</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ optional($periode->tanggal_mulai)->translatedFormat('d M Y') }} -
                                    {{ optional($periode->tanggal_selesai)->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium uppercase tracking-wide text-amber-700">
                                {{ $periode->status }}
                            </span>
                        </div>

                        <div class="mt-3 grid gap-2 text-sm text-slate-600">
                            <p>Tutup buku: {{ optional($periode->tanggal_tutup_buku)->translatedFormat('d M Y') }}</p>
                            <p>Patokan Hijriah: {{ $periode->tutup_buku_hari_hijriah }} {{ $hijriMonths[$periode->tutup_buku_bulan_hijriah] }}</p>
                            @if ($periode->catatan)
                            <p>{{ $periode->catatan }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-5 text-sm text-slate-500">
                        Belum ada periode buku. Generate dulu berdasarkan tanggal tutup buku Hijriah di atas.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection