(function () {
  const WELCOME_KEY = 'betakos_owner_onboarding_welcome_v4';
  const ACTIVE_KEY = 'betakos_owner_onboarding_active_v4';
  const COMPLETE_KEY = 'betakos_owner_onboarding_complete_v4';

  const absolute = (path) => String(window.BASE_URL || '') + path;
  const route = () => {
    const base = String(window.BASE_URL || '').replace(/\/$/, '');
    const path = window.location.pathname;
    return base && path.indexOf(base) === 0 ? (path.slice(base.length) || '/') : path;
  };

  function onboarding() {
    return {
      loading: true,
      welcome: false,
      panel: false,
      state: { completed: 0, total: 5, percent: 0, complete: false, steps: [], next: null, focus: {} },
      errorMessage: '',

      async init() {
        [
          'betakos_owner_onboarding_welcome_v3',
          'betakos_owner_onboarding_active_v3',
          'betakos_owner_onboarding_complete_v3',
          'betakos_owner_onboarding_skipped_v3'
        ].forEach((key) => localStorage.removeItem(key));
        window.addEventListener('betakos:onboarding-refresh', () => this.reload(true));
        window.addEventListener('betakos:onboarding-help', () => this.openAssistant());
        await this.reload(false);
      },

      async fetchState() {
        const response = await fetch(absolute('/api/pemilik/onboarding'), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' }
        });
        const json = await response.json();
        if (!response.ok || !json.success || !json.data) throw new Error(json.message || 'Status persiapan tidak tersedia.');
        return json.data;
      },

      async reload(showAfterUpdate = false) {
        this.loading = true;
        this.errorMessage = '';
        try {
          this.state = await this.fetchState();
          if (this.state.complete) {
            localStorage.setItem(COMPLETE_KEY, '1');
            localStorage.removeItem(ACTIVE_KEY);
            this.welcome = false;
            this.panel = showAfterUpdate;
            return;
          }

          localStorage.removeItem(COMPLETE_KEY);
          const firstVisit = localStorage.getItem(WELCOME_KEY) !== '1';
          this.welcome = firstVisit && route() === '/pemilik';

          // Form yang selesai mengarahkan kembali dengan ?onboarding=1. Buka
          // checklist agar pengguna langsung mengetahui tahap berikutnya.
          const returnedFromStep = new URLSearchParams(window.location.search).get('onboarding') === '1';
          this.panel = !this.welcome && (showAfterUpdate || returnedFromStep);
        } catch (error) {
          this.errorMessage = error.message || 'Panduan belum dapat dimuat.';
        } finally {
          this.loading = false;
        }
      },

      start() {
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.setItem(ACTIVE_KEY, '1');
        this.welcome = false;
        this.panel = true;
      },

      postpone() {
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.removeItem(ACTIVE_KEY);
        this.welcome = false;
        this.panel = false;
      },

      openAssistant() {
        this.welcome = false;
        this.panel = true;
      },

      closePanel() {
        this.panel = false;
      },

      continueSetup() {
        if (!this.state.next) return;
        this.go(this.state.next);
      },

      go(step) {
        if (!step || step.complete || !step.action_url) return;
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.setItem(ACTIVE_KEY, '1');
        window.location.href = absolute(step.action_url);
      }
    };
  }

  window.pemilikOnboarding = onboarding;
})();
