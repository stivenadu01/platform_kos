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
