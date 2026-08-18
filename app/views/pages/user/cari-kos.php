<?php
$title = 'Cari Kos';

// State pencarian dari URL agar hasil lokasi dapat dibagikan/reload tanpa kehilangan konteks.
$initialState = [
    'q' => trim($_GET['q'] ?? ''),
    'lokasi' => trim($_GET['lokasi'] ?? ''),
    'latitude' => isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float) $_GET['lat'] : null,
    'longitude' => isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float) $_GET['lng'] : null,
    'jarak_max' => trim($_GET['radius'] ?? ''),
    'jenis' => trim($_GET['jenis'] ?? ''),
    'kapasitas' => trim($_GET['kapasitas'] ?? ''),
    'harga_min' => trim($_GET['harga_min'] ?? ''),
    'harga_max' => trim($_GET['harga_max'] ?? ''),
    'fasilitas' => isset($_GET['fasilitas']) && is_array($_GET['fasilitas'])
        ? array_values(array_filter(array_map('intval', $_GET['fasilitas']), fn($id) => $id > 0))
        : [],
];
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  #search-map { z-index: 1; }
  .leaflet-pane, .leaflet-control { z-index: 2; }
  .leaflet-top, .leaflet-bottom { z-index: 10; }
</style>

<div
  x-data="kosSearchPage(<?= htmlspecialchars(json_encode($initialState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>)"
  x-init="init()"
  class="min-h-[calc(100vh-4rem)] bg-slate-50"
