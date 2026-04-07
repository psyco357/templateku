@extends('layouts.app')

@section('title', 'Appearance')

@section('content')
@php
	$initialAppearance = [
		'landing_theme' => old('landing_theme', $landingPage['theme_key']),
		'landing_hero_title' => old('landing_hero_title', $koperasi->landing_hero_title ?? $landingPage['hero_title']),
		'landing_hero_subtitle' => old('landing_hero_subtitle', $koperasi->landing_hero_subtitle ?? $landingPage['hero_subtitle']),
		'landing_hero_image' => old('landing_hero_image', $koperasi->landing_hero_image ?? $landingPage['hero_image']),
		'landing_about_title' => old('landing_about_title', $koperasi->landing_about_title ?? $landingPage['about_title']),
		'landing_about_description' => old('landing_about_description', $koperasi->landing_about_description ?? $landingPage['about_description']),
	];
@endphp

<div class="space-y-6"
	x-data="{
		themeOptions: @js($themeOptions),
		form: @js($initialAppearance),
		get activeTheme() {
			return this.themeOptions[this.form.landing_theme] ?? this.themeOptions.amber;
		},
		get activeThemeLabel() {
			return this.activeTheme.label ?? 'Amber Hangat';
		},
		get aboutPreview() {
			return this.form.landing_about_description?.trim() || 'Deskripsi profil koperasi akan tampil di sini.';
		},
		get heroImagePreview() {
			return this.form.landing_hero_image?.trim() || '{{ $landingPage['hero_image'] }}';
		}
	}">
	<div class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-8">
		<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
			<div>
				<h3 class="font-semibold text-gray-800 text-theme-xl dark:text-white/90 sm:text-2xl">
					Tampilan Landing Page
				</h3>
				<p class="mt-1 text-sm text-gray-500 dark:text-gray-400 sm:text-base">
					Atur tema visual, konten hero, dan profil singkat yang tampil di halaman depan koperasi.
				</p>
			</div>

			<a href="{{ url('/') }}" target="_blank"
				class="inline-flex h-11 items-center justify-center rounded-lg bg-slate-700 px-5 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
				Lihat Landing Page
			</a>
		</div>
	</div>

	<div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(360px,1fr)]">
		<div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-8">
			<h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Form Pengaturan</h4>
			<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perubahan akan disimpan ke profil koperasi utama dan langsung dipakai oleh halaman publik.</p>

			<form method="POST" action="{{ route('settings.appearance.update') }}" class="mt-6 space-y-8">
				@csrf
				@method('PUT')

				<section class="space-y-4">
					<div>
						<h5 class="text-base font-semibold text-gray-800 dark:text-white/90">Preset Tema</h5>
						<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih warna utama yang akan dipakai di hero, tombol, dan aksen landing page.</p>
					</div>

					<div class="grid gap-4 md:grid-cols-2">
						@foreach ($themeOptions as $themeKey => $theme)
						<label class="block cursor-pointer rounded-2xl border p-4 transition"
							:class="form.landing_theme === '{{ $themeKey }}' ? 'border-orange-400 bg-orange-50 dark:border-orange-500 dark:bg-orange-500/10' : 'border-gray-200 bg-white hover:border-orange-300 dark:border-gray-700 dark:bg-gray-900/40 dark:hover:border-orange-600'">
							<input type="radio" name="landing_theme" value="{{ $themeKey }}" class="sr-only" x-model="form.landing_theme">
							<div class="flex items-start justify-between gap-4">
								<div>
									<p class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $theme['label'] }}</p>
									<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $theme['description'] }}</p>
								</div>
								<span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border px-2 text-xs font-semibold"
									:class="form.landing_theme === '{{ $themeKey }}' ? 'border-orange-300 bg-white text-orange-600 dark:border-orange-500 dark:bg-orange-500/10 dark:text-orange-300' : 'border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400'"
									x-text="form.landing_theme === '{{ $themeKey }}' ? 'Aktif' : 'Pilih'"></span>
							</div>
							<div class="mt-4 flex gap-2">
								<span class="h-10 flex-1 rounded-xl {{ $theme['swatch_gradient_class'] }}"></span>
								<span class="h-10 w-10 rounded-xl {{ $theme['accent_bg_class'] }}"></span>
								<span class="h-10 w-10 rounded-xl border border-black/5 dark:border-white/10 {{ $theme['surface_class'] }}"></span>
							</div>
						</label>
						@endforeach
					</div>
					@error('landing_theme')<p class="text-sm text-red-500">{{ $message }}</p>@enderror
				</section>

				<section class="grid gap-5 md:grid-cols-2">
					<div class="md:col-span-2">
						<h5 class="text-base font-semibold text-gray-800 dark:text-white/90">Hero Section</h5>
						<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bagian ini tampil paling atas di halaman landing page.</p>
					</div>

					<div class="md:col-span-2">
						<label for="landing_hero_title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Hero</label>
						<textarea id="landing_hero_title" name="landing_hero_title" rows="3" x-model="form.landing_hero_title"
							class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $initialAppearance['landing_hero_title'] }}</textarea>
						<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pisahkan baris dengan enter jika ingin judul tampil dua baris.</p>
						@error('landing_hero_title')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
					</div>

					<div class="md:col-span-2">
						<label for="landing_hero_subtitle" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Subjudul Hero</label>
						<textarea id="landing_hero_subtitle" name="landing_hero_subtitle" rows="3" x-model="form.landing_hero_subtitle"
							class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $initialAppearance['landing_hero_subtitle'] }}</textarea>
						@error('landing_hero_subtitle')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
					</div>

					<div class="md:col-span-2">
						<label for="landing_hero_image" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL Gambar Hero</label>
						<input type="url" id="landing_hero_image" name="landing_hero_image" value="{{ $initialAppearance['landing_hero_image'] }}" x-model="form.landing_hero_image"
							class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
						<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Gunakan URL gambar publik dengan ukuran lebar agar hero terlihat proporsional.</p>
						@error('landing_hero_image')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
					</div>
				</section>

				<section class="grid gap-5 md:grid-cols-2">
					<div class="md:col-span-2">
						<h5 class="text-base font-semibold text-gray-800 dark:text-white/90">Profil Singkat</h5>
						<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Konten ini dipakai pada blok “Tentang Kami” di landing page.</p>
					</div>

					<div class="md:col-span-2">
						<label for="landing_about_title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Profil</label>
						<input type="text" id="landing_about_title" name="landing_about_title" value="{{ $initialAppearance['landing_about_title'] }}" x-model="form.landing_about_title"
							class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
						@error('landing_about_title')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
					</div>

					<div class="md:col-span-2">
						<label for="landing_about_description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi Profil</label>
						<textarea id="landing_about_description" name="landing_about_description" rows="6" x-model="form.landing_about_description"
							class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $initialAppearance['landing_about_description'] }}</textarea>
						@error('landing_about_description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
					</div>
				</section>

				<div>
					<button type="submit"
						class="inline-flex h-11 items-center justify-center rounded-lg bg-orange-500 px-5 text-sm font-medium text-white transition hover:bg-orange-600">
						Simpan Tampilan Landing Page
					</button>
				</div>
			</form>
		</div>

		<div class="space-y-6">
			<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
					<h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Preview Ringkas</h4>
					<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan hasil tema yang sedang aktif. Preview ini berubah realtime saat form diisi.</p>
				</div>

				<div class="p-5">
					<div class="relative overflow-hidden rounded-2xl" :class="activeTheme.hero_gradient_class">
						<img :src="heroImagePreview" alt="Preview hero" class="absolute inset-0 h-full w-full object-cover opacity-20">
						<div class="bg-black/20 px-5 py-8 text-white">
							<div class="relative">
								<p class="text-xs uppercase tracking-[0.3em] text-white/70">Landing Preview</p>
								<h5 class="mt-3 text-2xl font-semibold leading-tight whitespace-pre-line" x-text="form.landing_hero_title"></h5>
								<p class="mt-3 text-sm leading-6 text-white/85" x-text="form.landing_hero_subtitle"></p>
								<div class="mt-5 inline-flex rounded-full px-3 py-1 text-xs font-semibold text-white" :class="activeTheme.accent_bg_class" x-text="activeThemeLabel"></div>
							</div>
						</div>
					</div>

					<div class="mt-4 rounded-2xl border border-dashed border-gray-200 p-3 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
						<p class="font-semibold text-gray-700 dark:text-gray-200">Preview Gambar Hero</p>
						<p class="mt-1 break-all" x-text="heroImagePreview"></p>
					</div>

					<div class="mt-5 rounded-2xl p-5" :class="activeTheme.surface_class">
						<p class="text-sm font-semibold text-gray-800" x-text="form.landing_about_title"></p>
						<p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600" x-text="aboutPreview"></p>
					</div>

					<div class="mt-5 grid gap-3 sm:grid-cols-2">
						<div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
							<p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Kelas Tema</p>
							<p class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90" x-text="activeTheme.hero_gradient_class"></p>
						</div>
						<div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
							<p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tombol Aksen</p>
							<div class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold text-white" :class="activeTheme.accent_bg_class" x-text="activeThemeLabel"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 dark:border-gray-800 dark:bg-white/[0.03] xl:px-6">
				<h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sumber Data</h4>
				<dl class="mt-4 space-y-4 text-sm">
					<div>
						<dt class="text-gray-500 dark:text-gray-400">Koperasi Aktif</dt>
						<dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $koperasi->nama_koperasi ?? config('app.name') }}</dd>
					</div>
					<div>
						<dt class="text-gray-500 dark:text-gray-400">Email Kontak</dt>
						<dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $landingPage['contact_email'] }}</dd>
					</div>
					<div>
						<dt class="text-gray-500 dark:text-gray-400">Telepon</dt>
						<dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $landingPage['contact_phone'] }}</dd>
					</div>
					<div>
						<dt class="text-gray-500 dark:text-gray-400">Alamat</dt>
						<dd class="mt-1 font-medium text-gray-800 dark:text-white/90">{{ $landingPage['contact_address'] }}</dd>
					</div>
				</dl>
			</div>
		</div>
	</div>
</div>
@endsection
