document.addEventListener('alpine:init', () => {
  // Kunci scroll halaman utama saat sidebar mobile atau modal sedang terbuka.
  // Konten di dalam modal/sidebar tetap dapat di-scroll karena hanya root page yang dikunci.
  const syncBodyScrollLock = () => {
    const overlays = document.querySelectorAll('.fixed.inset-0[x-show], .modal-backdrop[x-show], .ui-filter-panel[x-show], .ui-menu-layer[x-show]');
    const locked = Array.from(overlays).some((element) => {
      if (!element.getClientRects().length || getComputedStyle(element).display === 'none') return false;

      const classList = element.classList;
      const isModal = classList.contains('modal-backdrop');
      const isFullscreenOverlay =
        classList.contains('fixed') &&
        classList.contains('inset-0');

      return isModal || isFullscreenOverlay || getComputedStyle(element).position === 'fixed';
    });

    // Avoid writing the same class on every observation frame.
    for (const root of [document.documentElement, document.body]) {
      if (root.classList.contains('scroll-locked') !== locked) root.classList.toggle('scroll-locked', locked);
    }
  };

  let pending = false;
  const observer = new MutationObserver(() => {
    if (pending) return;
    pending = true;
    requestAnimationFrame(() => { pending = false; syncBodyScrollLock(); });
  });
  observer.observe(document.body, {
    subtree: true,
    attributes: true,
    attributeFilter: ['style', 'class']
  });

  requestAnimationFrame(syncBodyScrollLock);
});

// One responsive filter controller; domain filters stay owned by each page.
window.uiFilterPanel = () => ({
  mobile: window.innerWidth < 768,
  filterOpen: window.innerWidth >= 768,
  trigger: null,
  resize() {
    const mobile = window.innerWidth < 768;
    if (mobile !== this.mobile) { this.mobile = mobile; this.filterOpen = !mobile; }
  },
  toggleFilter() {
    this.trigger = document.activeElement;
    this.filterOpen = !this.filterOpen;
    if (this.filterOpen && this.mobile) this.$nextTick(() => this.$refs.panel.querySelector('input,select,button')?.focus());
  },
  closeFilter() {
    if (!this.mobile) return;
    this.filterOpen = false;
    this.trigger?.focus();
  }
});

document.addEventListener('keydown', event => {
  if (event.key !== 'Tab') return;
  const dialogs = [...document.querySelectorAll('[role="dialog"][aria-modal="true"]')]
    .filter(el => el.getClientRects().length && getComputedStyle(el).display !== 'none');
  const dialog = dialogs.sort((a,b) => Number(getComputedStyle(a.closest('.ui-menu-layer') || a).zIndex) - Number(getComputedStyle(b.closest('.ui-menu-layer') || b).zIndex)).at(-1);
  if (!dialog) return;
  const items = [...dialog.querySelectorAll('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]')]
    .filter(el => el.getClientRects().length);
  if (!items.length) return;
  const first = items[0], last = items.at(-1);
  if (!dialog.contains(document.activeElement) || (!event.shiftKey && document.activeElement === last)) { event.preventDefault(); first.focus(); }
  else if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
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

