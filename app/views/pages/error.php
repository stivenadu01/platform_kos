<div class="section-center px-4">
  <div class="text-center">

    <!-- CODE -->
    <div class="text-8xl font-bold text-primary mb-4">
      <?php echo $status; ?>
    </div>

    <!-- TITLE -->
    <h1 class="text-4xl font-bold text-gray-800 mb-2">
      <?php echo $title; ?>
    </h1>

    <!-- MESSAGE -->
    <p class="text-lg text-gray-600 mb-8">
      <?php echo $message; ?>
    </p>

    <!-- ACTION -->
    <div class="flex-center gap-4">
      <a :href="BASE_URL" class="btn-primary whitespace-nowrap">
        Kembali ke Beranda
      </a>

      <button onclick="window.history.back()" class="btn-secondary">
        Kembali
      </button>
    </div>

  </div>
</div>