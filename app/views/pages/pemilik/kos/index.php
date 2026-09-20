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
      data-onboarding="fast-tambah-kos"
      data-help="help-kos-add" class="btn-primary sm:w-auto">
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
          data-onboarding="fast-tambah-kos"
          class="btn-primary inline-flex mt-5">
          + Tambah Kos
        </a>

      </div>
    </div>

  <?php else: ?>

    <div data-help="help-kos-list" class="owner-card-grid">

      <?php foreach ($kos as $item): ?>

        <article class="owner-panel owner-panel-hover !p-0">
          <div class="owner-card-header">
            <div class="absolute -right-8 -top-10 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="relative flex items-start gap-3.5">
              <div class="owner-card-ring flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path><path d="M9 15h1"></path><path d="M14 15h1"></path></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="owner-card-muted text-xs font-medium uppercase tracking-wide">Properti Kos</p>
                <h3 class="mt-1 truncate text-lg font-bold text-white"><?= htmlspecialchars($item['nama_kos']) ?></h3>
                <p class="owner-card-muted mt-1 line-clamp-1 text-xs"><?= htmlspecialchars($item['alamat']) ?></p>
              </div>
              <span class="owner-card-ring shrink-0 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span>
            </div>
          </div>

          <div class="p-4 sm:p-5">

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">

              <div class="rounded-xl bg-slate-50 p-3 text-center">
                <div class="text-lg font-bold leading-none text-slate-900">
                  <?= $item['jumlah_kamar'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Kamar
                </div>
              </div>

              <div class="rounded-xl bg-emerald-50 p-3 text-center">
                <div class="text-lg font-bold leading-none text-emerald-600">
                  <?= $item['kamar_tersedia'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Tersedia
                </div>
              </div>

              <div class="rounded-xl bg-red-50 p-3 text-center">
                <div class="text-lg font-bold leading-none text-red-600">
                  <?= $item['kamar_terisi'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Terisi
                </div>
              </div>

              <div class="rounded-xl bg-amber-50 p-3 text-center">
                <div class="text-lg font-bold leading-none text-amber-600">
                  <?= $item['kamar_tidak_tersedia'] ?>
                </div>
                <div class="text-xs text-slate-500">
                  Tidak tersedia
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
                <button type="button" data-onboarding="fast-ajukan-verifikasi" @click="ajukan(<?= $item['id_kos'] ?>)" class="w-full rounded-xl bg-primary-soft text-primary px-4 py-2.5 text-sm font-semibold hover:bg-blue-100">
                  Ajukan Verifikasi Admin
                </button>
              <?php elseif ($item['status'] === 'menunggu_verifikasi'): ?>
                <div class="w-full rounded-xl bg-amber-50 text-amber-700 px-4 py-2.5 text-sm font-semibold text-center">Menunggu Verifikasi Admin</div>
              <?php elseif ($item['status'] === 'aktif'): ?>
                <div class="w-full rounded-xl bg-emerald-50 text-emerald-700 px-4 py-2.5 text-sm font-semibold text-center">Kos Terverifikasi & Aktif</div>
              <?php endif; ?>
            </div>

            <div data-help="help-kos-action" class="mt-4 grid grid-cols-3 gap-2">

              <a
                data-onboarding="kos-photo"
                href="<?= BASE_URL ?>/pemilik/kos/foto?id=<?= $item['id_kos'] ?>"
                class="owner-action-secondary">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path></svg>
                Foto
              </a>

              <?php if ($item['status'] === 'menunggu_verifikasi'): ?>
                <span
                  class="owner-action-secondary cursor-not-allowed opacity-50"
                  title="Kos tidak dapat diedit selama menunggu verifikasi admin">
                  Edit
                </span>
              <?php else: ?>
                <a
                  href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= $item['id_kos'] ?>"
                  class="owner-action-secondary">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                  Edit
                </a>
              <?php endif; ?>

              <button
                type="button"
                @click="hapus(<?= $item['id_kos'] ?>)"
                class="owner-action-danger">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                Hapus
              </button>

            </div>

          </div>

        </article>

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
          if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
            localStorage.setItem('betakos_owner_onboarding_complete_v3', '1');
            localStorage.removeItem('betakos_owner_onboarding_active_v3');
            localStorage.removeItem('betakos_owner_onboarding_skipped_v3');
            localStorage.setItem('betakos_owner_onboarding_welcome_v3', '1');
            window.dispatchEvent(new CustomEvent('betakos:onboarding-completed'));
          }
          window.dispatchEvent(new CustomEvent('betakos:onboarding-refresh'));
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
