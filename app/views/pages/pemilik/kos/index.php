<div
  x-data="kosPage()"
  class="owner-page">

  <div class="owner-page-header">

    <div>
      <a href="<?= BASE_URL ?>/pemilik" @click.prevent="utils.goBack($el.href)" class="owner-back-link mb-3"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow">Properti</p>
      <h2 class="owner-title">
        Kos saya
      </h2>

      <p class="owner-subtitle">
        Kelola informasi dan data kos yang Anda miliki.
      </p>
    </div>

    <a
      href="<?= BASE_URL ?>/pemilik/kos/tambah"

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

          class="btn-primary inline-flex mt-5">
          + Tambah Kos
        </a>

      </div>
    </div>

  <?php else: ?>
    <section class="owner-panel !p-3"><label for="my-kos-search" class="sr-only">Cari kos Anda</label><input id="my-kos-search" class="input" type="search" x-model="search" placeholder="Cari nama atau alamat kos…"></section>
    <div class="owner-segmented" aria-label="Status kos">
      <?php foreach([''=>'Semua','aktif'=>'Aktif','menunggu_verifikasi'=>'Menunggu verifikasi','draft'=>'Draf','ditolak'=>'Perlu diperbaiki'] as $statusKey=>$statusLabel): $statusCount=$statusKey===''?count($kos):count(array_filter($kos,fn($item)=>$item['status']===$statusKey)); ?>
      <button type="button" @click="status='<?= $statusKey ?>'" :class="status==='<?= $statusKey ?>'?'is-active':''" :aria-pressed="status==='<?= $statusKey ?>'"><span><?= $statusLabel ?></span><span class="bk-tab-count"><?= $statusCount ?></span></button>
      <?php endforeach; ?>
    </div>
    <div x-show="!visibleCount" x-cloak class="owner-empty"><h3 class="font-bold">Tidak ada kos yang sesuai</h3><p class="mt-2 text-sm">Coba nama lain atau lihat semua status.</p><button type="button" class="btn-secondary mt-3" @click="search='';status=''">Reset pencarian</button></div>

    <div data-help="help-kos-list" class="owner-card-grid">

      <?php foreach ($kos as $item): ?>

        <?php
          $totalUnit = max(0, (int)($item['jumlah_kamar'] ?? 0));
          $occupiedUnit = max(0, (int)($item['kamar_terisi'] ?? 0));
          $occupancy = $totalUnit > 0 ? min(100, (int)round(($occupiedUnit / $totalUnit) * 100)) : 0;
        ?>

        <article x-show="matches(<?= htmlspecialchars(json_encode_safe(['name'=>$item['nama_kos'].' '.$item['alamat'],'status'=>$item['status']]),ENT_QUOTES,'UTF-8') ?>)" class="owner-panel owner-panel-hover !p-0">
          <div class="property-cover">
            <?= masterIconSvg('building-2', 'h-12 w-12') ?>
            <?php if (!empty($item['foto'])): ?>
              <img src="<?= BASE_URL . '/uploads' . htmlspecialchars($item['foto'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto <?= htmlspecialchars($item['nama_kos'], ENT_QUOTES, 'UTF-8') ?>" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
            <?php endif; ?>
          </div>
          <div class="property-content">
            <div class="relative flex items-start gap-3.5">
              <div class="owner-card-ring flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path><path d="M9 15h1"></path><path d="M14 15h1"></path></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="owner-card-muted text-xs font-medium uppercase tracking-wide">Properti Kos</p>
                <h3 class="owner-copy-full mt-1 text-lg font-bold text-white"><?= htmlspecialchars($item['nama_kos']) ?></h3>
                <p class="owner-card-muted owner-copy-full mt-1 text-xs"><?= htmlspecialchars($item['alamat']) ?></p>
              </div>
              <div class="ui-actions" x-data="uiActionMenu()" @betakos:close-menus.window="closeMenu(false)" @resize.window="closeMenu()"><button type="button" x-ref="menuTrigger" class="ui-more-trigger !text-slate-600" aria-label="Tindakan lainnya" aria-haspopup="dialog" :aria-expanded="menuOpen" @click="menuOpen ? closeMenu() : showMenu()"><?= masterIconSvg('ellipsis','h-5 w-5') ?></button><template x-teleport="body"><div x-show="menuOpen" x-cloak class="ui-menu-layer" @keydown.escape.stop.prevent="closeMenu()"><div class="ui-menu-backdrop" @click="closeMenu()"></div><div x-ref="menuPanel" role="dialog" aria-modal="true" aria-label="Tindakan properti" class="ui-action-menu" :style="menuPosition" @click="if ($event.target.closest('a,button')) closeMenu(false)"><a href="<?= BASE_URL ?>/pemilik/kos/foto?id=<?= $item['id_kos'] ?>"><?= masterIconSvg('image','h-4 w-4') ?> Kelola foto</a><?php if ($item['status'] === 'menunggu_verifikasi'): ?><span class="cursor-not-allowed opacity-50"><?= masterIconSvg('pencil','h-4 w-4') ?> Edit terkunci</span><?php else: ?><a href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= $item['id_kos'] ?>"><?= masterIconSvg('pencil','h-4 w-4') ?> Edit informasi</a><?php endif; ?><button type="button" @click="hapus(<?= $item['id_kos'] ?>)" class="owner-photo-action-danger"><?= masterIconSvg('trash-2','h-4 w-4') ?> Hapus properti</button></div></div></template></div>
            </div>

            <div class="owner-photo-stat relative mt-4 rounded-xl p-3">
              <span class="mb-2 inline-flex rounded-full bg-primary-soft px-2.5 py-1 text-[11px] font-semibold text-primary"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $item['status']))) ?></span>
              <div class="flex items-center justify-between gap-3 text-xs">
                <span class="owner-photo-stat-label">Keterisian kamar</span>
                <strong class="text-white"><?= $occupiedUnit ?> dari <?= $totalUnit ?> terisi</strong>
              </div>
              <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-emerald-400" style="width:<?= $occupancy ?>%"></div></div>
              <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                <span class="owner-stat-available font-semibold"><?= (int)$item['kamar_tersedia'] ?> tersedia</span>
                <span class="owner-photo-stat-label"><?= (int)$item['kamar_tidak_tersedia'] ?> perlu diperiksa</span>
                <span class="ml-auto font-semibold text-white"><?= $occupancy ?>%</span>
              </div>
            </div>

            <div class="owner-photo-actions space-y-2">
              <?php if ($item['status'] === 'draft' || $item['status'] === 'ditolak'): ?>
                <button type="button" @click="ajukan(<?= $item['id_kos'] ?>)" class="owner-photo-action owner-photo-action-primary">
                  <?= masterIconSvg('check-circle-2', 'h-4 w-4') ?>
                  <span>Ajukan Verifikasi Admin</span>
                  <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
                </button>
              <?php elseif ($item['status'] === 'menunggu_verifikasi'): ?>
                <div class="owner-photo-waiting rounded-xl px-3 py-2.5 text-center text-xs font-semibold">Menunggu Verifikasi Admin</div>
              <?php endif; ?>

              <?php if (!empty($item['id_kos'])): ?>
                <a href="<?= BASE_URL ?>/pemilik/kos/detail?id_kos=<?= (int)$item['id_kos'] ?>" class="owner-photo-action owner-photo-action-primary">
                  <?= masterIconSvg('bed', 'h-4 w-4') ?>
                  <span>Buka properti</span>
                  <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
                </a>
              <?php endif; ?>

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
      search:'',status:'',
      properties:<?= json_encode_safe(array_map(fn($item)=>['name'=>$item['nama_kos'].' '.$item['alamat'],'status'=>$item['status']],$kos ?? []),JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      matches(item){return (!this.status || item.status===this.status) && item.name.toLowerCase().includes(this.search.trim().toLowerCase());},
      get visibleCount(){return this.properties.filter(item=>this.matches(item)).length;},
      async ajukan(id) {
        const ok = await Alpine.store('ui').confirm('Ajukan kos ini untuk diperiksa Admin?');
        if (!ok) return;
        try {
          await API.post('/pemilik/kos/ajukan-verifikasi', { id_kos: id });
          window.dispatchEvent(new CustomEvent('betakos:onboarding-refresh'));
          window.location.href = BASE_URL + '/pemilik/kos?onboarding=1';
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
