(function () {
  const ACTIVE_KEY = 'betakos_owner_onboarding_active_v4';
  const COMPLETE_KEY = 'betakos_owner_onboarding_complete_v4';
  const remember = (key, value) => { try { value === null ? localStorage.removeItem(key) : localStorage.setItem(key, value); } catch (_) {} };

  const absolute = (path) => String(window.BASE_URL || '') + path;

  function onboarding() {
    return {
      loading: true,
      panel: false,
      state: { completed: 0, total: 5, percent: 0, complete: false, steps: [], next: null, focus: {} },
      errorMessage: '',

      async init() {
        [
          'betakos_owner_onboarding_welcome_v3',
          'betakos_owner_onboarding_active_v3',
          'betakos_owner_onboarding_complete_v3',
          'betakos_owner_onboarding_skipped_v3'
        ].forEach((key) => remember(key, null));
        window.addEventListener('betakos:onboarding-refresh', () => this.reload(true));
        window.addEventListener('betakos:onboarding-help', () => this.openAssistant());
        await this.reload(false);
      },

      async fetchState() {
        const response = await fetch(absolute('/api/pemilik/onboarding'), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' }
        });
        let json;
        try { json = await response.json(); } catch (_) { throw new Error('Sesi atau layanan belum tersedia. Muat ulang halaman lalu coba lagi.'); }
        if (!response.ok || !json.success || !json.data) throw new Error(json.message || 'Status persiapan tidak tersedia.');
        if (!Array.isArray(json.data.steps) || !json.data.focus) throw new Error('Data persiapan belum lengkap. Silakan coba lagi.');
        return json.data;
      },

      async reload(showAfterUpdate = false) {
        this.loading = true;
        this.errorMessage = '';
        if (showAfterUpdate || new URLSearchParams(window.location.search).get('onboarding') === '1') this.panel = true;
        try {
          this.state = await this.fetchState();
          window.dispatchEvent(new CustomEvent('betakos:setup-state', { detail: this.state }));
          const returnedFromStep = new URLSearchParams(window.location.search).get('onboarding') === '1';
          if (this.state.complete) {
            remember(COMPLETE_KEY, '1');
            remember(ACTIVE_KEY, null);
            this.panel = showAfterUpdate || returnedFromStep;
            return;
          }

          remember(COMPLETE_KEY, null);

          // Form yang selesai mengarahkan kembali dengan ?onboarding=1. Buka
          // checklist agar pengguna langsung mengetahui tahap berikutnya.
          this.panel = showAfterUpdate || returnedFromStep;
        } catch (error) {
          this.errorMessage = error.message || 'Panduan belum dapat dimuat.';
        } finally {
          this.loading = false;
        }
      },

      openAssistant() {
        this.trigger = document.activeElement;
        this.panel = true;
        this.$nextTick(() => this.$refs.setupClose?.focus());
        if (this.errorMessage) this.reload(true);
      },

      closePanel() {
        if (!this.panel) return;
        this.panel = false;
        this.trigger?.focus();
      },

      continueSetup() {
        if (this.loading || this.errorMessage || !this.state.next) return;
        this.go(this.state.next);
      },

      go(step) {
        if (this.loading || this.errorMessage || !step || step.complete || !step.action_url) return;
        remember(ACTIVE_KEY, '1');
        window.location.href = absolute(step.action_url);
      }
    };
  }

  window.pemilikOnboarding = onboarding;
})();
