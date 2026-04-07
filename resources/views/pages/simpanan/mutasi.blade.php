@extends('layouts.app')

@section('title', 'Buku Simpanan Anggota')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Buku Simpanan Anggota</h1>
            <p class="mt-2 text-sm text-slate-500">Lihat mutasi transaksi per anggota dan cetak buku simpanan sesuai periode yang dipilih.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($selectedAnggota)
            <a href="{{ route('simpanan.mutasi.print', request()->query()) }}" target="_blank" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                Cetak Buku Simpanan
            </a>
            <a href="{{ route('simpanan.mutasi.export', request()->query()) }}" class="inline-flex items-center rounded-xl border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                Export Excel
            </a>
            @endif
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Filter Buku Simpanan</h2>

        <form action="{{ route('simpanan.mutasi') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-4">
            @unless ($isAnggotaView)
            <div>
                <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Pilih anggota</option>
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

            <div>
                <label for="start_date" class="mb-2 block text-sm font-medium text-slate-700">Tanggal awal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div>
                <label for="end_date" class="mb-2 block text-sm font-medium text-slate-700">Tanggal akhir</label>
                <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div class="md:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">
                    Tampilkan Buku Simpanan
                </button>
                <a href="{{ route('simpanan.mutasi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if ($selectedAnggota)
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Anggota</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $selectedAnggota->profile?->nama_lengkap ?? '-' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $selectedAnggota->no_anggota }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Saldo Awal</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($openingBalance, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Saldo Akhir</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($closingBalance, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Mutasi Simpanan</h2>
        <p class="mt-1 text-sm text-slate-500">Riwayat transaksi posted dengan saldo berjalan.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">No. Bukti</th>
                        <th class="py-3 pr-4 font-medium">Jenis</th>
                        <th class="py-3 pr-4 font-medium">Keterangan</th>
                        <th class="py-3 pr-4 font-medium">Debit</th>
                        <th class="py-3 pr-4 font-medium">Kredit</th>
                        <th class="py-3 text-right font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="5" class="py-3 pr-4 font-medium text-slate-500">Saldo awal</td>
                        <td class="py-3 text-right font-medium text-slate-900">Rp {{ number_format($openingBalance, 0, ',', '.') }}</td>
                    </tr>
                    @forelse ($transactions as $transaction)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $transaction->tanggal_transaksi?->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $transaction->no_bukti ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $transaction->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $transaction->keterangan ?: '-' }}</td>
                        <td class="py-3 pr-4 text-emerald-700">{{ $transaction->jumlah > 0 ? 'Rp ' . number_format((float) $transaction->jumlah, 0, ',', '.') : '-' }}</td>
                        <td class="py-3 pr-4 text-rose-700">{{ $transaction->jumlah < 0 ? 'Rp ' . number_format(abs((float) $transaction->jumlah), 0, ',', '.') : '-' }}</td>
                        <td class="py-3 text-right font-medium text-slate-900">Rp {{ number_format((float) $transaction->running_balance, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Belum ada transaksi posted untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
        Pilih anggota terlebih dulu untuk menampilkan buku simpanan.
    </div>
    @endif
</div>
@endsection