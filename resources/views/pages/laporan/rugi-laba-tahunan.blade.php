@extends('layouts.app')

@section('title', 'Laporan Rugi Laba Tahunan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Laporan Rugi Laba Tahunan</h1>
            <p class="mt-2 text-sm text-slate-500">Versi tahunan khusus founder untuk melihat keuntungan bersih koperasi per tahun, tren per bulan, dan dasar SHU yang siap dibahas di RAT.</p>
        </div>

        <form action="{{ route('laporan.rugi-laba-tahunan') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun Buku</label>
                <input type="number" id="tahun" name="tahun" min="2020" value="{{ $year }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Tampilkan</button>
        </form>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">Laba bersih tahun ini siap dipakai untuk simulasi SHU</p>
                <p class="mt-1 text-sm text-slate-500">Buka modul SHU untuk mengatur persentase cadangan, jasa modal, jasa usaha, dana pengurus, dan dana sosial secara otomatis dari laba bersih tahun {{ $year }}.</p>
            </div>
            <a href="{{ route('shu.perhitungan', ['tahun' => $year]) }}" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-amber-400">Buka Simulasi SHU</a>
        </div>
    </div>

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
            <p class="text-sm text-slate-500">Laba Bersih Tahun Berjalan</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['laba_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Dasar SHU</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['shu_dasar'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400">Dasar SHU mengikuti laba bersih positif tahun {{ $year }}.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Penjelasan SHU Sederhana</h2>
            <div class="mt-5 space-y-4 text-sm text-slate-600">
                <div class="rounded-2xl border {{ $summary['shu_dasar'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-slate-50' }} p-4">
                    <p class="font-semibold text-slate-900">Status SHU</p>
                    @if ($summary['shu_status'] === 'siap-dibahas')
                    <p class="mt-2">Koperasi membentuk laba bersih positif sebesar <span class="font-semibold text-amber-700">Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</span>. Nilai ini bisa dipakai sebagai dasar pembahasan SHU pada RAT.</p>
                    @else
                    <p class="mt-2">Belum ada laba bersih positif di tahun {{ $year }}, jadi belum ada dasar SHU yang bisa dibagikan. Fokusnya masih pada perbaikan kinerja usaha.</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="font-semibold text-slate-900">Cara baca untuk founder</p>
                    <p class="mt-2">SHU sederhana di halaman ini bukan pembagian final ke anggota. Ini adalah laba bersih tahunan yang sudah terbentuk dari operasional yang tercatat di sistem.</p>
                    <p class="mt-2">Pembagian final SHU tetap diputuskan di RAT sesuai aturan koperasi, misalnya untuk cadangan, jasa anggota, jasa modal, atau pos lain.</p>
                </div>

                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="font-semibold text-slate-900">Komponen yang dihitung</p>
                    <p class="mt-2">Pendapatan: bunga pinjaman dan laba pelepasan aset.</p>
                    <p class="mt-2">Beban: rugi pelepasan aset dan penghapusan aset tanpa penjualan.</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Tren Laba Bersih per Bulan</h2>
            <p class="mt-1 text-sm text-slate-500">Founder bisa melihat bulan mana yang menghasilkan surplus dan bulan mana yang perlu perhatian.</p>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Bulan</th>
                            <th class="py-3 pr-4 font-medium text-right">Pendapatan</th>
                            <th class="py-3 pr-4 font-medium text-right">Beban</th>
                            <th class="py-3 pr-4 font-medium text-right">Laba Bersih</th>
                            <th class="py-3 pr-4 font-medium text-right">Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($monthlyRows as $item)
                        <tr>
                            <td class="py-3 pr-4 font-medium text-slate-900">{{ $item['bulan']->translatedFormat('F Y') }}</td>
                            <td class="py-3 pr-4 text-right font-medium text-emerald-700">Rp {{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-medium text-rose-700">Rp {{ number_format($item['beban'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-semibold {{ $item['laba_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($item['laba_bersih'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right text-slate-600">{{ $item['jumlah_transaksi'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Rincian Transaksi Tahun {{ $year }}</h2>
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
                        <td colspan="6" class="py-6 text-center text-slate-500">Belum ada komponen rugi laba pada tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection