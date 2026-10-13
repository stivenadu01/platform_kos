document.addEventListener('alpine:init', () => {
  const user = window.__USER__;
  const available = !!user && ['admin', 'pemilik', 'pelanggan'].includes(user.role);
  const storage = {
    get(key) { try { return localStorage.getItem(key); } catch { return null; } },
    set(key, value) { try { localStorage.setItem(key, value); } catch {} }
  };
  const preference = `betakos:notifications:${user?.id_user}`;
  let device = storage.get('betakos:notification-device');
  if (!/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/.test(device || '')) {
    device = crypto.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
      const n = crypto.getRandomValues(new Uint8Array(1))[0] % 16;
      return (c === 'x' ? n : (n & 3) | 8).toString(16);
    });
    storage.set('betakos:notification-device', device);
  }
  let timer, trigger, stopped = !available, baseline = null, backoff = 15000;
  const native = new Set();
  async function request(path = '', data = null) {
    const res = await fetch(BASE_URL + '/api/notifikasi' + path, {
      method: data === null ? 'GET' : 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', ...(data === null ? {} : {'Content-Type': 'application/json', 'X-CSRF-Token': window.__CSRF_TOKEN__}) },
      ...(data === null ? {} : {body: JSON.stringify({...data, user_id: user.id_user})})
    });
    const body = await res.json().catch(() => ({}));
    if (!res.ok || !body.success) { const error = new Error(body.message || 'Notifikasi belum dapat dimuat.'); error.status = res.status; throw error; }
    if (body.data?.user_id && Number(body.data.user_id) !== Number(user.id_user)) { const error = new Error('Sesi berubah. Muat ulang halaman.'); error.status = 401; throw error; }
    return body.data;
  }
  Alpine.store('notifications', {
    available, open: false, items: [], unread: 0, latest: 0, more: false,
    loading: false, busy: false, error: '', enabling: false,
    supported: 'Notification' in window && window.isSecureContext,
    enabled: storage.get(preference) === '1',
    get browserActive() { return this.supported && this.enabled && Notification.permission === 'granted'; },
    get canEnableBrowser() { return this.supported && Notification.permission !== 'denied' && !this.browserActive; },
    get browserStatus() {
      if (!this.supported) return 'Notifikasi tetap tersedia di sini. Browser ini belum mendukung pemberitahuan perangkat.';
      if (Notification.permission === 'denied') return 'Notifikasi diblokir. Izinkan melalui pengaturan situs di browser.';
      return this.browserActive ? 'Notifikasi browser aktif selama BetaKos terbuka.' : 'Terima pemberitahuan terbaru saat BetaKos terbuka.';
    },
    init() {
      if (!available) return;
      const tick = async () => { if (stopped) return; if (!document.hidden || this.browserActive) await this.refresh(); if (!stopped) timer = setTimeout(tick, backoff); };
      tick();
      document.addEventListener('visibilitychange', () => { if (!document.hidden && !stopped) this.refresh(); });
      window.addEventListener('betakos:notification-open', () => this.openPanel());
      window.addEventListener('pagehide', () => clearTimeout(timer));
      window.addEventListener('pageshow', event => { if (event.persisted && !stopped) tick(); });
    },
    stop() { stopped = true; clearTimeout(timer); this.closePanel(); this.items = []; this.unread = 0; this.dismissBrowser(); },
    openPanel(button = document.activeElement) { if (!available || stopped) return; trigger = button; this.open = true; this.refresh(); },
    closePanel() { if (!this.open) return; this.open = false; trigger?.focus?.(); },
    async refresh() {
      if (stopped || this.busy) return;
      this.busy = true; this.loading = !this.items.length;
      try {
        const data = await request(); if (stopped) return;
        const events = baseline === null ? [] : data.items.filter(item => Number(item.id) > baseline && !item.dibaca_pada);
        baseline = data.latest; this.latest = data.latest; this.items = data.items; this.more = data.more; this.unread = data.unread; this.error = ''; backoff = 15000;
        if (!this.unread) this.dismissBrowser();
        if (events.length) {
          const claims = await request('/klaim', {perangkat: device, ids: events.map(item => Number(item.id))});
          if (stopped) return;
          for (const item of events.filter(item => claims.ids.includes(Number(item.id)))) {
            if (this.browserActive) await this.showBrowser(item);
            else if (!document.hidden) Alpine.store('ui').toast(item.judul, 'success', 5000);
          }
        }
      } catch (e) {
        this.error = e.message; backoff = Math.min(backoff * 2, 60000);
        if ([401,403,409,419].includes(e.status)) this.stop();
      } finally { this.busy = false; this.loading = false; }
    },
    async loadMore() {
      if (this.busy || !this.more) return;
      this.busy = true;
      try { const data = await request('?before=' + this.items.at(-1).id); this.items.push(...data.items); this.more = data.more; }
      catch (e) { this.error = e.message; } finally { this.busy = false; }
    },
    async readAll() {
      if (this.busy || !this.latest) return;
      this.busy = true;
      try { await request('/baca', {upto: this.latest}); await this.dismissBrowser(); }
      catch (e) { this.error = e.message; } finally { this.busy = false; }
      await this.refresh();
    },
    async visit(item) {
      if (this.busy) return;
      this.busy = true;
      try {
        await request('/baca', {id: Number(item.id)}); await this.dismissBrowser(item.id);
        const target = new URL(BASE_URL + item.tujuan, location.origin);
        if (target.origin === location.origin && target.pathname.startsWith(new URL(BASE_URL, location.origin).pathname + '/')) location.href = target.href;
      } catch (e) { this.error = e.message; } finally { this.busy = false; }
    },
    async enableBrowser() {
      if (!this.supported) return;
      this.enabling = true;
      try { this.enabled = (await Notification.requestPermission()) === 'granted'; storage.set(preference, this.enabled ? '1' : '0'); }
      catch { this.error = 'Browser belum dapat mengaktifkan notifikasi.'; }
      finally { this.enabling = false; }
    },
    disableBrowser() { this.enabled = false; storage.set(preference, '0'); this.dismissBrowser(); },
    async showBrowser(item) {
      const options = {body: item.pesan, icon: BASE_URL + '/assets/icon/android-chrome-192x192.png', tag: `betakos:${user.id_user}:${item.id}`, data: {url: BASE_URL + '/user/akun', notificationUser: user.id_user}};
      // Open the center rather than exposing an old account's private URL.
      options.data.url = BASE_URL + (user.role === 'admin' ? '/admin' : user.role === 'pemilik' ? '/pemilik' : '/user/akun') + '?notifikasi=1';
      try {
        const worker = await navigator.serviceWorker?.getRegistration(BASE_URL + '/');
        if (worker?.active) { await worker.showNotification(item.judul, options); return; }
        const n = new Notification(item.judul, options); native.add(n);
        n.onclick = () => { window.focus(); this.openPanel(); n.close(); native.delete(n); };
      } catch { if (!document.hidden) Alpine.store('ui').toast(item.judul, 'success', 5000); }
    },
    async dismissBrowser(id = null) {
      const prefix = `betakos:${user?.id_user}:`;
      const matches = n => n.tag.startsWith(prefix) && (id === null || n.tag === prefix + id);
      for (const n of native) if (matches(n)) { n.close(); native.delete(n); }
      try { const worker = await navigator.serviceWorker?.getRegistration(BASE_URL + '/'); for (const n of worker ? await worker.getNotifications() : []) if (matches(n)) n.close(); } catch {}
    },
    time(value) { return new Intl.DateTimeFormat('id-ID', {dateStyle:'medium', timeStyle:'short'}).format(new Date(value)); }
  });
  if (available && new URLSearchParams(location.search).get('notifikasi') === '1') {
    document.addEventListener('alpine:initialized', () => Alpine.store('notifications').openPanel());
  }
});

