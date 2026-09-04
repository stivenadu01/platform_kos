<div
  x-data="penghuniPage()"
  x-init="init()"
  class="space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
        Kelola Penghuni
      </h2>

      <p class="mt-1 text-sm text-slate-500">
        Kelola data penghuni dari seluruh kamar yang Anda miliki.
      </p>
    </div>

    <a
      data-help="help-penghuni-add" href="<?= BASE_URL ?>/pemilik/penghuni/tambah"
      class="btn-primary">
      + Tambah Penghuni
    </a>

  </div>


  <!-- FILTER -->
  <div data-help="help-penghuni-filter" class="card border border-slate-200 shadow-sm">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

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
          @change="applyFilter()"
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


      <!-- STATUS -->
      <div class="form-group">

        <label class="label">
          Status
        </label>

        <select
          x-model="status"
          @change="applyFilter()"
          class="select">

          <option value="">
            Semua status
          </option>

          <option value="aktif">
            Aktif
          </option>

          <option value="keluar">
            Sudah Keluar
          </option>

        </select>

      </div>

    </div>

  </div>


  <!-- DATA -->
  <div data-help="help-penghuni-table" class="card border border-slate-200 shadow-sm overflow-hidden">

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
        👤
      </div>

      <h3 class="font-semibold text-slate-900">
        Tidak ada data penghuni
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Belum ada penghuni yang sesuai dengan filter yang dipilih.
      </p>

      <a
        href="<?= BASE_URL ?>/pemilik/penghuni/tambah"
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

            <th class="text-left px-5 py-3 font-semibold">
              Kos
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Kamar
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Tanggal Masuk
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Tanggal Keluar
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Status
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
              <td
                class="px-5 py-4 text-slate-700"
                x-text="item.nama_kos">
              </td>


              <!-- KAMAR -->
              <td class="px-5 py-4">

                <div
                  class="font-medium text-slate-900"
                  x-text="item.nomor_kamar">
                </div>

                <div
                  x-show="item.tipe_kamar"
                  class="mt-1 text-xs text-slate-500"
                  x-text="item.tipe_kamar">
                </div>

              </td>


              <!-- TANGGAL MASUK -->
              <td
                class="px-5 py-4 text-slate-600"
                x-text="formatDate(item.tanggal_masuk)">
              </td>

              <!-- TANGGAL KELUAR -->
              <td class="px-5 py-4">

                <template x-if="item.tanggal_keluar">
                  <span
                    class="text-slate-600"
                    x-text="formatDate(item.tanggal_keluar)">
                  </span>
                </template>

                <template x-if="!item.tanggal_keluar">
                  <span class="text-slate-400">
                    -
                  </span>
                </template>

              </td>


              <!-- STATUS -->
              <td class="px-5 py-4">

                <template x-if="item.status === 'aktif'">

                  <span
                    class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">

                    Aktif

                  </span>

                </template>


                <template x-if="item.status === 'keluar'">

                  <span
                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">

                    Sudah Keluar

                  </span>

                </template>


                <template x-if="
                  item.status !== 'aktif' &&
                  item.status !== 'keluar'
                ">

                  <span
                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600"
                    x-text="item.status || '-'">
                  </span>

                </template>

              </td>


              <!-- AKSI -->
              <td class="px-5 py-4">

                <div data-help="help-penghuni-actions" class="flex justify-end gap-2">

                  <a
                    :href="
                      BASE_URL +
                      '/pemilik/penghuni/edit?id_penghuni=' +
                      item.id_penghuni
                    "
                    class="btn-secondary">

                    Edit

                  </a>


                  <template x-if="item.status === 'aktif'">

                    <button
                      type="button"
                      @click="keluar(item)"
                      class="btn-secondary">

                      Keluar

                    </button>

                  </template>


                  <button
                    type="button"
                    @click="remove(item)"
                    class="btn-danger">

                    Hapus

                  </button>

                </div>

              </td>

            </tr>

          </template>

        </tbody>

      </table>

    </div>

    <div x-show="!loading && penghuni.length > 0" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in penghuni" :key="'m-' + item.id_penghuni">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><div class="font-semibold text-slate-900 truncate" x-text="item.nama"></div><div class="mt-1 text-xs text-slate-500" x-show="item.no_hp" x-text="item.no_hp"></div></div>
            <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : item.status === 'keluar' ? 'Sudah Keluar' : item.status || '-' "></span>
          </div>
          <div class="mt-3 space-y-2 text-sm">
            <div><span class="text-xs text-slate-400">Kos</span><div class="mt-0.5 text-slate-700" x-text="item.nama_kos"></div></div>
            <div><span class="text-xs text-slate-400">Kamar</span><div class="mt-0.5 font-medium text-slate-700" x-text="item.nomor_kamar + (item.tipe_kamar ? ' · ' + item.tipe_kamar : '')"></div></div>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div><div class="text-slate-400">Tanggal masuk</div><div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_masuk)"></div></div>
            <div><div class="text-slate-400">Tanggal keluar</div><div class="mt-1 text-slate-700" x-text="item.tanggal_keluar ? formatDate(item.tanggal_keluar) : '-' "></div></div>
          </div>
          <div class="mt-3 flex flex-wrap gap-2">
            <a :href="BASE_URL + '/pemilik/penghuni/edit?id_penghuni=' + item.id_penghuni" class="btn-secondary text-xs">Edit</a>
            <button x-show="item.status === 'aktif'" type="button" @click="keluar(item)" class="btn-secondary text-xs">Catat Keluar</button>
            <button type="button" @click="remove(item)" class="btn-danger text-xs">Hapus</button>
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


      search: utils.getQuery('search') || '',

      idKos: utils.getQuery('id_kos') || '',

      idKamar: utils.getQuery('id_kamar') || '',

      status: utils.getQuery('status') || '',


      loading: false,

      showKeluarModal: false,

      selectedPenghuni: null,

      tanggalKeluar: '',

      submittingKeluar: false,


      async init() {

        await this.loadKamar();

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


          if (this.status) {

            params.set(
              'status',
              this.status
            );

          }


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


        } catch (error) {

          console.error(
            'Gagal memuat daftar kamar:',
            error
          );

          this.kamarList = [];
          this.kosList = [];

        }

      },


      /*
      |--------------------------------------------------------------------------
      | FILTER KAMAR BERDASARKAN KOS
      |--------------------------------------------------------------------------
      */

      filterKamar() {

        if (!this.idKos) {

          this.filteredKamarList =
            this.kamarList;

        } else {

          this.filteredKamarList =
            this.kamarList.filter(
              item =>
              String(item.id_kos) ===
              String(this.idKos)
            );

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
          'status',
          this.status
        );


        this.load();

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