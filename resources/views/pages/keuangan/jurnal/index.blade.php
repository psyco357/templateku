@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Jurnal Umum</h1>
            <p class="mt-2 text-sm text-slate-500">Catat transaksi manual dan siapkan dasar posting untuk buku besar koperasi.</p>
        </div>
        <div class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            {{ $koperasi->nama_koperasi }}
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Jurnal</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['total_jurnal'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Debit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Kredit</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Form Jurnal Manual</h2>
            <p class="mt-1 text-sm text-slate-500">Isi minimal dua baris dan pastikan total debit sama dengan total kredit.</p>

            <form action="{{ route('keuangan.jurnal.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="tanggal_jurnal" class="mb-2 block text-sm font-medium text-slate-700">Tanggal jurnal</label>
                        <input type="date" id="tanggal_jurnal" name="tanggal_jurnal" value="{{ old('tanggal_jurnal', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('tanggal_jurnal')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                        <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Contoh: Penyesuaian kas harian">
                        @error('keterangan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-[minmax(0,1.2fr)_120px_120px_minmax(0,1fr)] gap-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <div>Akun</div>
                        <div>Debit</div>
                        <div>Kredit</div>
                        <div>Uraian</div>
                    </div>
                    @foreach ($lineDefaults as $index => $line)
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1.2fr)_120px_120px_minmax(0,1fr)]">
                        <div>
                            <select name="lines[{{ $index }}][akun_keuangan_id]" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                <option value="">Pilih akun</option>
                                @foreach ($akunOptions as $akun)
                                <option value="{{ $akun->id }}" @selected(($line['akun_keuangan_id'] ?? '' )==$akun->id)>{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="number" step="0.01" min="0" name="lines[{{ $index }}][debit]" value="{{ $line['debit'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="0">
                        </div>
                        <div>
                            <input type="number" step="0.01" min="0" name="lines[{{ $index }}][kredit]" value="{{ $line['kredit'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="0">
                        </div>
                        <div>
                            <input type="text" name="lines[{{ $index }}][uraian]" value="{{ $line['uraian'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Opsional">
                        </div>
                    </div>
                    @endforeach
                    @error('lines')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Simpan Jurnal</button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Filter Jurnal</h2>
                <form action="{{ route('keuangan.jurnal.index') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="tanggal_mulai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal mulai</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="mb-2 block text-sm font-medium text-slate-700">Tanggal selesai</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $filters['tanggal_selesai'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="flex flex-wrap gap-3 md:items-end">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Terapkan</button>
                        <a href="{{ route('keuangan.jurnal.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Daftar Jurnal</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($jurnal as $item)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $item->no_jurnal }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->tanggal_jurnal?->translatedFormat('d M Y') }} • {{ ucfirst($item->jenis_jurnal) }}</p>
                                @if ($item->keterangan)
                                <p class="mt-2 text-sm text-slate-600">{{ $item->keterangan }}</p>
                                @endif
                            </div>
                            <div class="text-sm text-slate-600 md:text-right">
                                <p>Debit: <span class="font-semibold text-slate-900">Rp {{ number_format((float) $item->total_debit, 0, ',', '.') }}</span></p>
                                <p>Kredit: <span class="font-semibold text-slate-900">Rp {{ number_format((float) $item->total_kredit, 0, ',', '.') }}</span></p>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500">
                                        <th class="py-2 pr-3 font-medium">Akun</th>
                                        <th class="py-2 pr-3 font-medium">Uraian</th>
                                        <th class="py-2 pr-3 font-medium">Debit</th>
                                        <th class="py-2 font-medium">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($item->details as $detail)
                                    <tr>
                                        <td class="py-2 pr-3 text-slate-900">{{ $detail->akun?->kode_akun }} - {{ $detail->akun?->nama_akun }}</td>
                                        <td class="py-2 pr-3 text-slate-600">{{ $detail->uraian ?: '-' }}</td>
                                        <td class="py-2 pr-3 text-slate-900">Rp {{ number_format((float) $detail->debit, 0, ',', '.') }}</td>
                                        <td class="py-2 text-slate-900">Rp {{ number_format((float) $detail->kredit, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @empty
                    <div class="rounded-2xl border border-slate-200 p-6 text-center text-slate-500">Belum ada jurnal umum yang tercatat.</div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $jurnal->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection