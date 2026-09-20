<div
  x-data="penghuniFormPage()"
  x-init="init()"
  class="space-y-6">

  <div>
    <a
      :href="backUrl"
      @click.prevent="utils.goBack($el.href)"
      class="owner-back-link">
      <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali
    </a>

    <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900"
      x-text="mode === 'edit' ? 'Edit Penghuni' : 'Tambah Penghuni'"></h2>

    <p class="mt-1 text-sm text-slate-500">
      <span x-show="mode === 'tambah'">
        Tambahkan penghuni baru. Sistem akan otomatis membuat atau menyesuaikan tagihan kamar.
      </span>
      <span x-show="mode === 'edit'">
        Perubahan pada halaman ini hanya untuk data identitas penghuni.
      </span>
    </p>
  </div>

  <div x-show="mode === 'tambah' && isTypeContext" x-cloak class="owner-context-panel">
    <div class="owner-context-header">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('info', 'h-5 w-5') ?></span>
      <div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-wider text-primary">Tujuan penambahan penghuni</p><h3 class="mt-1 truncate font-bold text-slate-900" x-text="contextInfo ? contextInfo.nama_kos + ' · ' + (contextInfo.nama_tipe || contextInfo.tipe_kamar) : 'Memuat kos dan tipe kamar...'"></h3><p class="mt-1 text-xs text-slate-500" x-text="contextInfo ? 'Pilih salah satu unit pada tipe ini. Kapasitas maksimal ' + contextInfo.kapasitas + ' orang per kamar.' : ''"></p></div>
    </div>
  </div>

  <form @submit.prevent="submit" class="card border border-slate-200 shadow-sm space-y-6">

    <nav x-show="mode === 'tambah'" class="grid grid-cols-2 gap-2 border-b border-slate-200 pb-5" aria-label="Tahapan tambah penghuni">
      <template x-for="(label, index) in ['Verifikasi NIK', 'Data & Kamar']" :key="label"><div class="flex items-center gap-2 rounded-xl px-3 py-2" :class="step === index + 1 ? 'bg-primary-soft text-primary' : step > index + 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400'"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="step === index + 1 ? 'bg-primary text-white' : step > index + 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'" x-text="step > index + 1 ? '✓' : index + 1"></span><span class="text-xs font-semibold sm:text-sm" x-text="label"></span></div></template>
    </nav>

    <div
      x-show="mode === 'tambah' && step === 1"
      x-cloak
      class="space-y-5">

      <div data-help="help-penghuni-nik" class="form-group">
        <label class="label">
          NIK Penghuni <span class="text-red-500">*</span>
        </label>

        <input
          type="text"
          x-model="form.nik"
          class="input"
          maxlength="16"
          inputmode="numeric"
          pattern="[0-9]{16}"
          placeholder="16 digit NIK"
          required>

        <p class="mt-2 text-sm text-slate-500">
          Masukkan NIK untuk mencari data akun mahasiswa yang sudah terdaftar.
        </p>
      </div>

      <div class="flex justify-end border-t border-slate-200 pt-5">
        <button
          type="button"
          @click="nextStep()"
          class="btn-primary"
          :disabled="lookingUp">
          <span x-show="!lookingUp">Selanjutnya</span>
          <span x-show="lookingUp" x-cloak>Mencari...</span>
        </button>
      </div>
    </div>

    <div
      x-show="mode === 'edit' || step === 2"
      x-cloak
      data-help="help-penghuni-edit-data"
      class="grid grid-cols-1 md:grid-cols-2 gap-5">

      <div data-help="help-penghuni-room" class="form-group md:col-span-2" x-show="mode === 'tambah'">
        <label class="label">Kamar <span class="text-red-500">*</span></label>

        <select
          x-model="form.id_kamar"
          @change="updateSelectedKamar()"
          class="select"
          :required="mode === 'tambah'"
          :disabled="mode === 'edit'">

          <option value="">Pilih kamar</option>

          <template x-for="item in kamarList" :key="item.id_kamar">
            <option
              :value="item.id_kamar"
              x-text="isTypeContext ? `Kamar ${item.nomor_kamar} (${item.jumlah_penghuni}/${item.kapasitas} orang)` : `${item.nama_kos} — ${item.tipe_kamar} — Kamar ${item.nomor_kamar} (${item.jumlah_penghuni}/${item.kapasitas} orang)`">
            </option>
          </template>

        </select>

        <div x-show="isTypeContext && kamarList.length === 0" x-cloak class="mt-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Tidak ada kamar dengan kapasitas tersisa pada tipe ini. Periksa unit kamar atau penghuni aktif terlebih dahulu.</div>

        <div
          x-show="selectedKamar"
          x-cloak
          class="mt-2 rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-800">

          <div>
            Kapasitas:
            <strong x-text="selectedKamar?.kapasitas || 0"></strong> orang
          </div>

          <div>
            Penghuni aktif:
            <strong x-text="selectedKamar?.jumlah_penghuni || 0"></strong> orang
          </div>

          <div class="mt-1">
            Setelah penghuni ditambahkan:
            <strong x-text="(Number(selectedKamar?.jumlah_penghuni || 0) + 1)"></strong> orang
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="label">
          Nama Lengkap <span class="text-red-500">*</span>
        </label>

        <input
          type="text"
          x-model="form.nama"
          class="input"
          maxlength="150"
          required
          :disabled="matchedUser"
          :class="matchedUser ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''"
          placeholder="Nama penghuni">
      </div>

      <div class="form-group">
        <label class="label">No. HP</label>

        <input
          type="text"
          x-model="form.no_hp"
          class="input"
          maxlength="30"
          :disabled="matchedUser"
          :class="matchedUser ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''"
          placeholder="08xxxxxxxxxx">
      </div>

      <div class="form-group" x-show="mode === 'edit'">
        <label class="label">NIK</label>

        <input
          type="text"
          x-model="form.nik"
          class="input"
          maxlength="16"
          inputmode="numeric"
          :disabled="matchedUser"
          :class="matchedUser ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''"
          placeholder="16 digit NIK">

        <p
          x-show="matchedUser"
          x-cloak
          class="mt-2 text-xs text-slate-500">
          Data nama, NIK, dan nomor HP berasal dari akun penghuni dan tidak dapat diubah oleh pemilik kos.
        </p>
      </div>

      <div x-show="mode === 'tambah'" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIK terverifikasi</p>
        <div class="mt-2 flex items-center gap-2 font-semibold text-slate-900"><?= masterIconSvg('user-check', 'h-4 w-4 text-emerald-600') ?><span x-text="form.nik"></span></div>
        <p class="mt-1 text-xs text-slate-500">Gunakan tombol “Ubah NIK” jika nomor ini tidak sesuai.</p>
      </div>

      <div data-help="help-penghuni-date" class="form-group" x-show="mode === 'tambah'">
        <label class="label">
          Tanggal Masuk <span class="text-red-500">*</span>
        </label>

        <input
          type="date"
          x-model="form.tanggal_masuk"
          class="input-date"
          :required="mode === 'tambah'">
      </div>

      <div
        x-show="mode === 'edit'"
        class="md:col-span-2 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">

        Kamar dan tanggal masuk tidak diubah dari halaman edit karena keduanya
        sudah menjadi dasar perhitungan tagihan. Jika terjadi pindah kamar atau
        perubahan tanggal masuk, kita akan buat alur khusus agar histori tagihan
        tetap aman.

      </div>

    </div>

    <div
      x-show="mode === 'edit' || step === 2"
      x-cloak
      class="flex justify-end gap-3 border-t border-slate-200 pt-5">

      <button x-show="mode === 'tambah'" type="button" @click="backToNik()" class="btn-secondary">← Ubah NIK</button>

      <a
        :href="backUrl"
        class="btn-secondary">
        Batal
      </a>

      <button
        data-help="help-penghuni-save"
        type="submit"
        class="btn-primary"
        :disabled="saving">

        <span x-show="!saving"
          x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Penghuni'"></span>

        <span x-show="saving">
          Menyimpan...
        </span>

      </button>

    </div>

  </form>
