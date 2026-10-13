// Shared presentation components. Domain actions remain in their page controllers.
window.uiActionMenu = () => ({
  menuOpen: false,
  menuPosition: '',
  showMenu() {
    window.dispatchEvent(new CustomEvent('betakos:close-menus'));
    this.menuOpen = true;
    this.$nextTick(() => {
      const trigger = this.$refs.menuTrigger.getBoundingClientRect();
      const panel = this.$refs.menuPanel;
      const width = Math.min(288, window.innerWidth - 24);
      const height = Math.min(panel.scrollHeight, window.innerHeight - 24);
      const left = Math.max(12, Math.min(trigger.right - width, window.innerWidth - width - 12));
      const top = Math.max(12, Math.min(trigger.bottom + 8, window.innerHeight - height - 12));
      this.menuPosition = `width:${width}px;left:${left}px;top:${top}px`;
      panel.querySelector('a[href],button:not([disabled])')?.focus();
    });
  },
  closeMenu(restore = true) {
    if (!this.menuOpen) return;
    this.menuOpen = false;
    if (restore) this.$refs.menuTrigger.focus();
  }
});

// The visible viewport changes when a phone keyboard opens. Only offset actions
// while a field is focused; browser toolbars alone must not lift the bottom bar.
(() => {
  const update = () => {
    const editing = document.activeElement?.matches('input,textarea,select,[contenteditable="true"]');
    const viewport = window.visualViewport;
    const offset = editing && viewport ? Math.max(0, innerHeight - viewport.height - viewport.offsetTop) : 0;
    document.documentElement.style.setProperty('--ui-keyboard-offset', offset + 'px');
    document.documentElement.classList.toggle('ui-keyboard-open', !!editing && (offset > 100 || (viewport && viewport.height < screen.height * .65)));
  };
  window.visualViewport?.addEventListener('resize', update);
  window.visualViewport?.addEventListener('scroll', update);
  document.addEventListener('focusin', update);
  document.addEventListener('focusout', () => requestAnimationFrame(update));
})();

