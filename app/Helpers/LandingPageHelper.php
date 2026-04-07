<?php

namespace App\Helpers;

use App\Models\Koperasi;
use Illuminate\Support\Str;

class LandingPageHelper
{
    public static function presets(): array
    {
        return [
            'amber' => [
                'label' => 'Amber Hangat',
                'description' => 'Nuansa hangat dan ramah untuk koperasi berbasis komunitas.',
                'hero_from' => '#92400e',
                'hero_to' => '#d97706',
                'accent' => '#f59e0b',
                'surface' => '#fff7ed',
                'surface_alt' => '#ffedd5',
                'accent_soft' => '#fef3c7',
                'hero_gradient_class' => 'bg-gradient-to-br from-amber-900 to-amber-500',
                'surface_class' => 'bg-orange-50',
                'surface_alt_class' => 'bg-amber-100',
                'accent_bg_class' => 'bg-amber-500 hover:bg-amber-600',
                'accent_text_class' => 'text-amber-500',
                'accent_soft_class' => 'bg-amber-100 text-amber-700',
                'swatch_gradient_class' => 'bg-gradient-to-br from-amber-900 to-amber-500',
            ],
            'ocean' => [
                'label' => 'Ocean Profesional',
                'description' => 'Tampilan bersih dan modern dengan kesan korporat yang stabil.',
                'hero_from' => '#0f3d63',
                'hero_to' => '#0284c7',
                'accent' => '#38bdf8',
                'surface' => '#eff6ff',
                'surface_alt' => '#dbeafe',
                'accent_soft' => '#e0f2fe',
                'hero_gradient_class' => 'bg-gradient-to-br from-sky-900 to-sky-500',
                'surface_class' => 'bg-sky-50',
                'surface_alt_class' => 'bg-blue-100',
                'accent_bg_class' => 'bg-sky-500 hover:bg-sky-600',
                'accent_text_class' => 'text-sky-400',
                'accent_soft_class' => 'bg-sky-100 text-sky-700',
                'swatch_gradient_class' => 'bg-gradient-to-br from-sky-900 to-sky-500',
            ],
            'emerald' => [
                'label' => 'Emerald Segar',
                'description' => 'Cocok untuk identitas koperasi yang ingin terasa tumbuh dan sehat.',
                'hero_from' => '#065f46',
                'hero_to' => '#10b981',
                'accent' => '#34d399',
                'surface' => '#ecfdf5',
                'surface_alt' => '#d1fae5',
                'accent_soft' => '#dcfce7',
                'hero_gradient_class' => 'bg-gradient-to-br from-emerald-900 to-emerald-500',
                'surface_class' => 'bg-emerald-50',
                'surface_alt_class' => 'bg-emerald-100',
                'accent_bg_class' => 'bg-emerald-500 hover:bg-emerald-600',
                'accent_text_class' => 'text-emerald-400',
                'accent_soft_class' => 'bg-emerald-100 text-emerald-700',
                'swatch_gradient_class' => 'bg-gradient-to-br from-emerald-900 to-emerald-500',
            ],
            'rose' => [
                'label' => 'Rose Enerjik',
                'description' => 'Lebih ekspresif untuk landing page yang ingin terlihat menonjol.',
                'hero_from' => '#9f1239',
                'hero_to' => '#f43f5e',
                'accent' => '#fb7185',
                'surface' => '#fff1f2',
                'surface_alt' => '#ffe4e6',
                'accent_soft' => '#ffe4e6',
                'hero_gradient_class' => 'bg-gradient-to-br from-rose-900 to-rose-500',
                'surface_class' => 'bg-rose-50',
                'surface_alt_class' => 'bg-rose-100',
                'accent_bg_class' => 'bg-rose-500 hover:bg-rose-600',
                'accent_text_class' => 'text-rose-400',
                'accent_soft_class' => 'bg-rose-100 text-rose-700',
                'swatch_gradient_class' => 'bg-gradient-to-br from-rose-900 to-rose-500',
            ],
        ];
    }

