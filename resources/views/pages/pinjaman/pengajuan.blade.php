@extends('layouts.app')

@section('title', 'Pengajuan Pinjaman')

@section('content')
@php
$selectedAnggotaLabel = $selectedAnggotaOption
? $selectedAnggotaOption->no_anggota .
' - ' .
($selectedAnggotaOption->profile?->nama_lengkap ?? 'Tanpa nama')
: '';
@endphp
<div class="space-y-6"
    x-data="{
        snapshots: @js($anggotaSnapshots),
        selectedAnggotaId: '{{ old('anggota_id', $selectedAnggotaId) }}',
        formatRupiah(value) {
            return window.formatRupiah(value);
        },
        get selectedSnapshot() {
            const snapshot = this.snapshots[this.selectedAnggotaId];
            return snapshot ?? { saldo_tabungan: 0, maksimal_pinjaman: 0, label: 'Belum memilih anggota' };
        }
    }">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Pengajuan Pinjaman</h1>
            <p class="mt-2 text-sm text-slate-500">Batas pinjaman maksimal 90% dari saldo tabungan dengan bunga tetap Rp {{ number_format($monthlyInterest, 0, ',', '.') }} per bulan.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pinjaman.simulasi') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Simulasi Cicilan
            </a>
            <a href="{{ route('pinjaman.jadwal-angsuran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Jadwal Angsuran
            </a>
            <a href="{{ route('pinjaman.status-pembayaran') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                Status Pembayaran
            </a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Form Pinjaman</h2>
                <p class="mt-1 text-sm text-slate-500">Jadwal pembayaran selalu jatuh setiap tanggal {{ $paymentDay }}. Jika pinjaman dibuat setelah tanggal {{ $paymentDay }}, angsuran pertama mundur ke bulan berikutnya lagi.</p>

                <form action="{{ route('pinjaman.store') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    @if ($isAnggotaView)
                    <input type="hidden" name="anggota_id" value="{{ $selectedAnggotaId }}">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-medium text-slate-500">Anggota</p>
                        <p class="mt-2 text-base font-semibold text-slate-900" x-text="selectedSnapshot.label"></p>
                    </div>
                    @else
                    <div>
                        <label for="anggota_id" class="mb-2 block text-sm font-medium text-slate-700">Anggota</label>
                        <div class="relative" data-anggota-search data-search-url="{{ route('pinjaman.anggota.search') }}">
                            <input type="hidden" id="anggota_id" name="anggota_id" x-model="selectedAnggotaId" value="{{ old('anggota_id', $selectedAnggotaId) }}">
                            <input type="text" id="anggota_lookup"
                                value="{{ old('anggota_id', $selectedAnggotaId) ? $selectedAnggotaLabel : '' }}" autocomplete="off"
                                placeholder="Ketik minimal 3 huruf nama atau nomor anggota"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <div class="mt-2 hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                                data-search-results></div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Cari berdasarkan nama atau nomor anggota. Hasil muncul setelah 3 karakter, maksimal 4 anggota.</p>
                        @error('anggota_id')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-sm font-medium text-emerald-700">Saldo Tabungan</p>
                            <p class="mt-2 text-2xl font-semibold text-emerald-900">Rp <span x-text="formatRupiah(selectedSnapshot.saldo_tabungan)"></span></p>
                        </div>
                        <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                            <p class="text-sm font-medium text-sky-700">Batas Maksimal 90%</p>
                            <p class="mt-2 text-2xl font-semibold text-sky-900">Rp <span x-text="formatRupiah(selectedSnapshot.maksimal_pinjaman)"></span></p>
                        </div>
                    </div>

                    <div>
                        <label for="jumlah_pinjaman" class="mb-2 block text-sm font-medium text-slate-700">Jumlah Pinjaman</label>
                        <input type="hidden" id="jumlah_pinjaman" name="jumlah_pinjaman" value="{{ old('jumlah_pinjaman') }}">
                        <input type="text" inputmode="numeric" id="jumlah_pinjaman_display"
                            value="{{ old('jumlah_pinjaman') ? 'Rp ' . number_format((float) old('jumlah_pinjaman'), 0, ',', '.') : '' }}"
                            data-currency-input="idr" data-currency-target="jumlah_pinjaman"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            placeholder="Rp 0">
                        <p class="mt-2 text-xs text-slate-500">Masukkan nominal dalam rupiah tanpa tanda minus.</p>
                        @error('jumlah_pinjaman')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="tenor_bulan" class="mb-2 block text-sm font-medium text-slate-700">Tenor (bulan)</label>
                            <input type="number" min="1" max="60" id="tenor_bulan" name="tenor_bulan" value="{{ old('tenor_bulan', 6) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @error('tenor_bulan')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_pinjaman" class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pinjaman</label>
                            <input type="date" id="tanggal_pinjaman" name="tanggal_pinjaman" value="{{ old('tanggal_pinjaman', now()->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @error('tanggal_pinjaman')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Catatan pinjaman bila diperlukan">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                        Simpan Pinjaman
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Aturan Pinjaman</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm font-semibold text-slate-900">Batas Nominal</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Pinjaman maksimal {{ (int) ($maxSavingsRatio * 100) }}% dari total simpanan posted anggota.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm font-semibold text-slate-900">Bunga Tetap</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Bunga flat Rp {{ number_format($monthlyInterest, 0, ',', '.') }} per bulan tanpa persentase.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm font-semibold text-slate-900">Tanggal Tagihan</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Setiap tanggal {{ $paymentDay }}. Jika pinjam lewat tanggal {{ $paymentDay }}, angsuran pertama jatuh pada tanggal {{ $paymentDay }} bulan berikutnya lagi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Pinjaman Terbaru</h2>
        <p class="mt-1 text-sm text-slate-500">Daftar pengajuan dan pinjaman yang sudah tercatat di koperasi.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">No. Pinjaman</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">Pokok</th>
                        <th class="py-3 pr-4 font-medium">Tagihan</th>
                        <th class="py-3 pr-4 font-medium">Jatuh Tempo</th>
                        <th class="py-3 pr-4 font-medium">Status</th>
                        @if ($canReviewPinjaman)
                        <th class="py-3 pr-4 font-medium">Aksi Verifikasi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pinjaman as $item)
                    <tr x-data="{ rejectOpen: {{ old('pinjaman_id') == $item->id ? 'true' : 'false' }} }">
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->no_pinjaman }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->anggota?->profile?->nama_lengkap ?? '-' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->tanggal_pinjaman?->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $item->jumlah_pinjaman, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-900">Rp {{ number_format((float) $item->total_tagihan, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-600">
                            {{ $item->first_due_date?->translatedFormat('d M Y') }}<br><span class="text-xs text-slate-400">akhir {{ $item->final_due_date?->translatedFormat('d M Y') }}</span>
                            @if ($item->status === 'aktif' && $item->approver)
                            <div class="mt-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs leading-5 text-emerald-700">
                                <span class="font-semibold">Disetujui oleh:</span> {{ $item->approver->profile?->nama_lengkap ?? $item->approver->username }}
                                @if ($item->disetujui_pada)
                                <br><span class="font-semibold">Waktu:</span> {{ $item->disetujui_pada->translatedFormat('d M Y H:i') }}
                                @endif
                            </div>
                            @endif
                            @if ($item->status === 'ditolak' && $item->alasan_penolakan)
                            <div class="mt-2 rounded-xl bg-rose-50 px-3 py-2 text-xs leading-5 text-rose-700">
                                <span class="font-semibold">Alasan penolakan:</span> {{ $item->alasan_penolakan }}
                                @if ($item->rejector)
                                <br><span class="font-semibold">Ditolak oleh:</span> {{ $item->rejector->profile?->nama_lengkap ?? $item->rejector->username }}
                                @endif
                                @if ($item->ditolak_pada)
                                <br><span class="font-semibold">Waktu:</span> {{ $item->ditolak_pada->translatedFormat('d M Y H:i') }}
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : ($item->status === 'lunas' ? 'bg-sky-50 text-sky-700' : ($item->status === 'ditolak' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        @if ($canReviewPinjaman)
                        <td class="py-3 pr-4">
                            @if ($item->status === 'diajukan')
                            <div class="flex min-w-[220px] gap-2">
                                <form action="{{ route('pinjaman.approve', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                                        Approve
                                    </button>
                                </form>
                                <button type="button" @click="rejectOpen = !rejectOpen" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-rose-700">
                                    Reject
                                </button>
                            </div>
                            <form x-show="rejectOpen" action="{{ route('pinjaman.reject', $item) }}" method="POST" class="mt-3 min-w-[260px] space-y-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="pinjaman_id" value="{{ $item->id }}">
                                <textarea name="alasan_penolakan" rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100" placeholder="Tulis alasan penolakan...">{{ old('pinjaman_id') == $item->id ? old('alasan_penolakan') : '' }}</textarea>
                                @if (old('pinjaman_id') == $item->id)
                                @error('alasan_penolakan')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                                @endif
                                <div class="flex gap-2">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-rose-700">
                                        Konfirmasi Tolak
                                    </button>
                                    <button type="button" @click="rejectOpen = false" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                        Batal
                                    </button>
                                </div>
                            </form>
                            @elseif ($item->status === 'aktif')
                            <span class="text-sm text-emerald-700">Sudah aktif</span>
                            @elseif ($item->status === 'ditolak')
                            <div class="space-y-1">
                                <span class="text-sm text-rose-600">Sudah ditolak</span>
                                @if ($item->alasan_penolakan)
                                <p class="text-xs leading-5 text-rose-700">{{ $item->alasan_penolakan }}</p>
                                @endif
                            </div>
                            @else
                            <span class="text-sm text-slate-400">Tidak ada aksi</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canReviewPinjaman ? 8 : 7 }}" class="py-6 text-center text-slate-500">Belum ada pinjaman yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection