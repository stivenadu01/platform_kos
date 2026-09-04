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

  <link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/app.css">

  <!-- GLOBAL CONFIG -->
  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.__CSRF_TOKEN__ = <?= json_encode(csrf_token()) ?>;
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
</head>

<body class="bg-slate-50 text-slate-800">

  <div
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('betakos_pemilik_sidebar_collapsed') === '1' }"
    @betakos:onboarding-open-sidebar.window="sidebarOpen = true; sidebarCollapsed = false"
    class="min-h-screen">

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
    <div class="min-h-screen transition-[margin] duration-300" :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">

      <?php include __DIR__ . '/../partials/pemilik/topbar.php'; ?>

      <main class="p-4 sm:p-6 lg:p-8">
        <?= $content ?>
      </main>

    </div>

  </div>

  <!-- ONBOARDING -->
  <div x-data="pemilikOnboarding()" x-init="init()" x-cloak>
    <!-- Spotlight: area di luar target diredupkan melalui box-shadow sehingga target tetap terang dan dapat dipelajari. -->
    <div
      x-show="open && rect"
      class="fixed z-[81] rounded-xl border-2 border-primary shadow-[0_0_0_9999px_rgba(15,23,42,.42)] pointer-events-none"
      :style="highlightStyle">
    </div>

    <div
      x-show="welcome"
      class="fixed inset-0 z-[90] flex items-center justify-center p-4"
      @keydown.escape.window="closeWelcome()">
      <div class="absolute inset-0 bg-slate-950/45"></div>
      <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-soft text-primary text-xl font-bold">✓</div>
          <div>
            <p class="text-sm font-semibold text-primary">Selamat datang di BetaKos</p>
            <h2 class="text-xl font-bold text-slate-900">Mari siapkan akun Anda</h2>
          </div>
        </div>
        <p class="mt-5 text-sm leading-6 text-slate-600">
          Panduan ini akan membantu Anda melakukan pengaturan awal melalui fitur BetaKos yang sebenarnya.
          Anda cukup mengikuti bagian yang disorot dan melakukan tindakan seperti biasa.
        </p>
        <div class="mt-5 space-y-2 text-sm text-slate-600">
          <div>① Lengkapi Profil</div>
          <div>② Tambahkan Kos</div>
          <div>③ Tambahkan Tipe Kamar dan Foto</div>
          <div>④ Tambahkan Kamar</div>
          <div>⑤ Ajukan Verifikasi</div>
        </div>
        <div class="mt-7 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
          <button type="button" @click="skip()" class="btn-secondary">Nanti saja</button>
          <button type="button" @click="start()" class="btn-primary">Mulai Panduan</button>
        </div>
      </div>
    </div>

    <div
      x-show="open && step && !welcome && rect"
      class="fixed z-[85] rounded-2xl bg-white p-4 shadow-2xl border border-slate-200"
      :style="tooltipStyle">
      <div class="text-xs font-semibold text-primary" x-text="title"></div>
      <h3 class="mt-1 font-bold text-slate-900" x-text="subTitle || step?.label"></h3>
      <p class="mt-2 text-sm leading-5 text-slate-600" x-text="message"></p>

      <div class="mt-4 flex items-center justify-between gap-3">
        <span class="text-xs text-slate-400" x-text="state?.completed + ' dari ' + state?.total + ' selesai'"></span>
        <div class="flex gap-2">
          <button type="button" @click="skip()" class="text-sm font-medium text-slate-500 hover:text-slate-800">Nanti saja</button>
          <button type="button" @click="nextSubstep()" class="btn-primary text-sm" x-text="currentSubstep < substeps.length - 1 ? 'Lanjut' : 'Saya sudah selesai'"></button>
        </div>
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