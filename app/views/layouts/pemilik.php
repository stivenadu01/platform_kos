<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <title><?= $title ?? $_ENV['APP_NAME'] ?></title>

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

  <link
    rel="icon"
    type="image/x-icon"
    href="<?= BASE_URL ?>/assets/icon/favicon.ico">

  <meta name="theme-color" content="#c2410c">

  <link
    rel="manifest"
    href="<?= BASE_URL ?>/assets/icon/site.webmanifest">

  <link
    rel="apple-touch-icon"
    href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">

  <link
    rel="stylesheet"
    href="<?= uiAsset('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= uiAsset('assets/css/workspace-redesign.css') ?>">

  <!-- GLOBAL CONFIG -->
  <script>
    window.BASE_URL = <?= json_encode_safe(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode_safe($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode_safe($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.__NAV_COUNTS__ = <?= json_encode_safe(initialNavigationCounts()) ?>;
    window.__CSRF_TOKEN__ = <?= json_encode_safe(csrf_token()) ?>;
  </script>

  <!-- ALPINE -->
  <script defer src="<?= uiAsset('assets/js/vendor/alpine-3.14.9.min.js') ?>"></script>

  <!-- APP JS -->
  <script src="<?= uiAsset('assets/js/api.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/store.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/navigation.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/notifications.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/utils.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/app.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/ui-components.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/onboarding.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/operational-help.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/owner-swipe.js') ?>"></script>
</head>

<?php
$__ownerLayoutPath = parse_url($_SERVER['REQUEST_URI'] ?? '/pemilik', PHP_URL_PATH) ?: '/pemilik';
$__ownerLayoutBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__ownerLayoutBase !== '' && str_starts_with($__ownerLayoutPath, $__ownerLayoutBase)) {
  $__ownerLayoutPath = substr($__ownerLayoutPath, strlen($__ownerLayoutBase)) ?: '/';
}
$__ownerLayoutHasBottomNav = isOwnerPrimaryNavigationPath($__ownerLayoutPath)
  && empty($_GET['context']) && empty($_GET['id_penghuni']);
$__ownerBackFallback = '/pemilik';
foreach (['/pemilik/kamar' => '/pemilik/kos', '/pemilik/tipe-kamar' => '/pemilik/kos', '/pemilik/kos/' => '/pemilik/kos', '/pemilik/penghuni' => '/pemilik/penghuni', '/pemilik/pembayaran' => '/pemilik/pembayaran', '/pemilik/riwayat/' => '/pemilik/riwayat', '/pemilik/langganan/' => '/pemilik/langganan'] as $prefix => $fallback) {
  if (str_starts_with($__ownerLayoutPath, $prefix)) { $__ownerBackFallback = $fallback; break; }
}
?>
<body class="bg-slate-50 text-slate-800 owner-ui">

  <script>
    // Set the shell state before the first paint. Alpine will bind the same value afterwards.
    (() => {
      let collapsed = false;
      try { collapsed = localStorage.getItem('betakos_pemilik_sidebar_collapsed') === '1'; } catch (_) {}
      document.body.setAttribute('data-betakos-pemilik-sidebar-collapsed', collapsed ? 'true' : 'false');
      window.__BETAKOS_PEMILIK_SIDEBAR_COLLAPSED__ = collapsed;
    })();
  </script>
  <div
    id="pemilik-layout-shell"
    x-data="{ sidebarOpen: false, sidebarCollapsed: window.__BETAKOS_PEMILIK_SIDEBAR_COLLAPSED__ === true }"
    :data-sidebar-collapsed="sidebarCollapsed"
    data-layout-shell="pemilik"
    class="min-h-screen"
    x-init="$el.setAttribute('data-sidebar-hydrated', 'true'); document.body.removeAttribute('data-betakos-admin-sidebar-collapsed'); document.body.removeAttribute('data-betakos-pemilik-sidebar-collapsed')">

    <!-- MOBILE BACKDROP -->
    <div
      x-show="sidebarOpen"
      x-cloak
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black/40 z-40 lg:hidden">
    </div>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../partials/pemilik/sidebar.php'; ?>

    <!-- MAIN -->
    <div class="pemilik-main-shell min-h-screen">

      <?php include __DIR__ . '/../partials/pemilik/topbar.php'; ?>

      <main class="px-3 pt-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8 <?= $__ownerLayoutHasBottomNav ? 'pb-24' : 'pb-6' ?>">
        <?php if ($__ownerLayoutPath === '/pemilik'): ?>
          <section x-data="{ setup: null }" @betakos:setup-state.window="setup = $event.detail" x-show="setup && !setup.complete" x-cloak class="setup-banner" aria-label="Persiapan kos">
            <div><h2>Siapkan kos pertama Anda</h2><p x-text="setup ? setup.completed + ' dari ' + setup.total + ' tahap selesai. Lanjutkan kapan saja.' : ''"></p></div>
            <button type="button" class="btn-primary" @click="window.dispatchEvent(new CustomEvent('betakos:onboarding-help'))">Lanjutkan persiapan <?= masterIconSvg('chevron-right','h-4 w-4') ?></button>
          </section>
        <?php endif; ?>
        <?= $content ?>
      </main>

      <?php if ($__ownerLayoutHasBottomNav): ?>
        <?php include __DIR__ . '/../partials/pemilik/bottom-nav.php'; ?>
      <?php endif; ?>

    </div>

  </div>

  <!-- One sheet structure: fixed heading and footer, scrollable content. -->
  <div x-data="pemilikOnboarding()" x-cloak>
    <div x-show="panel" role="dialog" aria-modal="true" aria-labelledby="setup-title" aria-describedby="setup-caption" class="ui-dialog fixed inset-0 flex items-center justify-center sm:p-4" @keydown.escape.window="if (panel) closePanel()">
      <div class="absolute inset-0 bg-slate-950/45" @click="closePanel()"></div>
      <section class="ui-sheet-panel relative">
        <header class="ui-sheet-header"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-primary">Persiapan properti</p><h2 id="setup-title" class="mt-1 text-xl font-bold" x-text="state.complete ? (state.focus?.status === 'aktif' ? 'Kos sudah tayang' : 'Menunggu pemeriksaan') : 'Siapkan kos Anda'"></h2></div><button x-ref="setupClose" type="button" @click="closePanel()" class="owner-icon-button" aria-label="Tutup persiapan"><?= masterIconSvg('x','h-5 w-5') ?></button></div><p id="setup-caption" class="mt-2 text-sm text-slate-500">Selesaikan satu langkah, lalu lanjutkan kapan saja dari Akun → Persiapan kos.</p><p x-show="state.focus?.nama_kos" class="mt-2 font-semibold break-words" x-text="state.focus?.nama_kos"></p></header>
        <div class="ui-sheet-body" :aria-busy="loading">
          <p x-show="loading" role="status" class="py-6 text-center text-slate-500">Memuat progres persiapan…</p>
          <div x-show="!loading && errorMessage" role="alert" class="rounded-xl bg-red-50 p-4"><p class="text-sm text-red-700" x-text="errorMessage"></p><button type="button" @click="reload(true)" class="btn-secondary mt-3">Coba lagi</button></div>
          <div x-show="!loading && !errorMessage">
            <div class="mb-4 flex items-center gap-3"><div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-primary" :style="'width:'+state.percent+'%'"></div></div><span class="text-xs font-semibold text-slate-500" x-text="state.completed+' dari '+state.total"></span></div>
            <div class="space-y-2"><template x-for="(item,index) in state.steps" :key="item.key"><button type="button" @click="go(item)" class="ui-setup-step flex w-full items-center gap-3 rounded-xl border p-3 text-left" :class="item.complete ? 'border-emerald-100 bg-emerald-50' : state.next?.key===item.key ? 'border-primary bg-primary-soft' : 'border-slate-100 bg-slate-50'" :disabled="loading || item.complete || state.next?.key!==item.key"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold"><span x-show="!item.complete" x-text="index+1"></span><span x-show="item.complete" class="text-emerald-700"><?= masterIconSvg('check','h-4 w-4') ?></span></span><span class="min-w-0 flex-1"><strong class="block text-sm" x-text="item.label"></strong><small class="mt-1 block text-xs leading-5 text-slate-500" x-text="item.description"></small></span></button></template></div>
          </div>
        </div>
        <footer class="ui-sheet-footer"><button type="button" @click="closePanel()" class="btn-secondary">Nanti saja</button><button x-show="!state.complete" type="button" @click="continueSetup()" class="btn-primary" :disabled="loading || !!errorMessage || !state.next" x-text="state.next?.action_label || 'Lanjutkan'"></button><button x-show="state.complete" type="button" @click="closePanel()" class="btn-primary">Selesai</button></footer>
      </section>
    </div>
  </div>

  <!-- OPERATIONAL HELP: header/caption and footer never scroll -->
  <div x-data="pemilikOperationalHelp()" x-cloak>
    <div x-show="open" class="fixed inset-0 z-[80] bg-slate-950/35" @click="close()"></div>
    <section x-show="open && guide" x-ref="tooltip" role="dialog" aria-modal="true" aria-labelledby="page-help-title" aria-describedby="page-help-caption" class="page-help-panel" @keydown.escape.window="close()">
      <header class="page-help-header"><div class="flex items-start justify-between gap-3"><h2 id="page-help-title" class="text-lg font-bold" x-text="guide?.title"></h2><button type="button" @click="close()" class="owner-icon-button shrink-0" aria-label="Tutup bantuan"><?= masterIconSvg('x','h-5 w-5') ?></button></div><p id="page-help-caption" class="mt-2 text-sm text-slate-600" x-text="guide?.intro"></p></header>
      <div class="page-help-body" tabindex="0">
        <template x-if="guide?.steps?.length"><div aria-live="polite"><p class="text-sm font-semibold text-primary" x-text="'Langkah '+(current+1)+' dari '+guide.steps.length"></p><h3 class="mt-4 text-lg font-bold" x-text="guide.steps[current]?.[1]"></h3><p class="mt-3 text-sm leading-6 text-slate-600" x-text="guide.steps[current]?.[2]"></p></div></template>
        <p x-show="!guide?.steps?.length" class="text-sm text-slate-500">Gunakan tombol dan menu pada halaman untuk melanjutkan pekerjaan Anda.</p>
      </div>
      <footer class="page-help-footer"><button type="button" @click="close()" class="btn-secondary">Tutup</button><div class="flex flex-wrap gap-2"><button type="button" x-show="current>0" @click="prev()" class="btn-secondary">Sebelumnya</button><button type="button" @click="next()" class="btn-primary" x-text="!guide?.steps?.length || current===guide.steps.length-1 ? 'Selesai' : 'Lanjut'"></button></div></footer>
    </section>
  </div>

  <!-- GLOBAL UI -->
  <?php include __DIR__ . '/../partials/toast.php'; ?>

  <?php include ROOT_PATH . '/app/views/partials/notifications.php'; ?>
</body>

</html>
