@extends('layouts.app')

@section('title', 'Master Jenis Simpanan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Master Jenis Simpanan</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola kode, nama, bunga, nominal default, dan status jenis simpanan dari UI.</p>
        </div>
        <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
            Kembali ke Transaksi
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ $editingJenis ? 'Edit Jenis Simpanan' : 'Tambah Jenis Simpanan' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Form ini dipakai untuk master data yang akan muncul di transaksi simpanan.</p>

            <form action="{{ $editingJenis ? route('simpanan.jenis.update', $editingJenis) : route('simpanan.jenis.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                @if ($editingJenis)
                @method('PATCH')
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="kode_jenis" class="mb-2 block text-sm font-medium text-slate-700">Kode jenis</label>
                        <input type="text" id="kode_jenis" name="kode_jenis" value="{{ old('kode_jenis', $editingJenis?->kode_jenis) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('kode_jenis')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_jenis" class="mb-2 block text-sm font-medium text-slate-700">Nama jenis</label>
                        <input type="text" id="nama_jenis" name="nama_jenis" value="{{ old('nama_jenis', $editingJenis?->nama_jenis) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('nama_jenis')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="bunga_persen" class="mb-2 block text-sm font-medium text-slate-700">Bunga (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="bunga_persen" name="bunga_persen" value="{{ old('bunga_persen', $editingJenis?->bunga_persen) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('bunga_persen')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nominal_default" class="mb-2 block text-sm font-medium text-slate-700">Nominal default</label>
                        <input type="number" step="0.01" min="0" id="nominal_default" name="nominal_default" value="{{ old('nominal_default', $editingJenis?->nominal_default) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('nominal_default')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                        <select id="status" name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('status', $editingJenis?->status ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center pt-8">
                        <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_wajib" value="1" @checked(old('is_wajib', $editingJenis?->is_wajib)) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Jenis simpanan wajib
                        </label>
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('keterangan', $editingJenis?->keterangan) }}</textarea>
                    @error('keterangan')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                        {{ $editingJenis ? 'Simpan Perubahan' : 'Tambah Jenis' }}
                    </button>
                    @if ($editingJenis)
                    <a href="{{ route('simpanan.jenis.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Batal Edit
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Daftar Jenis Simpanan</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan setiap jenis simpanan berikut total saldo dan jumlah transaksi posted.</p>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Kode</th>
                            <th class="py-3 pr-4 font-medium">Nama</th>
                            <th class="py-3 pr-4 font-medium">Status</th>
                            <th class="py-3 pr-4 font-medium">Wajib</th>
                            <th class="py-3 pr-4 font-medium">Nominal Default</th>
                            <th class="py-3 pr-4 font-medium">Total Saldo</th>
                            <th class="py-3 pr-4 font-medium">Transaksi</th>
                            <th class="py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($jenisSimpanan as $jenis)
                        <tr>
                            <td class="py-3 pr-4 text-slate-600">{{ $jenis->kode_jenis }}</td>
                            <td class="py-3 pr-4 text-slate-900">
                                <div>
                                    <p class="font-medium">{{ $jenis->nama_jenis }}</p>
                                    @if ($jenis->bunga_persen > 0)
                                    <p class="mt-1 text-xs text-slate-500">Bunga {{ number_format((float) $jenis->bunga_persen, 2, ',', '.') }}%</p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $jenis->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($jenis->status) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-slate-600">{{ $jenis->is_wajib ? 'Ya' : 'Tidak' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $jenis->nominal_default ? 'Rp ' . number_format((float) $jenis->nominal_default, 0, ',', '.') : '-' }}</td>
                            <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) ($jenis->total_saldo ?? 0), 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ number_format($jenis->transaksi_count) }}</td>
                            <td class="py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('simpanan.jenis.index', ['edit' => $jenis->id]) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                        Edit
                                    </a>
                                    @if ($jenis->status === 'aktif')
                                    <form action="{{ route('simpanan.jenis.deactivate', $jenis) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-amber-200 px-3 py-2 text-xs font-medium text-amber-700 transition hover:border-amber-300 hover:text-amber-800">
                                            Nonaktifkan
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('simpanan.jenis.destroy', $jenis) }}" method="POST" onsubmit="return confirm('Hapus jenis simpanan ini? Jika sudah dipakai transaksi, sistem akan menolak penghapusan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-medium text-rose-700 transition hover:border-rose-300 hover:text-rose-800">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-500">Belum ada jenis simpanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection