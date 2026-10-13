<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#c2410c">

  <title><?= htmlspecialchars($title ?? ($_ENV['APP_NAME'] ?? 'BetaKos')) ?></title>

  <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/icon/favicon.ico">
  <link rel="manifest" href="<?= BASE_URL ?>/assets/icon/site.webmanifest">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">

  <link rel="stylesheet" href="<?= uiAsset('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= uiAsset('assets/css/workspace-redesign.css') ?>">

  <script>
    window.BASE_URL = <?= json_encode_safe(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode_safe($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode_safe($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.__NAV_COUNTS__ = <?= json_encode_safe(initialNavigationCounts()) ?>;
    window.__CSRF_TOKEN__ = <?= json_encode_safe(csrf_token()) ?>;
  </script>

  <script src="<?= uiAsset('assets/js/api.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/store.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/navigation.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/notifications.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/google-identity.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/utils.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/app.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/ui-components.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/owner-swipe.js') ?>"></script>
  <script src="<?= uiAsset('assets/js/location-picker.js') ?>"></script>
  <script defer src="<?= uiAsset('assets/js/vendor/alpine-3.14.9.min.js') ?>"></script>
</head>

<?php
$__layoutPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__layoutBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/');
if ($__layoutBase !== '' && str_starts_with($__layoutPath, $__layoutBase)) $__layoutPath = substr($__layoutPath, strlen($__layoutBase)) ?: '/';
$__layoutHasBottomNav = isPublicPrimaryNavigationPath($__layoutPath);
?>
<body class="public-ui min-h-screen bg-slate-50 text-slate-800 antialiased">
  <?php if ($__layoutHasBottomNav && !empty($_SESSION['user'])): ?>
    <div class="bk-mobile-notification md:hidden"><?php include ROOT_PATH . '/app/views/partials/notification-button.php'; ?></div>
  <?php endif; ?>
  <div class="min-h-screen">
    <?php include ROOT_PATH . '/app/views/partials/user/navbar.php'; ?>
    <?php if (empty($_SESSION['user']) && GOOGLE_CLIENT_ID !== ''): ?>
      <div id="betakos-google-one-tap" class="bk-google-one-tap-anchor"></div>
    <?php endif; ?>

    <main class="min-h-[calc(100vh-4rem)] <?= $__layoutHasBottomNav ? 'pb-24 md:pb-0' : '' ?>">
      <?= $content ?>
    </main>

    <?php include ROOT_PATH . '/app/views/partials/user/footer.php'; ?>
    <?php include ROOT_PATH . '/app/views/partials/user/mobile-nav.php'; ?>
    <?php include ROOT_PATH . '/app/views/partials/toast.php'; ?>
  </div>
  <?php include ROOT_PATH . '/app/views/partials/notifications.php'; ?>
  <?php if (empty($_SESSION['user']) && GOOGLE_CLIENT_ID !== ''): ?>
    <?php require_once ROOT_PATH . '/app/helpers/google_identity_helper.php'; ?>
    <script>window.__GOOGLE_IDENTITY__ = <?= json_encode_safe(googleIdentityConfig(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
    <script data-google-client src="https://accounts.google.com/gsi/client" async onload="BetaKosGoogle.init()" onerror="BetaKosGoogle.failed()"></script>
  <?php endif; ?>
</body>
</html>
