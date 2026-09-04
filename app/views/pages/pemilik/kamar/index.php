<div
  x-data="kamarPage()"
  x-init="init()"
  class="space-y-6">

  <!-- HEADER -->
  <div class="space-y-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Kelola Kamar</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola unit kamar dari seluruh kos yang Anda miliki.</p>
    </div>

    <div class="flex flex-wrap gap-3 items-center">
      <a data-onboarding="fast-kelola-tipe-kamar" data-help="help-kamar-type" href="<?= BASE_URL ?>/pemilik/tipe-kamar" class="btn-secondary justify-center">
        Kelola Tipe Kamar
      </a>
      <a data-onboarding="fast-tambah-tipe-kamar" href="<?= BASE_URL ?>/pemilik/tipe-kamar/tambah" class="btn-secondary justify-center">
        + Tambah Tipe Kamar
      </a>

      <div data-help="help-kamar-add" data-onboarding="kamar-add-choice" class="flex flex-wrap gap-3">
        <a data-onboarding="fast-tambah-kamar-bulk" href="<?= BASE_URL ?>/pemilik/kamar/tambah?mode=bulk" class="btn-secondary justify-center">
          + Tambah Banyak Kamar
        </a>
        <a data-onboarding="fast-tambah-kamar" href="<?= BASE_URL ?>/pemilik/kamar/tambah" class="btn-primary justify-center">
          + Tambah Satu Kamar
        </a>
      </div>
    </div>
  </div>


  <!-- FILTER -->
  <div data-help="help-kamar-filter" class="card border border-slate-200 shadow-sm">

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

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
          Kos
        </label>

        <select
          x-model="idKos"
          @change="loadTipe().then(() => load())"
          class="select">

          <option value="">
            Semua kos
          </option>

          <template x-for="kos in kosList" :key="kos.id_kos">
            <option
              :value="kos.id_kos"
              x-text="kos.nama_kos">
            </option>
          </template>

        </select>
      </div>


      <div class="form-group">
        <label class="label">
          Tipe kamar
        </label>

        <select
          x-model="idTipeKamar"
          @change="load()"
          class="select">
          <option value="">Semua tipe</option>
          <template x-for="tipe in tipeList" :key="tipe.id_tipe_kamar">
            <option :value="tipe.id_tipe_kamar" x-text="tipe.nama_tipe"></option>
          </template>
        </select>
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
  <div data-help="help-kamar-summary" class="card border border-slate-200 shadow-sm overflow-hidden">

    <div
      x-show="loading"
      class="py-12 text-center text-sm text-slate-500">
      Memuat data kamar...
    </div>


    <div
      x-show="!loading && kamar.length === 0"
      x-cloak
      class="py-14 text-center">

      <div class="text-4xl mb-4">
        🛏️
      </div>

      <h3 class="font-semibold text-slate-900">
        Belum ada kamar
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Tambahkan kamar untuk mulai mengelola ketersediaan kos.
      </p>

      <div class="mt-5 flex flex-wrap justify-center gap-3">
        <a data-onboarding="fast-tambah-kamar-single" href="<?= BASE_URL ?>/pemilik/kamar/tambah" class="btn-primary">+ Tambah Satu Kamar</a>
        <a data-onboarding="fast-tambah-kamar-bulk-empty" href="<?= BASE_URL ?>/pemilik/kamar/tambah?mode=bulk" class="btn-secondary">+ Tambah Banyak Kamar</a>
      </div>

    </div>


    <div
      x-show="!loading && kamar.length > 0"
      x-cloak
      class="!hidden md:!block overflow-x-auto">

      <table class="w-full text-sm">

        <thead class="bg-slate-50 border-b border-slate-200">

          <tr>

            <th class="text-left px-5 py-3 font-semibold">
              Kos
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Kamar
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Tipe
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Kapasitas
            </th>

            <th class="text-left px-5 py-3 font-semibold">
              Harga
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

          <template x-for="item in kamar" :key="item.id_kamar">

            <tr class="hover:bg-slate-50">

              <td
                class="px-5 py-4 font-medium text-slate-900"
                x-text="item.nama_kos">
              </td>

              <td
                class="px-5 py-4"
                x-text="item.nomor_kamar">
              </td>

              <td
                class="px-5 py-4 text-slate-500"
                x-text="item.tipe_kamar || '-'">
              </td>

              <td
                class="px-5 py-4"
                x-text="item.kapasitas + ' orang'">
              </td>

              <td class="px-5 py-4">

                <template x-if="item.harga_min !== null">

                  <div>

                    <template x-if="Number(item.harga_min) === Number(item.harga_max)">

                      <span
                        class="font-medium text-slate-900"
                        x-text="$store.utils.formatRupiah(item.harga_min)">
                      </span>

                    </template>


                    <template x-if="Number(item.harga_min) !== Number(item.harga_max)">

                      <span
                        class="font-medium text-slate-900"
                        x-text="
            $store.utils.formatRupiah(item.harga_min)
            + ' - ' +
            $store.utils.formatRupiah(item.harga_max)
          ">
                      </span>

                    </template>

                  </div>

                </template>


                <template x-if="item.harga_min === null">

                  <span class="text-slate-400">
                    Belum diatur
                  </span>

                </template>

              </td>

              <td class="px-5 py-4">

                <template x-if="item.status === 'terisi'">

                  <span
                    class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
                    Terisi
                  </span>

                </template>

                <template x-if="item.status !== 'terisi'">

                  <select
                    class="select py-1.5 text-xs w-auto"
                    :value="item.status"
                    @change="changeStatus(item, $event.target.value)">

                    <option value="tersedia">
                      Tersedia
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

                </template>

              </td>

              <td class="px-5 py-4">

                <div class="flex justify-end gap-2">

                  <a
                    :href="BASE_URL + '/pemilik/kamar/edit?id_kamar=' + item.id_kamar"
                    class="btn-secondary">
                    Edit
                  </a>

                  <a
                    :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + item.id_tipe_kamar"
                    class="btn-secondary">
                    Kelola Tipe
                  </a>

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

    <div x-show="!loading && kamar.length > 0" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in kamar" :key="'m-' + item.id_kamar">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><div class="font-semibold text-slate-900" x-text="item.nama_kos"></div><div class="mt-1 text-sm text-slate-700" x-text="'Kamar ' + item.nomor_kamar"></div><div class="mt-1 text-xs text-slate-500" x-text="item.tipe_kamar || 'Tipe belum diatur'"></div></div>
            <template x-if="item.status === 'terisi'"><span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">Terisi</span></template>
            <template x-if="item.status !== 'terisi'"><span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700" x-text="item.status === 'tersedia' ? 'Tersedia' : item.status === 'tidak_tersedia' ? 'Tidak tersedia' : item.status === 'perbaikan' ? 'Perbaikan' : 'Nonaktif'"></span></template>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div><div class="text-slate-400">Kapasitas</div><div class="mt-1 font-medium text-slate-700" x-text="item.kapasitas + ' orang'"></div></div>
            <div><div class="text-slate-400">Harga</div><div class="mt-1 font-medium text-slate-700" x-text="item.harga_min === null ? 'Belum diatur' : Number(item.harga_min) === Number(item.harga_max) ? $store.utils.formatRupiah(item.harga_min) : $store.utils.formatRupiah(item.harga_min) + ' - ' + $store.utils.formatRupiah(item.harga_max)"></div></div>
          </div>
          <div class="mt-3 flex flex-wrap gap-2">
            <a :href="BASE_URL + '/pemilik/kamar/edit?id_kamar=' + item.id_kamar" class="btn-secondary text-xs">Edit</a>
            <a :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + item.id_tipe_kamar" class="btn-secondary text-xs">Kelola Tipe</a>
            <button type="button" @click="remove(item)" class="btn-danger text-xs">Hapus</button>
          </div>
        </article>
      </template>
    </div>

  </div>

