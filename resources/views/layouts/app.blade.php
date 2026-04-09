<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <title>@hasSection('title')@yield('title')@else{{ $title ?? 'Dashboard' }}@endif | {{ config('app.name', 'Sistem Koperasi') }}</title>

    <script>
        window.formatRupiah ??= (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- @livewireStyles -->

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const applyThemeClasses = (theme) => {
                const isDark = theme === 'dark';
                document.documentElement.classList.toggle('dark', isDark);

                if (document.body) {
                    document.body.classList.toggle('dark', isDark);
                    document.body.classList.toggle('bg-gray-900', isDark);
                }
            };

            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;

            applyThemeClasses(theme);
            document.addEventListener('DOMContentLoaded', () => applyThemeClasses(theme), {
                once: true
            });
        })();
    </script>

</head>

<body x-data="{ 'loaded': true }" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
const checkMobile = () => {
    if (window.innerWidth < 1280) {
        $store.sidebar.setMobileOpen(false);
        $store.sidebar.isExpanded = false;
    } else {
        $store.sidebar.isMobileOpen = false;
        $store.sidebar.isExpanded = true;
    }
};
window.addEventListener('resize', checkMobile);">



    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="min-w-0 flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px] xl:w-[calc(100%-290px)]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px] xl:w-[calc(100%-90px)]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0 w-full': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

</body>

@if (session('status'))
<div
    id="global-status-message"
    class="hidden"
    data-status="{{ session('status') }}"
    data-status-type="{{ session('status_type', 'success') }}"
    data-status-title="{{ match (session('status_type', 'success')) { 'info' => 'Informasi', 'warning' => 'Peringatan', 'error' => 'Gagal', default => 'Berhasil' } }}"
    data-status-color="{{ match (session('status_type', 'success')) { 'info' => '#2563eb', 'warning' => '#d97706', 'error' => '#dc2626', default => '#16a34a' } }}"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusElement = document.getElementById('global-status-message');

        if (!statusElement) {
            return;
        }

        window.Swal.fire({
            icon: statusElement.dataset.statusType,
            title: statusElement.dataset.statusTitle,
            text: statusElement.dataset.status,
            confirmButtonText: 'Oke',
            confirmButtonColor: statusElement.dataset.statusColor,
        });
    });
</script>
@endif

@livewireScripts
@stack('scripts')

</html>