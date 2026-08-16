<div
  x-data="penghuniForm()"
  x-init="init()"
  class="max-w-3xl mx-auto space-y-6">

  <!-- HEADER -->
  <div>

    <a
      href="<?= BASE_URL ?>/pemilik/penghuni"
      class="text-sm text-primary hover:underline">
      ← Kembali ke penghuni
    </a>

    <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">
      Tambah Penghuni
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Tambahkan penghuni baru ke salah satu kamar kos Anda.
    </p>

  </div>


  <!-- FORM -->
  <form
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm space-y-6">


    <!-- KAMAR -->
    <div class="form-group">

      <label class="label">
        Kamar <span class="text-red-500">*</span>
      </label>

      <select
        x-model="form.id_kamar"
        class="input"
        required>

        <option value="">
          Pilih kamar
        </option>

        <template
          x-for="kamar in kamarList"
          :key="kamar.id_kamar">

          <option
            :value="kamar.id_kamar"
            x-text="
              kamar.nama_kos +
              ' - Kamar ' +
              kamar.nomor_kamar +
              ' (' +
              kamar.jumlah_penghuni +
              '/' +
              kamar.kapasitas +
              ')'
            ">
          </option>

        </template>

      </select>

      <p
        x-show="kamarList.length === 0"
        class="mt-1 text-xs text-red-500">
        Belum ada kamar yang dapat digunakan.
      </p>

      <p class="mt-1 text-xs text-slate-500">
        Angka menunjukkan jumlah penghuni aktif dibandingkan kapasitas kamar.
      </p>

    </div>


    <!-- NAMA -->
    <div class="form-group">

      <label class="label">
        Nama Lengkap <span class="text-red-500">*</span>
      </label>

      <input
        type="text"
        x-model="form.nama"
        class="input"
        placeholder="Masukkan nama lengkap"
        maxlength="150"
        required>

    </div>


    <!-- HP + NIK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

      <div class="form-group">

        <label class="label">
          Nomor HP
        </label>

        <input
          type="tel"
          x-model="form.no_hp"
          class="input"
          placeholder="Contoh: 081234567890"
          maxlength="30">

      </div>


      <div class="form-group">

        <label class="label">
          NIK
        </label>

        <input
          type="text"
          x-model="form.nik"
          class="input"
          placeholder="Masukkan NIK"
          maxlength="30">

      </div>

    </div>


    <!-- TANGGAL MASUK -->
    <div class="form-group">

      <label class="label">
        Tanggal Masuk <span class="text-red-500">*</span>
      </label>

      <input
        type="date"
        x-model="form.tanggal_masuk"
        class="input"
        required>

      <p class="mt-1 text-xs text-slate-500">
        Tanggal mulai penghuni menempati kamar.
      </p>

    </div>


    <!-- INFO STATUS -->
    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">

      <div class="flex gap-3">

        <div class="text-blue-600">
          ℹ️
        </div>

        <div>

          <p class="text-sm font-medium text-blue-900">
            Status penghuni otomatis aktif
          </p>

          <p class="mt-1 text-xs text-blue-700">
            Setelah penghuni ditambahkan, statusnya otomatis menjadi
            <strong>Aktif</strong> dan status kamar akan disinkronkan
            berdasarkan jumlah penghuni.
          </p>

        </div>

      </div>

    </div>


    <!-- ACTION -->
    <div class="flex justify-end gap-3 pt-2">

      <a
        href="<?= BASE_URL ?>/pemilik/penghuni"
        class="btn-secondary">
        Batal
      </a>

      <button
        type="submit"
        class="btn-primary"
        :disabled="loading">

        <span x-show="!loading">
          Simpan Penghuni
        </span>

        <span x-show="loading" x-cloak>
          Menyimpan...
        </span>

      </button>

    </div>

  </form>

</div>


<script>
  function penghuniForm() {

    return {

      kamarList: [],

      loading: false,

      form: {
        id_kamar: '',
        nama: '',
        no_hp: '',
        nik: '',
        tanggal_masuk: ''
      },


      async init() {

        this.form.tanggal_masuk =
          new Date().toISOString().split('T')[0];

        await this.loadKamar();

      },


      async loadKamar() {

        try {

          const res = await API.get(
            '/pemilik/penghuni/kamar',
            false
          );

          this.kamarList =
            res.data || [];

        } catch (error) {

          console.error(
            'Gagal memuat kamar:',
            error
          );

          this.kamarList = [];

        }

      },


      async submit() {

        if (
          !this.form.id_kamar ||
          !this.form.nama.trim() ||
          !this.form.tanggal_masuk
        ) {

          Alpine.store('ui').toast(
            'Lengkapi data penghuni terlebih dahulu.',
            'warning'
          );

          return;

        }

        this.loading = true;

        try {

          await API.post(
            '/pemilik/penghuni',
            this.form
          );

          setTimeout(() => {

            window.location.href =
              BASE_URL + '/pemilik/penghuni';

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