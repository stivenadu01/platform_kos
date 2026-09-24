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


<div
  x-data="kosSearchPage(<?= htmlspecialchars(json_encode_safe($initialState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>)"
  x-init="init()"
  class="public-page">
  <section class="public-page-header">
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
      <div class="max-w-3xl">
        <p class="text-sm font-semibold text-primary">Cari Kos</p>
        <h1 class="mt-1 font-[Poppins] text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
          Temukan kos di lokasi yang kamu inginkan
        </h1>
        <p class="mt-2 hidden text-sm leading-6 text-slate-500 sm:block">
          Pilih kampus, area, lokasi populer, atau gunakan lokasi kamu untuk melihat kos di sekitarnya.
        </p>
      </div>

      <div
        class="mt-4 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-sm sm:mt-6 sm:p-4"
        @betakos-location-selected.window="selectLocation($event.detail)"
        @betakos-location-query.window="locationQuery = $event.detail.query; selectedLocation = null; search(1)">
        <?php $pickerMode = 'search'; ?>
        <?php include ROOT_PATH . '/app/views/partials/user/location-picker.php'; ?>

        <template x-if="selectedLocation">
          <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
            <span class="text-xs font-medium text-slate-500">Lokasi:</span>
            <span class="rounded-full bg-primary-soft px-3 py-1.5 text-xs font-semibold text-primary" x-text="shortLocationName(selectedLocation.nama)"></span>
            <button type="button" @click="clearLocation()" class="text-xs font-semibold text-slate-400 hover:text-red-600">Hapus lokasi</button>
          </div>
        </template>

        <p x-show="locationError" x-cloak class="mt-2 text-xs font-medium text-red-600" x-text="locationError"></p>
      </div>
    </div>
  </section>

  <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-6 lg:hidden">
    <button @click="filterOpen = true" type="button" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm">
      <span class="inline-flex items-center gap-2"><?= masterIconSvg('filter','h-4 w-4') ?> Filter pencarian</span>
      <span class="text-primary" x-text="activeFilterCount ? activeFilterCount + ' aktif' : 'Atur filter'"></span>
    </button>
  </div>

  <div x-show="filterOpen" x-cloak @keydown.escape.window="filterOpen = false" class="public-filter-backdrop fixed inset-0 lg:hidden">
    <div class="absolute inset-0 bg-slate-900/40" @click="filterOpen = false"></div>
  </div>

  <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[280px_1fr] lg:px-8">
    <aside
      x-bind:class="filterOpen ? 'public-filter-layer fixed inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-2xl border border-slate-200 bg-white p-5 pb-24 shadow-2xl' : 'hidden lg:block lg:sticky lg:top-20'"
      class="h-fit">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-semibold text-slate-900">Filter</h2>
          <p class="mt-0.5 text-[11px] text-slate-400">Sesuaikan hasil dengan kebutuhanmu.</p>
        </div>
        <div class="flex items-center gap-3">
          <button type="button" @click="resetFilters()" class="text-xs font-semibold text-primary">Reset</button>
          <button type="button" @click="filterOpen = false" class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Tutup filter"><?= masterIconSvg('x', 'h-5 w-5') ?></button>
        </div>
      </div>

      <div class="mt-4 space-y-3">
        <section class="public-search-filter-group" :class="!selectedLocation ? 'is-disabled' : ''">
          <div class="public-search-filter-heading"><span>Radius</span><small x-text="selectedLocation ? (filters.jarak_max ? '≤ '+filters.jarak_max+' km' : 'Semua jarak') : 'Pilih lokasi dahulu'"></small></div>
          <div class="public-search-segments public-search-segments-five">
            <template x-for="option in [{v:'',l:'Semua'},{v:'1',l:'1 km'},{v:'3',l:'3 km'},{v:'5',l:'5 km'},{v:'10',l:'10 km'}]" :key="option.v">
              <button type="button" :disabled="!selectedLocation" @click="filters.jarak_max=option.v; search(1)" :class="filters.jarak_max===option.v ? 'is-active' : ''" x-text="option.l"></button>
            </template>
          </div>
        </section>

        <section class="public-search-filter-group">
          <div class="public-search-filter-heading"><span>Jenis kos</span><small x-text="filters.jenis ? filters.jenis : 'Semua'"></small></div>
          <div class="public-search-segments public-search-segments-four">
            <template x-for="option in [{v:'',l:'Semua'},{v:'putra',l:'Putra'},{v:'putri',l:'Putri'},{v:'campur',l:'Campur'}]" :key="option.v">
              <button type="button" @click="filters.jenis=option.v; search(1)" :class="filters.jenis===option.v ? 'is-active' : ''" x-text="option.l"></button>
            </template>
          </div>
        </section>

        <section class="public-search-filter-group">
          <div class="public-search-filter-heading"><span>Kapasitas minimal</span><small x-text="filters.kapasitas ? filters.kapasitas+' orang' : 'Bebas'"></small></div>
          <div class="public-search-segments public-search-segments-five">
            <template x-for="option in [{v:'',l:'Bebas'},{v:'1',l:'1'},{v:'2',l:'2'},{v:'3',l:'3'},{v:'4',l:'4+'}]" :key="option.v">
              <button type="button" @click="filters.kapasitas=option.v; search(1)" :class="filters.kapasitas===option.v ? 'is-active' : ''" x-text="option.l"></button>
            </template>
          </div>
        </section>

        <section class="public-search-filter-group">
          <div class="public-search-filter-heading"><span>Harga per bulan</span><small x-text="priceRangeLabel"></small></div>
          <div class="public-price-presets">
            <button type="button" @click="setPriceRange('', '')" :class="isPriceRange('', '') ? 'is-active' : ''">Semua</button>
            <button type="button" @click="setPriceRange('', '750000')" :class="isPriceRange('', '750000') ? 'is-active' : ''">≤750 rb</button>
            <button type="button" @click="setPriceRange('750000', '1000000')" :class="isPriceRange('750000', '1000000') ? 'is-active' : ''">750 rb–1 jt</button>
            <button type="button" @click="setPriceRange('1000000', '1500000')" :class="isPriceRange('1000000', '1500000') ? 'is-active' : ''">1–1,5 jt</button>
          </div>
          <details class="public-search-filter-details mt-2">
            <summary><span>Atur harga khusus</span><?= masterIconSvg('chevron-down','h-4 w-4') ?></summary>
            <div class="public-custom-price grid grid-cols-2 gap-2 p-2.5 pt-2">
              <label><span>Minimum</span><input x-model="filters.harga_min" @change="search(1)" type="number" min="0" step="10000" placeholder="Rp0" class="input-number"></label>
              <label><span>Maksimum</span><input x-model="filters.harga_max" @change="search(1)" type="number" min="0" step="10000" placeholder="Tanpa batas" class="input-number"></label>
            </div>
          </details>
        </section>

        <details class="public-search-filter-details">
          <summary><span>Fasilitas <b x-show="filters.fasilitas.length" x-cloak x-text="filters.fasilitas.length"></b></span><?= masterIconSvg('chevron-down','h-4 w-4') ?></summary>
          <div class="border-t border-slate-100 p-2.5">
            <div class="mb-2 flex items-center justify-between"><small>Pilih satu atau beberapa</small><button x-show="filters.fasilitas.length" x-cloak @click.prevent="filters.fasilitas=[]; search(1)" type="button" class="text-[11px] font-semibold text-primary">Hapus pilihan</button></div>
            <div class="public-facility-options">
              <template x-if="fasilitasList.length===0"><p class="text-xs text-slate-400">Memuat fasilitas...</p></template>
              <template x-for="item in fasilitasList" :key="item.id_fasilitas">
                <label><input type="checkbox" :value="String(item.id_fasilitas)" x-model="filters.fasilitas" @change="search(1)"><span x-text="item.nama_fasilitas"></span></label>
              </template>
            </div>
          </div>
        </details>
      </div>

      <button type="button" @click="filterOpen = false" class="mt-5 w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white lg:hidden">Terapkan Filter</button>

    </aside>

    <main>
      <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
          <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Kos tersedia</h2>
          <p class="mt-1 text-xs text-slate-500" x-text="resultText"></p>
        </div>
        <button @click="search(pagination.page)" type="button" class="btn-secondary gap-2 text-xs">
          <?= masterIconSvg('refresh-cw', 'h-4 w-4') ?> Perbarui
        </button>
      </div>

      <div x-show="loading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="i in 6" :key="i">
          <div class="h-80 animate-pulse rounded-2xl bg-slate-200"></div>
        </template>
      </div>

      <div x-show="!loading && kosList.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="kos in kosList" :key="kos.id_kos">
          <article class="public-card public-card-hover group">
            <a :href="detailUrl(kos)" class="block">
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img
                :src="kos.foto ? '<?= BASE_URL ?>/uploads' + kos.foto : '<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'"
                :alt="kos.nama_kos"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                @error="$event.target.src='<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'">
              <span x-show="kos.jarak_km !== null" class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm" x-text="formatDistance(kos.jarak_km)"></span>
            </div>
            <div class="p-4">
              <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-slate-900" x-text="kos.nama_kos"></h3>
                <span class="shrink-0 rounded-full bg-primary-soft px-2 py-1 text-[11px] font-medium capitalize text-primary" x-text="kos.jenis"></span>
              </div>
              <p class="mt-1 line-clamp-2 text-xs text-slate-500" x-text="kos.alamat"></p>
              <p class="mt-2 text-xs font-medium text-slate-600" x-text="kos.tipe_kamar || 'Tipe kamar belum tersedia'"></p>
              <div class="mt-4 flex items-end justify-between gap-3">
                <div>
                  <span class="text-[11px] text-slate-500">Mulai dari</span>
                  <p class="font-bold text-primary" x-text="formatRupiah(kos.harga_mulai) + ' / bulan'"></p>
                </div>
                <span class="text-xs text-slate-500" x-text="kos.kamar_tersedia + ' kamar tersedia'"></span>
              </div>
            </div>
            </a>
            <div class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
              <span class="text-[11px] text-slate-400">Detail & lokasi tersedia</span>
              <button
                x-show="$store.auth.user?.role === 'pelanggan'"
                type="button"
                @click.stop.prevent="toggleFavorite(kos)"
                class="public-favorite-toggle inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-bold transition hover:bg-primary-soft"
                :class="kos.is_favorited ? 'is-favorite text-rose-500' : 'text-slate-500'"
                :aria-label="kos.is_favorited ? 'Hapus dari favorit' : 'Simpan ke favorit'">
                <span><?= masterIconSvg('heart', 'h-4 w-4') ?></span>
                <span x-text="kos.is_favorited ? 'Tersimpan' : 'Favorit'"></span>
              </button>
            </div>
          </article>
        </template>
      </div>

      <div x-show="!loading && !kosList.length" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500"><?= masterIconSvg('home', 'h-6 w-6') ?></div>
        <h3 class="mt-3 font-semibold text-slate-900">Kos tidak ditemukan</h3>
        <p class="mt-1 text-sm text-slate-500">Coba ganti kata pencarian, radius, atau filter lainnya.</p>
      </div>

      <div x-show="pagination.total_pages > 1" class="mt-6 flex items-center justify-center gap-2">
        <button @click="search(pagination.page - 1)" :disabled="pagination.page <= 1" class="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Sebelumnya</button>
        <span class="px-2 text-sm text-slate-600" x-text="pagination.page + ' / ' + pagination.total_pages"></span>
        <button @click="search(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Berikutnya</button>
      </div>
    </main>
  </div>
