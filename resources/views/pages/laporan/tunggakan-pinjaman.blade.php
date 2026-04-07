@extends('layouts.app')

@section('title', 'Tunggakan Pinjaman')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Tunggakan Pinjaman</h1>
            <p class="mt-2 text-sm text-slate-500">Pantau pinjaman aktif yang sudah melewati jatuh tempo dan belum dibayar penuh.</p>
        </div>

        <form action="{{ route('laporan.tunggakan-pinjaman') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tanggal" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Referensi</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $referenceDate->toDateString() }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Tampilkan</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pinjaman Menunggak</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['pinjaman_menunggak'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Angsuran Menunggak</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['angsuran_menunggak'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Nilai Tunggakan</p>
            <p class="mt-3 text-2xl font-semibold text-rose-700">Rp {{ number_format($summary['jumlah_tunggakan'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Terlama Menunggak</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['terlama_hari'] }} hari</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Daftar Tunggakan</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">No. Pinjaman</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">Pokok Tersisa</th>
                        <th class="py-3 pr-4 font-medium">Angsuran Menunggak</th>
                        <th class="py-3 pr-4 font-medium">Nilai Tunggakan</th>
                        <th class="py-3 pr-4 font-medium">Jatuh Tempo Tertua</th>
                        <th class="py-3 pr-4 font-medium">Hari Telat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $item)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $item['no_pinjaman'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['anggota'] }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($item['sisa_pokok'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['angsuran_menunggak'] }} angsuran</td>
                        <td class="py-3 pr-4 font-medium text-rose-700">Rp {{ number_format($item['jumlah_tunggakan'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item['jatuh_tempo_tertua'] ? $item['jatuh_tempo_tertua']->translatedFormat('d M Y') : '-' }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $item['hari_tunggakan'] }} hari</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Tidak ada tunggakan pinjaman pada tanggal referensi ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection