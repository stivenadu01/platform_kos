<section class="relative overflow-hidden bg-white">
  <div class="mx-auto max-w-7xl px-4 pb-14 pt-10 sm:px-6 sm:pt-16 lg:px-8 lg:pt-20">
    <div class="grid items-center gap-10 lg:grid-cols-[1.05fr_.95fr]">
      <div>
        <span class="inline-flex rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary">
          BetaKos Kupang
        </span>

        <h1 class="mt-5 max-w-3xl font-[Poppins] text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
          Cari kos sesuai lokasi, kebutuhan, dan budgetmu.
        </h1>

        <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
          Masukkan nama tempat, jalan, kawasan, atau lokasi tujuan untuk menemukan kos di area yang kamu inginkan.
        </p>

        <form action="<?= BASE_URL ?>/cari-kos" method="GET" class="mt-8 rounded-2xl border border-slate-200 bg-white p-2 shadow-lg shadow-slate-200/60 sm:flex sm:items-center">
          <div class="flex min-w-0 flex-1 items-center gap-3 px-3 py-2">
            <span class="text-lg text-slate-400">⌕</span>
            <input
              type="text"
              name="q"
              placeholder="Contoh: Oesapa, Kelapa Lima, atau Politeknik Negeri Kupang..."
              class="w-full bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-400"
            >
          </div>
          <button type="submit" class="mt-2 w-full rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark sm:mt-0 sm:w-auto">
            Cari Kos
          </button>
        </form>

        <div class="mt-6">
          <div class="flex items-center gap-2">
            <span class="h-px flex-1 bg-slate-200"></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cari berdasarkan lokasi</span>
            <span class="h-px flex-1 bg-slate-200"></span>
          </div>

          <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
            <?php
            $kampusCari = [
              [
                'nama' => 'Politeknik Negeri Kupang',
                'label' => 'Politeknik Negeri Kupang',
                'lat' => -10.1513944,
                'lng' => 123.6676240,
              ],
              [
                'nama' => 'Universitas Nusa Cendana',
                'label' => 'Undana',
                'lat' => -10.1544480,
                'lng' => 123.6588310,
              ],
              [
                'nama' => 'Universitas Muhammadiyah Kupang',
                'label' => 'Muhammadiyah',
                'lat' => -10.1585622,
                'lng' => 123.6197763,
              ],
              [
                'nama' => 'Sekolah Tinggi Manajemen Informatika Komputer Uyelindo Kupang',
                'label' => 'STIKOM',
                'lat' => -10.1629100,
                'lng' => 123.6238010,
              ],
              [
                'nama' => 'Universitas Citra Bangsa',
                'label' => 'UCB',
                'lat' => -10.1626807,
                'lng' => 123.6236238,
              ],
              [
                'nama' => 'Poltekkes Kemenkes Kupang',
                'label' => 'Poltekkes',
                'lat' => -10.1581806,
                'lng' => 123.6397611,
              ],
            ];
            ?>
            <?php foreach ($kampusCari as $kampus): ?>
              <a
                href="<?= BASE_URL ?>/cari-kos?lokasi=<?= urlencode($kampus['nama']) ?>&lat=<?= urlencode((string) $kampus['lat']) ?>&lng=<?= urlencode((string) $kampus['lng']) ?>"
                class="group rounded-xl border border-slate-200 bg-white px-3 py-3 text-left transition hover:-translate-y-0.5 hover:border-primary hover:bg-primary-soft hover:shadow-sm"
              >
                <span class="flex items-start gap-2">
                  <span class="mt-0.5 text-primary">⌖</span>
                  <span class="min-w-0">
                    <span class="block truncate text-xs font-semibold text-slate-800 group-hover:text-primary"><?= htmlspecialchars($kampus['label']) ?></span>
                    <span class="mt-0.5 block text-[10px] text-slate-400">Lihat kos di sekitarnya →</span>
                  </span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-slate-500">
          <span class="flex items-center gap-2"><span class="text-success">✓</span> Cari berdasarkan lokasi</span>
          <span class="flex items-center gap-2"><span class="text-success">✓</span> Sesuaikan budget</span>
          <span class="flex items-center gap-2"><span class="text-success">✓</span> Lihat kamar tersedia</span>
        </div>
      </div>

      <div class="hidden lg:block">
        <div class="relative mx-auto max-w-md rounded-[2rem] bg-primary-soft p-5">
          <div class="rounded-[1.5rem] bg-white p-6 shadow-xl shadow-blue-100">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-soft text-2xl">⌖</div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Contoh pencarian lokasi</p>
                <p class="mt-1 font-[Poppins] text-xl font-bold text-slate-900">Kos di sekitar lokasi pilihan</p>
              </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
              <p class="text-xs text-slate-400">Contoh lokasi</p>
              <p class="mt-1 text-sm font-semibold text-slate-800">Politeknik Negeri Kupang</p>
              <p class="mt-1 text-xs text-slate-500">Cari kos berdasarkan jarak dari lokasi pilihanmu.</p>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3">
              <div class="rounded-xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">Budget</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">Sesuai kantong</p>
              </div>
              <div class="rounded-xl border border-slate-100 p-4">
                <p class="text-xs text-slate-400">Kebutuhan</p>
                <p class="mt-1 text-sm font-semibold text-slate-800">Putra / Putri</p>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between rounded-xl bg-primary-soft px-4 py-3">
              <span class="text-xs font-medium text-slate-600">Pilihan kos di sekitar</span>
              <span class="text-xs font-bold text-primary">Lihat hasil →</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="border-y border-slate-100 bg-slate-50/70">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="text-center">
      <p class="text-sm font-semibold text-primary">Mulai dari sini</p>
      <h2 class="mt-1 font-[Poppins] text-2xl font-bold text-slate-900 sm:text-3xl">Temukan kos yang sesuai tanpa ribet</h2>
      <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-500">
        Tentukan lokasi, kebutuhan, dan budget. BetaKos membantu kamu menemukan pilihan yang paling sesuai.
      </p>
    </div>

    <div class="mt-8 grid gap-5 md:grid-cols-3">
      <a href="<?= BASE_URL ?>/cari-kos" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-soft text-primary">⌕</div>
        <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-primary">Langkah 1</p>
        <h3 class="mt-1 font-[Poppins] text-lg font-bold text-slate-900">Cari lokasi tujuan</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">Ketik nama tempat, jalan, kawasan, atau lokasi yang ingin kamu jadikan patokan.</p>
      </a>

      <a href="<?= BASE_URL ?>/cari-kos" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-soft text-primary">⚙</div>
        <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-primary">Langkah 2</p>
        <h3 class="mt-1 font-[Poppins] text-lg font-bold text-slate-900">Pilih yang kamu butuhkan</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">Atur jenis kos, kapasitas, fasilitas, jarak, dan kisaran harga.</p>
      </a>

      <a href="<?= BASE_URL ?>/cari-kos" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-soft text-primary">✓</div>
        <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-primary">Langkah 3</p>
        <h3 class="mt-1 font-[Poppins] text-lg font-bold text-slate-900">Bandingkan pilihan</h3>
        <p class="mt-2 text-sm leading-6 text-slate-500">Lihat foto, harga, lokasi, fasilitas, dan ketersediaan sebelum memilih.</p>
      </a>
    </div>
  </div>
