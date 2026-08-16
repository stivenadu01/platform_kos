<div
  x-data="editKamarForm()"
  x-init="init()"
  class="max-w-3xl mx-auto space-y-6">

  <!-- HEADER -->
  <div>

    <a
      href="<?= BASE_URL ?>/pemilik/kamar"
      class="text-sm text-primary hover:underline">
      ← Kembali ke kamar
    </a>

    <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">
      Edit Kamar
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Perbarui informasi kamar.
    </p>

  </div>


  <!-- LOADING -->
  <div
    x-show="loadingData"
    class="card text-center py-12 text-sm text-slate-500">
    Memuat data kamar...
  </div>


  <!-- FORM -->
  <form
    x-show="!loadingData && found"
    x-cloak
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm space-y-6">


    <!-- KOS -->
    <div class="form-group">

      <label class="label">
        Kos <span class="text-red-500">*</span>
      </label>

      <select
        x-model="form.id_kos"
        class="input"
        required>

        <option value="">
          Pilih kos
        </option>

        <template x-for="kos in kosList" :key="kos.id_kos">

          <option
            :value="kos.id_kos"
            x-text="kos.nama_kos">
          </option>

        </template>

      </select>

    </div>


    <!-- NOMOR + TIPE -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

      <div class="form-group">

        <label class="label">
          Nomor Kamar <span class="text-red-500">*</span>
        </label>

        <input
          type="text"
          x-model="form.nomor_kamar"
          class="input"
          maxlength="50"
          required>

      </div>


      <div class="form-group">

        <label class="label">
          Tipe Kamar
        </label>

        <input
          type="text"
          x-model="form.tipe_kamar"
          class="input"
          maxlength="100">

      </div>

    </div>


    <!-- KAPASITAS -->
    <div class="form-group">

      <label class="label">
        Kapasitas <span class="text-red-500">*</span>
      </label>

      <div class="relative">

        <input
          type="number"
          x-model.number="form.kapasitas"
          min="1"
          max="255"
          class="input pr-20"
          required>

        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">
          orang
        </span>

      </div>

    </div>


    <!-- DESKRIPSI -->
    <div class="form-group">

      <label class="label">
        Deskripsi
      </label>

      <textarea
        x-model="form.deskripsi"
        rows="5"
        class="input resize-none"></textarea>

    </div>

    <!-- HARGA KAMAR -->
    <div class="border-t border-slate-200 pt-6">

      <div class="flex items-start justify-between gap-4 mb-4">

        <div>
          <h3 class="font-semibold text-slate-900">
            Harga Kamar
          </h3>

          <p class="mt-1 text-sm text-slate-500">
            Atur harga total kamar berdasarkan jumlah penghuni.
          </p>
        </div>

        <button
          type="button"
          @click="addHarga()"
          class="btn-secondary whitespace-nowrap"
          :disabled="form.harga.length >= Number(form.kapasitas)">

          + Tambah Harga

        </button>

      </div>


      <!-- EMPTY -->
      <div
        x-show="form.harga.length === 0"
        class="rounded-lg border border-dashed border-slate-300 p-6 text-center">

        <p class="text-sm text-slate-500">
          Belum ada harga kamar.
        </p>

        <button
          type="button"
          @click="addHarga()"
          class="btn-primary mt-4">

          + Tambahkan Harga

        </button>

      </div>


      <!-- LIST HARGA -->
      <div
        x-show="form.harga.length > 0"
        class="space-y-3">

        <template
          x-for="(item, index) in form.harga"
          :key="index">

          <div
            class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 items-end rounded-lg border border-slate-200 p-4">

            <!-- JUMLAH ORANG -->
            <div class="form-group">

              <label class="label">
                Jumlah Orang
              </label>

              <select
                x-model.number="item.jumlah_orang"
                class="input">

                <template
                  x-for="jumlah in availableJumlahOrang(index)"
                  :key="jumlah">

                  <option
                    :value="jumlah"
                    x-text="jumlah + ' orang'">
                  </option>

                </template>

              </select>

            </div>


            <!-- HARGA -->
            <div class="form-group">

              <label class="label">
                Harga Total
              </label>

              <div class="relative">

                <span
                  class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                  Rp
                </span>

                <input
                  type="number"
                  x-model.number="item.harga_total"
                  min="0"
                  step="1000"
                  class="input pl-11"
                  placeholder="700000">

              </div>

            </div>


            <!-- HAPUS -->
            <button
              type="button"
              @click="removeHarga(index)"
              class="btn-danger">

              Hapus

            </button>

          </div>

        </template>

      </div>


      <p class="mt-3 text-xs text-slate-400">
        Maksimal konfigurasi harga mengikuti kapasitas kamar:
        <span
          x-text="form.kapasitas + ' orang'">
        </span>.
      </p>

    </div>


    <!-- ACTION -->
    <div class="flex justify-end gap-3 pt-2">

      <a
        href="<?= BASE_URL ?>/pemilik/kamar"
        class="btn-secondary">
        Batal
      </a>

      <button
        type="submit"
        class="btn-primary"
        :disabled="saving">

        <span x-show="!saving">
          Simpan Perubahan
        </span>

        <span x-show="saving" x-cloak>
          Menyimpan...
        </span>

      </button>

    </div>

  </form>


  <!-- NOT FOUND -->
  <div
    x-show="!loadingData && !found"
    x-cloak
    class="card text-center py-12">

    <div class="text-4xl mb-4">
      🔍
    </div>

    <h3 class="font-semibold">
      Kamar tidak ditemukan
    </h3>

    <a
      href="<?= BASE_URL ?>/pemilik/kamar"
      class="btn-primary inline-flex mt-5">
      Kembali
    </a>

  </div>

