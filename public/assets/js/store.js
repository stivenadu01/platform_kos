document.addEventListener('alpine:init', () => {

  /*
  |--------------------------------------------------------------------------
  | AUTH STORE
  |--------------------------------------------------------------------------
  */

  Alpine.store('auth', {

    user: window.__USER__ ?? null,

    get isLoggedIn() {
      return !!this.user;
    },

    async login(email, password) {
      const res = await API.post('/auth/login', {
        email,
        password
      });

      if (res.success) {
        this.user = res.data;
      }

      return res;
    },

    async logout() {

      const ok = await Alpine.store('ui').confirm(
        'Yakin ingin logout?'
      );

      if (!ok) {
        return;
      }

      try {

        const res = await API.post('/auth/logout');

        if (res.success) {
          this.user = null;

          window.location.href =
            window.BASE_URL + '/login';
        }

      } catch (error) {
        console.error('Logout error:', error);
      }
    },

    async refresh() {

      if (!this.user) {
        return null;
      }

      try {

        const res = await API.get(
          '/auth/me',
          false
        );

        if (res.success) {
          this.user = res.data;
          return res.data;
        }

      } catch (error) {

        this.user = null;

        console.error(
          'Refresh auth error:',
          error
        );
      }

      return null;
    }

  });


  /*
  |--------------------------------------------------------------------------
  | UI STORE
  |--------------------------------------------------------------------------
  */

  Alpine.store('ui', {

    loading: false,
    loadingCount: 0,

    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

    startLoading() {

      this.loadingCount++;

      this.loading = true;
    },

    stopLoading() {

      this.loadingCount--;

      if (this.loadingCount <= 0) {

        this.loadingCount = 0;
        this.loading = false;

      }
    },


    /*
    |--------------------------------------------------------------------------
    | TOAST
    |--------------------------------------------------------------------------
    */

    toastMessage: '',
    toastType: 'success',
    toastTimer: null,

    toast(
      message,
      type = 'success',
      duration = 3000
    ) {

      this.toastMessage = message;
      this.toastType = type;

      if (this.toastTimer) {
        clearTimeout(this.toastTimer);
      }

      this.toastTimer = setTimeout(() => {

        this.toastMessage = '';

      }, duration);
    },


    /*
    |--------------------------------------------------------------------------
    | CONFIRM
    |--------------------------------------------------------------------------
    */

    confirmMessage: '',
    confirmShow: false,
    confirmResolve: null,

    confirm(message) {

      this.confirmMessage = message;
      this.confirmShow = true;

      return new Promise((resolve) => {

        this.confirmResolve = resolve;

      });
    },

    confirmYes() {

      this.confirmShow = false;

      if (this.confirmResolve) {

        this.confirmResolve(true);
        this.confirmResolve = null;

      }
    },

    confirmNo() {

      this.confirmShow = false;

      if (this.confirmResolve) {

        this.confirmResolve(false);
        this.confirmResolve = null;

      }
    }

  });


  /*
  |--------------------------------------------------------------------------
  | UTILS STORE
  |--------------------------------------------------------------------------
  */

  Alpine.store('utils', {

    formatRupiah(
      angka,
      prefix = true
    ) {

      const number =
        Number(angka) || 0;

      const formatted =
        new Intl.NumberFormat('id-ID', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 0
        }).format(number);

      return prefix
        ? 'Rp' + formatted
        : formatted;
    },

    formatDate(dateStr) {

      const date = new Date(dateStr);

      return date.toLocaleDateString(
        'id-ID',
        {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }
      );
    },

    formatDateTime(dateStr) {

      const date = new Date(dateStr);

      return date.toLocaleString(
        'id-ID',
        {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        }
      );
    }

  });


  /*
  |--------------------------------------------------------------------------
  | AUTH INITIALIZATION
  |--------------------------------------------------------------------------
  */

  if (Alpine.store('auth').user) {

    Alpine.store('auth').refresh();
  }
});