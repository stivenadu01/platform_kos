<header
  class="admin-topbar fixed inset-x-0 top-0 z-40 h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 transition-[left] duration-300">
  <div class="flex items-center gap-3">
    <button type="button" @click="sidebarOpen = true" class="lg:hidden w-10 h-10 rounded-lg hover:bg-slate-100 flex items-center justify-center" aria-label="Buka menu">☰</button>
    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('betakos_admin_sidebar_collapsed', sidebarCollapsed ? '1' : '0')" class="hidden lg:inline-flex w-10 h-10 rounded-lg hover:bg-slate-100 items-center justify-center text-slate-600" :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'" :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
      <span x-text="sidebarCollapsed ? '»' : '«'"></span>
    </button>
    <div class="lg:hidden font-bold text-slate-900">BetaKos</div>
  </div>
  <div class="flex items-center gap-3">
    <button
      type="button"
      data-pwa-install data-pwa-install-mobile
      hidden
      class="inline-flex sm:hidden w-10 h-10 items-center justify-center rounded-lg border border-primary/20 bg-primary-soft text-primary hover:bg-blue-100"
      title="Pasang BetaKos di perangkat"
      aria-label="Pasang BetaKos di perangkat">
      <span aria-hidden="true">📱</span>
    </button>
    <button
      type="button"
      data-pwa-install
      hidden
      class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-primary/20 bg-primary-soft px-3 py-2 text-sm font-semibold text-primary hover:bg-blue-100"
      title="Pasang BetaKos di perangkat">
      <span aria-hidden="true">📱</span>
      Unduh Aplikasi
    </button>
    <div class="hidden sm:block text-right">
      <div class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Administrator') ?></div>
      <div class="text-xs text-slate-500">Administrator</div>
    </div>
    <div class="w-10 h-10 rounded-full bg-primary-soft text-primary flex items-center justify-center font-semibold">
      <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1))) ?>
    </div>
  </div>
</header>
