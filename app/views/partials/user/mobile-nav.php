<template x-if="$store.auth.isLoggedIn">
  <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden">
    <div class="mx-auto grid h-16 max-w-md grid-cols-5">
      <a href="<?= BASE_URL ?>/" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <span class="text-lg">⌂</span>
        <span>Beranda</span>
      </a>
      <a href="<?= BASE_URL ?>/cari-kos" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <span class="text-lg">⌕</span>
        <span>Cari</span>
      </a>
      <a href="<?= BASE_URL ?>/user/favorit" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <span class="text-lg">♡</span>
        <span>Favorit</span>
      </a>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/laporan" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
          <span class="text-lg">⚑</span>
          <span>Laporan</span>
        </a>
      </template>
      <a href="<?= BASE_URL ?>/user/profil" class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-500 hover:text-primary">
        <span class="text-lg">◯</span>
        <span>Profil</span>
      </a>
    </div>
  </nav>
</template>
