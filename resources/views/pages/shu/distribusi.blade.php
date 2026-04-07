@extends('layouts.app')

@section('title', 'Distribusi SHU Anggota')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Distribusi SHU Anggota</h1>
            <p class="mt-2 text-sm text-slate-500">Pembagian otomatis anggota dihitung dari pool jasa modal dan jasa usaha yang berasal dari simulasi SHU tahun {{ $year }}.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('shu.distribusi.export-excel', array_merge(['tahun' => $year], $percentages)) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">Export Excel</a>
            <a href="{{ route('shu.distribusi.export-pdf', array_merge(['tahun' => $year], $percentages)) }}" class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-3 text-sm font-medium text-rose-700 transition hover:border-rose-300 hover:text-rose-800">Export PDF</a>
            <a href="{{ route('shu.perhitungan', array_merge(['tahun' => $year], $percentages)) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Ubah Simulasi SHU</a>
        </div>
    </div>

    <div class="rounded-3xl border {{ $savedScheme ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-4 shadow-sm">
        <p class="text-sm font-semibold text-slate-900">{{ $savedScheme ? 'Distribusi memakai skema tersimpan' : 'Distribusi memakai simulasi sementara' }}</p>
        <p class="mt-1 text-sm text-slate-600">
            @if ($savedScheme)
            Skema tahun {{ $year }} sudah tersimpan sehingga distribusi ini konsisten dengan komposisi SHU default founder.
            @else
            Distribusi ini memakai persentase dari simulasi yang sedang aktif. Simpan skema di halaman perhitungan jika ingin menjadikannya default.
            @endif
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Dasar SHU</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['shu_dasar'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pool Jasa Modal</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($allocation['jasa_modal'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pool Jasa Usaha</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($allocation['jasa_usaha'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Anggota Penerima</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['anggota_penerima'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">SHU Sudah Dibayar</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-700">Rp {{ number_format($paymentSummary['total_terbayar'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Sisa Kewajiban SHU</p>
            <p class="mt-3 text-2xl font-semibold text-amber-700">Rp {{ number_format($paymentSummary['total_sisa'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Dasar Pembobotan</h2>
            <div class="mt-5 space-y-4 text-sm">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Total simpanan anggota tahun {{ $year }}</span><span class="font-semibold text-slate-900">Rp {{ number_format($weightSummary['total_simpanan'], 0, ',', '.') }}</span></div>
                    <p class="mt-2 text-slate-500">Dipakai untuk membagi porsi jasa modal.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-slate-700">Total bunga yang dibayar anggota</span><span class="font-semibold text-slate-900">Rp {{ number_format($weightSummary['total_jasa_usaha'], 0, ',', '.') }}</span></div>
                    <p class="mt-2 text-slate-500">Dipakai untuk membagi porsi jasa usaha.</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    <p class="font-semibold">Penjelasan sederhana</p>
                    <p class="mt-2">Anggota dengan simpanan lebih besar akan mendapat porsi jasa modal lebih besar. Anggota dengan kontribusi usaha lebih besar melalui pembayaran bunga pinjaman akan mendapat porsi jasa usaha lebih besar.</p>
                    <p class="mt-2">Sisa pembulatan dibagikan otomatis ke pecahan terbesar agar total distribusi tetap pas sampai rupiah terakhir.</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Distribusi per Anggota</h2>
            <p class="mt-1 text-sm text-slate-500">Nilai di bawah ini adalah simulasi otomatis dari komposisi SHU yang saat ini dipilih founder.</p>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-3 pr-4 font-medium">Anggota</th>
                            <th class="py-3 pr-4 font-medium">Status</th>
                            <th class="py-3 pr-4 font-medium text-right">Simpanan</th>
                            <th class="py-3 pr-4 font-medium text-right">Jasa Usaha</th>
                            <th class="py-3 pr-4 font-medium text-right">Bagian Modal</th>
                            <th class="py-3 pr-4 font-medium text-right">Bagian Usaha</th>
                            <th class="py-3 pr-4 font-medium text-right">Penyesuaian</th>
                            <th class="py-3 pr-4 font-medium text-right">Total SHU</th>
                            <th class="py-3 pr-4 font-medium text-right">Terbayar</th>
                            <th class="py-3 pr-4 font-medium text-right">Sisa</th>
                            <th class="py-3 pr-4 font-medium">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($paymentSummary['members'] as $item)
                        <tr>
                            <td class="py-3 pr-4">
                                <p class="font-medium text-slate-900">{{ $item['nama'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['no_anggota'] ?: '-' }}</p>
                            </td>
                            <td class="py-3 pr-4 text-slate-600">{{ ucfirst($item['status']) }}</td>
                            <td class="py-3 pr-4 text-right text-slate-600">Rp {{ number_format($item['total_simpanan'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right text-slate-600">Rp {{ number_format($item['total_jasa_usaha'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-medium text-slate-900">Rp {{ number_format($item['bagian_modal'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-medium text-slate-900">Rp {{ number_format($item['bagian_usaha'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right text-slate-600">{{ $item['penyesuaian_pembulatan'] > 0 ? '+' . number_format($item['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</td>
                            <td class="py-3 pr-4 text-right font-semibold {{ $item['total_shu'] > 0 ? 'text-emerald-700' : 'text-slate-500' }}">Rp {{ number_format($item['total_shu'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-medium text-slate-900">Rp {{ number_format($item['shu_terbayar'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-medium {{ $item['sisa_bayar'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">Rp {{ number_format($item['sisa_bayar'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4">
                                @if ($item['sisa_bayar'] > 0)
                                <form action="{{ route('shu.bayar') }}" method="POST" class="flex min-w-[240px] flex-col gap-2">
                                    @csrf
                                    <input type="hidden" name="tahun" value="{{ $year }}">
                                    <input type="hidden" name="anggota_id" value="{{ $item['anggota_id'] }}">
                                    <input type="date" name="tanggal_bayar" min="{{ $paymentDateMin }}" value="{{ old('tanggal_bayar', $paymentDateMin) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                    <input type="number" step="0.01" min="0.01" max="{{ $item['sisa_bayar'] }}" name="jumlah_bayar" value="{{ number_format($item['sisa_bayar'], 2, '.', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                    <p class="text-xs text-slate-500">Pembayaran SHU mulai bisa dicatat per {{ \Illuminate\Support\Carbon::parse($paymentDateMin)->translatedFormat('d M Y') }}.</p>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800">Bayar SHU</button>
                                </form>
                                @else
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-6 text-center text-slate-500">Belum ada data anggota untuk distribusi SHU.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td colspan="4" class="py-3 pr-4 font-semibold text-slate-900">Total Distribusi</td>
                            <td class="py-3 pr-4 text-right font-semibold text-slate-900">Rp {{ number_format($distributedTotals['bagian_modal'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-semibold text-slate-900">Rp {{ number_format($distributedTotals['bagian_usaha'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-semibold text-slate-900">{{ $distributedTotals['penyesuaian_pembulatan'] > 0 ? '+' . number_format($distributedTotals['penyesuaian_pembulatan'], 0, ',', '.') : '-' }}</td>
                            <td class="py-3 pr-4 text-right font-semibold text-emerald-700">Rp {{ number_format($distributedTotals['total_shu'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-semibold text-slate-900">Rp {{ number_format($paymentSummary['total_terbayar'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-right font-semibold text-amber-700">Rp {{ number_format($paymentSummary['total_sisa'], 0, ',', '.') }}</td>
                            <td class="py-3 pr-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="mt-3 text-xs text-slate-500">Total distribusi jasa modal dan jasa usaha pada tabel ini selalu sama persis dengan pool SHU yang disimulasikan.</p>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Histori Pembayaran SHU</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="py-3 pr-4 font-medium">No. Bukti</th>
                        <th class="py-3 pr-4 font-medium">Anggota</th>
                        <th class="py-3 pr-4 font-medium text-right">Jumlah</th>
                        <th class="py-3 pr-4 font-medium">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($paymentSummary['payments'] as $payment)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $payment->tanggal_bayar?->translatedFormat('d M Y') }}</td>
                        <td class="py-3 pr-4 font-medium text-slate-900">{{ $payment->no_bukti }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $payment->anggota?->profile?->nama_lengkap ?? '-' }}</td>
                        <td class="py-3 pr-4 text-right font-medium text-slate-900">Rp {{ number_format((float) $payment->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $payment->creator?->profile?->nama_lengkap ?? $payment->creator?->username ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-500">Belum ada pembayaran SHU yang dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Histori Skema Tahun {{ $year }}</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat ini menunjukkan perubahan skema yang menjadi dasar distribusi SHU anggota.</p>
            </div>
            <p class="text-sm text-slate-500">{{ $schemeHistory->count() }} riwayat terakhir</p>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Waktu</th>
                        <th class="py-3 pr-4 font-medium">Aksi</th>
                        <th class="py-3 pr-4 font-medium">Disimpan Oleh</th>
                        <th class="py-3 pr-4 font-medium text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($schemeHistory as $history)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $history->created_at?->translatedFormat('d M Y H:i') }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $history->aksi === 'create' ? 'Skema dibuat' : 'Skema diperbarui' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $history->user?->profile?->nama_lengkap ?? $history->user?->username ?? 'Founder' }}</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->total_persen, 2, ',', '.') }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-500">Belum ada histori skema untuk tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection