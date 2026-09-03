<?php $mode = ($mode ?? 'single') === 'bulk' ? 'bulk' : 'single'; ?>

<div
  x-data="kamarForm('<?= $mode ?>')"
  x-init="init()"
  class="max-w-3xl mx-auto space-y-6">

  <!-- HEADER -->
  <div>
    <a
      href="<?= BASE_URL ?>/pemilik/kamar"
      class="text-sm text-primary hover:underline">
      ← Kembali ke kelola kamar
    </a>

    <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">
      <?= $mode === 'bulk' ? 'Tambah Banyak Kamar' : 'Tambah Satu Kamar' ?>
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      <?= $mode === 'bulk'
        ? 'Buat beberapa unit kamar sekaligus dengan nomor berurutan.'
        : 'Tambahkan satu unit kamar ke salah satu kos Anda.' ?>
    </p>
  </div>

  <!-- FORM YANG SAMA UNTUK SINGLE & BULK -->
  <form
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm space-y-6">

    <!-- KOS -->
    <div class="form-group">
      <label class="label">
        Kos <span class="text-red-500">*</span>
      </label>

      <select
        data-help="help-kamar-form-kos" data-onboarding="kamar-field-kos"
        x-model="form.id_kos"
        @change="loadTipe()"
        class="input"
        required>
        <option value="">Pilih kos</option>

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

    <!-- TIPE KAMAR -->
    <div class="form-group">
      <label class="label">
        Tipe Kamar <span class="text-red-500">*</span>
      </label>

      <select
        data-help="help-kamar-form-type" data-onboarding="kamar-field-tipe"
        x-model="form.id_tipe_kamar"
        @change="loadTypeFacilities()"
        class="input"
        required
        :disabled="!form.id_kos">
        <option value="">Pilih tipe kamar</option>

        <template x-for="item in tipeList" :key="item.id_tipe_kamar">
          <option
            :value="item.id_tipe_kamar"
            x-text="item.nama_tipe + ' (' + item.kapasitas + ' orang)'">
          </option>
        </template>
      </select>

      <p
        x-show="form.id_kos && tipeList.length === 0"
        class="mt-1 text-xs text-amber-600">
        Belum ada tipe kamar untuk kos ini. Buat tipe kamar terlebih dahulu.
      </p>
    </div>

    <!-- NOMOR SINGLE -->
    <div
      data-help="help-kamar-form-number" data-onboarding="kamar-field-nomor-single"
      x-show="mode === 'single'"
      x-cloak
      class="form-group">
      <label class="label">
        Nomor Kamar <span class="text-red-500">*</span>
      </label>

      <input
        type="text"
        x-model="form.nomor_kamar"
        class="input"
        placeholder="Contoh: 101 atau A01"
        maxlength="50"
        :required="mode === 'single'">
    </div>

    <!-- NOMOR BULK -->
    <div
      data-help="help-kamar-form-number" data-onboarding="kamar-field-nomor-bulk"
      x-show="mode === 'bulk'"
      x-cloak
      class="grid grid-cols-1 sm:grid-cols-2 gap-5">

      <div class="form-group">
        <label class="label">
          Nomor Awal <span class="text-red-500">*</span>
        </label>

        <input
          type="text"
          x-model="form.nomor_awal"
          class="input"
          inputmode="numeric"
          pattern="[0-9]+"
          placeholder="Contoh: 101"
          :required="mode === 'bulk'">

        <p class="mt-1 text-xs text-slate-500">
          Harus berupa angka. Contoh 101.
        </p>
      </div>

      <div class="form-group">
        <label class="label">
          Jumlah Unit <span class="text-red-500">*</span>
        </label>

        <input
          type="number"
          x-model.number="form.jumlah"
          min="1"
          max="500"
          class="input"
          placeholder="10"
          :required="mode === 'bulk'">

        <p class="mt-1 text-xs text-slate-500">
          Maksimal 500 unit sekali proses.
        </p>
      </div>
    </div>

    <!-- PREVIEW -->
    <div
      x-show="mode === 'bulk' && form.nomor_awal && Number(form.jumlah) > 0"
      x-cloak
      class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
      Akan dibuat
      <strong x-text="form.jumlah"></strong>
      kamar dari nomor
      <strong x-text="form.nomor_awal"></strong>
      sampai
      <strong x-text="bulkEndNumber"></strong>.
    </div>

    <!-- FASILITAS TIPE KAMAR -->
    <div
      x-show="form.id_tipe_kamar"
      x-cloak
      class="rounded-xl border border-slate-200 bg-slate-50 p-4">

      <div class="flex items-start justify-between gap-4">
        <div>
          <label class="label">Fasilitas Kamar</label>
          <p class="mt-1 text-xs text-slate-500">
            Fasilitas mengikuti tipe kamar yang dipilih dan dikelola pada halaman Tipe Kamar.
          </p>
        </div>

        <a
          :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + form.id_tipe_kamar"
          class="text-xs font-medium text-primary hover:underline whitespace-nowrap">
          Kelola tipe
        </a>
      </div>

      <div
        x-show="facilityLoading"
        class="mt-3 text-sm text-slate-500">
        Memuat fasilitas...
      </div>

      <div
        x-show="!facilityLoading && typeFacilities.length"
        class="mt-3 flex flex-wrap gap-2">
        <template x-for="item in typeFacilities" :key="item.id_fasilitas">
          <span
            class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200"
            x-text="item.nama_fasilitas">
          </span>
        </template>
      </div>

      <p
        x-show="!facilityLoading && !typeFacilities.length"
        class="mt-3 text-sm text-slate-500">
        Belum ada fasilitas kamar pada tipe ini.
      </p>
    </div>

    <!-- ACTION -->
    <div class="flex justify-end gap-3 border-t border-slate-200 pt-5">
      <a
        href="<?= BASE_URL ?>/pemilik/kamar"
        class="btn-secondary">
        Batal
      </a>

      <button data-help="help-kamar-form-save"
        type="submit"
        data-onboarding="kamar-save"
        class="btn-primary"
        :disabled="loading">
        <span
          x-show="!loading"
          x-text="mode === 'bulk' ? 'Buat Kamar' : 'Simpan Kamar'"></span>
        <span x-show="loading" x-cloak>
          Memproses...
        </span>
      </button>
    </div>
  </form>
</div>

<script>
  function kamarForm(mode = 'single') {
    return {
      mode,
      kosList: [],
      tipeList: [],
      typeFacilities: [],
      facilityLoading: false,
      loading: false,

      form: {
        id_kos: '',
        id_tipe_kamar: '',
        nomor_kamar: '',
        nomor_awal: '',
        jumlah: 1
      },

      get bulkEndNumber() {
        if (!/^\d+$/.test(String(this.form.nomor_awal || ''))) return '-';
        const start = Number(this.form.nomor_awal);
        const jumlah = Number(this.form.jumlah) || 0;
        return jumlah > 0 ? start + jumlah - 1 : '-';
      },

      async init() {
        try {
          const res = await API.get('/pemilik/kamar/kos', false);
          this.kosList = res.data || [];
        } catch (error) {
          console.error('Gagal memuat kos:', error);
          this.kosList = [];
        }
      },

      async loadTipe() {
        this.form.id_tipe_kamar = '';
        this.typeFacilities = [];
        this.tipeList = [];

        if (!this.form.id_kos) return;

        try {
          const res = await API.get(
            '/pemilik/tipe-kamar?id_kos=' + encodeURIComponent(this.form.id_kos),
            false
          );
          this.tipeList = res.data || [];
        } catch (error) {
          console.error('Gagal memuat tipe kamar:', error);
        }
      },

      async loadTypeFacilities() {
        this.typeFacilities = [];
        if (!this.form.id_tipe_kamar) return;

        this.facilityLoading = true;
        try {
          const res = await API.get(
            '/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.form.id_tipe_kamar),
            false
          );
          this.typeFacilities = res.data?.fasilitas || [];
        } catch (error) {
          console.error('Gagal memuat fasilitas tipe kamar:', error);
        } finally {
          this.facilityLoading = false;
        }
      },

      async submit() {
        if (!this.form.id_kos || !this.form.id_tipe_kamar) {
          Alpine.store('ui').toast('Pilih kos dan tipe kamar terlebih dahulu.', 'warning');
          return;
        }

        if (this.mode === 'single' && !String(this.form.nomor_kamar).trim()) {
          Alpine.store('ui').toast('Nomor kamar wajib diisi.', 'warning');
          return;
        }

        if (this.mode === 'bulk') {
          if (!/^\d+$/.test(String(this.form.nomor_awal || ''))) {
            Alpine.store('ui').toast('Nomor awal harus berupa angka.', 'warning');
            return;
          }
          if (!Number.isInteger(Number(this.form.jumlah)) || Number(this.form.jumlah) < 1 || Number(this.form.jumlah) > 500) {
            Alpine.store('ui').toast('Jumlah unit harus antara 1 sampai 500.', 'warning');
            return;
          }
        }

        this.loading = true;

        try {
          if (this.mode === 'bulk') {
            await API.post('/pemilik/kamar/bulk', {
              id_tipe_kamar: Number(this.form.id_tipe_kamar),
              nomor_awal: String(this.form.nomor_awal),
              jumlah: Number(this.form.jumlah)
            });
          } else {
            await API.post('/pemilik/kamar', {
              id_tipe_kamar: Number(this.form.id_tipe_kamar),
              nomor_kamar: String(this.form.nomor_kamar).trim()
            });
          }

          if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
            window.location.href = BASE_URL + '/pemilik/kamar';
          } else {
            window.location.href = BASE_URL + '/pemilik/kamar';
          }
        } catch (error) {
          console.error('Gagal menyimpan kamar:', error);
        } finally {
          this.loading = false;
        }
      }
    };
  }
</script>
