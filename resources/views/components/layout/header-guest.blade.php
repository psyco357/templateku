@php
$navItems = [
['label' => 'Beranda', 'href' => url('/')],
['label' => 'Layanan', 'href' => url('/#layanan-utama')],
['label' => 'Tentang', 'href' => url('/#tentang-kami')],
['label' => 'Kegiatan', 'href' => url('/#sorotan-kegiatan')],
];
@endphp

<header
    x-data="{
        menuOpen: false,
        scrolled: false,
        init() {
            this.onScroll();
            this.$watch('menuOpen', value => {
                document.body.classList.toggle('overflow-hidden', value);
            });
        },
        onScroll() {
            this.scrolled = window.scrollY > 10;
        },
        closeMenu() {
            this.menuOpen = false;
        }
    }"
    @scroll.window="onScroll()"
    @resize.window="if (window.innerWidth >= 1024) closeMenu()"
    @keydown.escape.window="closeMenu()"
    class="fixed inset-x-0 top-0 z-50">
    <div class="border-b border-zinc-200/80 bg-white/95 backdrop-blur-xl transition-all duration-300"
        :class="scrolled || menuOpen ? 'shadow-lg shadow-black/5' : 'shadow-sm shadow-black/[0.03]'">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between transition-all duration-300"
                :class="scrolled ? 'h-15 sm:h-16 lg:h-[72px]' : 'h-16 sm:h-[72px] lg:h-20'">
                <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3" @click="closeMenu()">
                    <x-logo.app-logo class="h-10 w-10 flex-shrink-0 text-orange-500 sm:h-11 sm:w-11" />
                    <div class="min-w-0">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-[0.25em] text-orange-500 sm:text-xs">Koperasi</p>
                        <p class="truncate text-sm font-bold text-zinc-900 sm:text-base">{{ $landingPage['koperasi_name'] }}</p>
                    </div>
                </a>

                <div class="hidden items-center gap-5 lg:flex xl:gap-8">
                    @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" class="text-sm font-medium text-zinc-700 transition hover:text-orange-500">
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-900 px-5 text-sm font-semibold text-white transition hover:bg-black">
                        Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:border-orange-300 hover:text-orange-500">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center rounded-full bg-orange-500 px-5 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Daftar
                    </a>
                    @endauth
                </div>

                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-orange-200 hover:text-orange-500 lg:hidden"
                    @click="menuOpen = !menuOpen"
                    :aria-expanded="menuOpen.toString()"
                    aria-controls="guest-mobile-menu">
                    <span class="sr-only">Buka menu</span>
                    <svg x-show="!menuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                id="guest-mobile-menu"
                x-show="menuOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="border-t border-zinc-200 px-1 pb-4 pt-3 lg:hidden">
                <div class="space-y-1">
                    @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" @click="closeMenu()" class="block rounded-2xl px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-orange-50 hover:text-orange-600">
                        {{ $item['label'] }}
                    </a>
                    @endforeach
                </div>

                <div class="mt-4 grid gap-3 border-t border-zinc-200 pt-4">
                    @auth
                    <a href="{{ url('/dashboard') }}" @click="closeMenu()" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-900 px-5 text-sm font-semibold text-white transition hover:bg-black">
                        Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}" @click="closeMenu()" class="inline-flex h-12 items-center justify-center rounded-full border border-zinc-300 bg-white px-5 text-sm font-semibold text-zinc-800 transition hover:border-orange-300 hover:text-orange-500">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" @click="closeMenu()" class="inline-flex h-12 items-center justify-center rounded-full bg-orange-500 px-5 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Daftar
                    </a>
                    @endauth
                </div>
            </div>
        </nav>
    </div>

    <div
        x-show="menuOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 -z-10 bg-zinc-950/20 backdrop-blur-[1px] lg:hidden"
        @click="closeMenu()"></div>
</header>