</div>

<script>
  function kosSearchPage(initialState = {}) {
    const validCoord = (value) => Number.isFinite(Number(value));
    const validLocationCoords = (lat, lng) => {
      const a = Number(lat),
        b = Number(lng);
      return validCoord(a) && validCoord(b) && !(a === 0 && b === 0);
    };

    return {
      locationQuery: initialState.q || initialState.lokasi || '',
      selectedLocation: null,
      locating: false,
      locationError: '',
      loading: false,
      filterOpen: false,
      shareMessage: '',
      kosList: [],
      filters: {
        q: initialState.q || '',
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
      pagination: {
        page: 1,
        per_page: 12,
        total: 0,
        total_pages: 0
      },

      get resultText() {
        if (!this.pagination.total) {
          return this.selectedLocation ? 'Belum ada kos yang sesuai filter' : 'Belum ada hasil pencarian';
        }
        return this.selectedLocation ?
          `${this.pagination.total} kos ditemukan di sekitar ${this.shortLocationName(this.selectedLocation.nama)}` :
          `${this.pagination.total} kos ditemukan`;
      },

      get priceRangeLabel() {
        const min = Number(this.filters.harga_min || 0);
        const max = Number(this.filters.harga_max || 0);
        if (!min && !max) return 'Semua harga';
        if (!min) return 'Maks. ' + this.compactMoney(max);
        if (!max) return 'Mulai ' + this.compactMoney(min);
        return this.compactMoney(min) + '–' + this.compactMoney(max);
      },

      get activeFilterCount() {
        let count = 0;
        if (this.filters.jarak_max) count++;
        if (this.filters.jenis) count++;
        if (this.filters.kapasitas) count++;
        if (this.filters.harga_min || this.filters.harga_max) count++;
        if (this.filters.fasilitas.length) count++;
        return count;
      },

      detailUrl(kos) {
        const params = new URLSearchParams();
        if (this.selectedLocation) {
          params.set('lokasi', this.shortLocationName(this.selectedLocation.nama));
          params.set('lat', this.selectedLocation.latitude);
          params.set('lng', this.selectedLocation.longitude);
        }
        const query = params.toString();
        return '<?= BASE_URL ?>/kos/' + kos.id_kos + (query ? '?' + query : '');
      },

      async toggleFavorite(kos) {
        if (!kos || kos._favoriteSaving) return;
        kos._favoriteSaving = true;
        try {
          const res = await API.post('/pelanggan/favorit', { id_kos: Number(kos.id_kos) });
          if (res?.data?.favorited !== undefined) kos.is_favorited = !!res.data.favorited;
        } catch (e) {
          console.error('Gagal memperbarui favorit:', e);
        } finally {
          kos._favoriteSaving = false;
        }
      },

      async init() {
        await this.loadFasilitas();

        // Restore a shared search directly from URL.
        if (validLocationCoords(initialState.latitude, initialState.longitude)) {
          const lat = Number(initialState.latitude);
          const lng = Number(initialState.longitude);
          const name = initialState.lokasi || initialState.q || `Titik peta (${lat.toFixed(5)}, ${lng.toFixed(5)})`;

          this.selectedLocation = {
            nama: name,
            latitude: lat,
            longitude: lng
          };
          this.locationQuery = name;
          this.filters.latitude = lat;
          this.filters.longitude = lng;
          if (!this.filters.jarak_max) this.filters.jarak_max = '3';

          this.syncUrl(false, 1);
          await this.search(1, false);
          return;
        }

        if (this.locationQuery) this.filters.q = this.locationQuery;

        // First visit: do not request geolocation automatically.
        // The user chooses "Cari di lokasi sekitar saya" explicitly.
        await this.search(1, false);
      },

      async loadFasilitas() {
        try {
          const res = await fetch('<?= BASE_URL ?>/api/fasilitas', {
            headers: {
              'Accept': 'application/json'
            }
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

                this.filters.jarak_max = '3';
                await this.search(1, false);
                resolve(true);
              },
              (error) => {
                this.locating = false;
                // Initial location is optional: if permission is denied,
                // silently fall back to the normal all-kos view.
                console.info('Lokasi awal tidak tersedia:', error.code);
                resolve(false);
              }, {
                enableHighAccuracy: true,
                timeout: 12000,
                maximumAge: 300000
              }
          );
        });
      },

      onLocationInput() {
        this.filters.q = this.locationQuery.trim();
        this.selectedLocation = null;
        this.filters.latitude = '';
        this.filters.longitude = '';
        this.shareMessage = '';
      },

      selectLocation(item, updateUrl = true) {
        const lat = Number(item.latitude);
        const lng = Number(item.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        this.selectedLocation = { nama: item.nama || 'Lokasi pilihan', latitude: lat, longitude: lng };
        this.locationQuery = item.nama || '';
        this.filters.q = '';
        this.filters.latitude = lat;
        this.filters.longitude = lng;
        if (!this.filters.jarak_max) this.filters.jarak_max = '3';
        if (updateUrl) this.syncUrl(true, 1);
        this.search(1, false);
      },

      isPriceRange(min, max) {
        return String(this.filters.harga_min || '') === String(min || '') &&
          String(this.filters.harga_max || '') === String(max || '');
      },

      setPriceRange(min, max) {
        this.filters.harga_min = String(min || '');
        this.filters.harga_max = String(max || '');
        this.search(1);
      },

      compactMoney(value) {
        const amount = Number(value || 0);
        if (amount >= 1000000) return (amount / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
        if (amount >= 1000) return Math.round(amount / 1000).toLocaleString('id-ID') + ' rb';
        return amount.toLocaleString('id-ID');
      },

      clearLocation() {
        this.selectedLocation = null;
        this.locationQuery = '';
        this.locationError = '';
        this.filters.q = '';
        this.filters.latitude = '';
        this.filters.longitude = '';
        this.filters.jarak_max = '';
        this.kosList = [];
        this.pagination = { page: 1, per_page: 12, total: 0, total_pages: 0 };
        this.syncUrl(false, 1);
        this.search(1, false);
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
          }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000
          }
        );
      },

      syncUrl(push = false, page = 1) {
        const params = new URLSearchParams();

        if (this.selectedLocation) {
          params.set('lokasi', this.shortLocationName(this.selectedLocation.nama));
          params.set('lat', Number(this.selectedLocation.latitude).toFixed(7));
          params.set('lng', Number(this.selectedLocation.longitude).toFixed(7));
        } else if (this.filters.q) {
          params.set('q', this.filters.q);
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
        if (push) window.history.pushState({
          search: query
        }, '', url);
        else window.history.replaceState({
          search: query
        }, '', url);
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
        this.filters.q = this.selectedLocation ? '' : this.locationQuery.trim();
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
            headers: {
              'Accept': 'application/json'
            }
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
        return new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0
        }).format(Number(value || 0));
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
