@extends('layouts.app')

@section('title', 'Jadwal Angsuran')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Jadwal Angsuran</h1>
            <p class="mt-2 text-sm text-slate-500">Lihat jadwal pembayaran pinjaman aktif berdasarkan tanggal tagihan bulanan.</p>
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
        <form action="{{ route('pinjaman.jadwal-angsuran') }}" method="GET" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
            <div>
                <label for="pinjaman_id" class="mb-2 block text-sm font-medium text-slate-700">Pilih pinjaman</label>
                <select id="pinjaman_id" name="pinjaman_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @foreach ($pinjamanOptions as $item)
                    <option value="{{ $item->id }}" @selected((string) $selectedId===(string) $item->id)>
                        {{ $item->no_pinjaman }} - {{ $item->anggota?->profile?->nama_lengkap ?? '-' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                    Tampilkan Jadwal
                </button>
                <a href="{{ route('pinjaman.jadwal-angsuran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if ($selectedPinjaman)
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">No. Pinjaman</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $selectedPinjaman->no_pinjaman }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Anggota</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $selectedPinjaman->anggota?->profile?->nama_lengkap ?? '-' }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Pokok Pinjaman</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">Rp {{ number_format((float) $selectedPinjaman->jumlah_pinjaman, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Periode Pinjaman</p>
            <p class="mt-3 text-lg font-semibold text-slate-900">{{ $selectedPinjaman->first_due_date?->translatedFormat('d M Y') }} - {{ $selectedPinjaman->final_due_date?->translatedFormat('d M Y') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Rencana Angsuran</h2>
        <p class="mt-1 text-sm text-slate-500">Tagihan dibentuk setiap tanggal {{ $selectedPinjaman->tanggal_tagihan_bulanan }} dengan bunga tetap Rp {{ number_format((float) $selectedPinjaman->bunga_nominal_bulanan, 0, ',', '.') }} per bulan.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Angsuran</th>
                        <th class="py-3 pr-4 font-medium">Jatuh Tempo</th>
                        <th class="py-3 pr-4 font-medium">Pokok</th>
                        <th class="py-3 pr-4 font-medium">Bunga</th>
                        <th class="py-3 pr-4 font-medium">Total Tagihan</th>
                        <th class="py-3 text-right font-medium">Sisa Pokok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($schedule as $row)
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
        Belum ada pinjaman yang dapat ditampilkan.
    </div>
    @endif
</div>
@endsection