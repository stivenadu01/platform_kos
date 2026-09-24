<div
  x-data="penghuniPage()"
  x-init="init()"
  class="owner-page">

  <!-- HEADER -->
  <div class="owner-page-header">

    <div>
      <a x-show="isTypeContext" x-cloak :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link mb-3"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow">Operasional Kos</p>
      <h2 class="owner-title">
        Penghuni Aktif
      </h2>

      <p class="owner-subtitle" x-text="isTypeContext ? 'Penghuni aktif pada kos dan tipe kamar yang dipilih.' : 'Kelola penghuni yang masih tinggal di seluruh properti Anda.'"></p>
    </div>

    <a
      data-help="help-penghuni-add" :href="addUrl"
      class="btn-primary">
      <?= masterIconSvg('user-plus', 'h-4 w-4') ?> Tambah Penghuni
    </a>

  </div>

  <div x-show="isTypeContext" x-cloak class="owner-context-panel">
    <div class="owner-context-header">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('users-round', 'h-5 w-5') ?></span>
      <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wider text-violet-600">Penghuni pada tipe</p><h3 class="owner-copy-full mt-1 font-bold text-slate-900" x-text="contextInfo ? contextInfo.nama_kos + ' · ' + (contextInfo.nama_tipe || contextInfo.tipe_kamar) : 'Memuat konteks...'"></h3><p class="mt-1 text-xs text-slate-500" x-text="contextInfo ? 'Kapasitas ' + contextInfo.kapasitas + ' orang per kamar' : ''"></p></div>
    </div>
  </div>


  <!-- FILTER -->
  <div x-show="!isTypeContext" x-data="{ filterOpen: window.innerWidth >= 768 }" @resize.window="if (window.innerWidth >= 768) filterOpen = true" data-help="help-penghuni-filter" class="card border border-slate-200 shadow-sm">

    <button type="button" @click="filterOpen = !filterOpen" class="owner-mobile-filter-summary md:hidden">
      <span class="inline-flex items-center gap-2"><?= masterIconSvg('filter', 'h-4 w-4') ?> Cari & Filter Penghuni</span>
      <span class="inline-flex items-center gap-2"><span x-show="search || idKos || idTipeKamar || idKamar" class="owner-mobile-filter-count" x-text="[search,idKos,idTipeKamar,idKamar].filter(Boolean).length"></span><span x-text="filterOpen ? '−' : '+'"></span></span>
    </button>

    <div x-show="filterOpen" x-cloak class="mt-3 grid grid-cols-1 gap-3 md:mt-0 md:grid md:grid-cols-2 xl:grid-cols-4 xl:gap-4">

      <!-- SEARCH -->
      <div class="form-group">

        <label class="label">
          Cari penghuni
        </label>

        <input
          type="search"
          x-model="search"
          @input.debounce.400ms="applySearch()"
          class="input"
          placeholder="Nama atau no. HP">

      </div>


      <!-- KOS -->
      <div class="form-group">

        <label class="label">
          Kos
        </label>

        <select
          x-model="idKos"
          @change="changeKosFilter()"
          class="select">

          <option value="">
            Semua kos
          </option>

          <template
            x-for="kos in kosList"
            :key="kos.id_kos">

            <option
              :value="kos.id_kos"
              x-text="kos.nama_kos">
            </option>

          </template>

        </select>

      </div>


      <!-- TIPE KAMAR -->
      <div class="form-group">

        <label class="label">Tipe kamar</label>

        <select
          x-model="idTipeKamar"
          @change="changeTypeFilter()"
          class="select">

          <option value="">Semua tipe kamar</option>

          <template x-for="tipeItem in filteredTipeList" :key="tipeItem.id_tipe_kamar">
            <option :value="tipeItem.id_tipe_kamar" x-text="tipeItem.tipe_kamar"></option>
          </template>

        </select>

      </div>


      <!-- KAMAR -->
      <div class="form-group">

        <label class="label">
          Kamar
        </label>

        <select
          x-model="idKamar"
          @change="applyFilter()"
          class="select">

          <option value="">
            Semua kamar
          </option>

          <template
            x-for="kamarItem in filteredKamarList"
            :key="kamarItem.id_kamar">

            <option
              :value="kamarItem.id_kamar"
              x-text="kamarItem.nomor_kamar">
            </option>

          </template>

        </select>

      </div>


    </div>

  </div>


  <!-- DATA -->
  <div data-help="help-penghuni-table" class="owner-data-container card border border-slate-200 shadow-sm overflow-hidden">

    <!-- LOADING -->
    <div
      x-show="loading"
      class="py-12 text-center text-sm text-slate-500">

      Memuat data penghuni...

    </div>


    <!-- EMPTY -->
    <div
      x-show="!loading && penghuni.length === 0"
      x-cloak
      class="py-14 text-center">

      <div class="text-4xl mb-4">
        <?= masterIconSvg('user-round', 'h-8 w-8') ?>
      </div>

      <h3 class="font-semibold text-slate-900">
        Tidak ada data penghuni
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Belum ada penghuni yang sesuai dengan filter yang dipilih.
      </p>

      <a
        :href="addUrl"
        class="btn-primary inline-flex mt-5">

        + Tambah Penghuni

      </a>

    </div>


    <!-- TABLE -->
    <div
      x-show="!loading && penghuni.length > 0"
      x-cloak
      class="!hidden md:!block overflow-x-auto">

      <table class="w-full text-sm">

        <thead class="bg-slate-50 border-b border-slate-200">

          <tr>

            <th class="text-left px-5 py-3 font-semibold">
              Penghuni
            </th>

            <th x-show="!isTypeContext" class="text-left px-5 py-3 font-semibold">
              Kos
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Kamar
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Tanggal Masuk
            </th>

            <th class="text-right px-5 py-3 font-semibold">
              Aksi
            </th>

          </tr>

        </thead>


        <tbody class="divide-y divide-slate-100">

          <template
            x-for="item in penghuni"
            :key="item.id_penghuni">

            <tr class="hover:bg-slate-50">

              <!-- PENGHUNI -->
              <td class="px-5 py-4">

                <div class="font-medium text-slate-900"
                  x-text="item.nama">
                </div>

                <div
                  x-show="item.no_hp"
                  class="mt-1 text-xs text-slate-500"
                  x-text="item.no_hp">
                </div>

              </td>


              <!-- KOS -->
              <td x-show="!isTypeContext"
                class="px-5 py-4 text-slate-700"
                x-text="item.nama_kos">
              </td>


              <!-- KAMAR -->
              <td class="px-5 py-4">

                <div
                  class="font-medium text-slate-900"
                  x-text="item.nomor_kamar">
                </div>

                <div x-show="!isTypeContext && item.tipe_kamar"
                  class="mt-1 text-xs text-slate-500"
                  x-text="item.tipe_kamar">
                </div>

              </td>


              <!-- TANGGAL MASUK -->
              <td
                class="px-5 py-4 text-slate-600"
                x-text="formatDate(item.tanggal_masuk)">
              </td>

              <!-- AKSI -->
              <td class="px-5 py-4">

                <div data-help="help-penghuni-actions" class="flex justify-end gap-2">

                  <a :href="tagihanUrl(item)" class="btn-secondary" title="Lihat tagihan yang terhubung dengan penghuni ini"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan</a>

                  <a
                    :href="editUrl(item)"
                    class="btn-secondary">

                    <?= masterIconSvg('pencil', 'h-4 w-4') ?> Edit

                  </a>


                  <template x-if="item.status === 'aktif'">

                    <button
                      type="button"
                      @click="keluar(item)"
                      class="btn-secondary">

                      <?= masterIconSvg('log-out', 'h-4 w-4') ?> Keluar

                    </button>

                  </template>


                  <button
                    type="button"
                    @click="remove(item)"
                    class="btn-danger">

                    <?= masterIconSvg('trash-2', 'h-4 w-4') ?> Hapus

                  </button>

                </div>

              </td>

            </tr>

          </template>

        </tbody>

      </table>

    </div>

    <div x-show="!loading && penghuni.length > 0" class="owner-mobile-card-stack !block md:!hidden">
      <template x-for="item in penghuni" :key="'m-' + item.id_penghuni">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><div class="owner-copy-full font-bold text-slate-900" x-text="item.nama"></div><div class="mt-1 text-xs text-slate-500" x-show="item.no_hp" x-text="item.no_hp"></div></div>
            <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : item.status === 'keluar' ? 'Sudah Keluar' : item.status || '-' "></span>
          </div>
          <div class="mt-3 flex items-center gap-3 rounded-xl bg-violet-50 p-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-violet-600 shadow-sm"><?= masterIconSvg('bed', 'h-4 w-4') ?></span>
            <div class="min-w-0"><div class="text-[11px] font-semibold uppercase tracking-wider text-violet-500" x-text="isTypeContext ? 'Unit kamar' : item.nama_kos"></div><div class="owner-copy-full mt-0.5 font-bold text-violet-950" x-text="'Kamar ' + item.nomor_kamar + (!isTypeContext && item.tipe_kamar ? ' · ' + item.tipe_kamar : '')"></div></div>
          </div>
          <div class="mt-3 text-xs">
            <div><div class="text-slate-400">Tanggal masuk</div><div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_masuk)"></div></div>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-2">
            <a :href="tagihanUrl(item)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Lihat Tagihan</a>
            <a :href="editUrl(item)" class="owner-action-secondary text-xs"><?= masterIconSvg('pencil', 'h-4 w-4') ?> Edit</a>
            <button x-show="item.status === 'aktif'" type="button" @click="keluar(item)" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700"><?= masterIconSvg('log-out', 'h-4 w-4') ?> Catat Keluar</button>
            <button type="button" @click="remove(item)" class="owner-action-danger text-xs"><?= masterIconSvg('trash-2', 'h-4 w-4') ?> Hapus</button>
          </div>
        </article>
      </template>
    </div>

  </div>


  <!-- MODAL CATAT PENGHUNI KELUAR -->
  <div
    x-show="showKeluarModal"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="closeKeluarModal()">

    <!-- BACKDROP -->
    <div
      class="absolute inset-0 bg-slate-900/50"
      @click="closeKeluarModal()">
    </div>


    <!-- MODAL -->
    <div
      x-show="showKeluarModal"
      x-transition
      class="relative w-full max-w-md rounded-xl bg-white shadow-xl">

      <!-- HEADER -->
      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

        <div>
          <h3 class="text-lg font-semibold text-slate-900">
            Catat Penghuni Keluar
          </h3>

          <p class="mt-1 text-sm text-slate-500">
            Masukkan tanggal penghuni keluar.
          </p>
        </div>

        <button
          type="button"
          @click="closeKeluarModal()"
          class="text-slate-400 hover:text-slate-600 text-xl">

          &times;

        </button>

      </div>


      <!-- BODY -->
      <div class="px-5 py-5">

        <!-- NAMA PENGHUNI -->
        <div
          x-show="selectedPenghuni"
          class="mb-4 rounded-lg bg-slate-50 px-4 py-3">

          <div class="text-xs text-slate-500">
            Penghuni
          </div>

          <div
            class="mt-1 font-medium text-slate-900"
            x-text="selectedPenghuni?.nama || '-'">
          </div>

        </div>


        <!-- TANGGAL KELUAR -->
        <div class="form-group">

          <label class="label">
            Tanggal Keluar
          </label>

          <input
            type="date"
            x-model="tanggalKeluar"
            class="input-date"
            required>

          <p class="mt-1 text-xs text-slate-500">
            Pilih tanggal saat penghuni meninggalkan kamar.
          </p>

        </div>

      </div>


      <!-- FOOTER -->
      <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">

        <button
          type="button"
          @click="closeKeluarModal()"
          class="btn-secondary">

          Batal

        </button>

        <button
          type="button"
          @click="submitKeluar()"
          :disabled="!tanggalKeluar || submittingKeluar"
          class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed">

          <span
            x-show="!submittingKeluar">
            Simpan
          </span>

          <span
            x-show="submittingKeluar">
            Menyimpan...
          </span>

        </button>

      </div>

    </div>

  </div>
