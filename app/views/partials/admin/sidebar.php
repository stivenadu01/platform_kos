<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
  <div class="h-16 px-5 flex items-center border-b border-slate-200">
    <a href="<?= BASE_URL ?>/admin" class="flex items-center gap-3">
      <img src="<?= BASE_URL ?>/assets/icon/logo.png" alt="BetaKos" class="w-9 h-9 object-contain">
      <div>
        <div class="font-bold text-slate-900">BetaKos</div>
        <div class="text-xs text-slate-500">Panel Admin</div>
      </div>
    </a>
  </div>
  <nav class="flex min-h-[calc(100vh-4rem)] flex-col p-4">
    <div class="space-y-1">
    <a href="<?= BASE_URL ?>/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">📊</span><span>Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/verifikasi" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">✓</span><span>Verifikasi Kos</span>
    </a>
    <div class="pt-4 pb-2 px-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Manajemen</div>
    <a href="<?= BASE_URL ?>/admin/pengguna" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">👥</span><span>Pengguna</span>
    </a>
    <a href="<?= BASE_URL ?>/admin/laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">⚑</span><span>Laporan Kos</span>
    </a>
    </div>

    <div class="mt-auto border-t border-slate-100 pt-4">
      <button
        type="button"
        @click="$store.auth.logout()"
        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50"
      >
        <span class="text-lg">↪</span><span>Logout</span>
      </button>
    </div>
  </nav>
</aside>
