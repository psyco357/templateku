@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-700 px-6 py-8 text-white md:px-8">
            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/80">
                {{ $status ?? 'Dalam pengembangan' }}
            </span>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">{{ $title }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">{{ $description }}</p>
        </div>

        <div class="grid gap-6 px-6 py-8 md:grid-cols-2 md:px-8">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Tujuan Halaman</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Menu ini sudah aktif di sidebar agar struktur modul koperasi lengkap sejak awal. Implementasi detail fitur bisa dilanjutkan bertahap tanpa perlu ubah navigasi lagi.
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Langkah Berikutnya</h2>
                <p class="mt-3 text-sm leading-6 text-emerald-800">
                    Anda bisa lanjut isi modul ini dengan form, tabel, filter, laporan, atau integrasi transaksi sesuai prioritas operasional koperasi.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection