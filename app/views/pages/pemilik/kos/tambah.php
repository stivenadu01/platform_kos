<div
  x-data="kosForm()"
  class="owner-page mx-auto max-w-5xl">

  <!-- HEADER -->
  <div class="owner-form-heading">

    <a
      href="<?= BASE_URL ?>/pemilik/kos"
      class="owner-back-link">
      <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali
    </a>

    <h2 class="owner-title">
      Tambah Kos
    </h2>

    <p class="owner-subtitle">
      Masukkan informasi dasar kos dan tentukan lokasi kos pada peta.
    </p>

  </div>


  <form
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm p-4 sm:p-6 space-y-6">

    <nav class="owner-stepper grid grid-cols-2 gap-2 border-b border-slate-200 pb-5 sm:grid-cols-4" aria-label="Tahapan tambah kos">
      <template x-for="(label, index) in steps" :key="label">
        <div class="flex items-center gap-2 rounded-xl px-3 py-2" :class="step === index + 1 ? 'bg-primary-soft text-primary' : step > index + 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400'">
          <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="step === index + 1 ? 'bg-primary text-white' : step > index + 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'" x-text="step > index + 1 ? '✓' : index + 1"></span>
          <span class="text-xs font-semibold sm:text-sm" x-text="label"></span>
        </div>
      </template>
    </nav>

    <div x-show="step === 1" x-cloak data-step-panel="1" class="space-y-6">

    <!-- INFORMASI DASAR -->
    <div>

      <h3 class="font-semibold text-slate-900">
        Informasi Kos
      </h3>

      <p class="text-sm text-slate-500 mt-1">
        Informasi yang akan ditampilkan kepada pencari kos.
      </p>

    </div>


    <div data-help="help-kos-form-main" class="form-group">

      <label class="label">
        Nama Kos
      </label>

      <input
        type="text"
        data-onboarding="kos-field-nama" x-model="form.nama_kos"
        class="input"
        placeholder="Contoh: Kos Melati"
        required>

    </div>


    <div class="form-group">

      <label class="label">
        Alamat
      </label>

      <textarea
        data-onboarding="kos-field-alamat" x-model="form.alamat"
        class="input min-h-28"
        placeholder="Alamat lengkap kos"
        required></textarea>

    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      <div class="form-group">

        <label class="label">
          Jenis Kos
        </label>

        <select
          data-onboarding="kos-field-jenis" x-model="form.jenis"
          class="select"
          required>

          <option value="">
            Pilih jenis kos
          </option>

          <option value="putra">
            Putra
          </option>

          <option value="putri">
            Putri
          </option>

          <option value="campur">
            Campur
          </option>

        </select>

      </div>

    </div>


    <div class="form-group">

      <label class="label">
        Deskripsi
      </label>

      <textarea
        data-onboarding="kos-field-deskripsi" x-model="form.deskripsi"
        class="input min-h-32"
        placeholder="Jelaskan kondisi dan keunggulan kos..."></textarea>

    </div>

    </div>

    <div x-show="step === 2" x-cloak data-step-panel="2" class="space-y-6">

    <!-- ATURAN -->
    <div data-help="help-kos-form-rules" data-onboarding="kos-field-aturan" class="pt-4 border-t border-slate-200">
      <div>
        <h3 class="font-semibold text-slate-900">Aturan / Ketentuan Kos</h3>
        <p class="text-sm text-slate-500 mt-1">Pilih aturan yang berlaku di kos. Data ini akan ditampilkan pada detail kos.</p>
      </div>
      <div x-show="aturanLoading" class="mt-4 text-sm text-slate-500">Memuat daftar aturan...</div>
      <div x-show="!aturanLoading && aturan.length === 0" class="mt-4 p-4 rounded-lg bg-slate-50 text-sm text-slate-500">Belum ada daftar aturan aktif.</div>
      <div x-show="!aturanLoading && aturan.length > 0" class="mt-4 space-y-5">
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
          <template x-for="item in aturan" :key="item.id_aturan">
            <label class="flex min-h-14 items-center gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer transition hover:border-primary/30 hover:bg-slate-50">
              <input type="checkbox" :value="item.id_aturan" x-model="form.aturan" class="rounded border-slate-300 text-primary focus:ring-primary">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary" x-html="iconSvg(item.icon, 'h-4 w-4')"></span>
              <span class="min-w-0 text-sm font-medium text-slate-700" x-text="item.nama_aturan"></span>
            </label>
          </template>
        </div>
      </div>
    </div>

    <!-- FASILITAS -->
    <div data-help="help-kos-form-facility" data-onboarding="kos-field-fasilitas" class="pt-4 border-t border-slate-200">

      <div>
        <h3 class="font-semibold text-slate-900">
          Fasilitas Kos
        </h3>

        <p class="text-sm text-slate-500 mt-1">
          Pilih fasilitas yang tersedia di kos.
        </p>
      </div>


      <div
        x-show="!fasilitasLoading && fasilitas.length === 0"
        class="mt-4 p-4 rounded-lg bg-slate-50 text-sm text-slate-500">

        Belum ada daftar fasilitas.

      </div>


      <div
        x-show="fasilitasLoading"
        class="mt-4 text-sm text-slate-500">

        Memuat daftar fasilitas...

      </div>


      <div
        x-show="!fasilitasLoading && fasilitas.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">

        <template
          x-for="item in fasilitas"
          :key="item.id_fasilitas">

          <label
            class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50 transition">

            <input
              type="checkbox"
              :value="item.id_fasilitas"
              x-model="form.fasilitas"
              class="rounded border-slate-300 text-primary focus:ring-primary">

            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-primary" x-html="iconSvg(item.icon, 'h-4 w-4')"></span>
            <span class="text-sm text-slate-700" x-text="item.nama_fasilitas"></span>

          </label>

        </template>

      </div>

    </div>

    </div>

    <!-- LOKASI -->
    <div x-show="step === 3" x-cloak data-step-panel="3" data-help="help-kos-form-location" data-onboarding="kos-field-lokasi" class="space-y-5">
      <div>
        <h3 class="font-semibold text-slate-900">Lokasi Kos</h3>
        <p class="mt-1 text-sm text-slate-500">Tempel link Google Maps kos agar saat pengunjung membuka lokasi, Google Maps langsung menuju tempat yang Anda pilih.</p>
      </div>

      <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <label class="label">Link Google Maps</label>
        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
          <input
            type="url"
            x-model="form.google_maps_url"
            class="input flex-1"
            placeholder="Tempel link Google Maps di sini"
            inputmode="url">
          <button type="button" @click="applyGoogleMapsLink()" class="btn-primary min-h-12 whitespace-nowrap">
            Gunakan Link
          </button>
        </div>
        <p class="mt-2 text-xs leading-5 text-slate-500">
          <a href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">Buka Google Maps ↗</a>
          → pilih/cari kos → <b>Bagikan</b> → <b>Salin link</b> → tempel di sini. Link seperti <span class="font-mono">google.com/maps/place/...</span> dapat menyimpan tujuan tempatnya, bukan hanya koordinat.
        </p>
        <a
          href="https://www.google.com/maps"
          target="_blank"
          rel="noopener noreferrer"
          class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
          <?= masterIconSvg('map', 'h-4 w-4') ?> Buka Google Maps
        </a>
        <div x-show="form.google_maps_url" x-cloak class="mt-3 rounded-xl bg-white px-3 py-2 text-xs text-slate-600">
          <span class="font-semibold text-slate-800">Link Google Maps tersimpan.</span>
          Koordinat juga akan diambil dari link untuk kebutuhan pencarian dan jarak.
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <button type="button" @click="getCurrentLocation()" :disabled="locating" class="btn-secondary min-h-12">
          <span x-show="!locating">📍 Pakai Lokasi Saat Ini</span>
          <span x-show="locating" x-cloak>Mencari lokasi...</span>
        </button>
        <button type="button" @click="clearGoogleMapsLink()" :disabled="!form.google_maps_url" class="btn-secondary min-h-12">
          Hapus Link Google Maps
        </button>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="form-group">
          <label class="label">Latitude</label>
          <input type="text" x-model="form.latitude" class="input" inputmode="decimal" placeholder="Contoh: -10.1550825" required>
        </div>
        <div class="form-group">
          <label class="label">Longitude</label>
          <input type="text" x-model="form.longitude" class="input" inputmode="decimal" placeholder="Contoh: 123.6163430" required>
        </div>
      </div>

      <p class="mt-3 text-xs leading-5 text-slate-500">
        Jika memakai <b>Lokasi Saat Ini</b>, BetaKos hanya menyimpan latitude dan longitude; link Google Maps akan dikosongkan.
      </p>
    </div>

    <!-- ACTION -->
    <div class="owner-form-actions flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between">

      <a
        x-show="step === 1"
        href="<?= BASE_URL ?>/pemilik/kos"
        class="btn-secondary text-center">
        Batal
      </a>

      <button x-show="step > 1" x-cloak type="button" @click="previousStep()" class="btn-secondary">← Sebelumnya</button>

      <button x-show="step < 3" type="button" @click="nextStep()" class="btn-primary sm:ml-auto">Selanjutnya →</button>

      <button
        x-show="step === 3"
        x-cloak
        type="submit"
        data-help="help-kos-form-save" data-onboarding="kos-save"
        class="btn-primary"
        :disabled="loading || !form.latitude || !form.longitude">

        <span x-show="!loading">
          Simpan & Lanjut ke Foto
        </span>

        <span
          x-show="loading"
          x-cloak>
          Menyimpan...
        </span>

      </button>

    </div>

  </form>

</div>


<script>
  function kosForm() {

    return {
      iconPaths: <?= json_encode_safe(array_map(fn($v)=>$v['path'], masterIconCatalog()), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
      iconSvg(i,c='h-4 w-4') { const p=this.iconPaths[i]||this.iconPaths['map-pin']; return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="${c}" aria-hidden="true">${p}</svg>`; },

      loading: false,
      locating: false,
      step: 1,
      steps: ['Informasi', 'Aturan & Fasilitas', 'Lokasi', 'Foto'],

      fasilitas: [],
      fasilitasLoading: false,
      aturan: [],
      aturanLoading: false,
      form: {
        nama_kos: '',
        alamat: '',
        latitude: '',
        longitude: '',
        google_maps_url: '',
        jenis: '',
        deskripsi: '',
        aturan: [],
        fasilitas: []
      },


      init() {
        this.loadFasilitas();
        this.loadAturan();
      },

      scrollToForm() {
        this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' });
      },

      validateStep() {
        const panel = this.$root.querySelector(`[data-step-panel="${this.step}"]`);
        const invalid = panel ? panel.querySelector(':invalid') : null;
        if (invalid) {
          invalid.reportValidity();
          invalid.focus();
          return false;
        }
        return true;
      },

      nextStep() {
        if (!this.validateStep()) return;
        this.step = Math.min(3, this.step + 1);
        this.$nextTick(() => this.scrollToForm());
      },

      previousStep() {
        this.step = Math.max(1, this.step - 1);
        this.$nextTick(() => this.scrollToForm());
      },

      extractCoordinatesFromGoogleMapsUrl(value) {
        const url = String(value || '').trim();
        if (!url) return null;

        const decode = (text) => {
          try { return decodeURIComponent(text); } catch (_) { return text; }
        };
        const decoded = decode(url);
        const patterns = [
          /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/i,
          /@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i,
          /[?&](?:query|q|ll|center)=(-?\d+(?:\.\d+)?)[,%20]+(-?\d+(?:\.\d+)?)/i
        ];
        for (const pattern of patterns) {
          const match = decoded.match(pattern);
          if (!match) continue;
          const lat = Number(match[1]);
          const lng = Number(match[2]);
          if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            return { lat, lng };
          }
        }
        return null;
      },

      async applyGoogleMapsLink() {
        const value = String(this.form.google_maps_url || '').trim();
        if (!value) {
          Alpine.store('ui').toast('Tempel link Google Maps terlebih dahulu.', 'warning');
          return;
        }
        let parsed;
        try { parsed = new URL(value); } catch (_) {
          Alpine.store('ui').toast('Link Google Maps tidak valid.', 'warning');
          return;
        }
        const host = parsed.hostname.toLowerCase();
        const allowed = host === 'maps.app.goo.gl' || host === 'goo.gl' || host === 'google.com' || host === 'google.co.id' || host.endsWith('.google.com') || host.endsWith('.google.co.id');
        if (!allowed) {
          Alpine.store('ui').toast('Gunakan link yang berasal dari Google Maps.', 'warning');
          return;
        }

        // Link pendek maps.app.goo.gl tidak memuat koordinat di URL browser.
        // Resolusi dilakukan server-side agar redirect Google dapat diikuti tanpa API key.
        let coords = this.extractCoordinatesFromGoogleMapsUrl(value);
        if (!coords && (host === 'maps.app.goo.gl' || host === 'goo.gl')) {
          this.isLoading = true;
          try {
            // Gunakan helper API agar CSRF token ikut dikirim. Endpoint resolver
            // adalah POST dan seluruh request state-changing BetaKos wajib membawa token.
            const result = await API.post('/pemilik/kos/resolve-google-maps', { url: value }, false);
            if (!result || !result.success) {
              throw new Error(result?.message || 'Link Google Maps tidak dapat diproses.');
            }
            coords = { lat: Number(result.data.latitude), lng: Number(result.data.longitude) };
          } catch (error) {
            Alpine.store('ui').toast(error.message || 'Gagal membaca link Google Maps.', 'warning');
            this.isLoading = false;
            return;
          } finally {
            this.isLoading = false;
          }
        }

        if (!coords) {
          Alpine.store('ui').toast('Koordinat tidak ditemukan di link ini. Coba salin link dari tombol Bagikan Google Maps.', 'warning');
          return;
        }
        this.form.latitude = coords.lat.toFixed(7);
        this.form.longitude = coords.lng.toFixed(7);
        this.form.google_maps_url = value;
        Alpine.store('ui').toast('Link Google Maps dan koordinat berhasil digunakan.', 'success');
      },

      clearGoogleMapsLink() {
        this.form.google_maps_url = '';
        Alpine.store('ui').toast('Link Google Maps dihapus. Koordinat tetap disimpan.', 'success');
      },

      getCurrentLocation() {

        if (!navigator.geolocation) {

          Alpine.store('ui').toast(
            'Browser Anda tidak mendukung lokasi.',
            'error'
          );

          return;

        }


        this.locating = true;


        navigator.geolocation.getCurrentPosition(

          (position) => {

            this.form.latitude = Number(position.coords.latitude).toFixed(7);
            this.form.longitude = Number(position.coords.longitude).toFixed(7);
            this.form.google_maps_url = '';


            this.locating = false;


            Alpine.store('ui').toast(
              'Lokasi saat ini berhasil digunakan.',
              'success'
            );

          },


          (error) => {

            this.locating = false;


            let message =
              'Gagal mendapatkan lokasi.';


            if (error.code === 1) {
              message =
                'Izin lokasi ditolak. Silakan izinkan akses lokasi pada browser.';
            }

            if (error.code === 2) {
              message =
                'Lokasi tidak dapat ditemukan.';
            }

            if (error.code === 3) {
              message =
                'Waktu pencarian lokasi habis.';
            }


            Alpine.store('ui').toast(
              message,
              'error'
            );

          },


          {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
          }

        );

      },


      async submit() {

        if (
          !this.form.latitude ||
          !this.form.longitude
        ) {

          Alpine.store('ui').toast(
            'Silakan tentukan latitude dan longitude lokasi kos terlebih dahulu.',
            'warning'
          );

          return;

        }


        this.loading = true;


        try {

          const res = await API.post(
            '/pemilik/kos',
            this.form
          );


          if (res.success) {

            window.location.href =
              BASE_URL + '/pemilik/kos/foto?id=' + encodeURIComponent(res.data.id_kos) + '&wizard=1';

          }

        } catch (error) {

          console.error(error);

        } finally {

          this.loading = false;

        }

      },

      async loadAturan() {
        this.aturanLoading = true;
        try {
          const res = await API.get('/pemilik/kos/aturan');
          if (res.success) this.aturan = res.data || [];
        } catch (error) { console.error('Gagal memuat aturan:', error); }
        finally { this.aturanLoading = false; }
      },

      async loadFasilitas() {

        this.fasilitasLoading = true;

        try {

          const res = await API.get(
            '/pemilik/kos/fasilitas'
          );

          if (res.success) {
            this.fasilitas = res.data || [];
          }

        } catch (error) {

          console.error(error);

        } finally {

          this.fasilitasLoading = false;

        }

      },

    }

  }
</script>
