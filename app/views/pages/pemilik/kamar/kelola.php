<div
  x-data="kamarPage()"
  x-init="init()"
  class="owner-page">

  <!-- HEADER -->
  <div class="owner-page-header">
    <div>
      <a :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow mt-3">Unit Kamar</p>
      <h2 class="owner-title" x-text="tipe?.nama_tipe || 'Kelola Kamar'"></h2>
      <p class="owner-subtitle" x-text="tipe ? tipe.nama_kos + ' · Ubah nomor dan status operasional unit.' : 'Kelola nomor unit dan status operasional kamar.'"></p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <div data-help="help-kamar-add" data-onboarding="kamar-add-choice" class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto">
        <a data-onboarding="fast-tambah-kamar-bulk" :href="BASE_URL + '/pemilik/kamar/tambah?mode=bulk&id_tipe_kamar=' + idTipeKamar" class="btn-secondary justify-center">
          + Tambah Banyak Kamar
        </a>
        <a data-onboarding="fast-tambah-kamar" :href="BASE_URL + '/pemilik/kamar/tambah?id_tipe_kamar=' + idTipeKamar" class="btn-primary justify-center">
          + Tambah Satu Kamar
        </a>
      </div>
    </div>
  </div>

  <div x-show="tipe" x-cloak class="owner-context-panel">
    <div class="owner-context-header">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('info', 'h-5 w-5') ?></span>
      <div><p class="text-xs font-semibold uppercase tracking-wider text-primary">Kos dan tipe kamar</p><h3 class="mt-1 font-bold text-slate-900" x-text="tipe ? tipe.nama_kos + ' · ' + tipe.nama_tipe : ''"></h3></div>
    </div>
    <div class="owner-context-grid">
      <div class="owner-context-item"><p class="text-xs text-slate-400">Kapasitas</p><p class="mt-1 font-semibold text-slate-800" x-text="tipe ? tipe.kapasitas + ' orang per kamar' : '-' "></p></div>
      <div class="owner-context-item"><p class="text-xs text-slate-400">Rentang harga</p><p class="mt-1 font-semibold text-slate-800" x-text="tipePriceLabel"></p></div>
    </div>
  </div>


  <!-- PENCARIAN & STATUS -->
  <div class="card border border-slate-200 shadow-sm">
    <label class="label" for="room-search">Cari nomor kamar</label>
    <div class="relative"><span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400"><?= masterIconSvg('search', 'h-4 w-4') ?></span><input id="room-search" type="search" x-model="search" @input.debounce.400ms="searchRooms()" class="input !pl-10" placeholder="Contoh: 01 atau A-12"></div>
  </div>

  <div class="owner-segmented" aria-label="Filter status unit">
    <button type="button" @click="setStatus('')" :class="!status ? 'is-active' : ''">Semua</button>
    <button type="button" @click="setStatus('tersedia')" :class="status === 'tersedia' ? 'is-active' : ''"><span class="owner-segment-dot bg-emerald-400"></span>Tersedia</button>
    <button type="button" @click="setStatus('terisi')" :class="status === 'terisi' ? 'is-active' : ''"><span class="owner-segment-dot bg-rose-400"></span>Terisi</button>
    <button type="button" @click="setStatus('tidak_tersedia')" :class="status === 'tidak_tersedia' ? 'is-active' : ''"><span class="owner-segment-dot bg-amber-400"></span>Tidak tersedia</button>
    <button type="button" @click="setStatus('perbaikan')" :class="status === 'perbaikan' ? 'is-active' : ''"><span class="owner-segment-dot bg-blue-400"></span>Perbaikan</button>
    <button type="button" @click="setStatus('nonaktif')" :class="status === 'nonaktif' ? 'is-active' : ''"><span class="owner-segment-dot bg-slate-400"></span>Nonaktif</button>
  </div>

  <!-- DATA -->
  <section data-help="help-kamar-summary" class="space-y-4">

    <div
      x-show="loading"
      class="card border border-slate-200 py-12 text-center text-sm text-slate-500 shadow-sm">
      Memuat data kamar...
    </div>


    <div
      x-show="!loading && kamar.length === 0"
      x-cloak
      class="card border border-slate-200 py-14 text-center shadow-sm">

      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-soft text-primary"><?= masterIconSvg('bed', 'h-7 w-7') ?></div>

      <h3 class="font-semibold text-slate-900">
        Belum ada kamar
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Tambahkan kamar untuk mulai mengelola ketersediaan kos.
      </p>

      <div class="mt-5 flex flex-wrap justify-center gap-3">
        <a data-onboarding="fast-tambah-kamar-single" :href="BASE_URL + '/pemilik/kamar/tambah?id_tipe_kamar=' + idTipeKamar" class="btn-primary">+ Tambah Satu Kamar</a>
        <a data-onboarding="fast-tambah-kamar-bulk-empty" :href="BASE_URL + '/pemilik/kamar/tambah?mode=bulk&id_tipe_kamar=' + idTipeKamar" class="btn-secondary">+ Tambah Banyak Kamar</a>
      </div>

    </div>


    <div x-show="!loading && kamar.length > 0" x-cloak class="owner-unit-grid">
      <template x-for="item in kamar" :key="item.id_kamar">
        <article class="owner-panel owner-panel-hover !p-0 overflow-hidden">
          <div class="flex items-start justify-between gap-2.5 border-b border-slate-100 bg-slate-50/80 p-3">
            <div class="flex min-w-0 items-center gap-2.5">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary"><?= masterIconSvg('bed', 'h-4 w-4') ?></span>
              <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Unit</p>
                <h3 class="mt-0.5 truncate text-lg font-bold text-slate-900" x-text="item.nomor_kamar"></h3>
              </div>
            </div>
            <span class="shrink-0 rounded-full px-2 py-1 text-[11px] font-semibold" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>

          <div class="space-y-3 p-3">
            <div>
              <label class="text-xs font-semibold text-slate-500">Status unit</label>
              <template x-if="item.status === 'terisi'">
                <div class="mt-1.5 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-xs leading-5 text-rose-700">Diatur otomatis karena ada penghuni aktif.</div>
              </template>
              <template x-if="item.status !== 'terisi'">
                <select class="select mt-1.5 !py-2 text-sm" :value="item.status" @change="changeStatus(item, $event.target.value)">
                  <option value="tersedia">Tersedia</option>
                  <option value="tidak_tersedia">Tidak tersedia</option>
                  <option value="perbaikan">Perbaikan</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </template>
            </div>

            <div class="space-y-2 border-t border-slate-100 pt-3">
              <div class="grid grid-cols-2 gap-2">
                <a :href="BASE_URL + '/pemilik/kamar/operasional?id_kamar=' + item.id_kamar" class="owner-action-secondary justify-center"><?= masterIconSvg('users-round', 'h-4 w-4') ?> Penghuni</a>
                <a :href="BASE_URL + '/pemilik/kamar/operasional?id_kamar=' + item.id_kamar + '&section=keuangan'" class="owner-action-secondary justify-center"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan</a>
              </div>
              <button type="button" @click="remove(item)" class="owner-action-danger w-full justify-center"><?= masterIconSvg('trash-2', 'h-4 w-4') ?> Hapus Kamar</button>
            </div>
          </div>
        </article>
      </template>
    </div>

  </section>

