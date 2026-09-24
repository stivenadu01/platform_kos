<?php
$__publicPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__publicBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__publicBase !== '' && str_starts_with($__publicPath, $__publicBase)) $__publicPath = substr($__publicPath, strlen($__publicBase)) ?: '/';
$__publicUser = $_SESSION['user'] ?? null;
$__isCustomer = ($__publicUser['role'] ?? '') === 'pelanggan';
$__customerHref = static fn(string $path) => $__isCustomer ? BASE_URL . $path : BASE_URL . '/login';
$__active = static function (string $path) use ($__publicPath): bool { return $path === '/' ? $__publicPath === '/' : ($__publicPath === $path || str_starts_with($__publicPath, $path . '/')); };
$__showPrimaryNavigation = isPublicPrimaryNavigationPath($__publicPath);
?>
<?php if ($__showPrimaryNavigation): ?>
<div x-data="{ moreOpen:false }" @keydown.escape.window="moreOpen=false">
  <nav class="public-bottom-nav md:hidden" aria-label="Navigasi utama">
    <a href="<?= BASE_URL ?>/" class="public-bottom-nav-item <?= $__active('/') ? 'is-active' : '' ?>"><?= masterIconSvg('home','h-5 w-5') ?><span>Beranda</span></a>
    <a href="<?= BASE_URL ?>/cari-kos" class="public-bottom-nav-item <?= $__active('/cari-kos') || str_starts_with($__publicPath, '/kos/') ? 'is-active' : '' ?>"><?= masterIconSvg('search','h-5 w-5') ?><span>Cari</span></a>
    <a href="<?= $__customerHref('/user/favorit') ?>" class="public-bottom-nav-item <?= $__active('/user/favorit') ? 'is-active' : '' ?>"><?= masterIconSvg('heart','h-5 w-5') ?><span>Favorit</span></a>
    <a href="<?= $__customerHref('/user/kos-saya') ?>" class="public-bottom-nav-item <?= $__active('/user/kos-saya') ? 'is-active' : '' ?>"><?= masterIconSvg('building-2','h-5 w-5') ?><span>Kos Saya</span></a>
    <button type="button" @click="moreOpen=true" class="public-bottom-nav-item <?= $__active('/user/profil') || $__active('/user/riwayat-kos') || $__active('/user/laporan') ? 'is-active' : '' ?>" aria-label="Buka menu lainnya"><?= masterIconSvg('menu','h-5 w-5') ?><span>Lainnya</span></button>
  </nav>
  <div x-show="moreOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[1900] bg-slate-950/45 md:hidden" @click="moreOpen=false"></div>
  <aside x-show="moreOpen" x-cloak x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="public-more-sheet md:hidden">
    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4"><div><p class="text-xs font-semibold uppercase tracking-wider text-primary">Menu pelanggan</p><h2 class="mt-1 text-lg font-bold text-slate-900">Lainnya</h2></div><button type="button" @click="moreOpen=false" class="owner-icon-button" aria-label="Tutup"><?= masterIconSvg('x','h-5 w-5') ?></button></div>
    <div class="flex-1 overflow-y-auto p-4">
      <?php if ($__isCustomer): ?>
        <div class="mb-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 p-4"><p class="text-xs text-slate-500">Masuk sebagai</p><p class="mt-1 font-bold text-slate-900"><?= htmlspecialchars($__publicUser['nama'] ?? 'Pelanggan') ?></p><p class="mt-1 truncate text-xs text-slate-500"><?= htmlspecialchars($__publicUser['email'] ?? '') ?></p></div>
        <div class="space-y-1">
          <a href="<?= BASE_URL ?>/user/riwayat-kos" class="public-more-link"><?= masterIconSvg('history','h-5 w-5') ?><span><strong>Riwayat &amp; Klaim</strong><small>Kos lama dan pengajuan riwayat</small></span><?= masterIconSvg('chevron-right','ml-auto h-4 w-4') ?></a>
          <a href="<?= BASE_URL ?>/user/laporan" class="public-more-link"><?= masterIconSvg('flag','h-5 w-5') ?><span><strong>Laporan Saya</strong><small>Pantau laporan informasi kos</small></span><?= masterIconSvg('chevron-right','ml-auto h-4 w-4') ?></a>
          <a href="<?= BASE_URL ?>/user/profil" class="public-more-link"><?= masterIconSvg('user-round','h-5 w-5') ?><span><strong>Profil &amp; Akun</strong><small>Identitas dan keamanan akun</small></span><?= masterIconSvg('chevron-right','ml-auto h-4 w-4') ?></a>
          <button type="button" data-pwa-install hidden class="public-more-link w-full"><?= masterIconSvg('smartphone','h-5 w-5') ?><span><strong>Pasang BetaKos</strong><small>Akses lebih cepat dari perangkat</small></span></button>
        </div>
        <button type="button" @click="moreOpen=false; $nextTick(() => $store.auth.logout())" class="mt-5 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 text-sm font-semibold text-rose-600"><?= masterIconSvg('log-out','h-4 w-4') ?> Keluar</button>
      <?php else: ?>
        <div class="rounded-2xl bg-blue-50 p-5 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-primary shadow-sm"><?= masterIconSvg('user-round','h-6 w-6') ?></div><h3 class="mt-3 font-bold text-slate-900">Masuk ke BetaKos</h3><p class="mt-1 text-sm leading-6 text-slate-500">Simpan favorit dan hubungkan riwayat kos Anda.</p><div class="mt-4 grid grid-cols-2 gap-2"><a href="<?= BASE_URL ?>/login" class="btn-secondary justify-center">Masuk</a><a href="<?= BASE_URL ?>/register" class="btn-primary justify-center">Daftar</a></div></div>
      <?php endif; ?>
    </div>
  </aside>
</div>
<?php endif; ?>
