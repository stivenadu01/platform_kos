<div
  x-data="kamarPage()"
  x-init="init()"
  class="space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
        Kelola Kamar
      </h2>

      <p class="mt-1 text-sm text-slate-500">
        Kelola kamar dari seluruh kos yang Anda miliki.
      </p>
    </div>

    <a
      href="<?= BASE_URL ?>/pemilik/kamar/tambah"
      class="btn-primary">
      + Tambah Kamar
    </a>

  </div>


  <!-- FILTER -->
  <div class="card border border-slate-200 shadow-sm">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

      <div class="form-group">
        <label class="label">
          Cari kamar
        </label>

        <input
          type="search"
          x-model="search"
          @input.debounce.400ms="load()"
          class="input"
          placeholder="Nomor atau tipe kamar">
      </div>


      <div class="form-group">
        <label class="label">
          Kos
        </label>

        <select
          x-model="idKos"
          @change="load()"
          class="input">

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
          Status
        </label>

        <select
          x-model="status"
          @change="load()"
          class="input">

          <option value="">
            Semua status
          </option>

          <option value="tersedia">
            Tersedia
          </option>

          <option value="terisi">
            Terisi
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
  <div class="card border border-slate-200 shadow-sm overflow-hidden">

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

      <a
        href="<?= BASE_URL ?>/pemilik/kamar/tambah"
        class="btn-primary inline-flex mt-5">
        + Tambah Kamar
      </a>

    </div>


    <div
      x-show="!loading && kamar.length > 0"
      x-cloak
      class="overflow-x-auto">

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
                    class="input py-1.5 text-xs w-auto"
                    :value="item.status"
                    @change="changeStatus(item, $event.target.value)">

                    <option value="tersedia">
                      Tersedia
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
                    :href="BASE_URL + '/pemilik/kamar/harga?id_kamar=' + item.id_kamar"
                    class="btn-secondary">
                    Atur Harga
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

  </div>

</div>


<script>
  function kamarPage() {
    return {

      kamar: [],
      kosList: [],

      search: '',
      idKos: '',
      status: '',

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

        } catch (error) {
          console.error(error);
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