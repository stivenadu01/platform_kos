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
</head>

<body class="bg-slate-50 text-slate-800">

  <div
    x-data="{ sidebarOpen: false }"
    @betakos:onboarding-open-sidebar.window="sidebarOpen = true"
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
    <div class="lg:ml-64 min-h-screen">

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

  <!-- GLOBAL UI -->
  <?php include __DIR__ . '/../partials/toast.php'; ?>

</body>

</html>