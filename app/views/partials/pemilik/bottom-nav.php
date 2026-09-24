<?php
$__ownerMobilePath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/');
$__ownerMobileBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__ownerMobileBase !== '' && strpos($__ownerMobilePath, $__ownerMobileBase) === 0) {
  $__ownerMobilePath = substr($__ownerMobilePath, strlen($__ownerMobileBase));
}
$__ownerMobilePath = $__ownerMobilePath ?: '/pemilik';

$__ownerMobileActive = static function (string $route) use ($__ownerMobilePath): bool {
  if ($route === '/pemilik') return $__ownerMobilePath === '/pemilik';
  return $__ownerMobilePath === $route || strpos($__ownerMobilePath, $route . '/') === 0;
};
?>

<nav class="owner-bottom-nav lg:hidden" aria-label="Navigasi utama pemilik">
  <a href="<?= BASE_URL ?>/pemilik" class="owner-bottom-nav-item <?= $__ownerMobileActive('/pemilik') ? 'is-active' : '' ?>" <?= $__ownerMobileActive('/pemilik') ? 'aria-current="page"' : '' ?>>
    <?= masterIconSvg('home', 'h-5 w-5') ?>
    <span>Beranda</span>
  </a>
  <a href="<?= BASE_URL ?>/pemilik/kos" class="owner-bottom-nav-item <?= ($__ownerMobileActive('/pemilik/kos') || $__ownerMobileActive('/pemilik/kamar') || $__ownerMobileActive('/pemilik/tipe-kamar')) ? 'is-active' : '' ?>">
    <?= masterIconSvg('building-2', 'h-5 w-5') ?>
    <span>Properti</span>
  </a>
  <a href="<?= BASE_URL ?>/pemilik/penghuni" class="owner-bottom-nav-item <?= $__ownerMobileActive('/pemilik/penghuni') ? 'is-active' : '' ?>" <?= $__ownerMobileActive('/pemilik/penghuni') ? 'aria-current="page"' : '' ?>>
    <?= masterIconSvg('users-round', 'h-5 w-5') ?>
    <span>Penghuni</span>
  </a>
  <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="owner-bottom-nav-item <?= $__ownerMobileActive('/pemilik/pembayaran') ? 'is-active' : '' ?>" <?= $__ownerMobileActive('/pemilik/pembayaran') ? 'aria-current="page"' : '' ?>>
    <?= masterIconSvg('wallet', 'h-5 w-5') ?>
    <span>Keuangan</span>
  </a>
  <button type="button" @click="sidebarOpen = true" class="owner-bottom-nav-item <?= ($__ownerMobileActive('/pemilik/riwayat') || $__ownerMobileActive('/pemilik/profil') || $__ownerMobileActive('/pemilik/claim') || $__ownerMobileActive('/pemilik/langganan')) ? 'is-active' : '' ?>" aria-label="Buka menu lainnya">
    <?= masterIconSvg('menu', 'h-5 w-5') ?>
    <span>Lainnya</span>
  </button>
</nav>
