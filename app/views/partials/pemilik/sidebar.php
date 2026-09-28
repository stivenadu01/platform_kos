<?php
$__pemilikIsPro = false;
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pemilik') {
  model('Langganan');
  $__pemilikStatusLangganan = getStatusLanggananPemilik((int)$_SESSION['user']['id_user']);
  $__pemilikIsPro = !empty($__pemilikStatusLangganan['is_pro']);
}
?>

<aside
  :data-sidebar-collapsed="sidebarCollapsed"
  :data-mobile-sidebar-open="sidebarOpen ? 'true' : 'false'"
  class="
    fixed inset-y-0 left-0 z-50
    w-64
    bg-white
    border-r border-slate-200
    flex flex-col
    transform lg:translate-x-0
    pemilik-sidebar
  "
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

  <!-- LOGO -->
  <div class="h-16 shrink-0 px-5 flex items-center justify-between border-b border-slate-200">

    <a
      :href="window.BASE_URL + '/pemilik'"
      class="flex items-center gap-3">

      <img
        :src="window.BASE_URL + '/assets/icon/logo.png'"
        alt="BetaKos"
        class="w-9 h-9 object-contain">

      <div class="sidebar-brand-label">
        <div class="font-bold text-slate-900">
          BetaKos
        </div>

        <div class="text-xs text-slate-500">
          Panel Pemilik
        </div>
      </div>

    </a>

    <button type="button" @click="sidebarOpen = false" class="owner-mobile-sidebar-close h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100" aria-label="Tutup menu">
      <?= masterIconSvg('x', 'h-5 w-5') ?>
    </button>

  </div>


  <!-- NAVIGATION -->
  <nav class="owner-desktop-sidebar-nav min-h-0 flex-1 overflow-y-auto p-4 pb-6 space-y-1">

    <!-- DASHBOARD -->
    <a
      :href="window.BASE_URL + '/pemilik'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('home', 'h-5 w-5') ?></span>

      <span class="sidebar-label">
        Dashboard
      </span>

    </a>


    <!-- PROFIL -->
    <a
      :href="window.BASE_URL + '/pemilik/profil'"

      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('user-round', 'h-5 w-5') ?></span>

      <span class="sidebar-label">
        Profil Saya
      </span>

    </a>


    <!-- SECTION KOS -->
    <div class="sidebar-section-label pt-5 pb-2 px-4">

      <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Manajemen Kos</span>

    </div>


    <!-- KOS -->
    <a
      :href="window.BASE_URL + '/pemilik/kos'"
      data-owner-nav="property"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('building-2', 'h-5 w-5') ?></span>

      <span class="sidebar-label">
        Properti
      </span>

    </a>


    <!-- PENGHUNI -->
    <a
      :href="window.BASE_URL + '/pemilik/penghuni'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('users-round', 'h-5 w-5') ?></span>

      <span class="sidebar-label">
        Penghuni
      </span>
      <?php if (!$__pemilikIsPro): ?>
        <span class="sidebar-label ml-auto text-[10px] font-bold text-primary">PRO</span>
      <?php endif; ?>

    </a>


    <!-- PEMBAYARAN -->
    <a
      :href="window.BASE_URL + '/pemilik/pembayaran'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('wallet', 'h-5 w-5') ?></span>

      <span class="sidebar-label">
        Keuangan
      </span>
      <?php if (!$__pemilikIsPro): ?>
        <span class="sidebar-label ml-auto text-[10px] font-bold text-primary">PRO</span>
      <?php endif; ?>

    </a>


    <!-- SECTION LAYANAN -->
    <div class="sidebar-section-label pt-5 pb-2 px-4">
      <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Layanan</span>
    </div>


    <!-- CLAIM RIWAYAT -->
    <a
      :href="window.BASE_URL + '/pemilik/claim'"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('user-check', 'h-5 w-5') ?></span>
      <span class="sidebar-label">Klaim Riwayat</span>
    </a>


    <!-- LANGGANAN -->
    <a
      :href="window.BASE_URL + '/pemilik/langganan'"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('star', 'h-5 w-5') ?></span>
      <span class="sidebar-label">Langganan</span>
    </a>

    <a
      :href="window.BASE_URL + '/pemilik/riwayat'"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><?= masterIconSvg('history', 'h-5 w-5') ?></span>
      <span class="sidebar-label">Riwayat</span>
    </a>



    <!-- KELUAR (MOBILE) -->
    <div class="sticky bottom-0 mt-2 border-t border-slate-200 bg-white pt-3 lg:hidden">
      <button
        type="button"
        @click="$store.auth.logout()"
        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50">
        <?= masterIconSvg('log-out', 'h-5 w-5') ?>
        <span class="sidebar-label">Keluar</span>
      </button>
    </div>

  </nav>

  <nav class="owner-mobile-more-menu" aria-label="Menu lainnya">
    <div class="owner-mobile-more-intro mb-4">
      <p class="text-xs font-bold uppercase tracking-wider text-primary">Menu lainnya</p>
      <p class="mt-1 truncate font-bold text-slate-900"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pemilik Kos') ?></p>
      <p class="mt-1 text-xs text-slate-500"><?= $__pemilikIsPro ? 'Paket PRO aktif' : 'Paket reguler' ?></p>
    </div>

    <div class="space-y-1">
      <a :href="window.BASE_URL + '/pemilik/profil'" class="owner-mobile-more-link">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><?= masterIconSvg('user-round', 'h-5 w-5') ?></span>
        <span>Profil &amp; Keamanan</span>
        <?= masterIconSvg('chevron-right', 'ml-auto h-4 w-4 text-slate-400') ?>
      </a>
      <a :href="window.BASE_URL + '/pemilik/claim'" class="owner-mobile-more-link">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><?= masterIconSvg('user-check', 'h-5 w-5') ?></span>
        <span>Klaim Riwayat</span>
        <?= masterIconSvg('chevron-right', 'ml-auto h-4 w-4 text-slate-400') ?>
      </a>
      <a :href="window.BASE_URL + '/pemilik/langganan'" class="owner-mobile-more-link">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><?= masterIconSvg('star', 'h-5 w-5') ?></span>
        <span>Langganan</span>
        <?= masterIconSvg('chevron-right', 'ml-auto h-4 w-4 text-slate-400') ?>
      </a>
      <a :href="window.BASE_URL + '/pemilik/riwayat'" class="owner-mobile-more-link">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><?= masterIconSvg('history', 'h-5 w-5') ?></span>
        <span>Riwayat</span>
        <?= masterIconSvg('chevron-right', 'ml-auto h-4 w-4 text-slate-400') ?>
      </a>
      <button type="button" @click="sidebarOpen = false; window.dispatchEvent(new CustomEvent('betakos:operational-help'))" class="owner-mobile-more-link w-full text-left">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><?= masterIconSvg('info', 'h-5 w-5') ?></span>
        <span>Panduan Halaman</span>
        <?= masterIconSvg('chevron-right', 'ml-auto h-4 w-4 text-slate-400') ?>
      </button>
    </div>

    <div class="mt-auto border-t border-slate-200 pt-4">
      <button type="button" @click="$store.auth.logout()" class="owner-mobile-more-link w-full text-red-600 hover:!bg-red-50 hover:!text-red-700">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600"><?= masterIconSvg('log-out', 'h-5 w-5') ?></span>
        <span>Keluar</span>
      </button>
    </div>
  </nav>


  <!-- BOTTOM (DESKTOP) -->
  <div class="owner-desktop-sidebar-footer hidden lg:block shrink-0 p-4 border-t border-slate-200 bg-white">
    <button
      type="button"
      @click="$store.auth.logout()"
      class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50">
      <?= masterIconSvg('log-out', 'h-5 w-5') ?>
      <span class="sidebar-label">Keluar</span>
    </button>
  </div>

</aside>

<script>
  (() => {
    const markActiveOwnerNavigation = () => {
      const current = window.location.pathname.replace(/\/+$/, '');
      const links = document.querySelectorAll('.pemilik-sidebar nav a[href]');
      let best = null;
      if (current.includes('/pemilik/kamar') || current.includes('/pemilik/tipe-kamar')) {
        const propertyLink = document.querySelector('.pemilik-sidebar [data-owner-nav="property"]');
        if (propertyLink) {
          propertyLink.classList.add('pemilik-nav-active');
          propertyLink.setAttribute('aria-current', 'page');
          return;
        }
      }
      links.forEach((link) => {
        const path = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '');
        const matches = current === path || (path.endsWith('/pemilik') ? current === path : current.startsWith(path + '/'));
        if (matches && (!best || path.length > best.path.length)) best = { link, path };
      });
      if (best) {
        best.link.classList.add('pemilik-nav-active');
        best.link.setAttribute('aria-current', 'page');
      }
    };
    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', markActiveOwnerNavigation, { once: true })
      : markActiveOwnerNavigation();
  })();
</script>
