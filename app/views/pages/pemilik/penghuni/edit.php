<div
  x-data="editPenghuniForm()"
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
      Edit Penghuni
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Perbarui informasi penghuni.
    </p>

  </div>


  <!-- LOADING -->
  <div
    x-show="loadingData"
    class="card text-center py-12 text-sm text-slate-500">

    Memuat data penghuni...

  </div>


  <!-- FORM -->
  <form
    x-show="!loadingData && found"
    x-cloak
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

      <p class="mt-1 text-xs text-slate-500">
        Jika kamar diubah, sistem akan mengecek kapasitas
        kamar tujuan secara otomatis.
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

    </div>


    <!-- STATUS -->
    <div
      class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">

      <div class="flex items-center justify-between gap-4">

        <div>

          <p class="text-sm font-medium text-slate-800">
            Status Penghuni
          </p>

          <p class="mt-1 text-xs text-slate-500">
            Status dikelola oleh sistem melalui proses masuk dan keluar.
          </p>

        </div>

        <span
          x-show="currentStatus === 'aktif'"
          class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
          Aktif
        </span>

        <span
          x-show="currentStatus === 'keluar'"
          class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
          Keluar
        </span>

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

    <h3 class="font-semibold text-slate-900">
      Penghuni tidak ditemukan
    </h3>

    <p class="mt-1 text-sm text-slate-500">
      Data penghuni tidak ditemukan atau bukan milik Anda.
    </p>

    <a
      href="<?= BASE_URL ?>/pemilik/penghuni"
      class="btn-primary inline-flex mt-5">
      Kembali
    </a>

  </div>

</div>


<script>
  function editPenghuniForm() {

    return {

      idPenghuni: null,

      kamarList: [],

      loadingData: true,

      saving: false,

      found: false,

      currentStatus: '',

      form: {
        id_kamar: '',
        nama: '',
        no_hp: '',
        nik: '',
        tanggal_masuk: ''
      },


      async init() {

        this.idPenghuni = utils.getQuery('id_penghuni');


        if (!this.idPenghuni) {

          this.loadingData = false;

          return;

        }


        try {

          const [
            kamarRes,
            penghuniRes
          ] = await Promise.all([

            API.get(
              '/pemilik/penghuni/kamar',
              false
            ),

            API.get(
              '/pemilik/penghuni/show?id_penghuni=' +
              encodeURIComponent(this.idPenghuni),
              false
            )

          ]);


          this.kamarList =
            kamarRes.data || [];


          if (
            penghuniRes.success &&
            penghuniRes.data
          ) {

            const data =
              penghuniRes.data;

            this.form = {

              id_kamar: String(data.id_kamar),

              nama: data.nama || '',

              no_hp: data.no_hp || '',

              nik: data.nik || '',

              tanggal_masuk: data.tanggal_masuk || ''

            };

            this.currentStatus =
              data.status || '';

            this.found = true;

          }


        } catch (error) {

          console.error(
            'Gagal memuat penghuni:',
            error
          );

        } finally {

          this.loadingData = false;

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

        this.saving = true;

        try {

          await API.put(
            '/pemilik/penghuni', {
              id_penghuni: this.idPenghuni,

              ...this.form
            }
          );

          setTimeout(() => {

            window.location.href =
              BASE_URL + '/pemilik/penghuni';

          }, 500);

        } catch (error) {

          console.error(error);

        } finally {

          this.saving = false;

        }

      }

    };

  }
</script>