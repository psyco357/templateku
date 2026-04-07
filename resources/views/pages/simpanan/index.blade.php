@extends('layouts.app')

@section('title', 'Transaksi Simpanan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Setoran dan Penarikan Simpanan</h1>
            <p class="mt-2 text-sm text-slate-500">Input transaksi simpanan anggota dan pantau saldo per jenis simpanan.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('simpanan.jenis.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Master Jenis
            </a>
            <a href="{{ route('simpanan.rekap-saldo') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Lihat Rekap Saldo
            </a>
            <a href="{{ route('simpanan.mutasi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Buku Simpanan
            </a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Form Transaksi</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan nilai positif. Sistem akan otomatis menyimpan penarikan sebagai pengurang saldo.</p>

            <form action="{{ route('simpanan.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                    <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="">Pilih anggota</option>
                        @foreach ($anggotaOptions as $anggota)
                        <option value="{{ $anggota->id }}" @selected((string) old('anggota_id')===(string) $anggota->id)>
                            {{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}
                        </option>
                        @endforeach
                    </select>
                    @error('anggota_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_simpanan_id" class="mb-2 block text-sm font-medium text-slate-700">Jenis simpanan</label>
                    <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="">Pilih jenis simpanan</option>
                        @foreach ($jenisSimpanan as $jenis)
                        <option value="{{ $jenis->id }}" @selected((string) old('jenis_simpanan_id')===(string) $jenis->id)>
                            {{ $jenis->nama_jenis }} ({{ $jenis->kode_jenis }})
                        </option>
                        @endforeach
                    </select>
                    @error('jenis_simpanan_id')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="tipe_transaksi" class="mb-2 block text-sm font-medium text-slate-700">Tipe transaksi</label>
                        <select id="tipe_transaksi" name="tipe_transaksi" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="setor" @selected(old('tipe_transaksi', 'setor' )==='setor' )>Setoran</option>
                            <option value="tarik" @selected(old('tipe_transaksi')==='tarik' )>Penarikan</option>
                        </select>
                        @error('tipe_transaksi')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_transaksi" class="mb-2 block text-sm font-medium text-slate-700">Tanggal transaksi</label>
                        <input type="date" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('tanggal_transaksi')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Nomor bukti</label>
                    <input type="text" value="Otomatis dibuat saat transaksi disimpan" disabled class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                </div>

                <div>
                    <label for="jumlah" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                    <input type="number" step="0.01" min="0.01" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="0.00">
                    @error('jumlah')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Catatan transaksi jika diperlukan">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                    Simpan transaksi
                </button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Filter Transaksi</h2>
                        <p class="mt-1 text-sm text-slate-500">Saring daftar transaksi berdasarkan anggota, jenis simpanan, atau tipe transaksi.</p>
                    </div>
                </div>

                <form action="{{ route('simpanan.transaksi') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-4">
                    <div>
                        <label for="filter_anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                        <select id="filter_anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Semua anggota</option>
                            @foreach ($anggotaOptions as $anggota)
                            <option value="{{ $anggota->id }}" @selected($filters['anggota_id']===(string) $anggota->id)>
                                {{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter_jenis_simpanan_id" class="mb-2 block text-sm font-medium text-slate-700">Jenis simpanan</label>
                        <select id="filter_jenis_simpanan_id" name="jenis_simpanan_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Semua jenis</option>
                            @foreach ($jenisSimpanan as $jenis)
                            <option value="{{ $jenis->id }}" @selected($filters['jenis_simpanan_id']===(string) $jenis->id)>{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter_tipe" class="mb-2 block text-sm font-medium text-slate-700">Tipe transaksi</label>
                        <select id="filter_tipe" name="tipe" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Semua tipe</option>
                            <option value="setor" @selected($filters['tipe']==='setor' )>Setoran</option>
                            <option value="tarik" @selected($filters['tipe']==='tarik' )>Penarikan</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter_status" class="mb-2 block text-sm font-medium text-slate-700">Status transaksi</label>
                        <select id="filter_status" name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Semua status</option>
                            @foreach ($transactionStatuses as $status)
                            <option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4 flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                            Reset
                        </a>
                        <a href="{{ route('simpanan.rekap-saldo.export', request()->query()) }}" class="inline-flex items-center rounded-xl border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                            Export Excel Rekap
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Saldo per Jenis</h2>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @forelse ($saldoPerJenis as $jenis)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $jenis->nama_jenis }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $jenis->kode_jenis }}</p>
                        <p class="mt-4 text-lg font-semibold text-slate-900">Rp {{ number_format((float) $jenis->total_saldo, 0, ',', '.') }}</p>
                    </div>
                    @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-5 text-sm text-slate-500 md:col-span-2">
                        Belum ada jenis simpanan yang tersedia.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Riwayat Transaksi Simpanan</h2>
        <p class="mt-1 text-sm text-slate-500">Daftar transaksi terbaru sesuai filter yang dipilih.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">No. Bukti</th>
                        <th class="py-3 pr-4 font-medium">Jenis</th>
                        <th class="py-3 pr-4 font-medium">Periode Buku</th>
                        <th class="py-3 pr-4 font-medium">Tipe</th>
                        <th class="py-3 pr-4 font-medium">Status</th>
                        <th class="py-3 pr-4 font-medium">Keterangan</th>
                        <th class="py-3 text-right font-medium">Nominal</th>
                        <th class="py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transaksi as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->tanggal_transaksi?->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $item->anggota?->profile?->nama_lengkap ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->no_bukti ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->periodeBuku?->tahun_buku ? $item->periodeBuku->tahun_buku . ' H' : '-' }}</td>
                        <td class="py-3 pr-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->jumlah < 0 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $item->jumlah < 0 ? 'Tarik' : 'Setor' }}
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'batal' ? 'bg-slate-100 text-slate-600' : 'bg-sky-50 text-sky-700' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->keterangan ?: '-' }}</td>
                        <td class="py-3 text-right font-medium {{ $item->jumlah < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ $item->jumlah < 0 ? '-' : '+' }}Rp {{ number_format(abs((float) $item->jumlah), 0, ',', '.') }}
                        </td>
                        <td class="py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('simpanan.print', $item) }}" target="_blank" class="inline-flex items-center rounded-lg border border-emerald-200 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                                    Cetak Bukti
                                </a>
                                <a href="{{ route('simpanan.show', $item) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                    Detail / Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center text-slate-500">Belum ada transaksi simpanan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $transaksi->links() }}
        </div>
    </div>
</div>
@endsection