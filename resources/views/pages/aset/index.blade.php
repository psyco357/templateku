@extends('layouts.app')

@section('title', 'Aset Koperasi')

@section('content')
<div class="space-y-6" x-data="{
    deactivateModalOpen: false,
    deactivateFormAction: '',
    selectedAssetName: '',
    selectedAssetDateMin: '',
    selectedAssetValue: '',
    selectedDeactivateType: 'jual',
    openDeactivateModal(asset) {
        this.deactivateFormAction = asset.action;
        this.selectedAssetName = asset.name;
        this.selectedAssetDateMin = asset.minDate;
        this.selectedAssetValue = asset.value;
        this.selectedDeactivateType = 'jual';
        this.deactivateModalOpen = true;
        this.$nextTick(() => this.$refs.tanggalNonaktifInput?.focus());
    },
    closeDeactivateModal() {
        this.deactivateModalOpen = false;
    }
}" @keydown.escape.window="closeDeactivateModal()">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Aset Koperasi</h1>
            <p class="mt-2 text-sm text-slate-500">Catat aset koperasi seperti emas, inventaris, atau properti agar neraca dan RAT mencerminkan posisi keuangan sebenarnya.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('laporan.neraca-keuangan') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Lihat Neraca</a>
            <a href="{{ route('laporan.rat') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Lihat Rekap RAT</a>
        </div>
    </div>


    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Aset</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['total_aset'] }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Aset Aktif</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['aset_aktif'] }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Nilai</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['total_nilai'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Nilai Aset Emas</p>
                <p class="mt-3 text-2xl font-semibold text-amber-700">Rp {{ number_format($summary['total_emas'], 0, ',', '.') }}</p>
            </div>
        </div>


    </div>
    <div class="gap-6 ">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ $editAsset ? 'Edit Aset' : 'Form Aset Baru' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan jenis aset `emas` jika kas koperasi dipakai membeli emas.</p>

            <form action="{{ $editAsset ? route('aset.update', $editAsset) : route('aset.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                @if ($editAsset)
                @method('PATCH')
                @endif
                <div>
                    <label for="nama_aset" class="mb-2 block text-sm font-medium text-slate-700">Nama aset</label>
                    <input type="text" id="nama_aset" name="nama_aset" value="{{ old('nama_aset', $editAsset?->nama_aset) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Contoh: Emas Antam 10 gram">
                    @error('nama_aset')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="jenis_aset" class="mb-2 block text-sm font-medium text-slate-700">Jenis aset</label>
                        <select id="jenis_aset" name="jenis_aset" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @foreach ($jenisOptions as $jenis)
                            <option value="{{ $jenis }}" @selected(old('jenis_aset', $editAsset?->jenis_aset ?? 'emas' )===$jenis)>{{ ucfirst($jenis) }}</option>
                            @endforeach
                        </select>
                        @error('jenis_aset')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tanggal_perolehan" class="mb-2 block text-sm font-medium text-slate-700">Tanggal perolehan</label>
                        <input type="date" id="tanggal_perolehan" name="tanggal_perolehan" value="{{ old('tanggal_perolehan', $editAsset?->tanggal_perolehan?->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @error('tanggal_perolehan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="nilai_perolehan" class="mb-2 block text-sm font-medium text-slate-700">Nilai perolehan</label>
                        <input type="number" step="0.01" min="0.01" id="nilai_perolehan" name="nilai_perolehan" value="{{ old('nilai_perolehan', $editAsset?->nilai_perolehan) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="0.00">
                        @error('nilai_perolehan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="kuantitas" class="mb-2 block text-sm font-medium text-slate-700">Kuantitas</label>
                        <input type="number" step="0.0001" min="0.0001" id="kuantitas" name="kuantitas" value="{{ old('kuantitas', $editAsset?->kuantitas) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Opsional">
                        @error('kuantitas')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="satuan" class="mb-2 block text-sm font-medium text-slate-700">Satuan</label>
                        <input type="text" id="satuan" name="satuan" value="{{ old('satuan', $editAsset?->satuan) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="gram, unit, lot">
                        @error('satuan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Catatan pembelian aset">{{ old('keterangan', $editAsset?->keterangan) }}</textarea>
                    @error('keterangan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">{{ $editAsset ? 'Simpan perubahan' : 'Simpan aset' }}</button>
                    @if ($editAsset)
                    <a href="{{ route('aset.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Batal edit</a>
                    @endif
                </div>
            </form>
        </div>

    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Filter Aset</h2>
        <form action="{{ route('aset.index') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
            <div>
                <label for="filter_jenis_aset" class="mb-2 block text-sm font-medium text-slate-700">Jenis aset</label>
                <select id="filter_jenis_aset" name="jenis_aset" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua jenis</option>
                    @foreach ($jenisOptions as $jenis)
                    <option value="{{ $jenis }}" @selected($filters['jenis_aset']===$jenis)>{{ ucfirst($jenis) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_status" class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                <select id="filter_status" name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $status)
                    <option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-3 md:items-end">
                <button type="submit" class="inline-flex items-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-amber-400">Terapkan</button>
                <a href="{{ route('aset.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
            </div>
        </form>
    </div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Daftar Aset</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-3 pr-4 font-medium">Tanggal</th>
                        <th class="hidden py-3 pr-4 font-medium md:table-cell">Kode</th>
                        <th class="py-3 pr-4 font-medium">Nama</th>
                        <th class="py-3 pr-4 font-medium">Jenis</th>
                        <th class="hidden py-3 pr-4 font-medium lg:table-cell">Kuantitas</th>
                        <th class="py-3 pr-4 font-medium">Status</th>
                        <th class="hidden py-3 pr-4 font-medium xl:table-cell">Nonaktif / Pelepasan</th>
                        <th class="hidden py-3 pr-4 font-medium xl:table-cell">Keterangan</th>
                        <th class="py-3 pr-4 font-medium text-right">Nilai</th>
                        <th class="py-3 pr-4 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($aset as $item)
                    <tr>
                        <td class="py-3 pr-4 text-slate-600">{{ $item->tanggal_perolehan?->translatedFormat('d M Y') }}</td>
                        <td class="hidden py-3 pr-4 font-medium text-slate-900 md:table-cell">{{ $item->kode_aset }}</td>
                        <td class="py-3 pr-4 text-slate-900">
                            <div class="max-w-[12rem] break-words sm:max-w-none">{{ $item->nama_aset }}</div>
                        </td>
                        <td class="py-3 pr-4 text-slate-600">{{ ucfirst($item->jenis_aset) }}</td>
                        <td class="hidden py-3 pr-4 text-slate-600 lg:table-cell">{{ $item->kuantitas ? rtrim(rtrim(number_format((float) $item->kuantitas, 4, ',', '.'), '0'), ',') . ' ' . ($item->satuan ?? '') : '-' }}</td>
                        <td class="py-3 pr-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($item->status) }}</span></td>
                        <td class="hidden py-3 pr-4 text-slate-600 xl:table-cell">
                            @if ($item->tanggal_nonaktif)
                            {{ $item->tanggal_nonaktif->translatedFormat('d M Y') }}
                            @if ($item->nilai_pelepasan)
                            <br><span class="text-xs text-slate-400">Rp {{ number_format((float) $item->nilai_pelepasan, 0, ',', '.') }}</span>
                            @endif
                            @else
                            -
                            @endif
                        </td>
                        <td class="hidden py-3 pr-4 text-slate-600 xl:table-cell">
                            <div class="max-w-[16rem] break-words">{{ $item->keterangan ?: '-' }}</div>
                        </td>
                        <td class="py-3 pr-4 text-right font-medium text-slate-900">Rp {{ number_format((float) $item->nilai_perolehan, 0, ',', '.') }}</td>
                        <td class="py-3 pr-4 align-top">
                            <div class="flex min-w-0 flex-col gap-2 sm:w-40">
                                <a href="{{ route('aset.index', array_merge(request()->query(), ['edit' => $item->id])) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Edit</a>
                                @if ($item->status === 'aktif')
                                <button
                                    type="button"
                                    data-action="{{ route('aset.deactivate', $item) }}"
                                    data-name="{{ $item->nama_aset }}"
                                    data-min-date="{{ $item->tanggal_perolehan?->toDateString() }}"
                                    data-value="{{ (float) $item->nilai_perolehan }}"
                                    @click="openDeactivateModal({ action: $el.dataset.action, name: $el.dataset.name, minDate: $el.dataset.minDate || null, value: Number($el.dataset.value) })"
                                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-rose-500">Nonaktifkan</button>
                                @else
                                <span class="rounded-xl bg-slate-50 px-3 py-2 text-center text-xs text-slate-500">Sudah nonaktif</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center text-slate-500">Belum ada aset koperasi yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $aset->links() }}
        </div>
    </div>

    <div x-cloak x-show="deactivateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50" @click="closeDeactivateModal()"></div>

        <div x-show="deactivateModalOpen" x-transition class="relative z-10 w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Nonaktifkan Aset</h2>
                    <p class="mt-1 text-sm text-slate-500">Pilih apakah aset dinonaktifkan karena dijual atau hanya dikeluarkan dari aset aktif.</p>
                </div>
                <button type="button" @click="closeDeactivateModal()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700">
                    <span class="sr-only">Tutup modal</span>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-medium text-slate-900" x-text="selectedAssetName"></p>
                <p class="mt-1">Nilai perolehan: Rp <span x-text="window.formatRupiah(selectedAssetValue || 0)"></span></p>
            </div>

            <form :action="deactivateFormAction" method="POST" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="modal_tanggal_nonaktif" class="mb-2 block text-sm font-medium text-slate-700">Tanggal nonaktif</label>
                    <input x-ref="tanggalNonaktifInput" type="date" id="modal_tanggal_nonaktif" name="tanggal_nonaktif" value="{{ now()->toDateString() }}" :min="selectedAssetDateMin" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>

                <div>
                    <label for="modal_tipe_nonaktif" class="mb-2 block text-sm font-medium text-slate-700">Aksi nonaktif</label>
                    <select id="modal_tipe_nonaktif" name="tipe_nonaktif" x-model="selectedDeactivateType" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        @foreach ($tipeNonaktifOptions as $tipeNonaktif)
                        <option value="{{ $tipeNonaktif }}">{{ $tipeNonaktif === 'jual' ? 'Jual aset' : 'Nonaktif tanpa penjualan' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="modal_nilai_pelepasan" class="mb-2 block text-sm font-medium text-slate-700">Nilai pelepasan</label>
                    <input type="number" step="0.01" min="0" id="modal_nilai_pelepasan" name="nilai_pelepasan" :disabled="selectedDeactivateType !== 'jual'" :placeholder="selectedDeactivateType === 'jual' ? 'Kosongkan untuk pakai nilai perolehan' : 'Tidak dipakai untuk nonaktif tanpa penjualan'" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-50 disabled:text-slate-400">
                    <p class="mt-2 text-xs text-slate-400" x-show="selectedDeactivateType === 'jual'">Jika dikosongkan, sistem akan memakai nilai perolehan sebagai nilai jual.</p>
                    <p class="mt-2 text-xs text-slate-400" x-show="selectedDeactivateType !== 'jual'">Pilihan ini hanya menonaktifkan aset tanpa mencatat kas masuk.</p>
                </div>

                <div class="flex flex-wrap justify-end gap-3 pt-2">
                    <button type="button" @click="closeDeactivateModal()" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Batal</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-rose-500">Simpan Aksi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection