<footer class="mt-16 border-t border-slate-200 bg-white">
  <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
      <div class="max-w-md">
        <div class="flex items-center gap-3">
          <img src="<?= BASE_URL ?>/assets/icon/logo.png" alt="BetaKos" class="h-9 w-9 object-contain">
          <div>
            <div class="font-bold text-slate-900">BetaKos</div>
            <div class="text-xs text-slate-500">Temukan kos yang sesuai kebutuhanmu.</div>
          </div>
        </div>
        <p class="mt-4 text-sm leading-6 text-slate-500">
          Cari kos berdasarkan kampus, budget, jarak, fasilitas, dan ketersediaan.
        </p>
      </div>

      <div class="grid grid-cols-2 gap-x-10 gap-y-4 text-sm">
        <a href="<?= BASE_URL ?>/" class="text-slate-600 hover:text-primary">Beranda</a>
        <a href="<?= BASE_URL ?>/cari-kos" class="text-slate-600 hover:text-primary">Cari Kos</a>
        <a href="<?= BASE_URL ?>/login" class="text-slate-600 hover:text-primary">Masuk</a>
        <a href="<?= BASE_URL ?>/register" class="text-slate-600 hover:text-primary">Daftar</a>
      </div>
    </div>

    <div class="mt-8 border-t border-slate-100 pt-5 text-xs text-slate-400">
      © <?= date('Y') ?> BetaKos. Semua hak dilindungi.
    </div>
  </div>
</footer>