// Replace single native selects with one consistent, accessible BetaKos dropdown.
// The original select remains the form/API source of truth, so existing x-model,
// required validation and change handlers keep their current business behavior.
(() => {
  let active = null;
  const isMobile = () => window.matchMedia('(max-width: 639px)').matches;

  const close = (restore = false) => {
    if (!active) return;
    const { button, popup } = active;
    active.backdrop?.remove();
    popup.remove();
    document.documentElement.classList.remove('ui-select-open');
    button.setAttribute('aria-expanded', 'false');
    active = null;
    if (restore) button.focus();
  };

  const position = (button, popup) => {
    if (isMobile()) {
      popup.removeAttribute('style');
      popup.classList.add('is-sheet');
      return;
    }
    const trigger = button.getBoundingClientRect();
    const width = Math.min(Math.max(trigger.width, 220), Math.min(360, innerWidth - 24));
    const below = innerHeight - trigger.bottom - 12;
    const above = trigger.top - 12;
    const maxHeight = Math.max(160, Math.min(320, Math.max(below, above)));
    popup.style.width = `${width}px`;
    popup.style.maxHeight = `${maxHeight}px`;
    popup.style.left = `${Math.max(12, Math.min(trigger.left, innerWidth - width - 12))}px`;
    popup.style.top = below >= Math.min(240, maxHeight)
      ? `${trigger.bottom + 6}px`
      : `${Math.max(12, trigger.top - Math.min(popup.scrollHeight, maxHeight) - 6)}px`;
  };

  const enhance = (select) => {
    if (select.dataset.uiSelectReady || select.multiple || select.size > 1 || select.dataset.nativeSelect !== undefined) return;
    select.dataset.uiSelectReady = 'true';
    const shell = document.createElement('div');
    shell.className = 'ui-select';
    select.parentNode.insertBefore(shell, select);
    shell.appendChild(select);
    select.classList.add('ui-native-select');

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'ui-select-button';
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    button.innerHTML = '<span class="ui-select-label"></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>';
    shell.appendChild(button);

    const sync = () => {
      const option = select.selectedOptions[0];
      button.querySelector('.ui-select-label').textContent = option?.textContent?.trim() || 'Pilih';
      button.disabled = select.disabled;
      button.classList.toggle('is-placeholder', !select.value);
      button.setAttribute('aria-invalid', select.getAttribute('aria-invalid') || 'false');
    };

    const open = () => {
      if (select.disabled) return;
      close();
      const popup = document.createElement('div');
      popup.className = 'ui-select-popup';
      popup.setAttribute('role', 'listbox');
      popup.setAttribute('aria-label', select.getAttribute('aria-label') || select.labels?.[0]?.textContent?.trim() || 'Pilih opsi');
      const options = [...select.options];
      let list = popup;
      if (options.length > 8) {
        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'ui-select-search';
        search.placeholder = 'Cari pilihan…';
        search.setAttribute('aria-label', 'Cari pilihan');
        const optionsBox = document.createElement('div');
        optionsBox.className = 'ui-select-options';
        popup.append(search, optionsBox);
        list = optionsBox;
        search.addEventListener('input', () => {
          const query = search.value.trim().toLocaleLowerCase('id');
          [...optionsBox.children].forEach(item => item.hidden = !item.textContent.toLocaleLowerCase('id').includes(query));
        });
      }
      options.forEach(option => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'ui-select-option';
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', String(option.selected));
        item.disabled = option.disabled;
        item.dataset.value = option.value;
        item.innerHTML = `<span></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>`;
        item.querySelector('span').textContent = option.textContent.trim();
        item.addEventListener('click', () => {
          select.value = option.value;
          select.dispatchEvent(new Event('input', { bubbles: true }));
          select.dispatchEvent(new Event('change', { bubbles: true }));
          sync();
          close(true);
        });
        list.appendChild(item);
      });
      let backdrop = null;
      if (isMobile()) {
        backdrop = document.createElement('button');
        backdrop.type = 'button';
        backdrop.className = 'ui-select-backdrop';
        backdrop.setAttribute('aria-label', 'Tutup pilihan');
        backdrop.addEventListener('click', () => close(true));
        document.body.appendChild(backdrop);
        document.documentElement.classList.add('ui-select-open');
      }
      document.body.appendChild(popup);
      active = { select, button, popup, backdrop };
      button.setAttribute('aria-expanded', 'true');
      position(button, popup);
      (popup.querySelector('[aria-selected="true"]') || popup.querySelector('.ui-select-option:not(:disabled)'))?.scrollIntoView({ block: 'nearest' });
      (popup.querySelector('.ui-select-search') || popup.querySelector('[aria-selected="true"]') || popup.querySelector('.ui-select-option:not(:disabled)'))?.focus();
    };

    button.addEventListener('click', () => active?.select === select ? close(true) : open());
    button.addEventListener('keydown', event => {
      if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(event.key)) { event.preventDefault(); open(); }
    });
    select.addEventListener('input', sync);
    select.addEventListener('change', () => { button.setAttribute('aria-invalid', 'false'); sync(); });
    select.addEventListener('invalid', event => {
      event.preventDefault();
      button.setAttribute('aria-invalid', 'true');
      button.focus();
    });
    select.addEventListener('focus', () => button.focus());
    new MutationObserver(() => requestAnimationFrame(sync)).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['selected', 'disabled', 'label'] });
    sync();
  };

  const scan = (root = document) => root.querySelectorAll?.('select:not([multiple])').forEach(enhance);
  document.addEventListener('alpine:initialized', () => {
    scan();
    new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
      if (node.nodeType !== 1) return;
      if (node.matches?.('select:not([multiple])')) enhance(node);
      scan(node);
    }))).observe(document.body, { childList: true, subtree: true });
  });
  document.addEventListener('click', event => {
    if (active && !active.popup.contains(event.target) && !active.button.contains(event.target) && event.target !== active.backdrop) close();
  });
  document.addEventListener('keydown', event => {
    if (!active) return;
    if (event.key === 'Escape') { event.preventDefault(); close(true); }
    if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    event.preventDefault();
    const items = [...active.popup.querySelectorAll('.ui-select-option:not([disabled]):not([hidden])')];
    const index = items.indexOf(document.activeElement);
    items[(index + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length]?.focus();
  });
  addEventListener('resize', () => close());
  addEventListener('scroll', event => { if (active && !active.popup.contains(event.target)) close(); }, true);
})();
