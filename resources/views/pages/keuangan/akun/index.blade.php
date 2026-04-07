@extends('layouts.app')

@section('title', 'Daftar Akun Keuangan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Daftar Akun / COA</h1>
            <p class="mt-2 text-sm text-slate-500">Kelola chart of accounts koperasi sebagai dasar jurnal, buku besar, dan laporan keuangan.</p>
        </div>
        <div class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            {{ $koperasi->nama_koperasi }}
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Akun</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['total_akun'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Akun Posting</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['akun_posting'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Akun Header</p>
            <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $summary['akun_header'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Akun Aktif</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-700">{{ $summary['akun_aktif'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ $editAccount ? 'Edit Akun' : 'Tambah Akun' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan akun induk untuk membentuk struktur COA bertingkat per koperasi.</p>

            <form action="{{ $editAccount ? route('keuangan.akun.update', $editAccount) : route('keuangan.akun.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                @if ($editAccount)
                @method('PATCH')
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="kode_akun" class="mb-2 block text-sm font-medium text-slate-700">Kode akun</label>
                        <input type="text" id="kode_akun" name="kode_akun" value="{{ old('kode_akun', $editAccount?->kode_akun) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Contoh: 1110">
                        @error('kode_akun')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nama_akun" class="mb-2 block text-sm font-medium text-slate-700">Nama akun</label>
                        <input type="text" id="nama_akun" name="nama_akun" value="{{ old('nama_akun', $editAccount?->nama_akun) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Contoh: Kas Kecil">
                        @error('nama_akun')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="tipe_akun" class="mb-2 block text-sm font-medium text-slate-700">Tipe akun</label>
                        <select id="tipe_akun" name="tipe_akun" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @foreach ($tipeOptions as $tipe)
                            <option value="{{ $tipe }}" @selected(old('tipe_akun', $editAccount?->tipe_akun ?? 'aset' )===$tipe)>{{ ucfirst($tipe) }}</option>
                            @endforeach
                        </select>
                        @error('tipe_akun')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="parent_id" class="mb-2 block text-sm font-medium text-slate-700">Akun induk</label>
                        <select id="parent_id" name="parent_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Tanpa induk</option>
                            @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $editAccount?->parent_id)==$parent->id)>{{ $parent->kode_akun }} - {{ $parent->nama_akun }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                        <select id="status" name="status" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected(old('status', $editAccount?->status ?? 'aktif' )===$status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="is_header" value="1" @checked(old('is_header', $editAccount?->is_header)) class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                        Jadikan sebagai akun header
                    </label>
                    <p class="mt-2 text-xs text-slate-500">Akun header dipakai sebagai pengelompokan dan tidak untuk posting transaksi langsung.</p>
                </div>

                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-medium text-slate-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Catatan penggunaan akun">{{ old('keterangan', $editAccount?->keterangan) }}</textarea>
                    @error('keterangan')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">{{ $editAccount ? 'Simpan perubahan' : 'Simpan akun' }}</button>
                    @if ($editAccount)
                    <a href="{{ route('keuangan.akun.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Batal edit</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Filter Akun</h2>
                <form action="{{ route('keuangan.akun.index') }}" method="GET" class="mt-5 grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="filter_tipe_akun" class="mb-2 block text-sm font-medium text-slate-700">Tipe akun</label>
                        <select id="filter_tipe_akun" name="tipe_akun" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">Semua tipe</option>
                            @foreach ($tipeOptions as $tipe)
                            <option value="{{ $tipe }}" @selected($filters['tipe_akun']===$tipe)>{{ ucfirst($tipe) }}</option>
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
                        <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Terapkan</button>
                        <a href="{{ route('keuangan.akun.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Daftar Akun</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-3 pr-4 font-medium">Kode</th>
                                <th class="py-3 pr-4 font-medium">Nama Akun</th>
                                <th class="py-3 pr-4 font-medium">Tipe</th>
                                <th class="py-3 pr-4 font-medium">Induk</th>
                                <th class="py-3 pr-4 font-medium">Level</th>
                                <th class="py-3 pr-4 font-medium">Posting</th>
                                <th class="py-3 pr-4 font-medium">Status</th>
                                <th class="py-3 pr-4 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($akun as $item)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->kode_akun }}</td>
                                <td class="py-3 pr-4 text-slate-900">
                                    <div class="break-words">
                                        @for ($i = 1; $i < $item->level; $i++)
                                            <span class="mr-2 text-slate-300">└</span>
                                            @endfor
                                            {{ $item->nama_akun }}
                                            @if ($item->is_header)
                                            <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Header</span>
                                            @endif
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ ucfirst($item->tipe_akun) }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $item->parent?->kode_akun ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $item->level }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $item->can_post ? 'Ya' : 'Tidak' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 align-top">
                                    <div class="flex min-w-0 flex-col gap-2 sm:w-32">
                                        <a href="{{ route('keuangan.akun.index', array_merge(request()->query(), ['edit' => $item->id])) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">Edit</a>
                                        @if ($item->status === 'aktif')
                                        <form action="{{ route('keuangan.akun.deactivate', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-rose-500">Nonaktifkan</button>
                                        </form>
                                        @else
                                        <span class="rounded-xl bg-slate-50 px-3 py-2 text-center text-xs text-slate-500">Sudah nonaktif</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-500">Belum ada akun keuangan untuk koperasi ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $akun->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection