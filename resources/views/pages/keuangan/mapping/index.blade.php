@extends('layouts.app')

@section('title', 'Mapping Akun Default')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Mapping Akun Default</h1>
            <p class="mt-2 text-sm text-slate-500">Tentukan akun default per modul agar transaksi siap diposting otomatis ke jurnal.</p>
        </div>
        <div class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            {{ $koperasi->nama_koperasi }}
        </div>
    </div>

    <form action="{{ route('keuangan.mapping.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($templates as $module => $items)
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ ucfirst($module) }}</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($items as $item)
                <div class="rounded-2xl border border-slate-200 p-4">
                    <label for="mapping_{{ $item['key'] }}" class="block text-sm font-medium text-slate-900">{{ $item['label'] }}</label>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $item['description'] }}</p>
                    <select id="mapping_{{ $item['key'] }}" name="mappings[{{ $item['key'] }}]" class="mt-3 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <option value="">Pilih akun</option>
                        @foreach ($akunOptions as $akun)
                        <option value="{{ $akun->id }}" @selected(old('mappings.' . $item['key'], $mappings[$item['key']]->akun_keuangan_id ?? null) == $akun->id)>{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</option>
                        @endforeach
                    </select>
                    @error('mappings.' . $item['key'])<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-slate-800">Simpan Mapping</button>
    </form>
</div>
@endsection