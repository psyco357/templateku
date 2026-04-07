@extends('layouts.app')

@section('title', 'Laporan Rugi Laba')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Laporan Rugi Laba</h1>
            <p class="mt-2 text-sm text-slate-500">Menunjukkan keuntungan bersih koperasi dari pendapatan bunga, laba atau rugi pelepasan aset, dan beban penghapusan aset.</p>
        </div>

        <form action="{{ route('laporan.rugi-laba') }}" method="GET" class="flex flex-wrap items-end gap-3">
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

    @if (auth()->user()?->hasRole(\App\Models\User::ROLE_FOUNDER))
    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">Butuh gambaran satu tahun penuh?</p>
                <p class="mt-1 text-sm text-slate-500">Founder bisa membuka versi tahunan untuk melihat laba bersih per bulan dan dasar SHU tahun berjalan.</p>
            </div>
            <a href="{{ route('laporan.rugi-laba-tahunan', ['tahun' => $startDate->year]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Buka Rugi Laba Tahunan</a>
        </div>
    </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Pendapatan</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-700">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Beban</p>
            <p class="mt-3 text-2xl font-semibold text-rose-700">Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Laba Bersih</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['laba_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Periode {{ $startDate->translatedFormat('d M Y') }} sampai {{ $endDate->translatedFormat('d M Y') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pendapatan Bunga</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['pendapatan_bunga'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Komponen</h2>
            <div class="mt-5 space-y-4 text-sm">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Pendapatan</p>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Bunga pinjaman</span><span class="font-semibold text-emerald-700">Rp {{ number_format($summary['pendapatan_bunga'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Laba pelepasan aset</span><span class="font-semibold text-emerald-700">Rp {{ number_format($summary['laba_pelepasan_aset'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 border-t border-emerald-200 pt-3 flex items-center justify-between gap-4"><span class="font-semibold text-slate-900">Total Pendapatan</span><span class="text-base font-semibold text-emerald-700">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</span></div>
                </div>

                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Beban</p>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Rugi pelepasan aset</span><span class="font-semibold text-rose-700">Rp {{ number_format($summary['rugi_pelepasan_aset'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Penghapusan aset tanpa penjualan</span><span class="font-semibold text-rose-700">Rp {{ number_format($summary['beban_penghapusan_aset'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 border-t border-rose-200 pt-3 flex items-center justify-between gap-4"><span class="font-semibold text-slate-900">Total Beban</span><span class="text-base font-semibold text-rose-700">Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}</span></div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-semibold text-slate-900">Laba Bersih Periode</span><span class="text-base font-semibold {{ $summary['laba_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Rincian Transaksi Rugi Laba</h2>
            <p class="mt-1 text-sm text-slate-500">Pokok angsuran, setoran simpanan, dan pembelian aset tidak dihitung sebagai laba rugi karena itu perpindahan kas atau pembentukan aset, bukan pendapatan operasional.</p>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Tanggal</th>
                            <th class="py-3 pr-4 font-medium">Kategori</th>
                            <th class="py-3 pr-4 font-medium">Komponen</th>
                            <th class="py-3 pr-4 font-medium">Referensi</th>
                            <th class="py-3 pr-4 font-medium">Keterangan</th>
                            <th class="py-3 pr-4 font-medium text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rows as $item)
                        <tr>
                            <td class="py-3 pr-4 text-slate-600">{{ $item['tanggal']->translatedFormat('d M Y') }}</td>
                            <td class="py-3 pr-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item['kategori'] === 'Pendapatan' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $item['kategori'] }}</span></td>
                            <td class="py-3 pr-4 text-slate-900">{{ $item['komponen'] }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $item['referensi'] ?: '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $item['keterangan'] }}</td>
                            <td class="py-3 pr-4 text-right font-medium {{ $item['kategori'] === 'Pendapatan' ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($item['nilai'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">Belum ada komponen rugi laba pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection