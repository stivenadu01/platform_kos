<div
  x-data="kosEditForm()"
  class="max-w-5xl mx-auto">

  <!-- HEADER -->
  <div class="mb-6">

    <a
      href="<?= BASE_URL ?>/pemilik/kos"
      class="text-sm text-primary hover:underline">
      ← Kembali ke Kos Saya
    </a>

    <h2 class="mt-3 text-2xl font-bold text-slate-900">
      Edit Kos
    </h2>

    <p class="mt-1 text-sm text-slate-500">
      Perbarui informasi dan lokasi kos Anda.
    </p>

  </div>


  <form
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm p-6 space-y-6">


    <!-- INFORMASI -->
    <div>

      <h3 class="font-semibold text-slate-900">
        Informasi Kos
      </h3>

      <p class="text-sm text-slate-500 mt-1">
        Perbarui informasi dasar kos.
      </p>

    </div>


    <div class="form-group">

      <label class="label">
        Nama Kos
      </label>

      <input
        type="text"
        x-model="form.nama_kos"
        class="input"
        required>

    </div>


    <div class="form-group">

      <label class="label">
        Alamat
      </label>

      <textarea
        x-model="form.alamat"
        class="input min-h-28"
        required></textarea>

    </div>


    <div class="form-group">

      <label class="label">
        Jenis Kos
      </label>

      <select
        x-model="form.jenis"
        class="select"
        required>

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


    <div class="form-group">

      <label class="label">
        Deskripsi
      </label>

      <textarea
        x-model="form.deskripsi"
        class="input min-h-32"></textarea>

    </div>

    <!-- FASILITAS -->
    <div class="pt-4 border-t border-slate-200">

      <div>
        <h3 class="font-semibold text-slate-900">
          Fasilitas Kos
        </h3>

        <p class="text-sm text-slate-500 mt-1">
          Pilih fasilitas yang tersedia di kos.
        </p>
      </div>


      <div
        x-show="fasilitasLoading"
        class="mt-4 text-sm text-slate-500">

        Memuat daftar fasilitas...

      </div>


      <div
        x-show="!fasilitasLoading && fasilitas.length === 0"
        class="mt-4 p-4 rounded-lg bg-slate-50 text-sm text-slate-500">

        Belum ada daftar fasilitas.

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

            <span
              class="text-sm text-slate-700"
              x-text="item.nama_fasilitas">
            </span>

          </label>

        </template>

      </div>

    </div>

    <!-- LOKASI -->
    <div class="pt-4 border-t border-slate-200">

      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

        <div>

          <h3 class="font-semibold text-slate-900">
            Lokasi Kos
          </h3>

          <p class="text-sm text-slate-500 mt-1">
            Klik lokasi baru pada peta atau geser marker.
          </p>

        </div>


        <button
          type="button"
          @click="getCurrentLocation()"
          :disabled="locating"
          class="btn-secondary whitespace-nowrap">

          <span x-show="!locating">
            📍 Lokasi Saat Ini
          </span>

          <span
            x-show="locating"
            x-cloak>
            Mencari lokasi...
          </span>

        </button>

      </div>


      <!-- MAP -->
      <div
        id="map-edit-kos"
        class="mt-4 w-full h-[380px] rounded-xl overflow-hidden border border-slate-200">
      </div>


      <!-- COORDINATES -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">

        <div class="form-group">

          <label class="label">
            Latitude
          </label>

          <input
            type="text"
            x-model="form.latitude"
            class="input bg-slate-50"
            readonly
            required>

        </div>


        <div class="form-group">

          <label class="label">
            Longitude
          </label>

          <input
            type="text"
            x-model="form.longitude"
            class="input bg-slate-50"
            readonly
            required>

        </div>

      </div>


      <p class="mt-3 text-xs text-slate-500">
        💡 Klik lokasi baru pada peta, geser marker, atau gunakan
        <strong>Lokasi Saat Ini</strong>.
      </p>

    </div>


    <!-- ACTION -->
    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-200">

      <a
        href="<?= BASE_URL ?>/pemilik/kos"
        class="btn-secondary text-center">
        Batal
      </a>

      <button
        type="submit"
        class="btn-primary"
        :disabled="loading || !form.latitude || !form.longitude">

        <span x-show="!loading">
          Simpan Perubahan
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


