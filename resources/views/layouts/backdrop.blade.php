<div
    x-show="$store.sidebar.isMobileOpen"
    x-transition.opacity
    @click="$store.sidebar.setMobileOpen(false)"
    class="fixed inset-0 z-[60] bg-gray-900/50 xl:hidden"
></div>
