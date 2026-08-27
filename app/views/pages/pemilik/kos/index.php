<div
  x-data="kosPage()"
  class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
        Kos Saya
      </h2>

      <p class="mt-1 text-sm text-slate-500">
        Kelola informasi dan data kos yang Anda miliki.
      </p>
    </div>

    <a
      href="<?= BASE_URL ?>/pemilik/kos/tambah"
      class="btn-primary sm:w-auto">
      + Tambah Kos
    </a>

  </div>


  <?php if (empty($kos)): ?>

    <div class="card border border-slate-200 shadow-sm">
      <div class="py-14 text-center">

        <div class="text-4xl mb-4">
          🏠
        </div>

        <h3 class="font-semibold text-slate-900">
          Belum ada kos
        </h3>

        <p class="mt-1 text-sm text-slate-500">
          Tambahkan kos pertama Anda untuk mulai mengelolanya.
        </p>

        <a
          href="<?= BASE_URL ?>/pemilik/kos/tambah"
          class="btn-primary inline-flex mt-5">
          + Tambah Kos
        </a>

      </div>
    </div>

  <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

      <?php foreach ($kos as $item): ?>

        <div class="card border border-slate-200 shadow-sm">

          <div class="p-5">

            <div class="flex items-start justify-between gap-3">

              <div>
                <h3 class="font-semibold text-slate-900">
                  <?= htmlspecialchars($item['nama_kos']) ?>
                </h3>

                <p class="mt-1 text-sm text-slate-500 line-clamp-2">
                  <?= htmlspecialchars($item['alamat']) ?>
                </p>
              </div>

              <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                <?= htmlspecialchars($item['status']) ?>
              </span>

            </div>


            <div class="grid grid-cols-3 gap-2 mt-5">

              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="font-semibold">
                  <?= $item['jumlah_kamar'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Kamar
                </div>
              </div>

              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="font-semibold">
                  <?= $item['kamar_tersedia'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Tersedia
                </div>
              </div>

              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="font-semibold">
                  <?= $item['kamar_terisi'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Terisi
                </div>
              </div>

            </div>


            <?php if ($item['status'] === 'ditolak' && !empty($item['catatan_verifikasi'])): ?>
              <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3.5">
                <div class="flex items-center gap-2 text-sm font-semibold text-red-700">
                  <span>⚠</span> Alasan penolakan Admin
                </div>
                <p class="mt-1.5 text-sm leading-6 text-red-700 whitespace-pre-line"><?= htmlspecialchars($item['catatan_verifikasi']) ?></p>
                <p class="mt-2 text-xs text-red-500">Silakan perbaiki data kos kemudian ajukan kembali untuk verifikasi.</p>
              </div>
            <?php endif; ?>

            <div class="mt-4">
              <?php if ($item['status'] === 'draft' || $item['status'] === 'ditolak'): ?>
                <button type="button" @click="ajukan(<?= $item['id_kos'] ?>)" class="w-full rounded-xl bg-primary-soft text-primary px-4 py-2.5 text-sm font-semibold hover:bg-blue-100">
                  Ajukan Verifikasi Admin
                </button>
              <?php elseif ($item['status'] === 'menunggu_verifikasi'): ?>
                <div class="w-full rounded-xl bg-amber-50 text-amber-700 px-4 py-2.5 text-sm font-semibold text-center">Menunggu Verifikasi Admin</div>
              <?php elseif ($item['status'] === 'aktif'): ?>
                <div class="w-full rounded-xl bg-emerald-50 text-emerald-700 px-4 py-2.5 text-sm font-semibold text-center">Kos Terverifikasi & Aktif</div>
              <?php endif; ?>
            </div>

            <div class="flex gap-2 mt-5">

              <a
                href="<?= BASE_URL ?>/pemilik/kos/foto?id=<?= $item['id_kos'] ?>"
                class="btn-secondary flex-1 text-center">
                Foto
              </a>

              <a
                href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= $item['id_kos'] ?>"
                class="btn-secondary flex-1 text-center">
                Edit
              </a>

              <button
                type="button"
                @click="hapus(<?= $item['id_kos'] ?>)"
                class="btn-secondary flex-1 text-red-600">
                Hapus
              </button>

            </div>

          </div>

        </div>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

</div>


<script>
  function kosPage() {
    return {
      async ajukan(id) {
        const ok = await Alpine.store('ui').confirm('Ajukan kos ini untuk diperiksa Admin?');
        if (!ok) return;
        try {
          await API.post('/pemilik/kos/ajukan-verifikasi', { id_kos: id });
          window.location.reload();
        } catch (error) { console.error(error); }
      },

      async hapus(id) {

        const ok = await Alpine.store('ui').confirm(
          'Yakin ingin menghapus kos ini?'
        );

        if (!ok) return;

        try {

          await API.delete(
            '/pemilik/kos/' + id,
            null
          );

          window.location.reload();

        } catch (error) {
          console.error(error);
        }
      }
    }
  }
</script>