<!-- LEAFLET -->
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  crossorigin="">

<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  crossorigin="">
</script>


<script>
  function kosEditForm() {

    return {

      loading: false,
      locating: false,

      map: null,
      marker: null,

      fasilitas: [],
      fasilitasLoading: false,

      form: {

        nama_kos: <?= json_encode_safe($kos['nama_kos']) ?>,

        alamat: <?= json_encode_safe($kos['alamat']) ?>,

        latitude: <?= json_encode_safe($kos['latitude']) ?>,

        longitude: <?= json_encode_safe($kos['longitude']) ?>,

        jenis: <?= json_encode_safe($kos['jenis']) ?>,

        deskripsi: <?= json_encode_safe($kos['deskripsi'] ?? '') ?>,

        fasilitas: []

      },


      init() {

        this.$nextTick(() => {

          this.initMap();

        });

        this.loadDataFasilitas();

      },


      initMap() {

        const lat =
          Number(this.form.latitude);

        const lng =
          Number(this.form.longitude);


        /*
         * Pastikan koordinat lama valid.
         */
        const validLocation =
          Number.isFinite(lat) &&
          Number.isFinite(lng);


        /*
         * Kalau koordinat lama valid,
         * gunakan posisi kos.
         *
         * Kalau tidak valid,
         * gunakan posisi Kupang.
         */
        const initialLat =
          validLocation ?
          lat :
          -10.1600;

        const initialLng =
          validLocation ?
          lng :
          123.6000;

        const initialZoom =
          validLocation ?
          17 :
          13;


        this.map = L.map('map-edit-kos')
          .setView(
            [
              initialLat,
              initialLng
            ],
            initialZoom
          );


        L.tileLayer(
          'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
          }
        ).addTo(this.map);


        /*
         * Marker posisi lama
         */
        if (validLocation) {

          this.createMarker(
            initialLat,
            initialLng
          );

        }


        /*
         * Klik peta
         */
        this.map.on('click', (event) => {

          this.setLocation(
            event.latlng.lat,
            event.latlng.lng
          );

        });

      },


      createMarker(lat, lng) {

        this.marker = L.marker(
          [lat, lng], {
            draggable: true
          }
        ).addTo(this.map);


        this.marker.on(
          'dragend',
          (event) => {

            const position =
              event.target.getLatLng();

            this.setLocation(
              position.lat,
              position.lng
            );

          }
        );

      },


      setLocation(lat, lng) {

        lat = Number(lat);
        lng = Number(lng);


        this.form.latitude =
          lat.toFixed(7);

        this.form.longitude =
          lng.toFixed(7);


        if (!this.marker) {

          this.createMarker(
            lat,
            lng
          );

        } else {

          this.marker.setLatLng([
            lat,
            lng
          ]);

        }


        this.map.setView(
          [lat, lng],
          Math.max(
            this.map.getZoom(),
            16
          )
        );

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

            this.setLocation(
              position.coords.latitude,
              position.coords.longitude
            );


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
            'Lokasi kos belum ditentukan.',
            'warning'
          );

          return;

        }


        this.loading = true;


        try {

          const res = await API.put(
            '/pemilik/kos/<?= $kos['id_kos'] ?>',
            this.form
          );


          if (res.success) {

            window.location.href =
              BASE_URL + '/pemilik/kos';

          }

        } catch (error) {

          console.error(error);

        } finally {

          this.loading = false;

        }

      },

      async loadDataFasilitas() {

        this.fasilitasLoading = true;

        try {

          const [fasilitasRes, kosRes] =
          await Promise.all([

            API.get(
              '/pemilik/kos/fasilitas'
            ),

            API.get(
              '/pemilik/kos/<?= $kos['id_kos'] ?>'
            )

          ]);


          if (fasilitasRes.success) {

            this.fasilitas =
              fasilitasRes.data || [];

          }


          if (
            kosRes.success &&
            kosRes.data
          ) {

            this.form.fasilitas =
              (kosRes.data.fasilitas || [])
              .map(item =>
                Number(item.id_fasilitas)
              );

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