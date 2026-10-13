document.addEventListener('alpine:init', () => {
  let busy=false, timer, debounce;
  Alpine.store('navigation', {
    counts:window.__NAV_COUNTS__ || {},
    init() {
      if (!window.__USER__) return;
      timer=setInterval(() => { if (!document.hidden) this.refresh(); },30000);
      window.addEventListener('betakos:data-changed', () => { clearTimeout(debounce); debounce=setTimeout(() => this.refresh(),400); });
      document.addEventListener('visibilitychange', () => { if (!document.hidden) this.refresh(); });
      window.addEventListener('pagehide', () => clearInterval(timer));
      window.addEventListener('pageshow', event => { if (event.persisted) { this.refresh(); timer=setInterval(() => { if (!document.hidden) this.refresh(); },30000); } });
    },
    async refresh() {
      if (busy || !Alpine.store('auth')?.isLoggedIn) return;
      busy=true;
      try {
        const res=await fetch(BASE_URL+'/api/navigation/counts',{headers:{Accept:'application/json'},credentials:'same-origin',cache:'no-store'});
        if ([401,403].includes(res.status)) { clearInterval(timer); this.counts={}; return; }
        const body=await res.json();
        if (body.success && Number(body.data.user_id)===Number(window.__USER__.id_user)) this.counts=body.data.counts;
        else if (body.success) { clearInterval(timer); this.counts={}; }
      } catch { /* Keep the last server-confirmed counters during a connection failure. */ }
      finally { busy=false; }
    }
  });
});