    public static function build(?Koperasi $koperasi): array
    {
        $presets = self::presets();
        $themeKey = $koperasi?->landing_theme ?? 'amber';
        $theme = $presets[$themeKey] ?? $presets['amber'];
        $koperasiName = $koperasi?->nama_koperasi ?: config('app.name');
        $appSlug = Str::of($koperasiName)->lower()->replace(' ', '');

        return [
            'theme_key' => $themeKey,
            'theme' => $theme,
            'hero_title' => $koperasi?->landing_hero_title ?: "Selamat Datang di\nKoperasi {$koperasiName}",
            'hero_subtitle' => $koperasi?->landing_hero_subtitle ?: 'Solusi keuangan terpercaya untuk kesejahteraan bersama.',
            'hero_image' => self::resolveHeroImage($koperasi?->landing_hero_image, $themeKey),
            'about_title' => $koperasi?->landing_about_title ?: 'Tentang Kami',
            'about_description' => $koperasi?->landing_about_description ?: "{$koperasiName} hadir untuk meningkatkan kesejahteraan anggota melalui layanan simpan pinjam, dukungan usaha, dan pengelolaan koperasi yang transparan. Kami berkomitmen menghadirkan layanan yang mudah diakses, profesional, dan relevan dengan kebutuhan anggota saat ini.",
            'koperasi_name' => $koperasiName,
            'contact_email' => $koperasi?->email ?: "helpdesk@{$appSlug}.com",
            'contact_phone' => $koperasi?->telepon ?: '087711172853',
            'contact_address' => $koperasi?->alamat ?: 'Alamat koperasi belum diatur.',
        ];
    }

    protected static function resolveHeroImage(?string $heroImage, string $themeKey): string
    {
        if (blank($heroImage) || self::isLegacyPlaceholder($heroImage)) {
            return self::defaultHeroImage($themeKey);
        }

        return $heroImage;
    }

    protected static function isLegacyPlaceholder(string $heroImage): bool
    {
        return Str::startsWith($heroImage, 'https://placehold.co/1920x400/')
            && str_contains($heroImage, '?text=Koperasi+');
    }

    protected static function defaultHeroImage(string $themeKey): string
    {
        $theme = self::presets()[$themeKey] ?? self::presets()['amber'];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" fill="none">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1600" y2="900" gradientUnits="userSpaceOnUse">
            <stop stop-color="{$theme['hero_from']}"/>
            <stop offset="1" stop-color="{$theme['hero_to']}"/>
        </linearGradient>
        <linearGradient id="glass" x1="280" y1="180" x2="1220" y2="720" gradientUnits="userSpaceOnUse">
            <stop stop-color="rgba(255,255,255,0.38)"/>
            <stop offset="1" stop-color="rgba(255,255,255,0.06)"/>
        </linearGradient>
    </defs>
    <rect width="1600" height="900" fill="url(#bg)"/>
    <circle cx="1320" cy="140" r="220" fill="white" fill-opacity="0.12"/>
    <circle cx="240" cy="780" r="280" fill="white" fill-opacity="0.08"/>
    <circle cx="1130" cy="640" r="140" fill="white" fill-opacity="0.08"/>
    <rect x="190" y="170" width="1220" height="560" rx="40" fill="white" fill-opacity="0.08"/>
    <rect x="250" y="240" width="500" height="28" rx="14" fill="white" fill-opacity="0.55"/>
    <rect x="250" y="300" width="420" height="28" rx="14" fill="white" fill-opacity="0.35"/>
    <rect x="250" y="382" width="360" height="16" rx="8" fill="white" fill-opacity="0.3"/>
    <rect x="250" y="420" width="460" height="16" rx="8" fill="white" fill-opacity="0.2"/>
    <rect x="250" y="458" width="390" height="16" rx="8" fill="white" fill-opacity="0.2"/>
    <rect x="250" y="540" width="180" height="54" rx="27" fill="{$theme['accent']}"/>
    <rect x="930" y="250" width="280" height="330" rx="28" fill="white" fill-opacity="0.18"/>
    <rect x="980" y="320" width="180" height="18" rx="9" fill="white" fill-opacity="0.5"/>
    <rect x="980" y="364" width="140" height="18" rx="9" fill="white" fill-opacity="0.28"/>
    <rect x="980" y="430" width="180" height="90" rx="22" fill="white" fill-opacity="0.12"/>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }
}
