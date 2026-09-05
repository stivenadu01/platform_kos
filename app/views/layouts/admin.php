<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Admin BetaKos') ?></title>
  <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/icon/favicon.ico">
  <meta name="theme-color" content="#2563eb">
  <link rel="manifest" href="<?= BASE_URL ?>/assets/icon/site.webmanifest">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <script>
    window.BASE_URL = <?= json_encode_safe(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode_safe($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode_safe($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.__CSRF_TOKEN__ = <?= json_encode_safe(csrf_token()) ?>;
  </script>
  <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/store.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <script>
    // Set the shell state before the first paint. Alpine will bind the same value afterwards.
    (() => {
      let collapsed = false;
      try { collapsed = localStorage.getItem('betakos_admin_sidebar_collapsed') === '1'; } catch (_) {}
      document.body.setAttribute('data-betakos-admin-sidebar-collapsed', collapsed ? 'true' : 'false');
      window.__BETAKOS_ADMIN_SIDEBAR_COLLAPSED__ = collapsed;
    })();
  </script>
  <div id="admin-layout-shell" x-data="{ sidebarOpen: false, sidebarCollapsed: window.__BETAKOS_ADMIN_SIDEBAR_COLLAPSED__ === true }" :data-sidebar-collapsed="sidebarCollapsed" data-layout-shell="admin" class="min-h-screen" x-init="$el.setAttribute('data-sidebar-hydrated', 'true'); document.body.removeAttribute('data-betakos-admin-sidebar-collapsed'); document.body.removeAttribute('data-betakos-pemilik-sidebar-collapsed')">
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
    <?php include ROOT_PATH . '/app/views/partials/admin/sidebar.php'; ?>
    <div class="admin-main-shell min-h-screen min-w-0 overflow-x-hidden">
      <?php include ROOT_PATH . '/app/views/partials/admin/topbar.php'; ?>
      <main class="p-4 pt-20 sm:p-6 sm:pt-20 lg:p-8 lg:pt-24 min-w-0 overflow-x-hidden">
        <?= $content ?>
      </main>
    </div>
    <?php include ROOT_PATH . '/app/views/partials/toast.php'; ?>
  </div>
</body>
</html>
