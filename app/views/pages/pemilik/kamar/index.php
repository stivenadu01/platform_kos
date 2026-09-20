<div x-data="tipeKamarPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <a x-show="idKos" x-cloak :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link mb-3"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Kelola Kamar</h2>
      <p class="mt-1 text-sm text-slate-500" x-text="contextKos ? 'Pilih tipe kamar pada kos ini untuk melanjutkan pengelolaan.' : 'Pilih tipe kamar terlebih dahulu, lalu kelola unit kamar di dalamnya.'"></p>
    </div>
    <a data-onboarding="fast-tambah-tipe-kamar" data-help="help-tipe-add" :href="BASE_URL + '/pemilik/tipe-kamar/tambah'" class="btn-primary">+ Tambah Tipe Kamar</a>
  </div>

  <div x-show="contextKos" x-cloak class="owner-context-panel">
    <div class="owner-context-header">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('building-2', 'h-5 w-5') ?></span>
      <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Konteks Kos</p>
        <h3 class="mt-1 truncate font-bold text-slate-900" x-text="contextKos?.nama_kos || 'Memuat kos...'"></h3>
        <p class="mt-1 text-xs text-slate-500">Semua tipe kamar di bawah merupakan bagian dari kos ini.</p>
      </div>
    </div>
  </div>

  <div x-show="loading" class="card p-10 text-center text-sm text-slate-500">Memuat tipe kamar...</div>

  <div x-show="!loading && !items.length" x-cloak class="card px-6 py-12 text-center">
    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-soft text-primary"><?= masterIconSvg('bed', 'h-7 w-7') ?></div>
    <h3 class="font-semibold text-slate-900">Belum ada tipe kamar</h3>
    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">Buat tipe kamar terlebih dahulu. Setelah itu Anda dapat menambahkan dan mengelola unit kamar pada tipe tersebut.</p>
    <a :href="BASE_URL + '/pemilik/tipe-kamar/tambah'" class="btn-primary mt-5">+ Tambah Tipe Kamar</a>
  </div>

  <div data-help="help-tipe-list" x-show="!loading && items.length" x-cloak class="owner-card-grid">
    <template x-for="item in items" :key="item.id_tipe_kamar">
      <article class="owner-panel owner-panel-hover group !p-0">
        <div class="owner-card-header">
          <div class="absolute -right-8 -top-10 h-28 w-28 rounded-full bg-white/10"></div>
          <div class="absolute -bottom-10 right-10 h-20 w-20 rounded-full bg-primary-300/10"></div>

          <div class="relative flex items-start gap-3.5">
            <div class="owner-card-ring flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path><path d="M9 15h1"></path><path d="M14 15h1"></path>
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <p class="owner-card-muted truncate text-xs font-medium uppercase tracking-wide" x-text="item.nama_kos"></p>
              <h3 class="mt-1 truncate text-lg font-bold text-white" x-text="item.nama_tipe"></h3>
            </div>
            <span class="owner-card-ring shrink-0 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white" x-text="item.kapasitas + ' orang'"></span>
          </div>
        </div>

        <div class="p-4 sm:p-5">
          <div class="grid grid-cols-3 divide-x divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/70 py-3 text-center">
            <div class="px-2">
              <div class="text-lg font-bold leading-none text-slate-900" x-text="item.jumlah_kamar"></div>
              <div class="mt-1.5 text-[11px] font-medium text-slate-500">Total unit</div>
            </div>
            <div class="px-2">
              <div class="text-lg font-bold leading-none text-emerald-600" x-text="item.kamar_tersedia"></div>
              <div class="mt-1.5 text-[11px] font-medium text-slate-500">Tersedia</div>
            </div>
            <div class="px-2">
              <div class="text-lg font-bold leading-none text-rose-600" x-text="item.kamar_terisi"></div>
              <div class="mt-1.5 text-[11px] font-medium text-slate-500">Terisi</div>
            </div>
          </div>

          <div data-help="help-tipe-action" data-onboarding="kamar-select-type" class="mt-4 space-y-2.5">
            <a :href="BASE_URL + '/pemilik/kamar/kelola?id_tipe_kamar=' + item.id_tipe_kamar" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/30">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"></path><path d="M6 21V7l6-4 6 4v14"></path><path d="M9 21v-6h6v6"></path></svg>
              Kelola Kamar
              <svg class="ml-auto h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
            </a>

            <a :href="BASE_URL + '/pemilik/penghuni?id_kos=' + item.id_kos + '&id_tipe_kamar=' + item.id_tipe_kamar + '&context=tipe'" class="flex w-full items-center gap-2 rounded-xl border border-primary/20 bg-primary-soft px-4 py-3 text-sm font-semibold text-primary transition hover:bg-blue-100">
              <?= masterIconSvg('users-round', 'h-4 w-4') ?>
              <span>Kelola Penghuni</span>
              <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
            </a>

            <a :href="BASE_URL + '/pemilik/pembayaran?id_kos=' + item.id_kos + '&id_tipe_kamar=' + item.id_tipe_kamar + '&context=tipe'" class="flex w-full items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-primary/30 hover:bg-slate-50 hover:text-primary">
              <?= masterIconSvg('wallet', 'h-4 w-4') ?>
              <span>Tagihan & Pembayaran</span>
              <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
            </a>

            <div class="owner-card-actions">
              <a :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + item.id_tipe_kamar + '&from=kamar'" class="owner-action-secondary">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path></svg>
                Edit
              </a>
              <a :href="BASE_URL + '/pemilik/tipe-kamar/foto?id_tipe_kamar=' + item.id_tipe_kamar + '&from=kamar'" class="owner-action-secondary">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path></svg>
                Foto
              </a>
              <button type="button" @click="remove(item)" class="owner-action-danger">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                Hapus
              </button>
            </div>
          </div>
        </div>
      </article>
    </template>
  </div>
</div>

<script>
  function tipeKamarPage() {
    return {
      items: [],
      idKos: new URLSearchParams(window.location.search).get('id_kos') || '',
      contextKos: null,
      loading: false,
      get backUrl() { return BASE_URL + '/pemilik/kos'; },
      async init() { await this.load(); },
      async load() {
        this.loading = true;
        try {
          const query = this.idKos ? '?id_kos=' + encodeURIComponent(this.idKos) : '';
          const res = await API.get('/pemilik/tipe-kamar' + query, false);
          this.items = res.data || [];
          this.contextKos = this.idKos && this.items.length ? {
            id_kos: this.items[0].id_kos,
            nama_kos: this.items[0].nama_kos
          } : null;
          if (this.idKos && !this.contextKos) {
            const kosRes = await API.get('/pemilik/kamar/kos', false);
            this.contextKos = (kosRes.data || []).find(item => String(item.id_kos) === String(this.idKos)) || null;
          }
        } catch (error) {
          console.error('Gagal memuat tipe kamar:', error);
          this.items = [];
        } finally {
          this.loading = false;
        }
      },
      async remove(item) {
        const unitCount = Number(item.jumlah_kamar || 0);
        if (unitCount > 0) {
          Alpine.store('ui').toast(`Tipe ${item.nama_tipe} masih memiliki ${unitCount} kamar dan tidak dapat dihapus.`, 'warning');
          return;
        }
        if (!await Alpine.store('ui').confirm(`Hapus tipe kamar ${item.nama_tipe}?`)) return;
        try {
          await API.delete('/pemilik/tipe-kamar', { id_tipe_kamar: item.id_tipe_kamar });
          await this.load();
        } catch (error) {
          console.error(error);
        }
      }
    };
  }
</script>