>
  <section class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="max-w-3xl">
        <p class="text-sm font-semibold text-primary">Cari Kos</p>
        <h1 class="mt-1 font-[Poppins] text-3xl font-bold tracking-tight text-slate-900">
          Temukan kos berdasarkan lokasi
        </h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">
          Cari kampus, jalan, atau tempat di Kupang dari peta. Pilih lokasi lalu tentukan radius kos yang kamu inginkan.
        </p>
      </div>

      <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="relative p-3 sm:p-4">
          <div class="flex items-center gap-3 px-2">
            <span class="text-lg text-slate-400">⌖</span>
            <input
              x-model="locationQuery"
              @input.debounce.600ms="onLocationInput()"
              @keydown.enter.prevent="selectFirstLocation()"
              type="search"
              autocomplete="off"
              class="w-full bg-transparent py-2 text-sm outline-none"
              placeholder="Cari kampus, jalan, atau lokasi di Kupang..."
            >
            <button
              x-show="locationQuery"
              x-cloak
              @click="clearLocation()"
              type="button"
              class="rounded-lg px-2 py-1 text-xs text-slate-400 hover:bg-slate-100"
            >Hapus</button>
          </div>

          <div
            x-show="locationResults.length && !selectedLocation"
            x-cloak
            class="absolute left-3 right-3 top-[58px] z-[1000] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl sm:left-4 sm:right-4"
          >
            <template x-for="item in locationResults" :key="item.latitude + ',' + item.longitude + item.nama">
              <button
                @click="selectLocation(item)"
                type="button"
                class="flex w-full items-start gap-3 border-b border-slate-100 px-4 py-3 text-left last:border-0 hover:bg-slate-50"
              >
                <span class="mt-0.5 text-primary">⌖</span>
                <span class="min-w-0">
                  <span class="block text-sm font-semibold text-slate-800" x-text="shortLocationName(item.nama)"></span>
                  <span class="mt-0.5 block text-xs text-slate-500" x-text="item.nama"></span>
                </span>
              </button>
            </template>
          </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50 p-3 sm:p-4">
          <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="text-xs font-semibold text-primary">Lokasi pencarian</p>
              <p
                class="mt-0.5 truncate text-sm font-medium text-slate-700"
                x-text="selectedLocation ? selectedLocation.nama : 'Pilih lokasi dari pencarian atau peta'"
              ></p>
            </div>
            <button
              x-show="selectedLocation"
              x-cloak
              @click="clearLocation()"
              type="button"
              class="text-xs font-semibold text-primary hover:underline"
            >Ganti lokasi</button>
          </div>

          <div id="search-map" class="h-64 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-200 sm:h-80"></div>

          <p class="mt-2 text-[11px] leading-5 text-slate-500">
            Cari lokasi di atas, gunakan lokasi perangkat, atau klik langsung pada peta untuk menentukan titik pencarian.
          </p>

          <div class="mt-3 flex flex-col gap-2 sm:flex-row">
            <button
              @click="useMyLocation()"
              type="button"
              :disabled="locating"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
            >
              <span x-text="locating ? 'Mencari lokasi...' : '📍 Gunakan lokasi saya'"></span>
            </button>
            <button
              @click="search(1)"
              type="button"
              :disabled="!selectedLocation || loading"
              class="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-50"
            >
              Cari kos di sekitar
            </button>
          </div>

          <p
            x-show="locationError"
            x-cloak
            class="mt-2 text-xs font-medium text-red-600"
            x-text="locationError"
          ></p>
        </div>
      </div>    </div>
  </section>

  <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[280px_1fr] lg:px-8">
    <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-20">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-slate-900">Filter</h2>
        <button type="button" @click="resetFilters()" class="text-xs font-semibold text-primary">Reset</button>
      </div>

      <div class="mt-5 space-y-5">
        <label class="block">
          <span class="text-xs font-medium text-slate-600">Radius dari lokasi</span>
          <select x-model="filters.jarak_max" @change="search(1)" :disabled="!selectedLocation" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm disabled:cursor-not-allowed disabled:bg-slate-100">
            <option value="">Semua jarak</option>
            <option value="1">≤ 1 km</option>
            <option value="3">≤ 3 km</option>
            <option value="5">≤ 5 km</option>
            <option value="10">≤ 10 km</option>
          </select>
          <span x-show="!selectedLocation" class="mt-1 block text-[11px] text-slate-400">
            Pilih lokasi untuk mengaktifkan radius.
          </span>
        </label>

        <label class="block">
          <span class="text-xs font-medium text-slate-600">Jenis kos</span>
          <select x-model="filters.jenis" @change="search(1)" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">
            <option value="">Semua jenis</option>
            <option value="putra">Putra</option>
            <option value="putri">Putri</option>
            <option value="campur">Campur</option>
          </select>
        </label>

        <label class="block">
          <span class="text-xs font-medium text-slate-600">Kapasitas minimal</span>
          <select x-model="filters.kapasitas" @change="search(1)" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">
            <option value="">Bebas</option>
            <option value="1">1 orang</option>
            <option value="2">2 orang</option>
            <option value="3">3 orang</option>
            <option value="4">4 orang</option>
          </select>
        </label>

        <div>
          <span class="text-xs font-medium text-slate-600">Harga per bulan</span>
          <div class="mt-1.5 grid grid-cols-2 gap-2">
            <input x-model="filters.harga_min" @change="search(1)" type="number" min="0" step="10000" placeholder="Min" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            <input x-model="filters.harga_max" @change="search(1)" type="number" min="0" step="10000" placeholder="Max" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-medium text-slate-600">Fasilitas</span>
            <button
              x-show="filters.fasilitas.length"
              x-cloak
              @click="filters.fasilitas = []; search(1)"
              type="button"
              class="text-[11px] font-semibold text-primary hover:underline"
            >Reset</button>
          </div>

          <div class="mt-1.5 max-h-44 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-white p-3">
            <template x-if="fasilitasList.length === 0">
              <p class="text-xs text-slate-400">Memuat fasilitas...</p>
            </template>

            <template x-for="item in fasilitasList" :key="item.id_fasilitas">
              <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  :value="String(item.id_fasilitas)"
                  x-model="filters.fasilitas"
                  @change="search(1)"
                  class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                >
                <span x-text="item.nama_fasilitas"></span>
              </label>
            </template>
          </div>

          <p class="mt-1 text-[11px] text-slate-400">
            Bisa memilih lebih dari satu fasilitas.
          </p>
        </div>
      </div>

    </aside>

    <main>
      <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Kos tersedia</h2>
          <p class="mt-1 text-xs text-slate-500" x-text="resultText"></p>
        </div>
        <button @click="search(pagination.page)" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:border-primary hover:text-primary">
          Perbarui
        </button>
      </div>

      <div x-show="loading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="i in 6" :key="i">
          <div class="h-80 animate-pulse rounded-2xl bg-slate-200"></div>
        </template>
      </div>

      <div x-show="!loading && kosList.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="kos in kosList" :key="kos.id_kos">
          <a :href="'<?= BASE_URL ?>/kos/' + kos.id_kos" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img
                :src="kos.foto ? '<?= BASE_URL ?>/uploads' + kos.foto : '<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'"
                :alt="kos.nama_kos"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                @error="$event.target.src='<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'"
              >
              <span x-show="kos.jarak_km !== null" class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm" x-text="formatDistance(kos.jarak_km)"></span>
            </div>
            <div class="p-4">
              <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-slate-900" x-text="kos.nama_kos"></h3>
                <span class="shrink-0 rounded-full bg-primary-soft px-2 py-1 text-[11px] font-medium capitalize text-primary" x-text="kos.jenis"></span>
              </div>
              <p class="mt-1 line-clamp-2 text-xs text-slate-500" x-text="kos.alamat"></p>
              <div class="mt-4 flex items-end justify-between gap-3">
                <div>
                  <span class="text-[11px] text-slate-500">Mulai dari</span>
                  <p class="font-bold text-primary" x-text="formatRupiah(kos.harga_mulai) + ' / bulan'"></p>
                </div>
                <span class="text-xs text-slate-500" x-text="kos.kamar_tersedia + ' kamar tersedia'"></span>
              </div>
            </div>
          </a>
        </template>
      </div>

      <div x-show="!loading && !kosList.length" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <div class="text-4xl">⌂</div>
        <h3 class="mt-3 font-semibold text-slate-900">Kos tidak ditemukan</h3>
        <p class="mt-1 text-sm text-slate-500">Coba ganti lokasi, radius, atau filter lainnya.</p>
      </div>

      <div x-show="pagination.total_pages > 1" class="mt-6 flex items-center justify-center gap-2">
        <button @click="search(pagination.page - 1)" :disabled="pagination.page <= 1" class="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Sebelumnya</button>
        <span class="px-2 text-sm text-slate-600" x-text="pagination.page + ' / ' + pagination.total_pages"></span>
        <button @click="search(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Berikutnya</button>
      </div>
    </main>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function kosSearchPage(initialState = {}) {
  const validCoord = (value) => Number.isFinite(Number(value));
  const validLocationCoords = (lat, lng) => {
    const a = Number(lat), b = Number(lng);
    return validCoord(a) && validCoord(b) && !(a === 0 && b === 0);
  };

  return {
    locationQuery: initialState.q || initialState.lokasi || '',
    locationResults: [],
    selectedLocation: null,
    map: null,
    marker: null,
    locating: false,
    locationError: '',
    loading: false,
    shareMessage: '',
    kosList: [],
    filters: {
      q: '',
      latitude: '',
      longitude: '',
      jarak_max: initialState.jarak_max || '',
      jenis: initialState.jenis || '',
      kapasitas: initialState.kapasitas || '',
      harga_min: initialState.harga_min || '',
      harga_max: initialState.harga_max || '',
      fasilitas: Array.isArray(initialState.fasilitas) ? initialState.fasilitas.map(String) : []
    },
    fasilitasList: [],
    pagination: { page: 1, per_page: 12, total: 0, total_pages: 0 },

    get resultText() {
      if (!this.pagination.total) {
        return this.selectedLocation ? 'Belum ada kos yang sesuai filter' : 'Belum ada hasil pencarian';
      }
      return this.selectedLocation
        ? `${this.pagination.total} kos ditemukan di sekitar ${this.shortLocationName(this.selectedLocation.nama)}`
        : `${this.pagination.total} kos ditemukan`;
    },

    async init() {
      this.$nextTick(() => this.initMap());

      await this.loadFasilitas();

      // Restore a shared search directly from URL.
      if (validLocationCoords(initialState.latitude, initialState.longitude)) {
        const lat = Number(initialState.latitude);
        const lng = Number(initialState.longitude);
        const name = initialState.lokasi || initialState.q || `Titik peta (${lat.toFixed(5)}, ${lng.toFixed(5)})`;

        this.selectedLocation = { nama: name, latitude: lat, longitude: lng };
        this.locationQuery = name;
        this.filters.latitude = lat;
        this.filters.longitude = lng;

        this.$nextTick(() => {
          this.setMapMarker(lat, lng);
          setTimeout(() => this.map?.invalidateSize(), 100);
        });

        await this.search(1, false);
        return;
      }

      // If only text was supplied, search suggestions and let the user choose.
      if (this.locationQuery) {
        await this.searchLocations();
      }

      // First visit: try to initialize from the user's current location.
      // We keep radius empty, so location is used for the marker/distance,
      // while all kos remain eligible until the user chooses a radius.
      if (!this.locationQuery && !this.selectedLocation) {
        const located = await this.requestInitialLocation();
        if (!located) {
          await this.search(1, false);
        }
      }
    },

    async loadFasilitas() {
      try {
        const res = await fetch('<?= BASE_URL ?>/api/fasilitas', {
          headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();

        if (!res.ok || json.success === false) {
          throw new Error(json.message || 'Gagal memuat fasilitas');
        }

        const data = json.data || [];
        this.fasilitasList = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('Gagal memuat fasilitas:', e);
        this.fasilitasList = [];
      }
    },

    requestInitialLocation() {
      return new Promise((resolve) => {
        if (!navigator.geolocation) {
          resolve(false);
          return;
        }

        this.locating = true;

        navigator.geolocation.getCurrentPosition(
          async (position) => {
            this.locating = false;

            const lat = Number(position.coords.latitude);
            const lng = Number(position.coords.longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng) ||
                (lat === 0 && lng === 0)) {
              resolve(false);
              return;
            }

            this.selectLocation({
              nama: 'Lokasi saya',
              latitude: lat,
              longitude: lng
            }, false);

            // No radius is imposed automatically.
            this.filters.jarak_max = '';
            await this.search(1, false);
            resolve(true);
          },
          (error) => {
            this.locating = false;
            // Initial location is optional: if permission is denied,
            // silently fall back to the normal all-kos view.
            console.info('Lokasi awal tidak tersedia:', error.code);
            resolve(false);
          },
          {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 300000
          }
        );
      });
    },

    initMap() {
      if (this.map || typeof L === 'undefined') return;

      const defaultLat = -10.1772;
      const defaultLng = 123.6070;

      this.map = L.map('search-map', {
        zoomControl: true,
        attributionControl: true
      }).setView([defaultLat, defaultLng], 12);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(this.map);

      this.map.on('click', (event) => this.setMapLocation(event.latlng.lat, event.latlng.lng));
      setTimeout(() => this.map.invalidateSize(), 150);
    },

    onLocationInput() {
      // Saat user mulai mengetik pencarian baru, lokasi sebelumnya tidak lagi
      // menjadi pilihan aktif sehingga dropdown hasil pencarian dapat muncul.
      if (this.selectedLocation) {
        this.selectedLocation = null;
        this.filters.latitude = '';
        this.filters.longitude = '';
        if (this.marker) {
          this.marker.remove();
          this.marker = null;
        }
      }
      this.shareMessage = '';
      this.searchLocations();
    },

    async searchLocations() {
      const query = this.locationQuery.trim();
      this.locationError = '';

      if (query.length < 3) {
        this.locationResults = [];
        return;
      }

      try {
        const res = await fetch('<?= BASE_URL ?>/api/lokasi/search?q=' + encodeURIComponent(query), {
          headers: { 'Accept': 'application/json' }
        });

        if (res.ok) {
          const json = await res.json();
          if (json.success && Array.isArray(json.data) && json.data.length) {
            this.locationResults = json.data;
            return;
          }
        }
      } catch (e) {
        console.warn('Proxy lokasi gagal, mencoba Nominatim langsung.', e);
      }

      try {
        const queryVariants = [
          `${query}, Kupang, Nusa Tenggara Timur, Indonesia`,
          `${query}, Kota Kupang, Indonesia`,
          `${query}, Indonesia`
        ];
        let items = [];
        for (const qv of queryVariants) {
          const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
            q: qv, format: 'jsonv2', addressdetails: '1', limit: '8',
            countrycodes: 'id', 'accept-language': 'id'
          });
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
          if (res.ok) {
            const part = await res.json();
            if (Array.isArray(part)) items.push(...part);
          }
          if (items.length >= 6) break;
        }
        const seen = new Set();
        this.locationResults = items.filter(item => item.lat && item.lon).map(item => ({
          nama: item.display_name || query, latitude: Number(item.lat),
          longitude: Number(item.lon), type: item.type || null
        })).filter(item => {
          const key = `${item.latitude},${item.longitude}`;
          if (seen.has(key)) return false; seen.add(key); return true;
        }).sort((a,b) => {
          const n = query.toLowerCase();
          const ap = a.nama.toLowerCase().indexOf(n);
          const bp = b.nama.toLowerCase().indexOf(n);
          return (ap < 0 ? 999999 : ap) - (bp < 0 ? 999999 : bp);
        }).slice(0, 6);

        if (!this.locationResults.length) {
          this.locationError = 'Lokasi tidak ditemukan. Coba nama tempat yang lebih spesifik.';
        }
      } catch (e) {
        console.error(e);
        this.locationResults = [];
        this.locationError = 'Pencarian lokasi gagal. Pastikan koneksi internet tersedia.';
      }
    },

    selectFirstLocation() {
      if (this.locationResults.length) this.selectLocation(this.locationResults[0]);
    },

    selectLocation(item, updateUrl = true) {
      const lat = Number(item.latitude);
      const lng = Number(item.longitude);

      if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        this.locationError = 'Koordinat lokasi tidak valid.';
        return;
      }

      this.locationError = '';
      this.selectedLocation = {
        nama: item.nama,
        latitude: lat,
        longitude: lng
      };
      this.locationQuery = item.nama;
      this.locationResults = [];
      this.filters.latitude = lat;
      this.filters.longitude = lng;

      this.$nextTick(() => {
        this.setMapMarker(lat, lng);
        setTimeout(() => this.map?.invalidateSize(), 100);
      });

      if (updateUrl) this.syncUrl(true, 1);
      this.search(1, false);
    },

    setMapLocation(lat, lng) {
      this.locationError = '';
      this.selectedLocation = {
        nama: `Titik peta (${lat.toFixed(5)}, ${lng.toFixed(5)})`,
        latitude: lat,
        longitude: lng
      };
      this.locationQuery = '';
      this.locationResults = [];
      this.filters.latitude = lat;
      this.filters.longitude = lng;
      this.setMapMarker(lat, lng);
      this.syncUrl(true, 1);
      this.search(1, false);
    },

    setMapMarker(lat, lng) {
      this.initMap();
      if (!this.map) return;

      this.map.setView([lat, lng], Math.max(this.map.getZoom(), 14));

      if (this.marker) {
        this.marker.setLatLng([lat, lng]);
      } else {
        this.marker = L.marker([lat, lng]).addTo(this.map);
      }

      this.marker.bindPopup('Lokasi pencarian').openPopup();
    },

    clearLocation() {
      this.selectedLocation = null;
      this.locationQuery = '';
      this.locationResults = [];
      this.locationError = '';
      this.filters.latitude = '';
      this.filters.longitude = '';
      this.kosList = [];
      this.pagination = { page: 1, per_page: 12, total: 0, total_pages: 0 };

      if (this.marker) {
        this.marker.remove();
        this.marker = null;
      }
      if (this.map) {
        this.map.setView([-10.1772, 123.6070], 12);
        setTimeout(() => this.map.invalidateSize(), 100);
      }

      this.syncUrl(false, 1);
    },

    async useMyLocation() {
      this.locationError = '';

      if (!navigator.geolocation) {
        this.locationError = 'Browser kamu tidak mendukung fitur lokasi.';
        return;
      }

      this.locating = true;
      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.locating = false;
          this.selectLocation({
            nama: 'Lokasi saya',
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          });
        },
        (error) => {
          this.locating = false;
          const messages = {
            1: 'Izin lokasi ditolak. Izinkan akses lokasi pada pengaturan browser.',
            2: 'Lokasi perangkat tidak dapat ditemukan.',
            3: 'Permintaan lokasi terlalu lama. Silakan coba lagi.'
          };
          this.locationError = messages[error.code] || 'Lokasi tidak dapat diakses.';
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 300000 }
      );
    },

    syncUrl(push = false, page = 1) {
      const params = new URLSearchParams();

      if (this.selectedLocation) {
        params.set('lokasi', this.shortLocationName(this.selectedLocation.nama));
        params.set('lat', Number(this.selectedLocation.latitude).toFixed(7));
        params.set('lng', Number(this.selectedLocation.longitude).toFixed(7));
      }

      if (this.filters.jarak_max) params.set('radius', this.filters.jarak_max);
      if (this.filters.jenis) params.set('jenis', this.filters.jenis);
      if (this.filters.kapasitas) params.set('kapasitas', this.filters.kapasitas);
      if (this.filters.harga_min) params.set('harga_min', this.filters.harga_min);
      if (this.filters.harga_max) params.set('harga_max', this.filters.harga_max);
      this.filters.fasilitas.forEach(id => params.append('fasilitas[]', String(id)));
      if (page > 1) params.set('page', page);

      const query = params.toString();
      const url = window.location.pathname + (query ? '?' + query : '');
      if (push) window.history.pushState({ search: query }, '', url);
      else window.history.replaceState({ search: query }, '', url);
    },

    async search(page = 1, updateUrl = true) {
      if (page < 1 || (this.pagination.total_pages && page > this.pagination.total_pages)) return;

      // Tanpa lokasi, lakukan pencarian umum. Radius hanya digunakan
      // setelah user memilih lokasi.
      if (!this.selectedLocation) {
        this.filters.latitude = '';
        this.filters.longitude = '';
        this.filters.jarak_max = '';
      }

      if (updateUrl) this.syncUrl(false, page);
      this.loading = true;
      this.locationError = '';

      try {
        const params = new URLSearchParams();

        Object.entries(this.filters).forEach(([key, value]) => {
          if (key === 'fasilitas') return;
          if (value !== null && value !== undefined && value !== '') {
            params.set(key, value);
          }
        });

        this.filters.fasilitas.forEach(id => {
          params.append('fasilitas[]', String(id));
        });

        params.set('page', page);
        params.set('per_page', this.pagination.per_page);

        const res = await fetch('<?= BASE_URL ?>/api/kos/search?' + params.toString(), {
          headers: { 'Accept': 'application/json' }
        });
        const json = await res.json();

        if (!res.ok || json.success === false) throw new Error(json.message || 'Gagal memuat data kos');

        const payload = json.data || {};
        this.kosList = payload.items || json.items || [];
        this.pagination = payload.pagination || json.pagination || this.pagination;
      } catch (e) {
        console.error(e);
        this.kosList = [];
        this.locationError = 'Pencarian kos gagal. Periksa koneksi atau coba lagi.';
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.filters.jarak_max = this.selectedLocation ? '3' : '';
      this.filters.jenis = '';
      this.filters.kapasitas = '';
      this.filters.harga_min = '';
      this.filters.harga_max = '';
      this.filters.fasilitas = [];
      this.syncUrl(false, 1);
      this.search(1, false);
    },

    async shareSearch() {
      this.syncUrl(false, this.pagination.page || 1);
      const url = window.location.href;
      this.shareMessage = '';

      try {
        if (navigator.share) {
          await navigator.share({
            title: 'Pencarian Kos - BetaKos',
            text: this.selectedLocation ? `Cari kos dekat ${this.shortLocationName(this.selectedLocation.nama)}` : 'Pencarian kos',
            url
          });
          return;
        }

        await navigator.clipboard.writeText(url);
        this.shareMessage = '✓ Link pencarian disalin';
      } catch (e) {
        if (e && e.name === 'AbortError') return;
        this.shareMessage = 'Link siap dibagikan dari alamat browser';
      }

      setTimeout(() => this.shareMessage = '', 2500);
    },

    formatRupiah(value) {
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
    },

    formatDistance(value) {
      const km = Number(value);
      return Number.isFinite(km) ? (km < 1 ? `${Math.round(km * 1000)} m` : `${km.toFixed(1)} km`) : '';
    },

    shortLocationName(value) {
      const text = String(value || '').trim();
      if (!text) return 'Lokasi pilihan';
      return text.split(',')[0].trim();
    }
  };
}
</script>
