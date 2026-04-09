@extends('layouts.app')

@section('title', 'Simulasi Cicilan')

@section('content')
@php
$requestedAmount = $filters['jumlah_pinjaman'] !== '' ? (float) $filters['jumlah_pinjaman'] : null;
$exceedsLimit = $selectedSnapshot && $requestedAmount !== null && $requestedAmount > (float) $selectedSnapshot['maksimal_pinjaman'];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Simulasi Cicilan Pinjaman</h1>
            <p class="mt-2 text-sm text-slate-500">Hitung estimasi cicilan berdasarkan plafon pinjaman, tenor, dan tagihan tetap tanggal {{ $paymentDay }}.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pinjaman.pengajuan') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Pengajuan Pinjaman
            </a>
            <a href="{{ route('pinjaman.status-pembayaran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Status Pembayaran
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Form Simulasi</h2>

        <form action="{{ route('pinjaman.simulasi') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-4">
            @unless ($isAnggotaView)
            <div>
                <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Pilih anggota</option>
                    @foreach ($anggotaOptions as $anggota)
                    <option value="{{ $anggota->id }}" @selected($filters['anggota_id']===(string) $anggota->id)>{{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="anggota_id" value="{{ $filters['anggota_id'] }}">
            @endunless

            <div>
                <label for="jumlah_pinjaman" class="mb-2 block text-sm font-medium text-slate-700">Jumlah Pinjaman</label>
                <input type="hidden" id="jumlah_pinjaman" name="jumlah_pinjaman" value="{{ $filters['jumlah_pinjaman'] }}">
                <input type="text" inputmode="numeric" id="jumlah_pinjaman_display"
                    value="{{ $filters['jumlah_pinjaman'] !== '' ? 'Rp ' . number_format((float) $filters['jumlah_pinjaman'], 0, ',', '.') : '' }}"
                    data-currency-input="idr" data-currency-target="jumlah_pinjaman"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    placeholder="Rp 0">
            </div>

            <div>
                <label for="tenor_bulan" class="mb-2 block text-sm font-medium text-slate-700">Tenor</label>
                <input type="number" min="1" max="60" id="tenor_bulan" name="tenor_bulan" value="{{ $filters['tenor_bulan'] ?: 6 }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div>
                <label for="tanggal_pinjaman" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pinjaman</label>
                <input type="date" id="tanggal_pinjaman" name="tanggal_pinjaman" value="{{ $filters['tanggal_pinjaman'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div class="md:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                    Hitung Simulasi
                </button>
                <a href="{{ route('pinjaman.simulasi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if ($selectedSnapshot)
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Saldo Tabungan</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-900">Rp {{ number_format((float) $selectedSnapshot['saldo_tabungan'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-sky-700">Maksimal Pinjaman</p>
            <p class="mt-3 text-2xl font-semibold text-sky-900">Rp {{ number_format((float) $selectedSnapshot['maksimal_pinjaman'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Bunga per Bulan</p>
            <p class="mt-3 text-2xl font-semibold text-amber-900">Rp {{ number_format($monthlyInterest, 0, ',', '.') }}</p>
        </div>
    </div>
    @endif

    @if ($exceedsLimit)
    <div class="rounded-3xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-700 shadow-sm">
        Nominal yang disimulasikan melebihi batas 90% saldo tabungan anggota.
    </div>
    @endif

    @if ($simulation)
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Angsuran Pertama</p>
            <p class="mt-3 text-xl font-semibold text-slate-900">{{ $simulation['first_due_date']->translatedFormat('d M Y') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Jatuh Tempo Akhir</p>
            <p class="mt-3 text-xl font-semibold text-slate-900">{{ $simulation['final_due_date']->translatedFormat('d M Y') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Bunga</p>
            <p class="mt-3 text-xl font-semibold text-slate-900">Rp {{ number_format((float) $simulation['total_interest'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Tagihan</p>
            <p class="mt-3 text-xl font-semibold text-slate-900">Rp {{ number_format((float) $simulation['total_payment'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Jadwal Simulasi Angsuran</h2>
        <p class="mt-1 text-sm text-slate-500">Contoh perhitungan dengan bunga tetap Rp {{ number_format($monthlyInterest, 0, ',', '.') }} per bulan.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Angsuran</th>
                        <th class="py-3 pr-4 font-medium">Jatuh Tempo</th>
                        <th class="py-3 pr-4 font-medium">Pokok</th>
                        <th class="py-3 pr-4 font-medium">Bunga</th>
                        <th class="py-3 pr-4 font-medium">Tagihan</th>
                        <th class="py-3 font-medium text-right">Sisa Pokok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($simulation['schedule'] as $row)
                    <tr>
                        <td class="py-3 pr-4 text-slate-900">Ke-{{ $row['angsuran_ke'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $row['tanggal_jatuh_tempo']->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $row['pokok'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $row['bunga'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $row['jumlah_tagihan'], 0, ',', '.') }}</td>
                        <td class="py-3 text-right font-medium text-slate-900">Rp {{ number_format((float) $row['sisa_pokok'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
        Isi form simulasi terlebih dahulu untuk melihat jadwal angsuran.
    </div>
    @endif
</div>
@endsection