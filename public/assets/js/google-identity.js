window.BetaKosGoogle = (() => {
  let initialized = false, busy = false, role = '';
  const buttons = new WeakSet();



  const error = message => {
    document.querySelectorAll('[data-google-status]').forEach(el => { el.textContent=message; });
    if (window.Alpine) Alpine.store('ui')?.toast(message, 'error');
  };
  async function credential(response) {
    if (busy || !response.credential) return;
    busy = true;
    window.Alpine?.store('ui')?.startLoading();
    try {
      const result = await fetch(BASE_URL + '/api/auth/google/credential', {
        method:'POST',credentials:'same-origin',cache:'no-store',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':window.__CSRF_TOKEN__},
        body:JSON.stringify({credential:response.credential,role})
      });
      const body = await result.json();
      if (!result.ok || !body.success) throw new Error(body.message || 'Login Google belum dapat diselesaikan.');
      const target = new URL(body.data.redirect_url, location.origin);
      const base = new URL(BASE_URL,location.origin);
      if (target.origin !== base.origin || !target.pathname.startsWith(base.pathname + '/')) throw new Error('Tujuan login tidak valid.');
      google.accounts.id.cancel();
      location.assign(target.href);
    } catch (e) { error(e.message || 'Login Google belum dapat diselesaikan.'); }
    finally { busy=false; window.Alpine?.store('ui')?.stopLoading(); }
  }
  function render() {
    if (!initialized) return;
    document.querySelectorAll('[data-google-signin]').forEach(element => {
      if (buttons.has(element) || !element.getClientRects().length) return;
      try {
        google.accounts.id.renderButton(element, {
          type:'standard',theme:'outline',size:'large',text:'continue_with',shape:'rectangular',locale:'id',
          width:Math.min(400,Math.max(200,element.clientWidth || 240)),
          click_listener:() => { role=element.dataset.googleRole || ''; }
        });
        buttons.add(element);
        element.closest('[data-google-option]')?.querySelector('[data-google-status]')?.replaceChildren();
      } catch { error('Pilihan Google belum dapat dimuat. Muat ulang halaman untuk mencoba lagi.'); }
    });
  }
  function init() {
    const config = window.__GOOGLE_IDENTITY__;
    if (initialized || !config?.clientId || window.__USER__ || !window.google?.accounts?.id) return;
    try {
      google.accounts.id.initialize({
        client_id: config.clientId, nonce: config.nonce, callback: credential,
        auto_select: false, context: 'signin', ux_mode:'popup', use_fedcm_for_button:true, button_auto_select:false,
        prompt_parent_id:'betakos-google-one-tap', color_scheme:'dark'
      });
      initialized = true;
      render();
      let queued=false;
      new MutationObserver(() => {
        if (queued) return;
        queued=true; requestAnimationFrame(() => { queued=false; render(); });
      }).observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['style','data-google-role']});
      if (config.prompt) google.accounts.id.prompt();
    } catch { failed(); }
  }
  function failed() {
    error('Google belum dapat dimuat. Periksa koneksi lalu muat ulang halaman.');
    console.warn('BetaKos: Google One Tap belum tersedia pada browser ini.');
  }
  document.addEventListener('DOMContentLoaded', init);
  document.addEventListener('alpine:initialized',render);
  return {init, failed};
})();
