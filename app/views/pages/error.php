<div class="section-center px-4">
  <div class="text-center">

    <!-- CODE -->
    <div class="text-8xl font-bold text-primary mb-4">
      <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <!-- TITLE -->
    <h1 class="text-4xl font-bold text-gray-800 mb-2">
      <?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?>
    </h1>

    <!-- MESSAGE -->
    <p class="text-lg text-gray-600 mb-8">
      <?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <!-- ACTION -->
    <div class="flex-center gap-4">
      <a href="<?= htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') ?>" class="btn-primary whitespace-nowrap">
        Kembali ke Beranda
      </a>

      <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '<?= htmlspecialchars(BASE_URL . '/', ENT_QUOTES, 'UTF-8') ?>'; }" class="btn-secondary">
        Kembali
      </button>
    </div>

  </div>
</div>
