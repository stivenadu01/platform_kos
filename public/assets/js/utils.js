const utils = {
  formatRupiah(angka, prefix = true) {
    const number = Number(angka) || 0;

    const formatted = new Intl.NumberFormat('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(number);

    return prefix ? 'Rp' + formatted : formatted;
  },

  formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  },

  formatDateTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  },

  storageHelper(key, action, value = null) {
    let data;

    try {
      data = JSON.parse(localStorage.getItem(key)) || [];
    } catch {
      data = [];
    }

    switch (action) {
      case 'load':
        return data;

      case 'save':
        if (!value) return data;
        data.push(value);
        localStorage.setItem(key, JSON.stringify(data));
        return data;

      case 'set':
        if (!Array.isArray(value)) return data;
        localStorage.setItem(key, JSON.stringify(value));
        return value;

      case 'remove':
        data = data.filter((_, i) => i !== value);
        localStorage.setItem(key, JSON.stringify(data));
        return data;

      case 'clear':
        localStorage.removeItem(key);
        return [];

      default:
        console.warn('storageHelper: action tidak dikenal ->', action);
        return data;
    }
  },

  setQuery(key, value) {
    const url = new URL(window.location);
    if (value === null || value === undefined || value === '') {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, value);
    }
    window.history.replaceState({}, '', url);
  },

  getQuery(key) {
    const url = new URL(window.location);
    return url.searchParams.get(key);
  }
};

window.utils = utils;