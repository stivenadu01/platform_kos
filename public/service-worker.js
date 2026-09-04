const CACHE_NAME = 'betakos-static-v3';

const STATIC_ASSETS = [
  '/assets/css/app.css',
  '/assets/js/api.js',
  '/assets/js/app.js',
  '/assets/js/store.js',
  '/assets/js/utils.js',
  '/assets/icon/logo.png',
  '/assets/icon/android-chrome-192x192.png',
  '/assets/icon/android-chrome-512x512.png',
  '/assets/icon/apple-touch-icon.png',
  '/assets/icon/favicon.ico',
  '/assets/icon/site.webmanifest'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  // Jangan cache API, halaman dinamis, upload, atau request lintas origin.
  // Data login, transaksi, tagihan, dan halaman dashboard harus selalu dari server terbaru.
  if (url.origin !== self.location.origin) return;
  if (url.pathname === '/service-worker.js') return;
  if (url.pathname.startsWith('/api/')) return;
  if (url.pathname.startsWith('/uploads/')) return;
  if (request.mode === 'navigate') return;

  const isStaticAsset =
    url.pathname.startsWith('/assets/') ||
    url.pathname === '/service-worker.js';

  if (!isStaticAsset) return;

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) return cached;

      return fetch(request).then((response) => {
        if (!response || !response.ok) return response;
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
        return response;
      });
    })
  );
});
