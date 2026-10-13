<?php
$adminPath=normalized_request_path();
if(in_array($adminPath,['/admin/fasilitas','/admin/aturan','/admin/lokasi'],true)) $adminPath='/admin/data-master';
$adminGroups=[
 'Utama'=>[['/admin','Ringkasan','chart-column',null]],
 'Perlu ditangani'=>[['/admin/verifikasi','Verifikasi kos','user-check','verification'],['/admin/laporan','Laporan pengguna','flag','reports'],['/admin/langganan','BetaKos Pro','credit-card','payments']],
 'Pengaturan'=>[['/admin/pengguna','Pengguna','users-round',null],['/admin/data-master','Data referensi','folder-cog',null],['/admin/langganan/metode-pembayaran','Metode pembayaran','settings',null],['/admin/profil','Profil & akun','user-round',null]]
];
?>
<aside :data-sidebar-collapsed="sidebarCollapsed" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="admin-sidebar fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-[width,transform] duration-300 lg:translate-x-0">
 <div class="bk-sidebar-brand"><a href="<?= BASE_URL ?>/admin"><img src="<?= BASE_URL ?>/assets/icon/logo.png" alt="BetaKos"><div class="sidebar-brand-label"><strong>BetaKos</strong><small>Ruang kerja admin</small></div></a></div>
 <nav class="bk-workspace-nav" aria-label="Navigasi admin">
  <div class="bk-nav-scroll">
   <?php foreach($adminGroups as $group=>$items): ?>
    <p class="sidebar-section-label bk-nav-group"><?= htmlspecialchars($group) ?></p>
    <?php foreach($items as [$path,$label,$icon,$badge]): $active=$adminPath===$path || ($path!=='/admin' && str_starts_with($adminPath,$path.'/')); if($path==='/admin/langganan' && str_contains($adminPath,'metode-pembayaran')) $active=false; ?>
     <a href="<?= BASE_URL.$path ?>" title="<?= htmlspecialchars($label) ?>" class="bk-workspace-link <?= $active?'admin-nav-active':'' ?>" <?= $active?'aria-current="page"':'' ?>>
      <span class="bk-nav-icon"><?= masterIconSvg($icon,'h-5 w-5') ?></span><span class="sidebar-label"><?= htmlspecialchars($label) ?></span><?= $badge?navigationBadge($badge,'perlu ditangani'):'' ?>
     </a>
    <?php endforeach; ?>
   <?php endforeach; ?>
  </div>
  <div class="bk-nav-footer"><a href="<?= BASE_URL ?>/" class="bk-workspace-link" title="Lihat situs"><?= masterIconSvg('external-link','h-5 w-5') ?><span class="sidebar-label">Lihat situs</span></a><button type="button" @click="$store.auth.logout()" class="bk-workspace-link is-danger" title="Keluar"><?= masterIconSvg('log-out','h-5 w-5') ?><span class="sidebar-label">Keluar</span></button></div>
 </nav>
</aside>
