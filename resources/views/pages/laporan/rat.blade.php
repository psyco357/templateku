@extends('layouts.app')

@section('title', 'Rekap RAT')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Rekap RAT</h1>
            <p class="mt-2 text-sm text-slate-500">Ringkasan indikator utama koperasi untuk kebutuhan Rapat Anggota Tahunan.</p>
        </div>

        <form action="{{ route('laporan.rat') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun Buku</label>
                <input type="number" min="2020" id="tahun" name="tahun" value="{{ $year }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Tampilkan</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Anggota</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['anggota_total'] }}</p>
            <p class="mt-2 text-xs text-slate-400">Aktif {{ $summary['anggota_aktif'] }}, Nonaktif {{ $summary['anggota_nonaktif'] }}, Cuti {{ $summary['anggota_cuti'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Simpanan Tahun {{ $year }}</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['simpanan_tahun'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pinjaman Disalurkan</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['pinjaman_tahun'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Aset Dibeli Tahun {{ $year }}</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['aset_tahun'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Emas Rp {{ number_format($summary['aset_emas_tahun'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pendapatan Bunga</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-700">Rp {{ number_format($summary['bunga_tahun'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pelepasan Aset Tahun {{ $year }}</p>
            <p class="mt-3 text-2xl font-semibold text-sky-700">Rp {{ number_format($summary['aset_pelepasan_tahun'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan RAT</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Angsuran Masuk</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">Rp {{ number_format($summary['angsuran_tahun'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Piutang Berjalan Saat Ini</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">Rp {{ number_format($summary['piutang_berjalan'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Aset Berjalan Saat Ini</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">Rp {{ number_format($summary['aset_berjalan'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Top Peminjam</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Nama</th>
                            <th class="py-3 pr-4 font-medium">Jumlah Pinjaman</th>
                            <th class="py-3 pr-4 font-medium">Total Kontrak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topBorrowers as $item)
                        <tr>
                            <td class="py-3 pr-4 text-slate-900">{{ $item['nama'] }}</td>
                            <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($item['jumlah_pinjaman'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $item['total_pinjaman'] }} pinjaman</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-500">Belum ada data pinjaman pada tahun ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Komposisi Simpanan per Jenis</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Jenis Simpanan</th>
                        <th class="py-3 pr-4 font-medium">Jumlah Transaksi</th>
                        <th class="py-3 pr-4 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($simpananByJenis as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-900">{{ $item['jenis'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['jumlah_transaksi'] }}</td>
                        <td class="py-3 pr-4 font-medium text-slate-900">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-slate-500">Belum ada transaksi simpanan pada tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Komposisi Aset Berjalan per Jenis</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Jenis Aset</th>
                        <th class="py-3 pr-4 font-medium">Jumlah Item</th>
                        <th class="py-3 pr-4 font-medium">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assetsByType as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-900">{{ $item['jenis'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['jumlah'] }}</td>
                        <td class="py-3 pr-4 font-medium text-slate-900">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-slate-500">Belum ada aset berjalan pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection