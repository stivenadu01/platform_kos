<div
  x-data="kosPage()"
  class="owner-page">

  <div class="owner-page-header">

    <div>
      <a href="<?= BASE_URL ?>/pemilik" @click.prevent="utils.goBack($el.href)" class="owner-back-link mb-3"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow">Properti</p>
      <h2 class="owner-title">
        Properti
      </h2>

      <p class="owner-subtitle">
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

        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-soft text-primary"><?= masterIconSvg('building-2', 'h-7 w-7') ?></div>

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

        <?php
          $totalUnit = max(0, (int)($item['jumlah_kamar'] ?? 0));
          $occupiedUnit = max(0, (int)($item['kamar_terisi'] ?? 0));
          $occupancy = $totalUnit > 0 ? min(100, (int)round(($occupiedUnit / $totalUnit) * 100)) : 0;
        ?>

        <article class="owner-panel owner-panel-hover !p-0">
          <div class="relative overflow-hidden bg-slate-900 p-4 sm:p-5">
            <?php if (!empty($item['foto'])): ?>
              <img src="<?= BASE_URL . '/uploads' . htmlspecialchars($item['foto'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto <?= htmlspecialchars($item['nama_kos'], ENT_QUOTES, 'UTF-8') ?>" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="owner-photo-overlay absolute inset-0"></div>
            <div class="relative flex items-start gap-3.5">
              <div class="owner-card-ring flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path><path d="M9 15h1"></path><path d="M14 15h1"></path></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="owner-card-muted text-xs font-medium uppercase tracking-wide">Properti Kos</p>
                <h3 class="owner-copy-full mt-1 text-lg font-bold text-white"><?= htmlspecialchars($item['nama_kos']) ?></h3>
                <p class="owner-card-muted owner-copy-full mt-1 text-xs"><?= htmlspecialchars($item['alamat']) ?></p>
              </div>
              <span class="owner-card-ring shrink-0 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span>
            </div>

            <div class="owner-photo-stat relative mt-4 rounded-xl p-3">
              <div class="flex items-center justify-between gap-3 text-xs">
                <span class="owner-photo-stat-label">Keterisian kamar</span>
                <strong class="text-white"><?= $occupiedUnit ?> dari <?= $totalUnit ?> terisi</strong>
              </div>
              <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-emerald-400" style="width:<?= $occupancy ?>%"></div></div>
              <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px]">
                <span class="owner-stat-available font-semibold"><?= (int)$item['kamar_tersedia'] ?> tersedia</span>
                <span class="owner-photo-stat-label"><?= (int)$item['kamar_tidak_tersedia'] ?> perlu diperiksa</span>
                <span class="ml-auto font-semibold text-white"><?= $occupancy ?>%</span>
              </div>
            </div>

            <div class="owner-photo-actions space-y-2">
              <?php if ($item['status'] === 'draft' || $item['status'] === 'ditolak'): ?>
                <button type="button" data-onboarding="fast-ajukan-verifikasi" @click="ajukan(<?= $item['id_kos'] ?>)" class="owner-photo-action owner-photo-action-primary">
                  <?= masterIconSvg('check-circle-2', 'h-4 w-4') ?>
                  <span>Ajukan Verifikasi Admin</span>
                  <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
                </button>
              <?php elseif ($item['status'] === 'menunggu_verifikasi'): ?>
                <div class="owner-photo-waiting rounded-xl px-3 py-2.5 text-center text-xs font-semibold">Menunggu Verifikasi Admin</div>
              <?php endif; ?>

              <?php if ($item['status'] === 'aktif'): ?>
                <a href="<?= BASE_URL ?>/pemilik/kamar?id_kos=<?= (int)$item['id_kos'] ?>&context=kos" class="owner-photo-action owner-photo-action-primary">
                  <?= masterIconSvg('bed', 'h-4 w-4') ?>
                  <span>Kelola Kamar</span>
                  <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
                </a>
              <?php endif; ?>

              <div data-help="help-kos-action" class="grid grid-cols-3 gap-2 pt-0.5">
                <a data-onboarding="kos-photo" href="<?= BASE_URL ?>/pemilik/kos/foto?id=<?= $item['id_kos'] ?>" class="owner-photo-action owner-photo-action-neutral">
                  <?= masterIconSvg('image', 'h-3.5 w-3.5') ?> Foto
                </a>
                <?php if ($item['status'] === 'menunggu_verifikasi'): ?>
                  <span class="owner-photo-action owner-photo-action-neutral cursor-not-allowed opacity-50" title="Kos tidak dapat diedit selama menunggu verifikasi admin"><?= masterIconSvg('pencil', 'h-3.5 w-3.5') ?> Edit</span>
                <?php else: ?>
                  <a href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= $item['id_kos'] ?>" class="owner-photo-action owner-photo-action-neutral"><?= masterIconSvg('pencil', 'h-3.5 w-3.5') ?> Edit</a>
                <?php endif; ?>
                <button type="button" @click="hapus(<?= $item['id_kos'] ?>)" class="owner-photo-action owner-photo-action-danger"><?= masterIconSvg('trash-2', 'h-3.5 w-3.5') ?> Hapus</button>
              </div>
            </div>
          </div>

          <?php if ($item['status'] === 'ditolak' && !empty($item['catatan_verifikasi'])): ?>
            <div class="p-3">
              <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3.5">
                <div class="flex items-center gap-2 text-sm font-semibold text-red-700">
                  <?= masterIconSvg('alert-circle', 'h-4 w-4') ?> Alasan penolakan Admin
                </div>
                <p class="mt-1.5 text-sm leading-6 text-red-700 whitespace-pre-line"><?= htmlspecialchars($item['catatan_verifikasi']) ?></p>
                <p class="mt-2 text-xs text-red-500">Silakan perbaiki data kos kemudian ajukan kembali untuk verifikasi.</p>
              </div>
            </div>
          <?php endif; ?>

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
