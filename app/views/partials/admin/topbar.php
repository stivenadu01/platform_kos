<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
  <div class="flex items-center gap-3">
    <button type="button" @click="sidebarOpen = true" class="lg:hidden w-10 h-10 rounded-lg hover:bg-slate-100 flex items-center justify-center">☰</button>
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
