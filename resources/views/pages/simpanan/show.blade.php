@extends('layouts.app')

@section('title', 'Detail Transaksi Simpanan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Detail Transaksi Simpanan</h1>
            <p class="mt-2 text-sm text-slate-500">Edit transaksi atau batalkan transaksi yang tidak lagi valid.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('simpanan.print', $simpanan) }}" target="_blank" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                Cetak Bukti Transaksi
            </a>
            <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Informasi Transaksi</h2>
                    <p class="mt-1 text-sm text-slate-500">Status saat ini: <span class="font-medium">{{ ucfirst($simpanan->status) }}</span></p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $simpanan->status === 'batal' ? 'bg-slate-100 text-slate-600' : 'bg-sky-50 text-sky-700' }}">
                    {{ ucfirst($simpanan->status) }}
                </span>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Anggota</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-900">{{ $simpanan->anggota?->profile?->nama_lengkap ?? '-' }}</dd>
                    <dd class="mt-1 text-xs text-slate-500">{{ $simpanan->anggota?->no_anggota ?? '-' }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Jenis Simpanan</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-900">{{ $simpanan->jenisSimpanan?->nama_jenis ?? '-' }}</dd>
                    <dd class="mt-1 text-xs text-slate-500">{{ $simpanan->jenisSimpanan?->kode_jenis ?? '-' }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">No. Bukti</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-900">{{ $simpanan->no_bukti ?? '-' }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Tanggal Transaksi</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-900">{{ $simpanan->tanggal_transaksi?->translatedFormat('d F Y') }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Nominal</dt>
                    <dd class="mt-2 text-sm font-medium {{ $simpanan->jumlah < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ $simpanan->jumlah < 0 ? '-' : '+' }}Rp {{ number_format(abs((float) $simpanan->jumlah), 0, ',', '.') }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Edit Transaksi</h2>
                <p class="mt-1 text-sm text-slate-500">Transaksi yang sudah dibatalkan tidak bisa diubah lagi.</p>

                <form action="{{ route('simpanan.update', $simpanan) }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    <fieldset @disabled($simpanan->status === 'batal') class="space-y-5">
                        <div>
                            <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                            <select id="anggota_id" name="anggota_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                @foreach ($anggotaOptions as $anggota)
                                <option value="{{ $anggota->id }}" @selected((string) old('anggota_id', $simpanan->anggota_id) === (string) $anggota->id)>
                                    {{ $anggota->no_anggota }} - {{ $anggota->profile?->nama_lengkap ?? 'Tanpa nama' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="jenis_simpanan_id" class="mb-2 block text-sm font-medium text-slate-700">Jenis simpanan</label>
                            <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                @foreach ($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}" @selected((string) old('jenis_simpanan_id', $simpanan->jenis_simpanan_id) === (string) $jenis->id)>
                                    {{ $jenis->nama_jenis }} ({{ $jenis->kode_jenis }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="tipe_transaksi" class="mb-2 block text-sm font-medium text-slate-700">Tipe transaksi</label>
                                <select id="tipe_transaksi" name="tipe_transaksi" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                    <option value="setor" @selected(old('tipe_transaksi', $simpanan->jumlah < 0 ? 'tarik' : 'setor' )==='setor' )>Setoran</option>
                                    <option value="tarik" @selected(old('tipe_transaksi', $simpanan->jumlah < 0 ? 'tarik' : 'setor' )==='tarik' )>Penarikan</option>
                                </select>
                            </div>
                            <div>
                                <label for="tanggal_transaksi" class="mb-2 block text-sm font-medium text-slate-700">Tanggal transaksi</label>
                                <input type="date" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', optional($simpanan->tanggal_transaksi)->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            </div>
                        </div>

                        <div>
                            <label for="jumlah" class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                            <input type="number" step="0.01" min="0.01" id="jumlah" name="jumlah" value="{{ old('jumlah', abs((float) $simpanan->jumlah)) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        </div>

                        <div>
                            <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('keterangan', $simpanan->keterangan) }}</textarea>
                        </div>

                        @if ($errors->any())
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            Periksa kembali isian transaksi sebelum disimpan.
                        </div>
                        @endif

                        <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                            Simpan Perubahan
                        </button>
                    </fieldset>
                </form>
            </div>

            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-rose-900">Batalkan Transaksi</h2>
                <p class="mt-2 text-sm leading-6 text-rose-800">Pembatalan akan mengubah status transaksi menjadi <strong>batal</strong> dan transaksi tidak lagi dihitung dalam saldo simpanan.</p>

                <form action="{{ route('simpanan.cancel', $simpanan) }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="alasan_batal" class="mb-2 block text-sm font-medium text-rose-900">Alasan pembatalan</label>
                        <textarea id="alasan_batal" name="alasan_batal" rows="3" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100"></textarea>
                    </div>

                    <button type="submit" @disabled($simpanan->status === 'batal') class="inline-flex items-center rounded-xl bg-rose-700 px-5 py-3 text-sm font-medium text-white transition hover:bg-rose-600 disabled:cursor-not-allowed disabled:bg-rose-300">
                        Batalkan Transaksi
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Audit Trail</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat siapa yang membuat, mengubah, dan membatalkan transaksi ini.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($simpanan->audits as $audit)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ ucfirst($audit->action) }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $audit->description ?: '-' }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-wide text-slate-600">
                                {{ $audit->created_at?->format('d-m-Y H:i') }}
                            </span>
                        </div>

                        <div class="mt-3 text-sm text-slate-500">
                            Oleh: {{ $audit->user?->profile?->nama_lengkap ?? $audit->user?->username ?? 'Sistem' }}
                        </div>

                        @if (!empty($audit->metadata))
                        <div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                            <pre class="whitespace-pre-wrap break-words">{{ json_encode($audit->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-5 text-sm text-slate-500">
                        Belum ada riwayat audit untuk transaksi ini.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection