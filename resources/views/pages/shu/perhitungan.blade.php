@extends('layouts.app')

@section('title', 'Perhitungan SHU Tahunan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Perhitungan SHU Tahunan</h1>
            <p class="mt-2 text-sm text-slate-500">Simulasikan pembagian SHU dari laba bersih tahunan. Persentase dapat diatur founder dan langsung memakai basis laba bersih dari laporan rugi laba tahunan.</p>
        </div>

        <a href="{{ route('laporan.rugi-laba-tahunan', ['tahun' => $year]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Lihat Laba Rugi Tahunan</a>
    </div>

    <div class="rounded-3xl border {{ $savedScheme ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white' }} p-4 shadow-sm">
        <p class="text-sm font-semibold text-slate-900">{{ $savedScheme ? 'Skema tersimpan ditemukan' : 'Belum ada skema tersimpan' }}</p>
        <p class="mt-1 text-sm text-slate-600">
            @if ($savedScheme)
            Tahun {{ $year }} sudah memiliki skema SHU tersimpan. Form ini otomatis memakai nilai tersebut sebagai default agar founder tidak perlu mengisi ulang.
            @else
            Simpan skema tahun {{ $year }} jika komposisi ini ingin dipakai lagi sebagai default pada pembukaan berikutnya.
            @endif
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Laba Bersih Tahun {{ $year }}</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['laba_bersih'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Dasar SHU</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['shu_dasar'] > 0 ? 'text-amber-700' : 'text-slate-500' }}">Rp {{ number_format($summary['shu_dasar'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Persentase</p>
            <p class="mt-3 text-2xl font-semibold {{ abs($totalPercentage - 100) < 0.01 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($totalPercentage, 0, ',', '.') }}%</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Status Simulasi</p>
            <p class="mt-3 text-2xl font-semibold {{ $summary['shu_status'] === 'siap-disimulasikan' ? 'text-emerald-700' : 'text-slate-500' }}">{{ $summary['shu_status'] === 'siap-disimulasikan' ? 'Siap Dibagi' : 'Belum Ada SHU' }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Pengaturan Persentase</h2>
            <p class="mt-1 text-sm text-slate-500">Pastikan total persentase tepat 100% agar seluruh dasar SHU teralokasi.</p>

            <form action="{{ route('shu.perhitungan') }}" method="GET" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="tahun" class="mb-2 block text-sm font-medium text-slate-700">Tahun Buku</label>
                    <input type="number" id="tahun" name="tahun" min="2020" value="{{ $year }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="cadangan" class="mb-2 block text-sm font-medium text-slate-700">Cadangan</label>
                        <input type="number" id="cadangan" name="cadangan" min="0" max="100" step="0.01" value="{{ $percentages['cadangan'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="jasa_modal" class="mb-2 block text-sm font-medium text-slate-700">Jasa Modal Anggota</label>
                        <input type="number" id="jasa_modal" name="jasa_modal" min="0" max="100" step="0.01" value="{{ $percentages['jasa_modal'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="jasa_usaha" class="mb-2 block text-sm font-medium text-slate-700">Jasa Usaha Anggota</label>
                        <input type="number" id="jasa_usaha" name="jasa_usaha" min="0" max="100" step="0.01" value="{{ $percentages['jasa_usaha'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label for="dana_pengurus" class="mb-2 block text-sm font-medium text-slate-700">Dana Pengurus</label>
                        <input type="number" id="dana_pengurus" name="dana_pengurus" min="0" max="100" step="0.01" value="{{ $percentages['dana_pengurus'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="md:col-span-2">
                        <label for="dana_sosial" class="mb-2 block text-sm font-medium text-slate-700">Dana Sosial</label>
                        <input type="number" id="dana_sosial" name="dana_sosial" min="0" max="100" step="0.01" value="{{ $percentages['dana_sosial'] }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Hitung Simulasi</button>
                    <button type="submit" formmethod="POST" formaction="{{ route('shu.simpan-skema') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500">Simpan Skema SHU</button>
                    <a href="{{ route('shu.distribusi', array_merge(['tahun' => $year], $percentages)) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Lihat Distribusi Anggota</a>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Hasil Simulasi Alokasi</h2>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Cadangan</span><span class="font-semibold text-slate-900">Rp {{ number_format($allocation['cadangan'], 0, ',', '.') }}</span></div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Jasa Modal Anggota</span><span class="font-semibold text-slate-900">Rp {{ number_format($allocation['jasa_modal'], 0, ',', '.') }}</span></div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Jasa Usaha Anggota</span><span class="font-semibold text-slate-900">Rp {{ number_format($allocation['jasa_usaha'], 0, ',', '.') }}</span></div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Dana Pengurus</span><span class="font-semibold text-slate-900">Rp {{ number_format($allocation['dana_pengurus'], 0, ',', '.') }}</span></div>
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Dana Sosial</span><span class="font-semibold text-slate-900">Rp {{ number_format($allocation['dana_sosial'], 0, ',', '.') }}</span></div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3"><span class="font-semibold text-slate-900">Total Alokasi</span><span class="text-base font-semibold text-slate-900">Rp {{ number_format(array_sum($allocation), 0, ',', '.') }}</span></div>
            </div>

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">Catatan SHU</p>
                <p class="mt-2">Simulasi ini otomatis memakai laba bersih tahunan sebagai dasar SHU. Hasilnya adalah rancangan pembagian awal yang bisa dibawa ke RAT, bukan keputusan final yang mengikat.</p>
                <p class="mt-2">Alokasi dibulatkan ke rupiah penuh dan disesuaikan otomatis agar total pembagian tetap sama persis dengan dasar SHU sampai rupiah terakhir.</p>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Histori Perubahan Skema Tahun {{ $year }}</h2>
                <p class="mt-1 text-sm text-slate-500">Audit ringkas perubahan skema SHU, termasuk siapa yang menyimpan dan kapan perubahan dilakukan.</p>
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
                        <th class="py-3 pr-4 font-medium text-right">Cadangan</th>
                        <th class="py-3 pr-4 font-medium text-right">Jasa Modal</th>
                        <th class="py-3 pr-4 font-medium text-right">Jasa Usaha</th>
                        <th class="py-3 pr-4 font-medium text-right">Pengurus</th>
                        <th class="py-3 pr-4 font-medium text-right">Sosial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($schemeHistory as $history)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $history->created_at?->translatedFormat('d M Y H:i') }}</td>
                        <td class="py-3 pr-4 text-slate-900">{{ $history->aksi === 'create' ? 'Skema dibuat' : 'Skema diperbarui' }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $history->user?->profile?->nama_lengkap ?? $history->user?->username ?? 'Founder' }}</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->cadangan, 2, ',', '.') }}%</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->jasa_modal, 2, ',', '.') }}%</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->jasa_usaha, 2, ',', '.') }}%</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->dana_pengurus, 2, ',', '.') }}%</td>
                        <td class="py-3 pr-4 text-right text-slate-600">{{ number_format((float) $history->dana_sosial, 2, ',', '.') }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-500">Belum ada histori perubahan skema untuk tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection