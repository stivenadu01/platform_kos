<div
  x-data="editKamarForm()"
  x-init="init()"
  class="owner-page mx-auto max-w-3xl">

  <!-- HEADER -->
  <div class="owner-form-heading">

    <a
      :href="returnUrl"
      @click.prevent="utils.goBack($el.href)"
      class="owner-back-link">
      <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali
    </a>

    <h2 class="owner-title">
      Edit Kamar
    </h2>

    <p class="owner-subtitle">
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
        class="select"
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

        <select x-model="form.id_tipe_kamar" class="select" required>
          <option value="">Pilih tipe kamar</option>
          <template x-for="item in tipeList" :key="item.id_tipe_kamar">
            <option :value="item.id_tipe_kamar" x-text="item.nama_tipe + ' (' + item.kapasitas + ' orang)'"></option>
          </template>
        </select>

      </div>

    </div>


    <!-- ACTION -->
    <div class="owner-form-actions flex justify-end gap-3 border-t border-slate-200 pt-5">

      <a
        :href="returnUrl"
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
      :href="returnUrl"
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
      tipeList: [],

      loadingData: true,

      saving: false,

      found: false,

      form: {

        id_kos: '',

        nomor_kamar: '',

        id_tipe_kamar: ''

      },

      get returnUrl() {
        const fallback = this.form.id_tipe_kamar
          ? '/pemilik/kamar/kelola?id_tipe_kamar=' + encodeURIComponent(this.form.id_tipe_kamar)
          : '/pemilik/kamar';
        return BASE_URL + fallback;
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
            kamarRes,
            tipeRes
          ] = await Promise.all([

            API.get(
              '/pemilik/kamar/kos',
              false
            ),

            API.get(
              '/pemilik/kamar/show?id_kamar=' +
              encodeURIComponent(this.idKamar),
              false
            ),

            API.get(
              '/pemilik/tipe-kamar',
              false
            )

          ]);


          this.kosList =
            kosRes.data || [];

          this.tipeList =
            tipeRes.data || [];


          if (
            kamarRes.success &&
            kamarRes.data
          ) {

            const data =
              kamarRes.data;


            this.form = {

              id_kos: String(data.id_kos),

              nomor_kamar: data.nomor_kamar || '',

              id_tipe_kamar: String(data.id_tipe_kamar || '')

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


      async submit() {

        /*
         * Validasi dasar.
         */
        if (
          !this.form.nomor_kamar ||
          !this.form.id_tipe_kamar
        ) {

          Alpine.store('ui').toast(
            'Lengkapi data kamar terlebih dahulu.',
            'warning'
          );

          return;

        }


        this.saving = true;


        try {

          await API.put(
            '/pemilik/kamar', {

              id_kamar: this.idKamar,

              nomor_kamar: this.form.nomor_kamar,

              id_tipe_kamar: this.form.id_tipe_kamar

            }
          );


          Alpine.store('ui').toast(
            'Data kamar berhasil diperbarui.',
            'success'
          );


          setTimeout(() => {

            window.location.href = this.returnUrl;

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