</div>

<script>
  function penghuniFormPage() {
    return {
      mode: <?= json_encode_safe($mode ?? 'tambah') ?>,
      idPenghuni: utils.getQuery('id_penghuni') || '',
      idKos: utils.getQuery('id_kos') || '',
      idTipeKamar: utils.getQuery('id_tipe_kamar') || '',
      context: utils.getQuery('context') || '',

      kamarList: [],
      selectedKamar: null,
      contextInfo: null,

      get isTypeContext() {
        return this.context === 'tipe' && !!this.idKos && !!this.idTipeKamar;
      },

      get backUrl() {
        if (!this.isTypeContext) return BASE_URL + '/pemilik/penghuni';
        const params = new URLSearchParams({ id_kos: this.idKos, id_tipe_kamar: this.idTipeKamar, context: 'tipe' });
        return BASE_URL + '/pemilik/penghuni?' + params.toString();
      },

      saving: false,
      lookingUp: false,
      step: <?= ($mode ?? 'tambah') === 'edit' ? 2 : 1 ?>,
      matchedUser: false,

      form: {
        id_kamar: '',
        nama: '',
        no_hp: '',
        nik: '',
        tanggal_masuk: ''
      },

      async init() {
        this.setToday();

        if (this.mode === 'tambah') {
          await this.loadKamar();
        } else {
          await this.loadEdit();
        }
      },

      setToday() {
        const today = new Date();

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        this.form.tanggal_masuk = `${year}-${month}-${day}`;
      },

      async loadKamar() {
        try {
          const res = await API.get('/pemilik/penghuni/kamar');
          const allRooms = res.data || [];
          this.kamarList = this.isTypeContext
            ? allRooms.filter(item => String(item.id_kos) === String(this.idKos) && String(item.id_tipe_kamar) === String(this.idTipeKamar) && Number(item.jumlah_penghuni || 0) < Number(item.kapasitas || 0))
            : allRooms;
          this.contextInfo = this.isTypeContext && this.kamarList.length ? this.kamarList[0] : null;
          if (this.isTypeContext && !this.contextInfo) {
            const contextRes = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.idTipeKamar), false);
            this.contextInfo = contextRes.data || null;
          }
          if (this.isTypeContext && this.kamarList.length === 1) {
            this.form.id_kamar = String(this.kamarList[0].id_kamar);
            this.updateSelectedKamar();
          }
        } catch (error) {
          this.kamarList = [];
        }
      },

      updateSelectedKamar() {
        this.selectedKamar =
          this.kamarList.find(
            item => String(item.id_kamar) === String(this.form.id_kamar)
          ) || null;
      },

      async loadEdit() {
        if (!this.idPenghuni) {
          Alpine.store('ui').toast(
            'ID penghuni tidak valid.',
            'error'
          );

          window.location.href = this.backUrl;

          return;
        }

        try {
          const res = await API.get(
            '/pemilik/penghuni/show?id_penghuni=' +
            encodeURIComponent(this.idPenghuni)
          );

          const item = res.data;

          this.form.nama = item.nama || '';
          this.form.no_hp = item.no_hp || '';
          this.form.nik = item.nik || '';
          this.matchedUser = Boolean(item.id_user);
        } catch (error) {
          window.location.href = this.backUrl;
        }
      },

      async nextStep() {
        const nik = this.form.nik.trim();

        if (!/^\d{16}$/.test(nik)) {
          Alpine.store('ui').toast(
            'NIK harus terdiri dari 16 digit.',
            'error'
          );
          return;
        }

        this.lookingUp = true;

        try {
          const res = await API.get(
            '/pemilik/penghuni/user-by-nik?nik=' +
            encodeURIComponent(nik)
          );

          this.matchedUser = Boolean(res.found && res.data);

          if (this.matchedUser) {
            this.form.nama = res.data.nama || '';
            this.form.no_hp = res.data.no_hp || '';
            this.form.nik = nik;
            Alpine.store('ui').toast(
              'Data mahasiswa ditemukan dan diisi otomatis.',
              'success'
            );
          } else {
            Alpine.store('ui').toast(
              'Akun mahasiswa tidak ditemukan. Silakan isi data secara manual.',
              'info'
            );
          }

          this.step = 2;
        } catch (error) {
          console.error('Gagal mencari user berdasarkan NIK:', error);
        } finally {
          this.lookingUp = false;
        }
      },

      backToNik() {
        this.step = 1;
        this.matchedUser = false;
        this.form.nama = '';
        this.form.no_hp = '';
        this.$nextTick(() => this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      },

      async submit() {
        if (this.saving) return;

        if (this.mode === 'tambah' && this.step === 1) {
          await this.nextStep();
          return;
        }

        if (!this.form.nama.trim()) {
          Alpine.store('ui').toast(
            'Nama penghuni wajib diisi.',
            'error'
          );
          return;
        }

        if (this.mode === 'tambah') {
          if (!this.form.id_kamar) {
            Alpine.store('ui').toast(
              'Kamar wajib dipilih.',
              'error'
            );
            return;
          }

          if (!this.form.tanggal_masuk) {
            Alpine.store('ui').toast(
              'Tanggal masuk wajib diisi.',
              'error'
            );
            return;
          }
        }

        this.saving = true;

        try {
          if (this.mode === 'tambah') {
            await API.post(
              '/pemilik/penghuni',
              this.form
            );
          } else {
            // Gunakan endpoint POST khusus update agar kompatibel
            // dengan server yang tidak meneruskan request PUT JSON.
            await API.post(
              '/pemilik/penghuni/update', {
                id_penghuni: this.idPenghuni,
                nama: this.form.nama,
                no_hp: this.form.no_hp,
                nik: this.form.nik
              }
            );
          }

          window.location.href = this.backUrl;

        } catch (error) {
          // API sudah menampilkan toast.
        } finally {
          this.saving = false;
        }
      }
    };
  }
</script>
