<template x-if="$store.auth.isLoggedIn">
  <nav class="public-mobile-nav fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_20px_rgba(15,23,42,0.06)] backdrop-blur md:hidden">
    <div class="mx-auto grid h-16 max-w-md grid-cols-6">
      <a href="<?= BASE_URL ?>/" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <?= masterIconSvg('home', 'h-5 w-5') ?>
        <span>Beranda</span>
      </a>
      <a href="<?= BASE_URL ?>/cari-kos" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <?= masterIconSvg('search', 'h-5 w-5') ?>
        <span>Cari</span>
      </a>
      <a href="<?= BASE_URL ?>/user/favorit" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <?= masterIconSvg('heart', 'h-5 w-5') ?>
        <span>Favorit</span>
      </a>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/laporan" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
          <?= masterIconSvg('flag', 'h-5 w-5') ?>
          <span>Laporan</span>
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/riwayat-kos" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
          <?= masterIconSvg('history', 'h-5 w-5') ?>
          <span>Riwayat</span>
        </a>
      </template>
      <a href="<?= BASE_URL ?>/user/profil" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <?= masterIconSvg('user-round', 'h-5 w-5') ?>
        <span>Profil</span>
      </a>
    </div>
  </nav>
</template>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const current = window.location.pathname.replace(/\/$/, '') || '/';
  const links = Array.from(document.querySelectorAll('.public-mobile-nav a[href]'));
  let active = null;
  links.forEach((link) => {
    const path = new URL(link.href, window.location.origin).pathname.replace(/\/$/, '') || '/';
    const matches = path === '/' ? current === path : (current === path || current.startsWith(path + '/'));
    if (matches && (!active || path.length > active.path.length)) active = { link, path };
  });
  if (active) {
    active.link.classList.add('public-nav-active');
    active.link.setAttribute('aria-current', 'page');
  }
});
</script>
