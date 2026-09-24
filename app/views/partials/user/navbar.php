<?php
$__navbarPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__navbarBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__navbarBase !== '' && str_starts_with($__navbarPath, $__navbarBase)) $__navbarPath = substr($__navbarPath, strlen($__navbarBase)) ?: '/';
$__isPrimaryPublicPage = isPublicPrimaryNavigationPath($__navbarPath);
$__isKosDetailPage = str_starts_with($__navbarPath, '/kos/');
$__showMobileBack = !$__isPrimaryPublicPage;
$__mobileBackFallback = $__isKosDetailPage ? BASE_URL . '/cari-kos' : BASE_URL . '/';
$__mobileTitle = match (true) {
  $__navbarPath === '/user/favorit' => 'Favorit',
  $__navbarPath === '/user/kos-saya' => 'Kos Saya',
  $__navbarPath === '/user/riwayat-kos' => 'Riwayat & Klaim',
  $__navbarPath === '/user/laporan' => 'Laporan Saya',
  $__navbarPath === '/user/profil' => 'Profil',
  str_starts_with($__navbarPath, '/kos/') => (string)($kos['nama_kos'] ?? 'Detail Kos'),
  default => 'BetaKos',
};
?>
<header
  x-data="{ open: false<?= $__isKosDetailPage ? ", detailFavorited: " . (!empty($kos['is_favorited']) ? 'true' : 'false') : '' ?> }"
  <?= $__isKosDetailPage ? '@detail-kos:favorite-changed.window="detailFavorited = !!$event.detail.favorited"' : '' ?>
  class="public-navbar sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur <?= $__isPrimaryPublicPage ? 'hidden md:block' : '' ?>">
  <div class="public-navbar-inner mx-auto flex max-w-7xl items-center gap-2 px-3 sm:px-6 lg:px-8">
    <?php if ($__showMobileBack): ?>
      <button type="button" onclick="if (history.length > 1) history.back(); else location.href='<?= htmlspecialchars($__mobileBackFallback) ?>'" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-600 active:bg-slate-100 md:hidden" aria-label="Kembali"><?= masterIconSvg('arrow-left','h-5 w-5') ?></button>
      <span class="min-w-0 flex-1 truncate text-sm font-bold text-slate-900 md:hidden"><?= htmlspecialchars($__mobileTitle) ?></span>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/" class="<?= $__showMobileBack ? 'hidden md:flex' : 'flex' ?> items-center gap-3 shrink-0">
      <img
        src="<?= BASE_URL ?>/assets/icon/logo.png"
        alt="BetaKos"
        class="h-9 w-9 object-contain">
      <div class="leading-tight">
        <div class="font-bold text-slate-900">BetaKos</div>
        <div class="hidden text-[11px] font-medium text-slate-500 sm:block">Kupang</div>
      </div>
    </a>

    <nav class="ml-auto hidden items-center gap-1 md:flex">
      <a href="<?= BASE_URL ?>/" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
        Beranda
      </a>
      <a href="<?= BASE_URL ?>/cari-kos" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
        Cari Kos
      </a>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/favorit" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
          Favorit
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/kos-saya" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
          Kos Saya
        </a>
      </template>
    </nav>

    <?php if ($__isKosDetailPage): ?>
      <div class="ml-auto flex shrink-0 items-center gap-1.5">
        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pelanggan'): ?>
          <button type="button" @click="window.dispatchEvent(new CustomEvent('detail-kos:report'))" class="public-navbar-action public-navbar-action-danger" aria-label="Laporkan kos" title="Laporkan kos"><?= masterIconSvg('flag', 'h-4 w-4') ?><span>Laporkan</span></button>
          <button type="button" @click="window.dispatchEvent(new CustomEvent('detail-kos:favorite'))" class="public-navbar-action public-favorite-toggle" :class="detailFavorited ? 'is-favorite' : ''" :aria-pressed="detailFavorited" :aria-label="detailFavorited ? 'Hapus dari favorit' : 'Simpan ke favorit'" title="Favorit"><?= masterIconSvg('heart', 'h-4 w-4') ?><span x-text="detailFavorited ? 'Tersimpan' : 'Favorit'"></span></button>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login" class="public-navbar-action public-navbar-action-danger" aria-label="Login untuk melaporkan kos" title="Login untuk melapor"><?= masterIconSvg('flag', 'h-4 w-4') ?><span>Laporkan</span></a>
        <?php endif; ?>
        <button type="button" @click="window.dispatchEvent(new CustomEvent('detail-kos:share'))" class="public-navbar-action" aria-label="Bagikan kos" title="Bagikan"><?= masterIconSvg('share-2', 'h-4 w-4') ?><span>Bagikan</span></button>
      </div>
    <?php endif; ?>

    <div class="<?= $__isKosDetailPage ? 'hidden' : 'ml-auto hidden md:flex' ?> items-center gap-2">
      <button
        type="button"
        data-pwa-install
        hidden
        class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary-soft px-4 py-2 text-sm font-semibold text-primary hover:bg-blue-100"
        title="Pasang BetaKos di perangkat">
        <?= masterIconSvg('smartphone', 'h-4 w-4') ?>
        Unduh Aplikasi
      </button>
      <template x-if="!$store.auth.isLoggedIn">
        <div class="flex items-center gap-2">
          <a href="<?= BASE_URL ?>/login" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
            Masuk
          </a>
          <a href="<?= BASE_URL ?>/register" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark">
            Daftar
          </a>
        </div>
      </template>

      <template x-if="$store.auth.isLoggedIn">
        <div class="flex items-center gap-2">
          <template x-if="$store.auth.user?.role === 'pelanggan'">
            <a href="<?= BASE_URL ?>/user/profil" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Profil</a>
          </template>
          <template x-if="$store.auth.user?.role === 'pemilik'">
            <a href="<?= BASE_URL ?>/pemilik" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
              Dashboard
            </a>
          </template>
          <template x-if="$store.auth.user?.role === 'admin'">
            <a href="<?= BASE_URL ?>/admin" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
              Dashboard
            </a>
          </template>
          <button type="button" @click="$store.auth.logout()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Keluar
          </button>
        </div>
      </template>
    </div>

    <button
      type="button"
      class="hidden h-10 w-10 items-center justify-center rounded-xl text-slate-700 hover:bg-slate-100"
      @click="open = !open"
      :aria-expanded="open"
      aria-label="Buka menu">
      <span x-show="!open"><?= masterIconSvg('menu', 'h-5 w-5') ?></span>
      <span x-show="open" x-cloak><?= masterIconSvg('x', 'h-5 w-5') ?></span>
    </button>
  </div>

  <div x-show="open" x-cloak x-transition class="hidden border-t border-slate-100 bg-white">
    <nav class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
      <a @click="open = false" href="<?= BASE_URL ?>/" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Beranda
      </a>
      <a @click="open = false" href="<?= BASE_URL ?>/cari-kos" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cari Kos
      </a>
      <template x-if="$store.auth.isLoggedIn">
        <a @click="open = false" href="<?= BASE_URL ?>/user/favorit" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Favorit
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a @click="open = false" href="<?= BASE_URL ?>/user/profil" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Profil & Akun
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a @click="open = false" href="<?= BASE_URL ?>/user/laporan" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Laporan Saya
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a @click="open = false" href="<?= BASE_URL ?>/user/riwayat-kos" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Riwayat Kos Saya
        </a>
      </template>

      <button
        type="button"
        data-pwa-install
        hidden
        @click="open = false"
        class="flex w-full items-center gap-3 rounded-xl bg-primary-soft px-4 py-3 text-left text-sm font-semibold text-primary hover:bg-blue-100"
        title="Pasang BetaKos di perangkat">
        <?= masterIconSvg('smartphone', 'h-5 w-5') ?>
        Unduh Aplikasi
      </button>

      <div class="border-t border-slate-100 pt-3">
        <template x-if="!$store.auth.isLoggedIn">
          <div class="grid grid-cols-2 gap-2">
            <a @click="open = false" href="<?= BASE_URL ?>/login" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700">
              Masuk
            </a>
            <a @click="open = false" href="<?= BASE_URL ?>/register" class="rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-white">
              Daftar
            </a>
          </div>
        </template>

        <template x-if="$store.auth.isLoggedIn">
          <div class="space-y-2">
            <template x-if="$store.auth.user?.role === 'pemilik'">
              <a @click="open = false" href="<?= BASE_URL ?>/pemilik" class="block rounded-xl bg-primary-soft px-4 py-3 text-sm font-semibold text-primary">
                Dashboard Pemilik
              </a>
            </template>
            <button @click="open = false; $store.auth.logout()" type="button" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-left text-sm font-semibold text-slate-700">
              Keluar
            </button>
          </div>
        </template>
      </div>
    </nav>
  </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const current = window.location.pathname.replace(/\/$/, '') || '/';
  const links = Array.from(document.querySelectorAll('.public-navbar nav a[href]'));
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
