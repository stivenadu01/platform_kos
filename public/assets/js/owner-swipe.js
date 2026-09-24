(function () {
  'use strict';

  const MIN_DISTANCE = 56;
  const DIRECTION_RATIO = 1.25;
  let gesture = null;

  function findTabs(target) {
    const scope = target.closest('.owner-page, [data-swipe-page]');
    if (!scope) return null;
    const tabs = scope.querySelector('.owner-segmented, [data-swipe-tabs]');
    return tabs && tabs.dataset.swipeDisabled !== 'true' ? tabs : null;
  }

  function findGallery(target) {
    return target.closest('[data-swipe-gallery]');
  }

  function canStart(target) {
    return !target.closest('input, select, textarea, [contenteditable="true"], [data-no-swipe]');
  }

  document.addEventListener('touchstart', function (event) {
    if (event.touches.length !== 1 || !canStart(event.target)) return;
    const gallery = findGallery(event.target);
    const tabs = gallery ? null : findTabs(event.target);
    if (!gallery && !tabs) return;
    const touch = event.touches[0];
    gesture = { x: touch.clientX, y: touch.clientY, tabs: tabs, gallery: gallery };
  }, { passive: true });

  document.addEventListener('touchend', function (event) {
    if (!gesture || !event.changedTouches.length) return;
    const current = gesture;
    gesture = null;
    const touch = event.changedTouches[0];
    const dx = touch.clientX - current.x;
    const dy = touch.clientY - current.y;
    if (Math.abs(dx) < MIN_DISTANCE || Math.abs(dx) < Math.abs(dy) * DIRECTION_RATIO) return;

    if (current.gallery) {
      const action = current.gallery.querySelector(dx < 0 ? '[data-swipe-next]' : '[data-swipe-prev]');
      if (action) action.click();
      return;
    }

    const buttons = Array.from(current.tabs.querySelectorAll('button:not([disabled])'))
      .filter(function (button) { return button.offsetParent !== null; });
    if (buttons.length < 2) return;
    let activeIndex = buttons.findIndex(function (button) {
      return button.classList.contains('is-active') || button.getAttribute('aria-current') === 'true' || button.classList.contains('border-primary');
    });
    if (activeIndex < 0) activeIndex = 0;
    const nextIndex = dx < 0
      ? Math.min(buttons.length - 1, activeIndex + 1)
      : Math.max(0, activeIndex - 1);
    if (nextIndex === activeIndex) return;
    buttons[nextIndex].click();
    buttons[nextIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }, { passive: true });

  document.addEventListener('touchcancel', function () { gesture = null; }, { passive: true });
})();
