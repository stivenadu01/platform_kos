<?php
$accountOwner = !empty($accountOwner);
$accountUser = $_SESSION['user'] ?? null;
$accountLinks = $accountOwner ? [
  ['/pemilik/profil','user-round','Profil & keamanan','Identitas, kontak, dan kata sandi'],
  ['/pemilik/langganan','star','BetaKos Pro','Paket dan transaksi langganan'],
  ['/pemilik/riwayat','history','Riwayat operasional','Penghuni, tagihan, dan pembayaran terdahulu'],
  ['/pemilik/claim','user-check','Klaim riwayat','Pengajuan hubungan dengan penghuni'],
] : [
  ['/user/profil','user-round','Profil & keamanan','Identitas, kontak, dan kata sandi'],
  ['/user/riwayat-kos','history','Riwayat & klaim','Masa tinggal dan pembayaran terdahulu'],
  ['/user/laporan','flag','Laporan saya','Pantau laporan informasi kos'],
];
?>
<div class="<?= $accountOwner ? 'owner-page' : 'mx-auto max-w-3xl px-4 py-6' ?>">
  <div class="owner-page-header"><div><h1 class="owner-title">Akun</h1><p class="owner-subtitle">Profil, riwayat, dan pengaturan Anda.</p></div></div>
  <?php if ($accountUser): ?>
    <section class="owner-panel mt-4"><p class="text-sm text-slate-500">Masuk sebagai</p><h2 class="text-xl font-bold break-words"><?= htmlspecialchars($accountUser['nama'] ?? '') ?></h2><p class="text-sm text-slate-500 break-all"><?= htmlspecialchars($accountUser['email'] ?? '') ?></p></section>
    <nav class="owner-panel mt-4" aria-label="Pengaturan akun">
      <?php foreach ($accountLinks as [$path,$icon,$label,$description]): ?>
        <a href="<?= BASE_URL . $path ?>" class="public-more-link"><?= masterIconSvg($icon,'h-5 w-5 shrink-0') ?><span><strong><?= $label ?></strong><small><?= $description ?></small></span><?php if($path==='/pemilik/claim') echo navigationBadge('claims','klaim menunggu'); elseif($path==='/pemilik/langganan') echo navigationBadge('subscription','pembayaran menunggu'); elseif($path==='/user/riwayat-kos') echo navigationBadge('claims','klaim menunggu'); ?><?= masterIconSvg('chevron-right','h-4 w-4') ?></a>
      <?php endforeach; ?>
      <?php if ($accountOwner): ?><button type="button" class="public-more-link w-full" onclick="window.dispatchEvent(new CustomEvent('betakos:onboarding-help'))"><?= masterIconSvg('clipboard-check','h-5 w-5') ?><span><strong>Persiapan kos</strong><small>Lihat progres kelengkapan properti</small></span></button><?php endif; ?>
      <button type="button" data-pwa-install hidden class="public-more-link w-full"><?= masterIconSvg('smartphone','h-5 w-5') ?><span><strong>Pasang BetaKos</strong><small>Akses dari layar utama perangkat</small></span></button>
    </nav>
    <button type="button" x-data @click="$store.auth.logout()" class="btn-danger mt-4 w-full"><?= masterIconSvg('log-out','h-4 w-4') ?> Keluar</button>
  <?php else: ?>
    <section class="owner-panel mt-4"><h2 class="text-lg font-bold">Selamat datang di BetaKos</h2><p class="my-3 text-sm text-slate-600">Masuk untuk menyimpan favorit dan melihat kos Anda.</p><a href="<?= BASE_URL ?>/login" class="btn-primary">Masuk</a><a href="<?= BASE_URL ?>/register" class="btn-secondary">Daftar</a><?php include ROOT_PATH . '/app/views/partials/google-signin-button.php'; ?></section>
  <?php endif; ?>
</div>