</div>


<script>
  function penghuniPage() {

    return {

      penghuni: [],

      kosList: [],

      kamarList: [],

      filteredKamarList: [],

      tipeList: [],

      filteredTipeList: [],


      search: utils.getQuery('search') || '',

      idKos: utils.getQuery('id_kos') || '',

      idTipeKamar: utils.getQuery('id_tipe_kamar') || '',

      context: utils.getQuery('context') || '',

      idKamar: utils.getQuery('id_kamar') || '',

      status: '',

      contextInfo: null,

      get isTypeContext() {
        return this.context === 'tipe' && !!this.idKos && !!this.idTipeKamar;
      },

      get addUrl() {
        if (!this.isTypeContext) return BASE_URL + '/pemilik/penghuni/tambah';
        const params = new URLSearchParams({ id_kos: this.idKos, id_tipe_kamar: this.idTipeKamar, context: 'tipe' });
        return BASE_URL + '/pemilik/penghuni/tambah?' + params.toString();
      },

      get backUrl() {
        return BASE_URL + '/pemilik/kamar?id_kos=' + encodeURIComponent(this.idKos) + '&context=kos';
      },


      loading: false,

      showKeluarModal: false,

      selectedPenghuni: null,

      tanggalKeluar: '',

      submittingKeluar: false,


      async init() {

        await this.loadKamar();

        if (this.isTypeContext && !this.contextInfo) {
          try {
            const contextRes = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.idTipeKamar), false);
            this.contextInfo = contextRes.data || null;
          } catch (error) {
            console.error('Gagal memuat konteks tipe kamar:', error);
          }
        }

        this.filterTipe();
        this.filterKamar();

        await this.load();

      },


      /*
      |--------------------------------------------------------------------------
      | LOAD DATA
      |--------------------------------------------------------------------------
      */

      async load() {

        this.loading = true;

        try {

          const params = new URLSearchParams();


          if (this.search.trim()) {

            params.set(
              'search',
              this.search.trim()
            );

          }


          if (this.idKos) {

            params.set(
              'id_kos',
              this.idKos
            );

          }


          if (this.idKamar) {

            params.set(
              'id_kamar',
              this.idKamar
            );

          }

          if (this.idTipeKamar) {
            params.set('id_tipe_kamar', this.idTipeKamar);
          }


          if (this.status) {

            params.set(
              'status',
              this.status
            );

          }

          params.set('scope', 'aktif');


          const query = params.toString();


          const res = await API.get(

            '/pemilik/penghuni' +
            (query ? '?' + query : ''),

            false

          );


          this.penghuni =
            res.data || [];


        } catch (error) {

          console.error(
            'Gagal memuat penghuni:',
            error
          );

          this.penghuni = [];

          Alpine.store('ui').toast(
            'Gagal memuat data penghuni.',
            'error'
          );

        } finally {

          this.loading = false;

        }

      },


      /*
      |--------------------------------------------------------------------------
      | LOAD KAMAR
      |--------------------------------------------------------------------------
      */

      async loadKamar() {

        try {

          const res = await API.get(
            '/pemilik/penghuni/kamar',
            false
          );


          this.kamarList =
            res.data || [];

          if (this.isTypeContext) {
            this.contextInfo = this.kamarList.find(item => String(item.id_kos) === String(this.idKos) && String(item.id_tipe_kamar) === String(this.idTipeKamar)) || null;
          }

          /*
          |--------------------------------------------------------------------------
          | Bentuk daftar kos dari daftar kamar
          |--------------------------------------------------------------------------
          */

          const map = new Map();


          this.kamarList.forEach(item => {

            if (!map.has(item.id_kos)) {

              map.set(
                item.id_kos, {
                  id_kos: item.id_kos,
                  nama_kos: item.nama_kos
                }
              );

            }

          });


          this.kosList =
            Array.from(map.values());

          const tipeMap = new Map();

          this.kamarList.forEach(item => {
            const key = String(item.id_tipe_kamar);
            if (!tipeMap.has(key)) {
              tipeMap.set(key, {
                id_tipe_kamar: item.id_tipe_kamar,
                id_kos: item.id_kos,
                tipe_kamar: item.tipe_kamar
              });
            }
          });

          this.tipeList = Array.from(tipeMap.values());


        } catch (error) {

          console.error(
            'Gagal memuat daftar kamar:',
            error
          );

          this.kamarList = [];
          this.kosList = [];
          this.tipeList = [];
          this.filteredTipeList = [];

        }

      },


      filterTipe() {
        this.filteredTipeList = this.idKos
          ? this.tipeList.filter(item => String(item.id_kos) === String(this.idKos))
          : this.tipeList;

        const exists = this.filteredTipeList.some(
          item => String(item.id_tipe_kamar) === String(this.idTipeKamar)
        );

        if (this.idTipeKamar && !exists) {
          this.idTipeKamar = '';
        }
      },


      /*
      |--------------------------------------------------------------------------
      | FILTER KAMAR BERDASARKAN KOS
      |--------------------------------------------------------------------------
      */

      filterKamar() {

        if (this.isTypeContext) {
          this.filteredKamarList = this.kamarList.filter(item => String(item.id_kos) === String(this.idKos) && String(item.id_tipe_kamar) === String(this.idTipeKamar));
        } else {

          this.filteredKamarList = this.kamarList.filter(item => {
            const kosMatches = !this.idKos || String(item.id_kos) === String(this.idKos);
            const typeMatches = !this.idTipeKamar || String(item.id_tipe_kamar) === String(this.idTipeKamar);
            return kosMatches && typeMatches;
          });

        }


        /*
        |--------------------------------------------------------------------------
        | Jika kamar yang dipilih bukan bagian dari kos,
        | reset pilihan kamar.
        |--------------------------------------------------------------------------
        */

        const exists =
          this.filteredKamarList.some(
            item =>
            String(item.id_kamar) ===
            String(this.idKamar)
          );


        if (
          this.idKamar &&
          !exists
        ) {

          this.idKamar = '';

          utils.setQuery(
            'id_kamar',
            ''
          );

        }

      },


      /*
      |--------------------------------------------------------------------------
      | SEARCH
      |--------------------------------------------------------------------------
      */

      applySearch() {

        const value =
          this.search.trim();


        utils.setQuery(
          'search',
          value
        );


        this.load();

      },


      /*
      |--------------------------------------------------------------------------
      | FILTER
      |--------------------------------------------------------------------------
      */

      applyFilter() {

        /*
        | Kos berubah
        */

        this.filterKamar();


        /*
        | Simpan filter ke URL
        */

        utils.setQuery(
          'id_kos',
          this.idKos
        );

        utils.setQuery(
          'id_kamar',
          this.idKamar
        );

        utils.setQuery(
          'id_tipe_kamar',
          this.idTipeKamar
        );

        utils.setQuery(
          'status',
          this.status
        );


        this.load();

      },

      changeKosFilter() {
        this.filterTipe();
        this.idKamar = '';
        this.applyFilter();
      },

      changeTypeFilter() {
        this.idKamar = '';
        this.applyFilter();
      },


      /*
      |--------------------------------------------------------------------------
      | FORMAT TANGGAL
      |--------------------------------------------------------------------------
      */

      formatDate(date) {

        if (!date) {
          return '-';
        }

        return utils.formatDate(date);

      },

      tagihanUrl(item) {
        return BASE_URL + '/pemilik/kamar/operasional?id_kamar=' + encodeURIComponent(item.id_kamar) + '&section=keuangan';
      },

      editUrl(item) {
        const params = new URLSearchParams({ id_penghuni: item.id_penghuni });
        if (this.isTypeContext) {
          params.set('id_kos', this.idKos);
          params.set('id_tipe_kamar', this.idTipeKamar);
          params.set('context', 'tipe');
        }
        return BASE_URL + '/pemilik/penghuni/edit?' + params.toString();
      },


      /*
      |--------------------------------------------------------------------------
      | KELUAR
      |--------------------------------------------------------------------------
      */

      keluar(item) {

        this.selectedPenghuni = item;

        /*
        |----------------------------------------------------------------------
        | Default tanggal keluar = hari ini
        |----------------------------------------------------------------------
        */

        const today = new Date();

        const year = today.getFullYear();

        const month = String(
          today.getMonth() + 1
        ).padStart(2, '0');

        const day = String(
          today.getDate()
        ).padStart(2, '0');


        this.tanggalKeluar =
          `${year}-${month}-${day}`;


        this.showKeluarModal = true;

      },

      closeKeluarModal() {

        if (this.submittingKeluar) {
          return;
        }

        this.showKeluarModal = false;

        this.selectedPenghuni = null;

        this.tanggalKeluar = '';

      },

      async submitKeluar() {

        if (!this.selectedPenghuni) {
          return;
        }


        if (!this.tanggalKeluar) {

          Alpine.store('ui').toast(
            'Tanggal keluar wajib diisi.',
            'error'
          );

          return;
        }


        /*
        |----------------------------------------------------------------------
        | Konfirmasi sebelum menyimpan
        |----------------------------------------------------------------------
        */

        const ok =
          await Alpine.store('ui').confirm(
            `Catat ${this.selectedPenghuni.nama} sebagai penghuni yang keluar pada ${this.formatDate(this.tanggalKeluar)}?`
          );


        if (!ok) {
          return;
        }


        this.submittingKeluar = true;


        try {

          await API.put(
            '/pemilik/penghuni/keluar', {
              id_penghuni: this.selectedPenghuni.id_penghuni,

              tanggal_keluar: this.tanggalKeluar
            }
          );


          Alpine.store('ui').toast(
            'Penghuni berhasil dicatat keluar.',
            'success'
          );


          this.closeKeluarModal();

          await this.load();


        } catch (error) {

          console.error(
            'Gagal mencatat penghuni keluar:',
            error
          );


          Alpine.store('ui').toast(
            error.message ||
            'Gagal mencatat penghuni keluar.',
            'error'
          );


        } finally {

          this.submittingKeluar = false;

        }

      },


      /*
      |--------------------------------------------------------------------------
      | DELETE
      |--------------------------------------------------------------------------
      */

      async remove(item) {

        const ok =
          await Alpine.store('ui').confirm(

            `Hapus data penghuni ${item.nama}?`

          );


        if (!ok) {
          return;
        }


        try {

          await API.delete(
            '/pemilik/penghuni', {
              id_penghuni: item.id_penghuni
            }
          );


          Alpine.store('ui').toast(
            'Data penghuni berhasil dihapus.',
            'success'
          );


          await this.load();


        } catch (error) {

          console.error(error);

          Alpine.store('ui').toast(
            error.message ||
            'Gagal menghapus data penghuni.',
            'error'
          );

        }

      }

    };

  }
</script>
