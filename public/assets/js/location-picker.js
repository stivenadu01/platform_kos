/**
 * BetaKos public location picker.
 * No Google Maps API is required. Presets use stored coordinates and
 * free-text location search is handled by the existing server endpoint.
 */
window.BetaKosLocationPicker = function (config = {}) {
  const baseUrl = String(config.baseUrl || window.BASE_URL || '').replace(/\/$/, '');
  const groups = config.groups || {};

  return {
    open: false,
    activeTab: 'kampus',
    locationQuery: '',
    suggestions: [],
    searching: false,
    locationError: '',
    searchTimer: null,

    async init() {
      this.$watch('open', (isOpen) => {
        document.body.style.overflow = isOpen ? 'hidden' : '';
      });

      try {
        const response = await fetch(`${baseUrl}/api/lokasi/referensi`, { headers: { Accept: 'application/json' } });
        const json = await response.json();
        if (response.ok && json.success && json.data) {
          Object.keys(groups).forEach((key) => { groups[key] = Array.isArray(json.data[key]) ? json.data[key] : []; });
        }
      } catch (error) {
        console.warn('Lokasi referensi publik tidak dapat dimuat:', error);
      }
    },

    get activeItems() {
      return Array.isArray(groups[this.activeTab]) ? groups[this.activeTab] : [];
    },

    openPicker() {
      this.open = true;
      this.locationError = '';
      this.suggestions = [];
      this.$nextTick(() => {
        this.$refs.locationInput?.focus();
      });
    },

    closePicker() {
      this.open = false;
      this.locationError = '';
      this.suggestions = [];
      document.body.style.overflow = '';
    },

    setTab(tab) {
      this.activeTab = tab;
      this.locationError = '';
      this.suggestions = [];
    },

    async useMyLocation() {
      this.locationError = '';

      if (!navigator.geolocation) {
        this.locationError = 'Browser kamu tidak mendukung fitur lokasi.';
        return;
      }

      this.searching = true;

      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.searching = false;
          this.choose({
            nama: 'Lokasi saya',
            label: 'Lokasi saya',
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            subtitle: 'Lokasi perangkat saat ini'
          });
        },
        (error) => {
          this.searching = false;
          const messages = {
            1: 'Izin lokasi ditolak. Izinkan akses lokasi pada pengaturan browser.',
            2: 'Lokasi perangkat tidak dapat ditemukan.',
            3: 'Permintaan lokasi terlalu lama. Silakan coba lagi.'
          };
          this.locationError = messages[error.code] || 'Lokasi tidak dapat diakses.';
        },
        {
          enableHighAccuracy: true,
          timeout: 15000,
          maximumAge: 300000
        }
      );
    },

    choose(item) {
      const lat = Number(item.latitude);
      const lng = Number(item.longitude);

      if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        this.locationError = 'Lokasi belum memiliki koordinat.';
        return;
      }

      const selected = {
        nama: item.nama || item.label || 'Lokasi pilihan',
        latitude: lat,
        longitude: lng
      };

      // Setelah pengguna memilih hasil, tampilkan nama lokasi lengkap
      // pada kolom input, bukan query parsial yang sebelumnya diketik.
      this.locationQuery = selected.nama;

      this.open = false;
      this.suggestions = [];
      document.body.style.overflow = '';

      if (config.redirect) {
        const params = new URLSearchParams();
        params.set('lokasi', selected.nama);
        params.set('lat', lat.toFixed(7));
        params.set('lng', lng.toFixed(7));
        window.location.href = `${baseUrl}/cari-kos?${params.toString()}`;
        return;
      }

      this.$dispatch('betakos-location-selected', selected);
    },

    onQueryInput() {
      const query = this.locationQuery.trim();
      this.locationError = '';

      if (this.searchTimer) clearTimeout(this.searchTimer);
      this.suggestions = [];

      if (query.length < 3) return;

      this.searchTimer = setTimeout(() => this.searchLocations(query), 350);
    },

    normalizeText(value) {
      return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    },

    searchReferenceLocations(query) {
      const needle = this.normalizeText(query);
      if (!needle) return [];
      const items = [];
      Object.entries(groups).forEach(([category, categoryItems]) => {
        (Array.isArray(categoryItems) ? categoryItems : []).forEach((item) => {
          const haystack = this.normalizeText([item.nama, item.label, item.alamat].filter(Boolean).join(' '));
          if (!haystack.includes(needle)) return;
          const name = this.normalizeText(item.nama || item.label);
          const label = this.normalizeText(item.label);
          const score = name === needle ? 0 : (name.startsWith(needle) || label.startsWith(needle) ? 1 : 2);
          items.push({ ...item, source: 'referensi', sourceLabel: 'Lokasi referensi BetaKos', category, _score: score });
        });
      });
      items.sort((a, b) => a._score - b._score || String(a.nama || '').localeCompare(String(b.nama || '')));
      return items;
    },

    mergeLocationSuggestions(referenceItems, remoteItems) {
      const merged = [];
      const seen = new Set();
      [...referenceItems, ...remoteItems].forEach((item) => {
        const lat = Number(item.latitude);
        const lng = Number(item.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        const key = `${lat.toFixed(6)},${lng.toFixed(6)}`;
        if (seen.has(key)) return;
        seen.add(key);
        merged.push({ ...item, latitude: lat, longitude: lng });
      });
      return merged.slice(0, 10);
    },

    async searchLocations(query) {
      this.searching = true;
      const referenceItems = this.searchReferenceLocations(query);

      try {
        const response = await fetch(
          `${baseUrl}/api/lokasi/search?q=${encodeURIComponent(query)}`,
          {
            headers: { Accept: 'application/json' }
          }
        );

        const raw = await response.text();
        let json = null;
        try { json = raw ? JSON.parse(raw) : null; } catch (parseError) {
          console.warn('Respons pencarian lokasi bukan JSON:', raw.slice(0, 200));
        }
        const remoteItems = response.ok && json?.success !== false && Array.isArray(json?.data) ? json.data : [];
        this.suggestions = this.mergeLocationSuggestions(referenceItems, remoteItems);
        if (!response.ok && this.suggestions.length === 0) {
          throw new Error(json?.message || 'Pencarian lokasi gagal.');
        }
      } catch (error) {
        console.error('Pencarian lokasi gagal:', error);
        this.suggestions = referenceItems;
        if (this.suggestions.length === 0) this.locationError = 'Lokasi tidak ditemukan. Coba nama tempat atau kawasan lain.';
      } finally {
        this.searching = false;
      }
    },

    submitQuery() {
      const query = this.locationQuery.trim();
      if (!query) {
        this.openPicker();
        return;
      }

      if (this.suggestions.length) {
        this.choose(this.suggestions[0]);
        return;
      }

      if (config.redirect) {
        const params = new URLSearchParams();
        params.set('q', query);
        window.location.href = `${baseUrl}/cari-kos?${params.toString()}`;
      } else {
        this.$dispatch('betakos-location-query', { query });
      }
    }
  };
};
