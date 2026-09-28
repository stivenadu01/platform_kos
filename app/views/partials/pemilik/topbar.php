<?php
$__topbarIsPro = false;
$__topbarSubscriptionLabel = 'Gratis';
$__topbarPromoEligible = false;
$__topbarPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$__topbarPageLabel = 'Dashboard';
$__topbarPageMap = [
  '/pemilik/pembayaran' => 'Keuangan',
  '/pemilik/riwayat' => 'Riwayat',
  '/pemilik/penghuni' => 'Penghuni',
  '/pemilik/kamar' => 'Kamar',
  '/pemilik/tipe-kamar' => 'Tipe Kamar',
  '/pemilik/kos' => 'Properti',
  '/pemilik/claim' => 'Klaim Riwayat',
  '/pemilik/langganan' => 'Langganan',
  '/pemilik/profil' => 'Profil',
];
foreach ($__topbarPageMap as $__topbarPrefix => $__topbarLabel) {
  if (strpos($__topbarPath, $__topbarPrefix) !== false) {
    $__topbarPageLabel = $__topbarLabel;
    break;
  }
}
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pemilik') {
  model('Langganan');
  $__topbarStatusLangganan = getStatusLanggananPemilik((int)$_SESSION['user']['id_user']);
  $__topbarIsPro = !empty($__topbarStatusLangganan['is_pro']);
  $__topbarSubscriptionLabel = $__topbarIsPro ? 'BetaKos Pro' : 'Akun Gratis';
  $__topbarPromo = getKelayakanPromoLangganan((int)$_SESSION['user']['id_user']);
  $__topbarPromoEligible = !empty($__topbarPromo['eligible']);
}
?>

<header
  class="
    h-16
    bg-white
    border-b border-slate-200
    flex items-center justify-between
    px-4 sm:px-6
    sticky top-0 z-30 pemilik-topbar
  ">

  <!-- LEFT -->
  <div class="flex items-center gap-3">

    <!-- DESKTOP SIDEBAR TOGGLE -->
    <button
      type="button"
      @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('betakos_pemilik_sidebar_collapsed', sidebarCollapsed ? '1' : '0')"
      class="hidden lg:inline-flex w-10 h-10 rounded-lg hover:bg-slate-100 items-center justify-center text-slate-600"
      :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
      :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
      <span x-text="sidebarCollapsed ? '»' : '«'"></span>
    </button>

    <!-- MOBILE BACK: sumber tujuan mengikuti tombol kembali milik halaman -->
    <button
      id="owner-mobile-back"
      type="button"
      hidden
      class="
        owner-mobile-navbar-back md:hidden
        w-10 h-10
        rounded-lg
        hover:bg-slate-100
        flex items-center justify-center
      "
      aria-label="Kembali"
      title="Kembali">

      <?= masterIconSvg('chevron-left', 'h-6 w-6') ?>

    </button>

    <!-- TABLET MENU: bottom navigation hanya aktif di layar mobile -->
    <button
      type="button"
      @click="sidebarOpen = true"
      class="hidden h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 md:inline-flex lg:hidden"
      aria-label="Buka menu">
      <?= masterIconSvg('menu', 'h-5 w-5') ?>
    </button>


    <div class="lg:hidden leading-tight">
      <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Panel Pemilik</div>
      <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($__topbarPageLabel) ?></div>
    </div>

  </div>


  <!-- RIGHT -->
  <div class="flex items-center gap-3">

    <?php if ($__topbarPromoEligible): ?>
      <a
        href="<?= BASE_URL ?>/pemilik/langganan?paket=pro_6_bulan"
        class="inline-flex h-9 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 text-xs font-bold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 sm:px-3"
        title="Klaim gratis 6 bulan pertama BetaKos Pro">
        <?= masterIconSvg('gift', 'h-4 w-4') ?>
        <span class="hidden md:inline">Klaim gratis 6 bulan</span>
        <span class="md:hidden">6 bulan gratis</span>
      </a>
    <?php endif; ?>

    <button
      type="button"
      data-pwa-install data-pwa-install-mobile
      hidden
      class="inline-flex sm:hidden w-10 h-10 items-center justify-center rounded-lg border border-primary/20 bg-primary-soft text-primary hover:bg-blue-100"
      title="Pasang BetaKos di perangkat"
      aria-label="Pasang BetaKos di perangkat">
      <?= masterIconSvg('smartphone', 'h-5 w-5') ?>
    </button>
    <button
      type="button"
      data-pwa-install
      hidden
      class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-primary/20 bg-primary-soft px-3 py-2 text-sm font-semibold text-primary hover:bg-blue-100"
      title="Pasang BetaKos di perangkat">
      <?= masterIconSvg('smartphone', 'h-5 w-5') ?>
      Unduh Aplikasi
    </button>

    <!-- CONTEXTUAL HELP -->
    <button
      type="button"
      @click="
        if (localStorage.getItem('betakos_owner_onboarding_complete_v4') !== '1') {
          window.dispatchEvent(new CustomEvent('betakos:onboarding-help'));
        } else {
          window.dispatchEvent(new CustomEvent('betakos:operational-help'));
        }
      "
      class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
      title="Buka bantuan sesuai halaman yang sedang dibuka">
      <span class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-400 text-xs font-bold">?</span>
      <span class="hidden sm:inline">Bantuan</span>
    </button>

    <!-- USER -->
    <div class="hidden sm:block text-right">

      <div
        class="text-sm font-semibold text-slate-800"
        x-text="$store.auth.user?.nama || 'Pemilik'">
      </div>

      <div class="mt-0.5 flex items-center justify-end gap-2 text-xs">
        <span class="text-slate-500">Pemilik Kos</span>
        <?php if ($__topbarIsPro): ?>
          <span class="rounded-full bg-primary-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">PRO</span>
        <?php else: ?>
          <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Gratis</span>
        <?php endif; ?>
      </div>

    </div>


    <!-- AVATAR -->
    <template x-if="$store.auth.user?.foto">
      <img
        :src="window.BASE_URL + '/uploads' + $store.auth.user.foto"
        :alt="$store.auth.user?.nama || 'Pemilik'"
        class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200">
    </template>

    <template x-if="!$store.auth.user?.foto">
      <div
        class="
          w-10 h-10
          rounded-full
          bg-primary-soft
          text-primary
          flex items-center justify-center
          font-semibold
        "
        x-text="
          ($store.auth.user?.nama || 'P')
            .charAt(0)
            .toUpperCase()
        ">
      </div>
    </template>

  </div>

</header>

<script>
  (() => {
    const button = document.getElementById('owner-mobile-back');
    if (!button) return;

    const getBackSource = () => Array.from(document.querySelectorAll('main .owner-back-link'))
      .find((link) => link.style.display !== 'none');

    const sync = () => {
      button.hidden = !getBackSource();
    };

    button.addEventListener('click', () => {
      const source = getBackSource();
      if (source) source.click();
    });

    const start = () => {
      sync();
      const main = document.querySelector('main');
      if (main) new MutationObserver(sync).observe(main, { subtree: true, attributes: true, attributeFilter: ['style'] });
      window.setTimeout(sync, 100);
    };

    document.readyState === 'loading'
      ? document.addEventListener('DOMContentLoaded', start, { once: true })
      : start();
  })();
</script>
