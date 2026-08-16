<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">

  <title><?= $title ?? $_ENV['APP_NAME'] ?></title>

  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

  <!-- FAVICON -->
  <link
    rel="icon"
    type="image/x-icon"
    href="<?= BASE_URL ?>/assets/icon/favicon.ico">

  <link
    rel="manifest"
    href="<?= BASE_URL ?>/assets/icon/site.webmanifest">

  <link
    rel="apple-touch-icon"
    href="<?= BASE_URL ?>/assets/icon/apple-touch-icon.png">

  <!-- GOOGLE FONT -->
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;700&family=Montserrat:wght@600;700&display=swap"
    rel="stylesheet">

  <!-- APP CSS -->
  <link
    rel="stylesheet"
    href="<?= BASE_URL ?>/assets/css/app.css">


  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.NOMOR_WA = <?= json_encode($_ENV['NOMOR_WA'] ?? '') ?>;
    window.__USER__ = <?= json_encode($_SESSION['user'] ?? null) ?>;
  </script>

  <script src="<?= BASE_URL ?>/assets/js/api.js"></script>
  <!-- Store mendaftarkan Alpine.store() -->
  <script src="<?= BASE_URL ?>/assets/js/store.js"></script>
  <!-- Utility -->
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <!-- App -->
  <script src="<?= BASE_URL ?>/assets/js/app.js">
  </script>

  <script defer src="https://unpkg.com/alpinejs"></script>




</head>

<body x-data>

  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <main>
    <?= $content ?>
  </main>

  <?php include __DIR__ . '/../partials/toast.php'; ?>

</body>

</html>