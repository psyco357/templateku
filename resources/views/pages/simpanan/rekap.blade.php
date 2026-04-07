@extends('layouts.app')

@section('title', 'Rekap Saldo Simpanan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Rekap Saldo Simpanan</h1>
            <p class="mt-2 text-sm text-slate-500">Ringkasan saldo per anggota dan per jenis simpanan pada {{ $koperasi->nama_koperasi }}.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @unless ($isAnggotaView)
            <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Input Transaksi
            </a>
            @endunless
            <a href="{{ route('simpanan.rekap-saldo.export', request()->query()) }}" class="inline-flex items-center rounded-xl border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                Export Excel
            </a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Saldo</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format((float) $summary['total_saldo'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Anggota Tercakup</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_anggota']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Jenis Simpanan</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_jenis']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Filter Rekap</h2>
        <form action="{{ route('simpanan.rekap-saldo') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
            @unless ($isAnggotaView)
            <div>
                <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua anggota</option>
                    @foreach ($anggotaOptions as $anggota)
                    <option value="{{ $anggota->id }}" @selected($filters['anggota_id']===(string) $anggota->id)>
                        {{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endunless

            <div>
                <label for="jenis_simpanan_id" class="mb-2 block text-sm font-medium text-slate-700">Jenis simpanan</label>
                <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua jenis</option>
                    @foreach ($jenisSimpanan as $jenis)
                    <option value="{{ $jenis->id }}" @selected($filters['jenis_simpanan_id']===(string) $jenis->id)>{{ $jenis->nama_jenis }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Terapkan Filter
                </button>
                <a href="{{ route('simpanan.rekap-saldo') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Tabel Rekap Saldo</h2>
        <p class="mt-1 text-sm text-slate-500">Saldo dihitung dari akumulasi setoran dikurangi penarikan.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">No. Anggota</th>
                        <th class="py-3 pr-4 font-medium">Nama Anggota</th>
                        <th class="py-3 pr-4 font-medium">Kode Jenis</th>
                        <th class="py-3 pr-4 font-medium">Jenis Simpanan</th>
                        <th class="py-3 text-right font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rekap as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->no_anggota }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $item->nama_lengkap }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->kode_jenis }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->nama_jenis }}</td>
                        <td class="py-3 text-right font-medium {{ (float) $item->saldo < 0 ? 'text-rose-700' : 'text-slate-900' }}">
                            Rp {{ number_format((float) $item->saldo, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-500">Belum ada data saldo simpanan untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection