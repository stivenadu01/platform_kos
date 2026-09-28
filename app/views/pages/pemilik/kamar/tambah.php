<?php $mode = ($mode ?? 'single') === 'bulk' ? 'bulk' : 'single'; ?>

<div
  x-data="kamarForm('<?= $mode ?>')"
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
      <?= $mode === 'bulk' ? 'Tambah Banyak Kamar' : 'Tambah Satu Kamar' ?>
    </h2>

    <p class="owner-subtitle">
      <?= $mode === 'bulk'
        ? 'Buat beberapa unit kamar sekaligus dengan nomor berurutan.'
        : 'Tambahkan satu unit kamar ke salah satu kos Anda.' ?>
    </p>
  </div>

  <!-- FORM YANG SAMA UNTUK SINGLE & BULK -->
  <form
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm space-y-6">

    <!-- KONTEKS KOS & TIPE (otomatis dari kartu yang dipilih) -->
    <div x-show="contextLocked" x-cloak class="owner-context-panel">
      <div class="owner-context-header">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-white shadow-sm">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path></svg>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-primary">Kamar akan ditambahkan ke</p>
          <h3 class="mt-0.5 font-bold text-slate-900" x-text="contextType?.nama_tipe || '-'"></h3>
        </div>
      </div>
      <div class="owner-context-grid">
        <div class="owner-context-item">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kos</p>
          <p class="mt-1 text-sm font-semibold text-slate-800" x-text="contextType?.nama_kos || '-'"></p>
        </div>
        <div class="owner-context-item">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tipe & kapasitas</p>
          <p class="mt-1 text-sm font-semibold text-slate-800"><span x-text="contextType?.nama_tipe || '-'"></span><span class="font-normal text-slate-500" x-text="contextType ? ' · ' + contextType.kapasitas + ' orang' : ''"></span></p>
        </div>
      </div>
      <p class="px-4 pb-4 text-xs leading-5 text-slate-500">Kos dan tipe kamar sudah ditentukan dari halaman sebelumnya sehingga tidak perlu dipilih kembali.</p>
    </div>

    <!-- PILIH KOS (fallback jika halaman dibuka langsung) -->
    <div x-show="!contextLocked" x-cloak class="form-group">
      <label class="label">
        Kos <span class="text-red-500">*</span>
      </label>

      <select
        data-help="help-kamar-form-kos"
        x-model="form.id_kos"
        @change="loadTipe()"
        class="select"
        required
        :disabled="contextLocked">
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

    <!-- PILIH TIPE KAMAR (fallback jika halaman dibuka langsung) -->
    <div x-show="!contextLocked" x-cloak class="form-group">
      <label class="label">
        Tipe Kamar <span class="text-red-500">*</span>
      </label>

      <select
        data-help="help-kamar-form-type"
        x-model="form.id_tipe_kamar"
        @change="loadTypeFacilities()"
        class="select"
        required
        :disabled="contextLocked || !form.id_kos">
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
      data-help="help-kamar-form-number"
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
      data-help="help-kamar-form-number"
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
          class="input-number"
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
    <div class="owner-form-actions flex justify-end gap-3 border-t border-slate-200 pt-5">
      <a
        :href="returnUrl"
        class="btn-secondary">
        Batal
      </a>

      <button data-help="help-kamar-form-save"
        type="submit"

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
      contextLocked: false,
      contextTypeId: new URLSearchParams(window.location.search).get('id_tipe_kamar') || '',
      contextType: null,

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
          const kosRes = await API.get('/pemilik/kamar/kos', false);
          this.kosList = kosRes.data || [];

          if (this.contextTypeId) {
            const typeRes = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.contextTypeId), false);
            const type = typeRes.data;
            if (!type) throw new Error('Tipe kamar tidak ditemukan.');
            this.contextType = type;
            this.form.id_kos = String(type.id_kos);
            await this.loadTipe(false);
            this.form.id_tipe_kamar = String(type.id_tipe_kamar);
            this.contextLocked = true;
            await this.loadTypeFacilities();
          }
        } catch (error) {
          console.error('Gagal memuat kos:', error);
          Alpine.store('ui').toast('Tipe kamar tidak ditemukan atau bukan milik Anda.', 'error');
          if (this.contextTypeId) window.location.replace(BASE_URL + '/pemilik/kamar');
        }
      },

      get returnUrl() {
        const fallback = this.contextTypeId
          ? '/pemilik/kamar/kelola?id_tipe_kamar=' + encodeURIComponent(this.contextTypeId)
          : '/pemilik/kamar';
        return BASE_URL + fallback;
      },

      async loadTipe(resetSelection = true) {
        if (resetSelection) this.form.id_tipe_kamar = '';
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

          let target = this.contextTypeId
            ? BASE_URL + '/pemilik/kamar/kelola?id_tipe_kamar=' + encodeURIComponent(this.contextTypeId)
            : BASE_URL + '/pemilik/kamar';
          if (localStorage.getItem('betakos_owner_onboarding_active_v4') === '1') {
            target += (target.includes('?') ? '&' : '?') + 'onboarding=1';
          }
          window.location.href = target;
        } catch (error) {
          console.error('Gagal menyimpan kamar:', error);
        } finally {
          this.loading = false;
        }
      }
    };
  }
</script>
