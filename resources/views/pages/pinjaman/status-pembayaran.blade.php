@extends('layouts.app')

@section('title', 'Status Pembayaran Pinjaman')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Status Pembayaran Pinjaman</h1>
            <p class="mt-2 text-sm text-slate-500">Pantau total tagihan, cicilan terbayar, dan sisa kewajiban pinjaman anggota.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pinjaman.pengajuan') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Pengajuan Pinjaman
            </a>
            <a href="{{ route('pinjaman.jadwal-angsuran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Jadwal Angsuran
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Filter</h2>

        <form action="{{ route('pinjaman.status-pembayaran') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
            @unless ($isAnggotaView)
            <div>
                <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua anggota</option>
                    @foreach ($anggotaOptions as $anggota)
                    <option value="{{ $anggota->id }}" @selected($filters['anggota_id']===(string) $anggota->id)>{{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}</option>
                    @endforeach
                </select>
            </div>
            @endunless

            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $status)
                    <option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:items-end">
                <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                    Terapkan Filter
                </button>
                <a href="{{ route('pinjaman.status-pembayaran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Pinjaman</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['total_pinjaman'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Pinjaman Aktif</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['total_berjalan'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Tagihan</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format((float) $summary['total_tagihan'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Sisa Tagihan</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format((float) $summary['total_sisa'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Daftar Status Pembayaran</h2>
        <p class="mt-1 text-sm text-slate-500">Angsuran berikutnya bisa dibayar kapan saja, termasuk sebelum jatuh tempo. Sistem akan mencatatnya ke angsuran aktif berikutnya.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">No. Pinjaman</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">Pokok</th>
                        <th class="py-3 pr-4 font-medium">Total Tagihan</th>
                        <th class="py-3 pr-4 font-medium">Terbayar</th>
                        <th class="py-3 pr-4 font-medium">Sisa</th>
                        <th class="py-3 pr-4 font-medium">Angsuran</th>
                        <th class="py-3 pr-4 font-medium">Tagihan Berikutnya</th>
                        <th class="py-3 pr-4 font-medium">Status</th>
                        <th class="py-3 pr-4 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pinjaman as $item)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->no_pinjaman }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->anggota?->profile?->nama_lengkap ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $item->jumlah_pinjaman, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $item->total_tagihan, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-emerald-700">Rp {{ number_format((float) $item->total_terbayar, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 {{ $item->sisa_tagihan > 0 ? 'text-rose-700' : 'text-slate-900' }}">Rp {{ number_format((float) $item->sisa_tagihan, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->angsuran_terbayar }}/{{ $item->angsuran_total }}</td>
                        <td class="py-3 pr-4 text-slate-600">
                            @if ($item->next_due_date)
                            {{ $item->next_due_date->translatedFormat('d M Y') }}<br><span class="text-xs text-slate-400">Rp {{ number_format((float) $item->next_due_amount, 0, ',', '.') }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : ($item->status === 'lunas' ? 'bg-sky-50 text-sky-700' : ($item->status === 'ditolak' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            @if ($item->can_pay_now)
                            <form action="{{ route('pinjaman.bayar', $item) }}" method="POST" class="flex min-w-[230px] flex-col gap-2">
                                @csrf
                                <input type="date" name="tanggal_bayar" value="{{ now()->toDateString() }}" min="{{ $item->tanggal_pinjaman?->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800">
                                    Bayar Angsuran Ke-{{ $item->next_installment_number }}
                                </button>
                                <p class="text-xs text-slate-400">Boleh dibayar sebelum {{ $item->next_due_date?->translatedFormat('d M Y') }}.</p>
                            </form>
                            @else
                            <span class="text-sm text-slate-400">Tidak ada angsuran aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center text-slate-500">Belum ada data pinjaman untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection