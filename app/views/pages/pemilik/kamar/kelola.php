<div
  x-data="kamarPage()"
  x-init="init()"
  class="space-y-4 sm:space-y-5">

  <!-- HEADER -->
  <div class="space-y-4">
    <div>
      <a :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">Kelola Kamar</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola nomor unit dan status operasional kamar.</p>
    </div>

    <div class="flex flex-wrap gap-3 items-center">
      <div data-help="help-kamar-add" data-onboarding="kamar-add-choice" class="flex flex-wrap gap-3">
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


  <!-- FILTER -->
  <div data-help="help-kamar-filter" class="card border border-slate-200 shadow-sm">

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

      <div class="form-group">
        <label class="label">
          Cari kamar
        </label>

        <input
          type="search"
          x-model="search"
          @input.debounce.400ms="load()"
          class="input"
          placeholder="Cari nomor kamar...">
      </div>


      <div class="form-group">
        <label class="label">
          Status
        </label>

        <select
          x-model="status"
          @change="load()"
          class="select">

          <option value="">
            Semua status
          </option>

          <option value="tersedia">
            Tersedia
          </option>

          <option value="terisi">
            Terisi
          </option>

          <option value="tidak_tersedia">
            Tidak tersedia
          </option>

          <option value="perbaikan">
            Perbaikan
          </option>

          <option value="nonaktif">
            Nonaktif
          </option>

        </select>
      </div>

    </div>

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

            <div class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
              <a :href="BASE_URL + '/pemilik/kamar/edit?id_kamar=' + item.id_kamar" class="owner-action-secondary justify-center"><?= masterIconSvg('pencil', 'h-4 w-4') ?> Edit</a>
              <button type="button" @click="remove(item)" class="owner-action-danger justify-center"><?= masterIconSvg('trash-2', 'h-4 w-4') ?> Hapus</button>
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

      search: '',
      idTipeKamar: new URLSearchParams(window.location.search).get('id_tipe_kamar') || '',
      status: '',

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
