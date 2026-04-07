@extends('layouts.app')

@section('title', 'Buku Besar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Buku Besar</h1>
            <p class="mt-2 text-sm text-slate-500">Lacak mutasi per akun dari jurnal otomatis dan jurnal manual yang sudah diposting.</p>
        </div>
        <div class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            {{ $koperasi->nama_koperasi }}
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('keuangan.buku-besar.index') }}" method="GET" class="grid gap-4 md:grid-cols-4">
            <div>
                <label for="akun_keuangan_id" class="mb-2 block text-sm font-medium text-slate-700">Akun</label>
                <select id="akun_keuangan_id" name="akun_keuangan_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @foreach ($akunOptions as $akun)
                    <option value="{{ $akun->id }}" @selected(($selectedAkun?->id ?? null) === $akun->id)>{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tanggal_mulai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $filters['tanggal_mulai']->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $filters['tanggal_selesai']->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div class="flex flex-wrap gap-3 md:items-end">
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Terapkan</button>
                <a href="{{ route('keuangan.buku-besar.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Saldo Awal</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['saldo_awal'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Debit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kredit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Saldo Akhir</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-1">
            <h2 class="text-lg font-semibold text-slate-900">{{ $selectedAkun?->kode_akun }} - {{ $selectedAkun?->nama_akun }}</h2>
            <p class="text-sm text-slate-500">Periode {{ $filters['tanggal_mulai']->translatedFormat('d M Y') }} sampai {{ $filters['tanggal_selesai']->translatedFormat('d M Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">No. Jurnal</th>
                        <th class="py-3 pr-4 font-medium">Sumber</th>
                        <th class="py-3 pr-4 font-medium">Keterangan</th>
                        <th class="py-3 pr-4 font-medium">Debit</th>
                        <th class="py-3 pr-4 font-medium">Kredit</th>
                        <th class="py-3 font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                    <tr>
                        <td class="py-3 pr-4 text-slate-900">{{ $entry['tanggal']?->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $entry['no_jurnal'] }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $entry['sumber_referensi'] ?: strtoupper($entry['jenis_jurnal']) }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $entry['keterangan'] }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($entry['debit'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($entry['kredit'], 0, ',', '.') }}</td>
                        <td class="py-3 text-slate-900">Rp {{ number_format($entry['saldo'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Belum ada mutasi jurnal untuk akun ini pada periode yang dipilih.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection