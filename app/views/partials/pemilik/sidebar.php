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


  <?php
  $ownerPath=normalized_request_path();
  if(str_starts_with($ownerPath,'/pemilik/profil')) $ownerPath='/pemilik/akun';
  $ownerGroups=[
    'Utama'=>[['/pemilik','Ringkasan','home',null]],
    'Kelola kos'=>[['/pemilik/kos','Kos saya','building-2','properties'],['/pemilik/penghuni','Penghuni','users-round','claims'],['/pemilik/pembayaran','Keuangan sewa','wallet','overdue']],
    'Aktivitas'=>[['/pemilik/claim','Klaim penghuni','user-check','claims'],['/pemilik/riwayat','Riwayat','history',null]],
    'Akun'=>[['/pemilik/langganan','BetaKos Pro','star','subscription'],['/pemilik/akun','Profil & pengaturan','user-round',null]]
  ];
  ?>
  <nav class="owner-desktop-sidebar-nav bk-workspace-nav" aria-label="Navigasi pemilik">
   <div class="bk-nav-scroll">
    <?php foreach($ownerGroups as $group=>$items): ?>
     <p class="sidebar-section-label bk-nav-group"><?= htmlspecialchars($group) ?></p>
     <?php foreach($items as [$path,$label,$icon,$badge]): $active=$ownerPath===$path || ($path!=='/pemilik' && str_starts_with($ownerPath,$path.'/')); if($path==='/pemilik/kos' && (str_starts_with($ownerPath,'/pemilik/kamar') || str_starts_with($ownerPath,'/pemilik/tipe-kamar'))) $active=true; ?>
      <a href="<?= BASE_URL.$path ?>" title="<?= htmlspecialchars($label) ?>" <?= $path==='/pemilik/kos'?'data-owner-nav="property"':'' ?> class="bk-workspace-link <?= $active?'owner-nav-active':'' ?>" <?= $active?'aria-current="page"':'' ?>>
       <span class="bk-nav-icon"><?= masterIconSvg($icon,'h-5 w-5') ?></span><span class="sidebar-label"><?= htmlspecialchars($label) ?></span><?= $badge?navigationBadge($badge,$badge==='overdue'?'tagihan lewat jatuh tempo':'aktivitas menunggu'):'' ?>
       <?php if(!$__pemilikIsPro && in_array($path,['/pemilik/penghuni','/pemilik/pembayaran'],true)): ?><span class="sidebar-label bk-pro-label">PRO</span><?php endif; ?>
      </a>
     <?php endforeach; ?>
    <?php endforeach; ?>
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
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('user-check', 'h-5 w-5') ?></span>
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
