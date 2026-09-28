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

  <meta name="theme-color" content="#2563eb">

  <link
    rel="manifest"
    href="<?= BASE_URL ?>/assets/icon/site.webmanifest">

  <link
    rel="apple-touch-icon"
    href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">

  <link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/app.css">

  <!-- GLOBAL CONFIG -->
  <script>
    window.BASE_URL = <?= json_encode_safe(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode_safe($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode_safe($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.__CSRF_TOKEN__ = <?= json_encode_safe(csrf_token()) ?>;
  </script>

  <!-- ALPINE -->
  <script defer src="https://unpkg.com/alpinejs"></script>

  <!-- APP JS -->
  <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/store.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/onboarding.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/operational-help.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/owner-swipe.js"></script>
</head>

<?php
$__ownerLayoutPath = parse_url($_SERVER['REQUEST_URI'] ?? '/pemilik', PHP_URL_PATH) ?: '/pemilik';
$__ownerLayoutBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__ownerLayoutBase !== '' && str_starts_with($__ownerLayoutPath, $__ownerLayoutBase)) {
  $__ownerLayoutPath = substr($__ownerLayoutPath, strlen($__ownerLayoutBase)) ?: '/';
}
$__ownerLayoutHasBottomNav = isOwnerPrimaryNavigationPath($__ownerLayoutPath);
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
        <?= $content ?>
      </main>

      <?php if ($__ownerLayoutHasBottomNav): ?>
        <?php include __DIR__ . '/../partials/pemilik/bottom-nav.php'; ?>
      <?php endif; ?>

    </div>

  </div>

  <!-- ONBOARDING -->
  <div x-data="pemilikOnboarding()" x-init="init()" x-cloak>
    <div x-show="welcome" class="fixed inset-0 z-[90] flex items-center justify-center p-4" @keydown.escape.window="postpone()">
      <div class="absolute inset-0 bg-slate-950/45"></div>
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl sm:p-7">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><?= masterIconSvg('clipboard-check', 'h-6 w-6') ?></div>
          <div><p class="text-sm font-semibold text-emerald-700">Selamat datang di BetaKos</p><h2 class="text-xl font-bold text-slate-900">Mari siapkan kos pertama</h2></div>
        </div>
        <p class="mt-4 text-sm leading-6 text-slate-600">Selesaikan lima tahap sederhana agar kos siap diperiksa dan ditampilkan kepada pencari. Anda dapat berhenti dan melanjutkannya kapan saja.</p>
        <div class="mt-4 grid gap-2 text-sm text-slate-700 sm:grid-cols-2"><div>① Profil pemilik</div><div>② Data dan foto kos</div><div>③ Tipe dan foto kamar</div><div>④ Nomor unit kamar</div><div>⑤ Ajukan verifikasi</div></div>
        <div class="mt-7 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
          <button type="button" @click="postpone()" class="btn-secondary">Nanti saja</button>
          <button type="button" @click="start()" class="btn-success">Mulai Siapkan Kos</button>
        </div>
      </div>
    </div>

    <div x-show="panel" class="fixed inset-0 z-[90] flex items-center justify-center p-3" @keydown.escape.window="closePanel()">
      <button type="button" class="absolute inset-0 bg-slate-950/45" @click="closePanel()" aria-label="Tutup asisten"></button>
      <div class="relative max-h-[calc(100vh-24px)] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Asisten Persiapan Kos</p><h2 class="mt-1 text-xl font-bold text-slate-900" x-text="state.complete ? 'Kos siap digunakan' : 'Selesaikan kos pertama Anda'"></h2><p x-show="state.focus?.nama_kos" class="mt-1 text-sm text-slate-500" x-text="state.focus.nama_kos"></p></div><button type="button" @click="closePanel()" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-label="Tutup"><?= masterIconSvg('x', 'h-4 w-4') ?></button></div>
        <div class="mt-5 flex items-center gap-3"><div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500 transition-all" :style="'width:'+state.percent+'%'"></div></div><strong class="text-xs text-slate-600" x-text="state.completed+' dari '+state.total"></strong></div>
        <div x-show="errorMessage" class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700" x-text="errorMessage"></div>
        <div class="mt-4 space-y-2">
          <template x-for="(item,index) in state.steps" :key="item.key"><button type="button" @click="go(item)" class="flex w-full items-center gap-3 rounded-xl border p-3 text-left" :class="item.complete ? 'border-emerald-100 bg-emerald-50/60' : state.next?.key===item.key ? 'border-emerald-300 bg-white shadow-sm' : 'border-slate-100 bg-slate-50'" :disabled="item.complete || state.next?.key!==item.key">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold" :class="item.complete ? 'bg-emerald-600 text-white' : state.next?.key===item.key ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500'"><span x-show="!item.complete" x-text="index+1"></span><span x-show="item.complete"><?= masterIconSvg('check', 'h-4 w-4') ?></span></span>
            <span class="min-w-0 flex-1"><strong class="block text-sm text-slate-900" x-text="item.label"></strong><small class="mt-0.5 block text-xs leading-5 text-slate-500" x-text="item.description"></small></span>
            <span x-show="state.next?.key===item.key" class="text-emerald-600"><?= masterIconSvg('chevron-right', 'h-5 w-5') ?></span>
          </button></template>
        </div>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between"><button type="button" @click="closePanel()" class="btn-secondary">Tutup</button><button x-show="!state.complete" type="button" @click="continueSetup()" class="btn-success" x-text="state.next?.action_label || 'Lanjutkan Persiapan'"></button><button x-show="state.complete" type="button" @click="closePanel()" class="btn-success">Selesai</button></div>
        <p class="mt-3 text-center text-xs text-slate-400">Progres tersimpan otomatis dan dapat dilanjutkan dari tombol Bantuan.</p>
      </div>
    </div>
  </div>

  <!-- OPERATIONAL HELP -->
  <div x-data="pemilikOperationalHelp()" x-init="init()" x-cloak>
    <div
      x-show="open && rect"
      class="fixed z-[81] rounded-xl border-2 border-primary shadow-[0_0_0_9999px_rgba(15,23,42,.42)] pointer-events-none"
      :style="highlightStyle">
    </div>

    <div
      x-show="open"
      class="fixed inset-0 z-[80] pointer-events-none">
      <div class="absolute inset-x-0 top-0 h-16 bg-transparent"></div>
    </div>

    <div
      x-show="open && guide"
      x-ref="tooltip"
      class="fixed z-[85] rounded-2xl bg-white p-4 shadow-2xl border border-slate-200 max-h-[calc(100vh-24px)] overflow-y-auto"
      :style="rect ? tooltipStyle : 'left:50%;top:50%;transform:translate(-50%,-50%);width:min(390px,calc(100vw - 24px));'"
      @keydown.escape.window="close()">
      <div class="flex items-start justify-between gap-4">
        <div>
          <div class="text-xs font-semibold text-primary">Bantuan halaman</div>
          <h3 class="mt-1 font-bold text-slate-900" x-text="guide?.title"></h3>
          <p class="mt-1 text-xs leading-5 text-slate-500" x-text="guide?.intro"></p>
        </div>
        <button type="button" @click="close()" class="text-slate-400 hover:text-slate-700 text-xl leading-none">×</button>
      </div>

      <template x-if="guide?.steps?.length">
        <div>
          <div class="mt-3 flex items-center gap-2">
            <span class="text-xs font-medium text-slate-500" x-text="(current + 1) + ' dari ' + guide.steps.length"></span>
            <div class="h-1.5 flex-1 rounded-full bg-slate-100 overflow-hidden">
              <div class="h-full rounded-full bg-primary transition-all" :style="'width:' + (((current + 1) / guide.steps.length) * 100) + '%'"> </div>
            </div>
          </div>
          <h4 class="mt-4 font-semibold text-slate-900" x-text="guide.steps[current]?.[1]"></h4>
          <p class="mt-2 text-sm leading-5 text-slate-600" x-text="guide.steps[current]?.[2]"></p>
          <div class="mt-4 flex items-center justify-between gap-3">
            <button type="button" @click="close()" class="text-sm font-medium text-slate-500 hover:text-slate-800">Tutup</button>
            <div class="flex gap-2">
              <button type="button" x-show="current > 0" @click="prev()" class="btn-secondary text-sm">Sebelumnya</button>
              <button type="button" @click="next()" class="btn-primary text-sm" x-text="current === guide.steps.length - 1 ? 'Selesai' : 'Lanjut'"></button>
            </div>
          </div>
        </div>
      </template>

      <template x-if="!guide?.steps?.length">
        <div>
          <p class="mt-4 text-sm leading-6 text-slate-600" x-text="guide?.intro"></p>
          <div class="mt-5 flex justify-end">
            <button type="button" @click="close()" class="btn-primary text-sm">Mengerti</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- GLOBAL UI -->
  <?php include __DIR__ . '/../partials/toast.php'; ?>

</body>

</html>
