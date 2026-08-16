<div class="space-y-6">

  <!-- HEADER -->
  <div>

    <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
      Selamat datang,
      <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pemilik') ?> 👋
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Kelola kos dan pantau aktivitas usaha Anda dari sini.
    </p>

  </div>


  <!-- SUMMARY -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- TOTAL KOS -->
    <div class="card border border-slate-200 shadow-sm">

      <p class="text-sm text-slate-500">
        Total Kos
      </p>

      <div class="mt-2 flex items-end justify-between">

        <p class="text-2xl font-bold text-slate-900">
          0
        </p>

        <span class="text-2xl">
          🏠
        </span>

      </div>

    </div>


    <!-- KAMAR -->
    <div class="card border border-slate-200 shadow-sm">

      <p class="text-sm text-slate-500">
        Total Kamar
      </p>

      <div class="mt-2 flex items-end justify-between">

        <p class="text-2xl font-bold text-slate-900">
          0
        </p>

        <span class="text-2xl">
          🚪
        </span>

      </div>

    </div>


    <!-- TERISI -->
    <div class="card border border-slate-200 shadow-sm">

      <p class="text-sm text-slate-500">
        Kamar Terisi
      </p>

      <div class="mt-2 flex items-end justify-between">

        <p class="text-2xl font-bold text-slate-900">
          0
        </p>

        <span class="text-2xl">
          👤
        </span>

      </div>

    </div>


    <!-- TERSEDIA -->
    <div class="card border border-slate-200 shadow-sm">

      <p class="text-sm text-slate-500">
        Kamar Tersedia
      </p>

      <div class="mt-2 flex items-end justify-between">

        <p class="text-2xl font-bold text-slate-900">
          0
        </p>

        <span class="text-2xl">
          ✓
        </span>

      </div>

    </div>

  </div>


  <!-- QUICK ACTION -->
  <div class="card border border-slate-200 shadow-sm">

    <div class="flex-between">

      <div>

        <h3 class="font-semibold text-slate-900">
          Mulai Kelola Kos
        </h3>

        <p class="mt-1 text-sm text-slate-500">
          Tambahkan kos pertama Anda untuk mulai mengelola kamar.
        </p>

      </div>

    </div>

    <div class="mt-5">

      <a
        href="<?= BASE_URL ?>/pemilik/kos"
        class="btn-primary inline-flex w-auto">

        + Tambah / Kelola Kos

      </a>

    </div>

  </div>


  <!-- EMPTY STATE -->
  <div class="card border border-slate-200 shadow-sm text-center py-12">

    <div class="text-4xl mb-4">
      🏠
    </div>

    <h3 class="font-semibold text-slate-900">
      Belum ada data kos
    </h3>

    <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
      Tambahkan data kos Anda terlebih dahulu. Setelah itu Anda dapat
      mengatur kamar, harga, fasilitas, dan informasi kos.
    </p>

    <div class="mt-5">

      <a
        href="<?= BASE_URL ?>/pemilik/kos"
        class="btn-primary inline-flex w-auto">

        Tambah Kos

      </a>

    </div>

  </div>

</div>