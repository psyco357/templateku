@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-600">Dashboard Utama</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Ringkasan Operasional Koperasi</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ $koperasi?->nama_koperasi ?? 'Koperasi belum tersedia' }}
                @if ($koperasi)
                • Snapshot {{ $bulanBerjalanLabel }}
                @endif
            </p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Anggota Aktif</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ number_format($anggotaAktif) }}</p>
            <p class="mt-2 text-sm text-slate-500">Anggota aktif dan akun masih bisa digunakan.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Simpanan</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Akumulasi neto dari setoran dan penarikan simpanan posted.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Pinjaman Berjalan</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format($totalPinjamanBerjalan, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Nilai pinjaman dengan status belum lunas.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Omzet Toko Bulan Ini</p>
            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format($omzetTokoBulanIni, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Total transaksi toko berstatus selesai pada bulan berjalan.</p>
        </div>
    </div>

    @if ($isFounder)
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-amber-900">Preview Skema SHU Tersimpan</h2>
                    <p class="mt-1 text-sm text-amber-800">Ringkasan komposisi SHU terbaru yang tersimpan untuk tahun-tahun terakhir.</p>
                </div>
                <a href="{{ route('shu.perhitungan') }}" class="inline-flex items-center rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm font-medium text-amber-900 transition hover:border-amber-300">Kelola SHU</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($latestShuSchemes as $scheme)
                <div class="rounded-2xl border border-amber-200 bg-white px-4 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Tahun {{ $scheme->tahun }}</p>
                            <p class="mt-1 text-xs text-slate-500">Disimpan oleh {{ $scheme->user?->profile?->nama_lengkap ?? $scheme->user?->username ?? 'Founder' }} pada {{ $scheme->updated_at?->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ number_format((float) $scheme->total_persen, 0, ',', '.') }}%</p>
                    </div>
                    <div class="mt-3 grid gap-2 text-xs text-slate-600 md:grid-cols-2">
                        <p>Cadangan: {{ number_format((float) $scheme->cadangan, 0, ',', '.') }}%</p>
                        <p>Jasa Modal: {{ number_format((float) $scheme->jasa_modal, 0, ',', '.') }}%</p>
                        <p>Jasa Usaha: {{ number_format((float) $scheme->jasa_usaha, 0, ',', '.') }}%</p>
                        <p>Pengurus + Sosial: {{ number_format((float) $scheme->dana_pengurus + (float) $scheme->dana_sosial, 0, ',', '.') }}%</p>
                    </div>
                </div>
                @empty
                <div class="rounded-2xl border border-dashed border-amber-300 px-4 py-5 text-sm text-amber-900">
                    Belum ada skema SHU tersimpan. Founder bisa menyimpan skema dari modul SHU tahunan.
                </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Histori Perubahan Skema SHU</h2>
            <p class="mt-1 text-sm text-slate-500">Menunjukkan siapa yang terakhir menyimpan perubahan skema SHU dan untuk tahun berapa.</p>

            <div class="mt-5 space-y-3">
                @forelse ($shuSchemeHistory as $history)
                <div class="rounded-2xl border border-slate-200 px-4 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Tahun {{ $history->tahun }} • {{ $history->aksi === 'create' ? 'Skema dibuat' : 'Skema diperbarui' }}</p>
                            <p class="mt-1 text-xs text-slate-500">Oleh {{ $history->user?->profile?->nama_lengkap ?? $history->user?->username ?? 'Founder' }} pada {{ $history->created_at?->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ number_format((float) $history->total_persen, 0, ',', '.') }}%</p>
                    </div>
                    <div class="mt-3 grid gap-2 text-xs text-slate-600 md:grid-cols-2">
                        <p>Cadangan: {{ number_format((float) $history->cadangan, 0, ',', '.') }}%</p>
                        <p>Jasa Modal: {{ number_format((float) $history->jasa_modal, 0, ',', '.') }}%</p>
                        <p>Jasa Usaha: {{ number_format((float) $history->jasa_usaha, 0, ',', '.') }}%</p>
                        <p>Dana Pengurus + Sosial: {{ number_format((float) $history->dana_pengurus + (float) $history->dana_sosial, 0, ',', '.') }}%</p>
                    </div>
                </div>
                @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-500">
                    Belum ada histori perubahan skema SHU.
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Transaksi Simpanan Terbaru</h2>
                    <p class="mt-1 text-sm text-slate-500">Pemantauan cepat aktivitas setoran dan penarikan simpanan anggota.</p>
                </div>
                <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Kelola Simpanan
                </a>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Tanggal</th>
                            <th class="py-3 pr-4 font-medium">Anggota</th>
                            <th class="py-3 pr-4 font-medium">Jenis</th>
                            <th class="py-3 pr-4 font-medium">Tipe</th>
                            <th class="py-3 text-right font-medium">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transaksiSimpananTerbaru as $transaksi)
                        <tr>
                            <td class="py-3 pr-4 text-slate-600">{{ $transaksi->tanggal_transaksi?->translatedFormat('d M Y') }}</td>
                            <td class="py-3 pr-4 text-slate-900">{{ $transaksi->anggota?->profile?->nama_lengkap ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $transaksi->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $transaksi->jumlah < 0 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $transaksi->jumlah < 0 ? 'Tarik' : 'Setor' }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-medium {{ $transaksi->jumlah < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $transaksi->jumlah < 0 ? '-' : '+' }}Rp {{ number_format(abs((float) $transaksi->jumlah), 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Belum ada transaksi simpanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Saldo per Jenis Simpanan</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan saldo aktif setiap jenis simpanan pada koperasi.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($ringkasanJenisSimpanan as $jenis)
                    <div class="rounded-2xl border border-slate-200 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $jenis->nama_jenis }}</p>
                                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $jenis->kode_jenis }}</p>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">Rp {{ number_format((float) $jenis->total_saldo, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-500">
                        Belum ada jenis simpanan aktif.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-emerald-900">Prioritas Modul Saat Ini</h2>
                <p class="mt-2 text-sm leading-6 text-emerald-800">Modul simpanan sudah aktif untuk input setoran dan penarikan, lengkap dengan rekap saldo per anggota dan jenis simpanan.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('simpanan.transaksi') }}" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-600">
                        Input Transaksi
                    </a>
                    <a href="{{ route('simpanan.rekap-saldo') }}" class="inline-flex items-center rounded-xl border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-800 transition hover:border-emerald-300">
                        Lihat Rekap Saldo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection