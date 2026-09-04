<aside :data-sidebar-collapsed="sidebarCollapsed" :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-64']" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-[width,transform] duration-300 lg:translate-x-0">
  <div class="h-16 px-5 flex items-center border-b border-slate-200">
    <a href="<?= BASE_URL ?>/admin" title="Dashboard" class="flex items-center gap-3">
      <img src="<?= BASE_URL ?>/assets/icon/logo.png" alt="BetaKos" class="w-9 h-9 object-contain">
      <div class="sidebar-brand-label">
        <div class="font-bold text-slate-900">BetaKos</div>
        <div class="text-xs text-slate-500">Panel Admin</div>
      </div>
    </a>
  </div>
  <nav class="flex h-[calc(100vh-4rem)] min-h-0 flex-col p-4">
    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain pr-1 space-y-1 [scrollbar-width:thin]">
    <a href="<?= BASE_URL ?>/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">📊</span><span class="sidebar-label">Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/verifikasi" title="Verifikasi Kos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">✓</span><span class="sidebar-label">Verifikasi Kos</span>
    </a>
    <div class="sidebar-section-label pt-4 pb-2 px-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Manajemen</div>
    <a href="<?= BASE_URL ?>/admin/pengguna" title="Pengguna" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">👥</span><span class="sidebar-label">Pengguna</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/laporan" title="Laporan Kos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">⚑</span><span class="sidebar-label">Laporan Kos</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/langganan" title="Langganan" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">💳</span><span class="sidebar-label">Langganan</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/langganan/metode-pembayaran" title="Metode Pembayaran" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">⚙️</span><span class="sidebar-label">Metode Pembayaran</span>
    </a>
    </div>

    <div class="shrink-0 border-t border-slate-100 pt-4">
      <button
        type="button"
        @click="$store.auth.logout()"
        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50"
      >
        <span class="text-lg">↪</span><span class="sidebar-label">Logout</span>
      </button>
    </div>
  </nav>
</aside>
