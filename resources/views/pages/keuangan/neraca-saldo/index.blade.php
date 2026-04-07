@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Neraca Saldo</h1>
            <p class="mt-2 text-sm text-slate-500">Ringkasan saldo per akun berdasarkan seluruh jurnal yang sudah masuk sampai tanggal laporan.</p>
        </div>
        <div class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            {{ $koperasi->nama_koperasi }}
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('keuangan.neraca-saldo.index') }}" method="GET" class="grid gap-4 md:grid-cols-[minmax(0,220px)_auto]">
            <div>
                <label for="tanggal_selesai" class="mb-2 block text-sm font-medium text-slate-700">Per tanggal</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $filters['tanggal_selesai']->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>
            <div class="flex flex-wrap gap-3 md:items-end">
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Terapkan</button>
                <a href="{{ route('keuangan.neraca-saldo.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Debit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kredit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Saldo Debit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['saldo_debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Saldo Kredit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['saldo_kredit'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Kode</th>
                        <th class="py-3 pr-4 font-medium">Nama Akun</th>
                        <th class="py-3 pr-4 font-medium">Tipe</th>
                        <th class="py-3 pr-4 font-medium">Mutasi Debit</th>
                        <th class="py-3 pr-4 font-medium">Mutasi Kredit</th>
                        <th class="py-3 pr-4 font-medium">Saldo Debit</th>
                        <th class="py-3 font-medium">Saldo Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $row['akun']->kode_akun }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $row['akun']->nama_akun }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ ucfirst($row['akun']->tipe_akun) }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($row['total_debit'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($row['total_kredit'], 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format($row['saldo_debit'], 0, ',', '.') }}</td>
                        <td class="py-3 text-slate-900">Rp {{ number_format($row['saldo_kredit'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Belum ada saldo akun yang terbentuk dari jurnal.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection