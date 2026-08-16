<div
  x-data="kamarForm()"
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
      Tambah Kamar
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Tambahkan kamar baru ke salah satu kos Anda.
    </p>

  </div>


  <!-- FORM -->
  <form
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

      <p
        x-show="kosList.length === 0"
        class="mt-1 text-xs text-red-500">
        Anda belum memiliki kos.
      </p>

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
          placeholder="Contoh: A01"
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
          placeholder="Contoh: Standard"
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

        <span
          class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">
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
        class="input resize-none"
        placeholder="Informasi tambahan mengenai kamar..."></textarea>

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
        :disabled="loading">

        <span x-show="!loading">
          Simpan Kamar
        </span>

        <span x-show="loading" x-cloak>
          Menyimpan...
        </span>

      </button>

    </div>

  </form>

</div>


<script>
  function kamarForm() {

    return {

      kosList: [],

      loading: false,

      form: {
        id_kos: '',
        nomor_kamar: '',
        tipe_kamar: '',
        kapasitas: 1,
        deskripsi: ''
      },


      async init() {

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


      async submit() {

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

        this.loading = true;

        try {

          await API.post(
            '/pemilik/kamar',
            this.form
          );

          setTimeout(() => {

            window.location.href =
              BASE_URL + '/pemilik/kamar';

          }, 500);

        } catch (error) {

          console.error(error);

        } finally {

          this.loading = false;

        }

      }

    };
  }
</script>