</section>

<section class="bg-white">
  <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-primary">Kos pilihan</p>
        <h2 class="mt-1 font-[Poppins] text-2xl font-bold text-slate-900 sm:text-3xl">Kos tersedia di Kupang</h2>
        <p class="mt-2 text-sm text-slate-500">Lihat kos yang sedang tersedia atau mulai pencarian untuk menemukan lebih banyak pilihan.</p>
      </div>
      <a href="<?= BASE_URL ?>/cari-kos" class="hidden text-sm font-semibold text-primary hover:text-primary-dark sm:block">Lihat semua →</a>
    </div>

    <?php if (!empty($kosUnggulan)): ?>
      <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($kosUnggulan as $kos): ?>
          <?php $foto = !empty($kos['foto']) ? BASE_URL . '/uploads' . $kos['foto'] : ''; ?>
          <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            <a href="<?= BASE_URL ?>/kos/<?= (int) $kos['id_kos'] ?>" class="block">
              <div class="aspect-[16/10] bg-slate-100">
                <?php if ($foto): ?>
                  <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($kos['nama_kos']) ?>" class="h-full w-full object-cover" loading="lazy">
                <?php else: ?>
                  <div class="flex h-full items-center justify-center text-sm text-slate-400">Foto belum tersedia</div>
                <?php endif; ?>
              </div>
              <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h3 class="truncate font-[Poppins] text-lg font-bold text-slate-900"><?= htmlspecialchars($kos['nama_kos']) ?></h3>
                    <p class="mt-1 truncate text-xs text-slate-500"><?= htmlspecialchars($kos['alamat']) ?></p>
                  </div>
                  <span class="shrink-0 rounded-full bg-primary-soft px-2.5 py-1 text-[11px] font-semibold capitalize text-primary"><?= htmlspecialchars($kos['jenis']) ?></span>
                </div>
                <div class="mt-5 flex items-end justify-between gap-4">
                  <div>
                    <p class="text-[11px] text-slate-400">Mulai dari</p>
                    <p class="mt-0.5 text-base font-bold text-slate-900">
                      <?= $kos['harga_mulai'] !== null ? 'Rp ' . number_format((float) $kos['harga_mulai'], 0, ',', '.') : 'Hubungi pemilik' ?>
                    </p>
                  </div>
                  <p class="text-xs font-medium text-success"><?= (int) $kos['kamar_tersedia'] ?> kamar tersedia</p>
                </div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl shadow-sm">⌂</div>
        <h3 class="mt-4 font-semibold text-slate-800">Belum ada kos yang tersedia</h3>
        <p class="mt-1 text-sm text-slate-500">Kos yang sudah aktif dan memiliki kamar tersedia akan muncul di sini.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="bg-slate-50">
  <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-primary p-7 sm:p-10">
      <div class="grid items-center gap-8 lg:grid-cols-[1fr_auto]">
        <div>
          <p class="text-sm font-semibold text-blue-100">Belum tahu harus pilih yang mana?</p>
          <h2 class="mt-2 font-[Poppins] text-2xl font-bold text-white sm:text-3xl">Cari kos dari lokasi yang kamu inginkan.</h2>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100">
            Cari kos di sekitar lokasi yang kamu pilih, lalu sesuaikan dengan kebutuhan dan budgetmu.
          </p>
        </div>
        <a href="<?= BASE_URL ?>/cari-kos" class="rounded-xl bg-white px-6 py-3 text-center text-sm font-bold text-primary hover:bg-blue-50">
          Mulai Cari Kos
        </a>
      </div>
    </div>
  </div>
</section>
