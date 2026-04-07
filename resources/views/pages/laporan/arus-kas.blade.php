@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Laporan Arus Kas</h1>
            <p class="mt-2 text-sm text-slate-500">Monitor kas masuk dari simpanan, angsuran, pelepasan aset, serta kas keluar dari pencairan pinjaman dan pembelian aset.</p>
        </div>

        <form action="{{ route('laporan.arus-kas') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tanggal_mulai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $startDate->toDateString() }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $endDate->toDateString() }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Tampilkan</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kas Masuk</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-700">Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kas Keluar</p>
            <p class="mt-3 text-2xl font-semibold text-rose-700">Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Arus Bersih</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['arus_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['arus_bersih'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Jumlah Transaksi</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['jumlah_transaksi'] }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Mutasi Kas</h2>
        <p class="mt-1 text-sm text-slate-500">Periode {{ $startDate->translatedFormat('d M Y') }} sampai {{ $endDate->translatedFormat('d M Y') }}.</p>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">Kategori</th>
                        <th class="py-3 pr-4 font-medium">Sumber</th>
                        <th class="py-3 pr-4 font-medium">Referensi</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">Masuk</th>
                        <th class="py-3 pr-4 font-medium">Keluar</th>
                        <th class="py-3 pr-4 font-medium">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['tanggal']->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item['kategori'] === 'Kas Masuk' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $item['kategori'] }}</span></td>
                        <td class="py-3 pr-4 text-slate-900">{{ $item['sumber'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['referensi'] ?: '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['anggota'] }}</td>
                        <td class="py-3 pr-4 font-medium text-emerald-700">{{ $item['masuk'] > 0 ? 'Rp ' . number_format($item['masuk'], 0, ',', '.') : '-' }}</td>
                        <td class="py-3 pr-4 font-medium text-rose-700">{{ $item['keluar'] > 0 ? 'Rp ' . number_format($item['keluar'], 0, ',', '.') : '-' }}</td>
                        <td class="py-3 pr-4 font-medium text-slate-900">Rp {{ number_format($item['saldo_berjalan'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-500">Belum ada mutasi kas pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection