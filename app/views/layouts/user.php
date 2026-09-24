<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#2563eb">

  <title><?= htmlspecialchars($title ?? ($_ENV['APP_NAME'] ?? 'BetaKos')) ?></title>

  <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/icon/favicon.ico">
  <link rel="manifest" href="<?= BASE_URL ?>/assets/icon/site.webmanifest">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
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
  <script src="<?= BASE_URL ?>/assets/js/owner-swipe.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/location-picker.js"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>

<?php
$__layoutPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__layoutBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__layoutBase !== '' && str_starts_with($__layoutPath, $__layoutBase)) $__layoutPath = substr($__layoutPath, strlen($__layoutBase)) ?: '/';
$__layoutHasBottomNav = isPublicPrimaryNavigationPath($__layoutPath);
?>
<body class="public-ui min-h-screen bg-slate-50 text-slate-800 antialiased">
  <div class="min-h-screen">
    <?php include ROOT_PATH . '/app/views/partials/user/navbar.php'; ?>

    <main class="min-h-[calc(100vh-4rem)] <?= $__layoutHasBottomNav ? 'pb-24 md:pb-0' : '' ?>">
      <?= $content ?>
    </main>

    <?php include ROOT_PATH . '/app/views/partials/user/footer.php'; ?>
    <?php include ROOT_PATH . '/app/views/partials/user/mobile-nav.php'; ?>
    <?php include ROOT_PATH . '/app/views/partials/toast.php'; ?>
  </div>
</body>
</html>
