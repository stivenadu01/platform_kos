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
    window.__USER__ = <?= json_encode($_SESSION['user'] ?? null) ?>;
  </script>

  <!-- ALPINE -->
  <script defer src="https://unpkg.com/alpinejs"></script>

  <!-- APP JS -->
  <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/store.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</head>

<body class="bg-slate-50 text-slate-800">

  <div
    x-data="{ sidebarOpen: false }"
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

  <!-- GLOBAL UI -->
  <?php include __DIR__ . '/../partials/toast.php'; ?>

</body>

</html>