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
<nav class="public-bottom-nav md:hidden" aria-label="Navigasi utama">
  <a href="<?= BASE_URL ?>/cari-kos" class="public-bottom-nav-item <?= ($__active('/') || $__active('/cari-kos')) ? 'is-active' : '' ?>"><?= masterIconSvg('search','h-5 w-5') ?><span>Jelajahi</span></a>
  <a href="<?= $__customerHref('/user/favorit') ?>" class="public-bottom-nav-item <?= $__active('/user/favorit') ? 'is-active' : '' ?>"><?= masterIconSvg('heart','h-5 w-5') ?><span>Favorit</span><?php echo navigationBadge('favorites','kos favorit', true); ?></a>
  <a href="<?= $__customerHref('/user/kos-saya') ?>" class="public-bottom-nav-item <?= $__active('/user/kos-saya') ? 'is-active' : '' ?>"><?= masterIconSvg('building-2','h-5 w-5') ?><span>Kos Saya</span><?php echo navigationBadge('overdue','tagihan lewat jatuh tempo'); ?></a>
  <a href="<?= BASE_URL ?>/user/akun" class="public-bottom-nav-item <?= $__active('/user/akun') ? 'is-active' : '' ?>"><?= masterIconSvg('user-round','h-5 w-5') ?><span>Akun</span></a>
</nav>
<?php endif; ?>