</div>


<script>
  function kamarPage() {
    return {

      kamar: [],
      kosList: [],

      search: '',
      idKos: '',
      idTipeKamar: '',
      status: '',
      tipeList: [],

      loading: false,

      async init() {
        await Promise.all([
          this.loadKos(),
          this.load()
        ]);
      },

      async loadKos() {
        try {
          const res = await API.get(
            '/pemilik/kamar/kos',
            false
          );

          this.kosList = res.data || [];
          await this.loadTipe();

        } catch (error) {
          console.error(error);
        }
      },

      async loadTipe() {
        try {
          const query = this.idKos ? '?id_kos=' + encodeURIComponent(this.idKos) : '';
          const res = await API.get('/pemilik/tipe-kamar' + query, false);
          this.tipeList = res.data || [];
          if (this.idTipeKamar && !this.tipeList.some((item) => String(item.id_tipe_kamar) === String(this.idTipeKamar))) {
            this.idTipeKamar = '';
          }
        } catch (error) {
          console.error('Gagal memuat tipe kamar:', error);
          this.tipeList = [];
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

          if (this.idKos) {
            utils.setQuery('id_kos', this.idKos);
            params.set('id_kos', this.idKos);
          }

          if (this.idTipeKamar) {
            utils.setQuery('id_tipe_kamar', this.idTipeKamar);
            params.set('id_tipe_kamar', this.idTipeKamar);
          }

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