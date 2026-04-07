@extends('layouts.app')

@section('title', 'Neraca Keuangan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Neraca Keuangan</h1>
            <p class="mt-2 text-sm text-slate-500">Ringkasan posisi aset, kewajiban, dan ekuitas koperasi per tanggal laporan.</p>
        </div>

        <form action="{{ route('laporan.neraca-keuangan') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tanggal" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Laporan</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $reportDate->toDateString() }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Tampilkan</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Kas Tersedia</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['kas_tersedia'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Termasuk hasil pelepasan aset Rp {{ number_format($summary['hasil_pelepasan_aset'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Aset</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_aset'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Piutang Pinjaman</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['piutang_pinjaman'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Aset Investasi</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['aset_investasi'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Emas tercatat Rp {{ number_format($summary['aset_emas'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kewajiban</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_kewajiban'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Ekuitas</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['ekuitas'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['ekuitas'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Total kewajiban + ekuitas: Rp {{ number_format($summary['total_pasiva'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
        <p class="font-semibold">Cara baca sederhana</p>
        <p class="mt-1">Neraca akan seimbang kalau Total Aset sama dengan Total Kewajiban ditambah Ekuitas. Jika aset sudah nonaktif tanpa penjualan, nilainya keluar dari aset tetapi kas tidak bertambah, jadi ekuitas akan turun dan ini memang normal.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Struktur Neraca</h2>
            <div class="mt-5 space-y-4 text-sm">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Aset</p>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Aset Lancar - Kas</span><span class="font-semibold text-slate-900">Rp {{ number_format($summary['kas_tersedia'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Aset Produktif - Piutang Pinjaman</span><span class="font-semibold text-slate-900">Rp {{ number_format($summary['piutang_pinjaman'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Aset Investasi / Persediaan</span><span class="font-semibold text-slate-900">Rp {{ number_format($summary['aset_investasi'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 border-t border-slate-200 pt-3 flex items-center justify-between gap-4"><span class="font-semibold text-slate-900">Total Aset</span><span class="text-base font-semibold text-slate-900">Rp {{ number_format($summary['total_aset'], 0, ',', '.') }}</span></div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Sumber Dana</p>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Kewajiban Simpanan Anggota</span><span class="font-semibold text-slate-900">Rp {{ number_format($summary['simpanan_anggota'], 0, ',', '.') }}</span></div>
                    <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Pendapatan bunga pinjaman</span><span class="font-semibold text-slate-900">Rp {{ number_format($summary['bunga_diterima'], 0, ',', '.') }}</span></div>
                        <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Laba / rugi pelepasan aset</span><span class="font-semibold {{ $summary['laba_rugi_pelepasan_aset'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['laba_rugi_pelepasan_aset'], 0, ',', '.') }}</span></div>
                        <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Aset nonaktif tanpa penjualan</span><span class="font-semibold text-rose-700">Rp {{ number_format($summary['penurunan_aset_tanpa_penjualan'], 0, ',', '.') }}</span></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Total Ekuitas</span><span class="font-semibold {{ $summary['ekuitas'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['ekuitas'], 0, ',', '.') }}</span></div>
                    <div class="mt-3 border-t border-slate-200 pt-3 flex items-center justify-between gap-4"><span class="font-semibold text-slate-900">Total Kewajiban + Ekuitas</span><span class="text-base font-semibold text-slate-900">Rp {{ number_format($summary['total_pasiva'], 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Aset Koperasi Tercatat</h2>
            <p class="mt-1 text-sm text-slate-500">Termasuk aset emas atau aset lain yang dibeli memakai kas koperasi.</p>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Kode Aset</th>
                            <th class="py-3 pr-4 font-medium">Nama</th>
                            <th class="py-3 pr-4 font-medium">Jenis</th>
                            <th class="py-3 pr-4 font-medium">Status per Tanggal</th>
                            <th class="py-3 pr-4 font-medium">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assetRows as $item)
                        <tr>
                            <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->kode_aset }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $item->nama_aset }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ ucfirst($item->jenis_aset) }}</td>
                            <td class="py-3 pr-4 text-slate-600">Aktif</td>
                            <td class="py-3 pr-4 font-medium text-slate-900">Rp {{ number_format((float) $item->nilai_perolehan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Belum ada aset koperasi yang tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Piutang Pinjaman Terbesar</h2>
        <p class="mt-1 text-sm text-slate-500">Menunjukkan pinjaman dengan sisa pokok terbesar per {{ $reportDate->translatedFormat('d M Y') }}.</p>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">No. Pinjaman</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">Terbayar</th>
                        <th class="py-3 pr-4 font-medium">Sisa Pokok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($loanSummaries as $item)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $item['no_pinjaman'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['anggota'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['angsuran_terbayar'] }}/{{ $item['angsuran_total'] }} angsuran</td>
                        <td class="py-3 pr-4 font-medium text-slate-900">Rp {{ number_format($item['sisa_pokok'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-500">Belum ada data pinjaman.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection