document.addEventListener('alpine:init', () => {
  // Kunci scroll halaman utama saat sidebar mobile atau modal sedang terbuka.
  // Konten di dalam modal/sidebar tetap dapat di-scroll karena hanya root page yang dikunci.
  const syncBodyScrollLock = () => {
    const overlays = document.querySelectorAll('[x-show]');
    const locked = Array.from(overlays).some((element) => {
      if (element.style.display === 'none') return false;

      const classList = element.classList;
      const isModal = classList.contains('modal-backdrop');
      const isFullscreenOverlay =
        classList.contains('fixed') &&
        classList.contains('inset-0');

      return isModal || isFullscreenOverlay;
    });

    document.documentElement.classList.toggle('scroll-locked', locked);
    document.body.classList.toggle('scroll-locked', locked);
  };

  const observer = new MutationObserver(syncBodyScrollLock);
  observer.observe(document.body, {
    subtree: true,
    attributes: true,
    attributeFilter: ['style', 'class']
  });

  requestAnimationFrame(syncBodyScrollLock);
});

// PWA: registrasi service worker dan kontrol tombol install aplikasi.
(() => {
  if (!('serviceWorker' in navigator)) return;

  window.addEventListener('load', () => {
    const basePath = (() => {
      try {
        return new URL(window.BASE_URL || window.location.origin).pathname.replace(/\/$/, '');
      } catch (_) {
        return '';
      }
    })();

    navigator.serviceWorker.register(`${basePath}/service-worker.js`, { scope: `${basePath}/` })
      .catch((error) => console.warn('BetaKos PWA service worker gagal didaftarkan:', error));
  });
})();

let betakosDeferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
  event.preventDefault();
  betakosDeferredInstallPrompt = event;
  document.querySelectorAll('[data-pwa-install]').forEach((button) => {
    button.hidden = false;
    button.disabled = false;
  });
});

window.addEventListener('appinstalled', () => {
  betakosDeferredInstallPrompt = null;
  document.querySelectorAll('[data-pwa-install]').forEach((button) => {
    button.hidden = true;
  });
});

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-pwa-install]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!betakosDeferredInstallPrompt) {
        const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
        const message = isIOS
          ? 'Untuk memasang BetaKos di iPhone/iPad, buka menu Bagikan lalu pilih “Tambahkan ke Layar Utama”.'
          : 'Jika pilihan instalasi belum muncul, buka menu browser lalu pilih “Install App” atau “Tambahkan ke layar utama”.';
        window.alert(message);
        return;
      }

      betakosDeferredInstallPrompt.prompt();
      await betakosDeferredInstallPrompt.userChoice;
      betakosDeferredInstallPrompt = null;
    });
  });
});