</div>


<script>
  function editKamarForm() {

    return {

      idKamar: null,

      kosList: [],

      loadingData: true,

      saving: false,

      found: false,

      form: {

        id_kos: '',

        nomor_kamar: '',

        tipe_kamar: '',

        kapasitas: 1,

        deskripsi: '',

        harga: []

      },


      async init() {

        this.idKamar =
          utils.getQuery('id_kamar');

        if (!this.idKamar) {

          this.loadingData = false;

          return;
        }


        try {

          const [
            kosRes,
            kamarRes
          ] = await Promise.all([

            API.get(
              '/pemilik/kamar/kos',
              false
            ),

            API.get(
              '/pemilik/kamar/show?id_kamar=' +
              encodeURIComponent(this.idKamar),
              false
            )

          ]);


          this.kosList =
            kosRes.data || [];


          if (
            kamarRes.success &&
            kamarRes.data
          ) {

            const data =
              kamarRes.data;


            this.form = {

              id_kos: String(data.id_kos),

              nomor_kamar: data.nomor_kamar || '',

              tipe_kamar: data.tipe_kamar || '',

              kapasitas: Number(data.kapasitas) || 1,

              deskripsi: data.deskripsi || '',

              harga: Array.isArray(data.harga) ?
                data.harga.map(item => ({
                  id_harga: Number(item.id_harga),

                  jumlah_orang: Number(item.jumlah_orang),

                  harga_total: Number(item.harga_total)
                })) : []

            };


            this.found = true;

          }

        } catch (error) {

          console.error(
            'Gagal memuat kamar:',
            error
          );

        } finally {

          this.loadingData = false;

        }

      },


      /*
       * Daftar jumlah orang yang belum
       * digunakan oleh baris harga lain.
       */
      availableJumlahOrang(index) {

        const used =
          this.form.harga
          .filter((_, i) => i !== index)
          .map(item =>
            Number(item.jumlah_orang)
          );


        const result = [];


        for (
          let i = 1; i <= Number(this.form.kapasitas); i++
        ) {

          if (!used.includes(i)) {
            result.push(i);
          }

        }


        /*
         * Pastikan nilai yang sedang dipilih
         * tetap tersedia.
         */
        const current =
          Number(
            this.form.harga[index]?.jumlah_orang
          );


        if (
          current >= 1 &&
          current <= Number(this.form.kapasitas) &&
          !result.includes(current)
        ) {

          result.push(current);

          result.sort((a, b) => a - b);

        }


        return result;

      },


      addHarga() {

        if (
          this.form.harga.length >=
          Number(this.form.kapasitas)
        ) {

          Alpine.store('ui').toast(
            'Jumlah harga sudah mencapai kapasitas kamar.',
            'warning'
          );

          return;
        }


        const used =
          this.form.harga.map(item =>
            Number(item.jumlah_orang)
          );


        let jumlah = 1;


        while (
          used.includes(jumlah) &&
          jumlah <= Number(this.form.kapasitas)
        ) {

          jumlah++;

        }


        if (
          jumlah >
          Number(this.form.kapasitas)
        ) {

          Alpine.store('ui').toast(
            'Tidak ada jumlah orang yang tersedia.',
            'warning'
          );

          return;

        }


        this.form.harga.push({

          jumlah_orang: jumlah,

          harga_total: ''

        });

      },


      removeHarga(index) {

        this.form.harga.splice(
          index,
          1
        );

      },


      async submit() {

        /*
         * Validasi dasar.
         */
        if (
          !this.form.id_kos ||
          !this.form.nomor_kamar ||
          !this.form.kapasitas
        ) {

          Alpine.store('ui').toast(
            'Lengkapi data kamar terlebih dahulu.',
            'warning'
          );

          return;

        }


        /*
         * Validasi harga.
         */
        for (
          const item of this.form.harga
        ) {

          const jumlah =
            Number(item.jumlah_orang);

          const harga =
            Number(item.harga_total);


          if (
            jumlah < 1 ||
            jumlah > Number(this.form.kapasitas)
          ) {

            Alpine.store('ui').toast(
              'Jumlah orang pada harga tidak valid.',
              'warning'
            );

            return;

          }


          if (
            item.harga_total === '' ||
            !Number.isFinite(harga) ||
            harga < 0
          ) {

            Alpine.store('ui').toast(
              'Harga kamar harus berupa angka yang valid.',
              'warning'
            );

            return;

          }

        }


        this.saving = true;


        try {

          await API.put(
            '/pemilik/kamar', {

              id_kamar: this.idKamar,

              id_kos: this.form.id_kos,

              nomor_kamar: this.form.nomor_kamar,

              tipe_kamar: this.form.tipe_kamar,

              kapasitas: this.form.kapasitas,

              deskripsi: this.form.deskripsi,

              harga: this.form.harga.map(item => ({

                jumlah_orang: Number(item.jumlah_orang),

                harga_total: Number(item.harga_total)

              }))

            }
          );


          Alpine.store('ui').toast(
            'Kamar dan harga berhasil diperbarui.',
            'success'
          );


          setTimeout(() => {

            window.location.href =
              BASE_URL +
              '/pemilik/kamar';

          }, 500);


        } catch (error) {

          console.error(
            'Gagal menyimpan kamar:',
            error
          );

        } finally {

          this.saving = false;

        }

      }

    };

  }
</script>