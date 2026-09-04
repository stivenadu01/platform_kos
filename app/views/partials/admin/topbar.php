<header
  :class="sidebarCollapsed ? 'lg:left-20' : 'lg:left-64'"
  class="fixed inset-x-0 top-0 z-40 h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 transition-[left] duration-300">
  <div class="flex items-center gap-3">
    <button type="button" @click="sidebarOpen = true" class="lg:hidden w-10 h-10 rounded-lg hover:bg-slate-100 flex items-center justify-center" aria-label="Buka menu">☰</button>
    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('betakos_admin_sidebar_collapsed', sidebarCollapsed ? '1' : '0')" class="hidden lg:inline-flex w-10 h-10 rounded-lg hover:bg-slate-100 items-center justify-center text-slate-600" :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'" :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
      <span x-text="sidebarCollapsed ? '»' : '«'"></span>
    </button>
    <div class="lg:hidden font-bold text-slate-900">BetaKos</div>
  </div>
  <div class="flex items-center gap-3">
    <div class="hidden sm:block text-right">
      <div class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Administrator') ?></div>
      <div class="text-xs text-slate-500">Administrator</div>
    </div>
    <div class="w-10 h-10 rounded-full bg-primary-soft text-primary flex items-center justify-center font-semibold">
      <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1))) ?>
    </div>
  </div>
</header>