</div>


<script>
  function kamarPage() {
    return {

      kamar: [],
      tipe: null,

      search: new URLSearchParams(window.location.search).get('search') || '',
      idTipeKamar: new URLSearchParams(window.location.search).get('id_tipe_kamar') || '',
      status: new URLSearchParams(window.location.search).get('status') || '',

      get tipePriceLabel() {
        const prices = Array.isArray(this.tipe?.harga) ? this.tipe.harga.map(item => Number(item.harga_total || 0)).filter(value => value >= 0) : [];
        if (!prices.length) return 'Belum diatur';
        const min = Math.min(...prices), max = Math.max(...prices);
        return min === max ? utils.formatRupiah(min) : utils.formatRupiah(min) + ' – ' + utils.formatRupiah(max);
      },

      get backUrl() {
        const fallback = this.tipe?.id_kos ? '/pemilik/kamar?id_kos=' + encodeURIComponent(this.tipe.id_kos) + '&context=kos' : '/pemilik/kamar';
        return BASE_URL + fallback;
      },

      loading: false,

      async init() {
        if (!this.idTipeKamar) {
          window.location.replace(BASE_URL + '/pemilik/kamar');
          return;
        }
        try {
          const res = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.idTipeKamar), false);
          this.tipe = res.data || null;
          await this.load();
        } catch (error) {
          console.error('Gagal memuat tipe kamar:', error);
          Alpine.store('ui').toast('Tipe kamar tidak ditemukan atau bukan milik Anda.', 'error');
          window.location.replace(BASE_URL + '/pemilik/kamar');
        }
      },

      async load() {

        this.loading = true;

        try {

          const params = new URLSearchParams();

          if (this.search.trim()) {
            utils.setQuery('search', this.search.trim())
            params.set('search', this.search.trim());
          }

          params.set('id_tipe_kamar', this.idTipeKamar);

          if (this.status) {
            utils.setQuery('status', this.status);
            params.set('status', this.status);
          }

          const query = params.toString();

          const res = await API.get(
            '/pemilik/kamar' + (query ? '?' + query : ''),
            false
          );

          this.kamar = res.data || [];

        } catch (error) {

          console.error('Gagal memuat kamar:', error);

          this.kamar = [];

        } finally {

          this.loading = false;

        }
      },

      async changeStatus(item, status) {

        try {

          await API.put(
            '/pemilik/kamar/status', {
              id_kamar: item.id_kamar,
              status: status
            }
          );

          item.status = status;

        } catch (error) {

          await this.load();

        }

      },

      setStatus(status) {
        this.status = status;
        utils.setQuery('status', status || null);
        this.load();
      },

      searchRooms() {
        utils.setQuery('search', this.search.trim() || null);
        this.load();
      },

      statusLabel(status) {
        return ({
          tersedia: 'Tersedia',
          terisi: 'Terisi',
          tidak_tersedia: 'Tidak tersedia',
          perbaikan: 'Perbaikan',
          nonaktif: 'Nonaktif'
        })[status] || status;
      },

      statusClass(status) {
        return ({
          tersedia: 'bg-emerald-50 text-emerald-700',
          terisi: 'bg-rose-50 text-rose-700',
          tidak_tersedia: 'bg-amber-50 text-amber-700',
          perbaikan: 'bg-blue-50 text-blue-700',
          nonaktif: 'bg-slate-200 text-slate-600'
        })[status] || 'bg-slate-100 text-slate-600';
      },


      async remove(item) {

        const ok = await Alpine.store('ui').confirm(
          `Hapus kamar ${item.nomor_kamar}?`
        );

        if (!ok) {
          return;
        }

        try {

          await API.delete(
            '/pemilik/kamar', {
              id_kamar: item.id_kamar
            }
          );

          await this.load();

        } catch (error) {
          console.error(error);
        }

      }

    };
  }
</script>
