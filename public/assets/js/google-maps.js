window.BetaKosGoogleMaps = (() => {
  let loaderPromise = null;

  function load(apiKey) {
    if (window.google?.maps?.Map) return Promise.resolve(window.google.maps);
    if (!apiKey) {
      return Promise.reject(new Error('Google Maps API key belum dikonfigurasi.'));
    }
    if (loaderPromise) return loaderPromise;

    loaderPromise = new Promise((resolve, reject) => {
      const existing = document.querySelector('script[data-betakos-google-maps]');
      if (existing) {
        existing.addEventListener('load', () => resolve(window.google.maps), { once: true });
        existing.addEventListener('error', () => reject(new Error('Gagal memuat Google Maps.')), { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://maps.googleapis.com/maps/api/js?key='
        + encodeURIComponent(apiKey)
        + '&libraries=places&v=weekly';
      script.async = true;
      script.defer = true;
      script.dataset.betakosGoogleMaps = '1';
      script.onload = () => resolve(window.google.maps);
      script.onerror = () => reject(new Error('Gagal memuat Google Maps. Periksa API key dan koneksi internet.'));
      document.head.appendChild(script);
    });

    return loaderPromise;
  }

  return { load };
})();
