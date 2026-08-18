<div class="section-center bg-slate-50 px-4">

  <div class="card max-w-md w-full text-center p-8">

    <img
      :src="BASE_URL + '/assets/icon/logo.png'"
      alt="BetaKos"
      class="w-16 h-16 object-contain mx-auto mb-5">

    <?php if ($status === 'success'): ?>

      <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-success-soft text-success flex-center text-xl">
        ✓
      </div>

      <h1 class="text-2xl font-bold text-heading mb-2">
        Verifikasi Berhasil
      </h1>

      <p class="text-sm text-muted mb-6">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </p>

      <a
        :href="BASE_URL + '/login'"
        class="btn-primary w-full">
        Masuk Sekarang
      </a>

    <?php else: ?>

      <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-danger-soft text-danger flex-center text-xl">
        !
      </div>

      <h1 class="text-2xl font-bold text-heading mb-2">
        Verifikasi Gagal
      </h1>

      <p class="text-sm text-muted mb-6">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </p>

      <div class="space-y-2">
        <a
          :href="BASE_URL + '/login'"
          class="btn-secondary w-full">
          Kembali ke Login
        </a>

        <a
          :href="BASE_URL + '/register'"
          class="btn-link-primary text-sm">
          Buat akun baru
        </a>
      </div>

    <?php endif; ?>

  </div>

</div>