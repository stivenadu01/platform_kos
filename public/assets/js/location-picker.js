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

      this.open = false;
      this.suggestions = [];

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

    async searchLocations(query) {
      this.searching = true;

      try {
        const response = await fetch(
          `${baseUrl}/api/lokasi/search?q=${encodeURIComponent(query)}`,
          {
            headers: { Accept: 'application/json' }
          }
        );

        const json = await response.json();

        if (!response.ok || json.success === false) {
          throw new Error(json.message || 'Pencarian lokasi gagal.');
        }

        this.suggestions = Array.isArray(json.data) ? json.data : [];
      } catch (error) {
        console.error('Pencarian lokasi gagal:', error);
        this.suggestions = [];
        this.locationError = 'Lokasi tidak ditemukan. Coba nama tempat atau kawasan lain.';
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
