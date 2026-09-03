(function () {
  const SKIP_KEY = 'betakos_owner_onboarding_skipped_v3';
  const WELCOME_KEY = 'betakos_owner_onboarding_welcome_v3';
  const ACTIVE_KEY = 'betakos_owner_onboarding_active_v3';
  const COMPLETE_KEY = 'betakos_owner_onboarding_complete_v3';

  const route = () => {
    const base = String(window.BASE_URL || '').replace(/\/$/, '');
    const path = window.location.pathname;
    return base && path.indexOf(base) === 0 ? (path.slice(base.length) || '/') : path;
  };
  const absolute = (path) => String(window.BASE_URL || '') + path;
  const isSkipped = () => localStorage.getItem(SKIP_KEY) === '1';
  const isActive = () => localStorage.getItem(ACTIVE_KEY) === '1';
  const hasStarted = () => localStorage.getItem(WELCOME_KEY) === '1';

  window.pemilikOnboardingSkipped = isSkipped();

  function onboarding() {
    return {
      open: false,
      welcome: false,
      loading: true,
      state: null,
      step: null,
      target: null,
      rect: null,
      title: '',
      message: '',
      subTitle: '',
      stepIndex: 0,
      substeps: [],
      currentSubstep: 0,
      targetKey: '',
      waitingForTarget: false,
      errorMessage: '',
      resizeHandler: null,
      refreshHandler: null,
      helpHandler: null,
      mutationTimer: null,
      resolveTimer: null,
      sidebarOpenRequested: false,

      async init() {
        this.resizeHandler = () => {
          if (window.innerWidth >= 1024) this.sidebarOpenRequested = false;
          this.refreshTarget();
        };
        this.refreshHandler = () => this.reload(true);
        this.helpHandler = () => this.openFromHelp();

        window.addEventListener('resize', this.resizeHandler);
        window.addEventListener('scroll', this.resizeHandler, true);
        window.addEventListener('betakos:onboarding-refresh', this.refreshHandler);
        window.addEventListener('betakos:onboarding-help', this.helpHandler);

        await this.reload(false);
      },

      async fetchState() {
        const response = await fetch(absolute('/api/pemilik/onboarding'), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' }
        });
        const json = await response.json();
        return json.success && json.data ? json.data : null;
      },

      async reload(forceOpen = false) {
        try {
          const data = await this.fetchState();
          if (!data) return;

          this.state = data;

          if (this.state.complete) {
            this.finish(true);
            return;
          }

          this.step = this.state.next;
          this.stepIndex = Math.max(0, this.state.steps.findIndex(item => item.key === this.step.key));

          // Welcome hanya sekali pada dashboard pemilik.
          const shouldWelcome =
            !hasStarted() &&
            route() === '/pemilik' &&
            !isSkipped() &&
            !isActive() &&
            !forceOpen;

          if (shouldWelcome) {
            this.open = false;
            this.welcome = true;
            return;
          }

          if (isSkipped() && !forceOpen) {
            this.open = false;
            this.welcome = false;
            return;
          }

          if (forceOpen) {
            localStorage.removeItem(SKIP_KEY);
            localStorage.setItem(ACTIVE_KEY, '1');
            localStorage.setItem(WELCOME_KEY, '1');
            window.pemilikOnboardingSkipped = false;
          }

          // Jika panduan sudah dimulai, status database adalah satu-satunya sumber
          // kebenaran. Tidak ada lagi redirect otomatis yang dapat membuat overlay
          // hilang di halaman perantara.
          if (!isActive()) {
            localStorage.setItem(ACTIVE_KEY, '1');
          }

          this.welcome = false;
          this.open = true;
          this.errorMessage = '';
          this.configureSubsteps();
          this.$nextTick(() => this.resolveTarget());
        } catch (error) {
          console.error('Onboarding gagal dimuat:', error);
          this.errorMessage = 'Panduan belum dapat dimuat. Silakan coba lagi.';
        } finally {
          this.loading = false;
        }
      },

      start() {
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.setItem(ACTIVE_KEY, '1');
        localStorage.removeItem(SKIP_KEY);
        window.pemilikOnboardingSkipped = false;
        this.welcome = false;
        this.open = true;
        this.errorMessage = '';
        this.configureSubsteps();
        this.$nextTick(() => this.resolveTarget());
      },

      skip() {
        clearTimeout(this.mutationTimer);
        clearTimeout(this.resolveTimer);
        localStorage.setItem(SKIP_KEY, '1');
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.removeItem(ACTIVE_KEY);
        window.pemilikOnboardingSkipped = true;
        this.open = false;
        this.welcome = false;
        this.target = null;
        this.rect = null;
        this.waitingForTarget = false;
        window.dispatchEvent(new CustomEvent('betakos:onboarding-skipped'));
      },

      closeWelcome() {
        this.skip();
      },

      openFromHelp() {
        if (localStorage.getItem(COMPLETE_KEY) === '1') return;
        localStorage.removeItem(SKIP_KEY);
        localStorage.setItem(WELCOME_KEY, '1');
        localStorage.setItem(ACTIVE_KEY, '1');
        window.pemilikOnboardingSkipped = false;
        this.open = false;
        this.welcome = false;
        this.reload(true);
        window.dispatchEvent(new CustomEvent('betakos:onboarding-help-closed'));
      },

      finish(completed = false) {
        if (completed) {
          localStorage.setItem(COMPLETE_KEY, '1');
          localStorage.removeItem(SKIP_KEY);
          localStorage.setItem(WELCOME_KEY, '1');
          window.dispatchEvent(new CustomEvent('betakos:onboarding-completed'));
        }
        localStorage.removeItem(ACTIVE_KEY);
        this.open = false;
        this.welcome = false;
        this.rect = null;
        clearTimeout(this.mutationTimer);
        clearTimeout(this.resolveTimer);
        window.pemilikOnboardingSkipped = false;
      },

      configureSubsteps() {
        const key = this.step?.key;
        const current = route();
        const typeSetup = this.state?.type_setup || {};
        const missing = typeSetup.missing || [];
        const typeId = typeSetup.incomplete_id;
        const params = new URLSearchParams(window.location.search);
        const bulk = params.get('mode') === 'bulk';

        let definitions = [];

        if (key === 'profil') {
          definitions = current === '/pemilik/profil'
            ? [
                ['profil-field-nama', 'Nama lengkap', 'Isi nama lengkap Anda.'],
                ['profil-field-hp', 'Nomor HP', 'Isi nomor HP aktif untuk komunikasi akun.'],
                ['profil-save', 'Simpan Profil', 'Simpan perubahan profil untuk melanjutkan.']
              ]
            : [['sidebar-profil', 'Profil Saya', 'Buka Profil Saya untuk melengkapi data pemilik.']];
        }

        if (key === 'kos') {
          const kosSetup = this.state?.kos_setup || {};
          const kosId = kosSetup.incomplete_id;
          const missingKos = kosSetup.missing || [];

          if (current === '/pemilik/kos/tambah') {
            definitions = [
              ['kos-field-nama', 'Nama Kos', 'Masukkan nama kos yang akan ditampilkan kepada pencari.'],
              ['kos-field-alamat', 'Alamat', 'Masukkan alamat lengkap kos.'],
              ['kos-field-jenis', 'Jenis Kos', 'Pilih jenis kos yang sesuai.'],
              ['kos-field-deskripsi', 'Deskripsi', 'Jelaskan kos secara singkat agar calon penghuni memahami tempatnya.'],
              ['kos-field-fasilitas', 'Fasilitas Kos', 'Pilih fasilitas yang tersedia di kos.'],
              ['kos-field-lokasi', 'Lokasi Kos', 'Tentukan lokasi kos pada peta.'],
              ['kos-save', 'Simpan Kos', 'Setelah data lengkap, simpan kos.']
            ];
          } else if (current === '/pemilik/kos/foto') {
            definitions = [
              ['kos-foto-pilih', 'Pilih Foto Kos', 'Pilih foto yang akan digunakan untuk menampilkan properti kos Anda.'],
              ['kos-foto-upload', 'Upload Foto Kos', 'Upload minimal satu foto kos. Setelah berhasil, panduan akan melanjutkan ke pengaturan tipe kamar.']
            ];
          } else if (current === '/pemilik/kos' && !kosSetup.has_any) {
            definitions = [['fast-tambah-kos', 'Tambah Kos', 'Klik Tambah Kos untuk membuat kos pertama.']];
          } else if (current === '/pemilik/kos' && kosId && missingKos.includes('foto')) {
            definitions = [['kos-photo', 'Foto Kos', 'Kos sudah dibuat. Sekarang tambahkan minimal satu foto kos agar calon penghuni dapat melihat properti Anda.']];
          } else {
            definitions = [['sidebar-kos', 'Kos Saya', 'Buka Kos Saya untuk melanjutkan.']];
          }
        }

        if (key === 'tipe_kamar') {
          if (current === '/pemilik/kamar') {
            definitions = typeSetup.has_any
              ? [['fast-kelola-tipe-kamar', 'Kelola Tipe Kamar', 'Tipe kamar sudah ada. Buka Kelola Tipe Kamar untuk melengkapi tipe yang masih diperlukan.']]
              : [['fast-tambah-tipe-kamar', 'Tambah Tipe Kamar', 'Belum ada tipe kamar. Buat satu tipe kamar terlebih dahulu.']];
          } else if (current === '/pemilik/tipe-kamar') {
            if (typeId && missing.includes('foto')) {
              definitions = [['tipe-photo-existing', 'Foto Tipe Kamar', 'Tipe kamar sudah tersimpan. Buka Foto pada tipe yang ditunjuk untuk menambahkan minimal satu foto.']];
            } else if (typeId && missing.includes('harga')) {
              definitions = [['tipe-edit-existing', 'Lengkapi Tipe Kamar', 'Tipe kamar sudah ada, tetapi harga belum tersedia. Buka Kelola untuk melengkapinya.']];
            } else {
              definitions = [['fast-tambah-tipe-kamar-list', 'Tambah Tipe Kamar', 'Buat tipe kamar yang akan digunakan oleh unit kamar Anda.']];
            }
          } else if (current === '/pemilik/tipe-kamar/tambah' || current === '/pemilik/tipe-kamar/edit') {
            definitions = [
              ['tipe-field-kos', 'Pilih Kos', 'Pilih kos yang menggunakan tipe kamar ini.'],
              ['tipe-field-nama', 'Nama Tipe', 'Contoh: Standard, Deluxe, atau VIP.'],
              ['tipe-field-kapasitas', 'Kapasitas', 'Tentukan jumlah maksimal penghuni.'],
              ['tipe-field-harga', 'Harga', 'Atur harga kamar sesuai kapasitas dan kebijakan kos.'],
              ['tipe-field-fasilitas', 'Fasilitas Kamar', 'Pilih fasilitas yang tersedia pada tipe kamar.'],
              ['tipe-save', 'Simpan Tipe', 'Simpan tipe kamar. Setelah tersimpan, panduan berlanjut ke foto.']
            ];
          } else if (current === '/pemilik/tipe-kamar/foto') {
            definitions = [
              ['tipe-foto-pilih', 'Pilih Foto', 'Pilih foto kamar yang jelas dan representatif.'],
              ['tipe-foto-upload', 'Upload Foto', 'Upload foto. Minimal satu foto diperlukan agar tipe kamar lengkap.']
            ];
          } else {
            definitions = [['sidebar-kamar', 'Kelola Kamar', 'Buka Kelola Kamar untuk mengatur tipe kamar.']];
          }
        }

        if (key === 'kamar') {
          if (current === '/pemilik/tipe-kamar') {
            definitions = [['tipe-back-kamar', 'Kembali ke Kelola Kamar', 'Tipe kamar sudah lengkap. Kembali ke Kelola Kamar untuk menambahkan unit kamar.']];
          } else if (current === '/pemilik/kamar') {
            definitions = [['kamar-add-choice', 'Pilih Cara Menambah Kamar', 'Pilih Tambah Banyak Kamar atau Tambah Satu Kamar. Keduanya akan membuat unit kamar menggunakan tipe kamar yang sudah dibuat.']];
          } else if (current === '/pemilik/kamar/tambah') {
            definitions = [
              ['kamar-field-kos', 'Pilih Kos', 'Pilih kos tempat kamar ini berada.'],
              ['kamar-field-tipe', 'Pilih Tipe Kamar', 'Pilih tipe kamar yang sudah Anda siapkan.'],
              [bulk ? 'kamar-field-nomor-bulk' : 'kamar-field-nomor-single', 'Nomor Kamar', bulk ? 'Masukkan nomor awal dan jumlah kamar yang ingin dibuat.' : 'Masukkan nomor kamar yang akan dibuat.'],
              ['kamar-save', 'Buat Kamar', 'Simpan untuk membuat unit kamar.']
            ];
          } else {
            definitions = [['sidebar-kamar', 'Kelola Kamar', 'Buka Kelola Kamar untuk menambahkan unit kamar.']];
          }
        }

        if (key === 'verifikasi') {
          definitions = current === '/pemilik/kos'
            ? [['fast-ajukan-verifikasi', 'Ajukan Verifikasi', 'Ajukan kos yang sudah lengkap untuk diperiksa Admin.']]
            : [['sidebar-kos', 'Kos Saya', 'Buka Kos Saya untuk mengajukan verifikasi.']];
        }

        this.substeps = definitions;
        // Substep selalu ditentukan oleh halaman saat ini. Tidak disimpan lintas
        // halaman agar perpindahan route tidak mengembalikan panduan ke langkah lama.
        this.currentSubstep = 0;
        this.sidebarOpenRequested = false;
        this.applyCurrentSubstep();
      },

      applyCurrentSubstep() {
        const item = this.substeps[this.currentSubstep];
        if (!item) return;
        this.targetKey = item[0];
        this.subTitle = item[1];
        this.message = item[2];
      },

      async nextSubstep() {
        // Tombol pada langkah terakhir tidak mengubah state secara manual.
        // Database menjadi sumber kebenaran sehingga onboarding tidak pernah
        // menandai langkah selesai padahal data belum benar-benar tersimpan.
        if (this.currentSubstep < this.substeps.length - 1) {
          this.currentSubstep += 1;
          this.applyCurrentSubstep();
          this.$nextTick(() => this.resolveTarget());
          return;
        }

        this.loading = true;
        const previousKey = this.step?.key;
        try {
          const data = await this.fetchState();
          if (data) {
            this.state = data;
            if (data.complete) {
              this.finish(true);
              return;
            }
            this.step = data.next;
            this.stepIndex = Math.max(0, data.steps.findIndex(item => item.key === this.step.key));
            this.configureSubsteps();
            this.errorMessage = this.step.key === previousKey
              ? 'Data untuk langkah ini belum terdeteksi lengkap. Selesaikan tindakan pada bagian yang disorot terlebih dahulu.'
              : '';
            this.$nextTick(() => this.resolveTarget());
          }
        } catch (error) {
          console.error('Gagal memeriksa progres onboarding:', error);
          this.errorMessage = 'Gagal memeriksa progres. Silakan coba lagi.';
        } finally {
          this.loading = false;
        }
      },

      resolveTarget() {
        if (!this.step || !this.open) return;

        this.target = null;
        this.rect = null;
        this.waitingForTarget = true;

        // Pada mobile, item sidebar berada di luar viewport karena sidebar tertutup.
        // Buka sidebar terlebih dahulu agar spotlight benar-benar menunjuk elemen yang
        // dapat dilihat dan dipelajari pengguna.
        const needsMobileSidebar =
          window.innerWidth < 1024 &&
          typeof this.targetKey === 'string' &&
          this.targetKey.indexOf('sidebar-') === 0;

        if (needsMobileSidebar && !this.sidebarOpenRequested) {
          this.sidebarOpenRequested = true;
          window.dispatchEvent(new CustomEvent('betakos:onboarding-open-sidebar'));
          this.scheduleResolve(350);
          return;
        }

        // Untuk tipe/kos yang belum lengkap, cari tombol berdasarkan ID dari API.
        if (this.step.key === 'kos' && route() === '/pemilik/kos') {
          const id = this.state.kos_setup?.incomplete_id;
          if (id && (this.state.kos_setup?.missing || []).includes('foto')) {
            this.target = document.querySelector('a[href*="/pemilik/kos/foto?id=' + id + '"]');
          }
        }

        if (this.step.key === 'tipe_kamar' && route() === '/pemilik/tipe-kamar') {
          const id = this.state.type_setup?.incomplete_id;
          const missing = this.state.type_setup?.missing || [];
          if (id && missing.includes('foto')) {
            this.target = document.querySelector('a[href*="/pemilik/tipe-kamar/foto?id_tipe_kamar=' + id + '"]');
          } else if (id && missing.includes('harga')) {
            this.target = document.querySelector('a[href*="/pemilik/tipe-kamar/edit?id_tipe_kamar=' + id + '"]');
          }
        }

        if (!this.target && this.targetKey) {
          this.target = document.querySelector('[data-onboarding="' + this.targetKey + '"]');
        }

        if (!this.target) {
          this.scheduleResolve(250);
          return;
        }

        // Jika viewport berubah dari mobile ke desktop, reset flag agar permintaan
        // membuka sidebar dapat dilakukan lagi saat memang diperlukan.
        if (window.innerWidth >= 1024) this.sidebarOpenRequested = false;

        this.waitingForTarget = false;
        this.title = 'Langkah ' + (this.stepIndex + 1) + ' dari ' + this.state.total;
        this.target.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        setTimeout(() => this.refreshTarget(), 120);
      },

      scheduleResolve(delay = 250) {
        clearTimeout(this.resolveTimer);
        this.resolveTimer = setTimeout(() => this.resolveTarget(), delay);
      },

      refreshTarget() {
        if (!this.target || !document.body.contains(this.target)) {
          if (this.open) this.resolveTarget();
          return;
        }
        const r = this.target.getBoundingClientRect();
        this.rect = { top: r.top, left: r.left, width: r.width, height: r.height };
      },

      get highlightStyle() {
        if (!this.rect) return '';
        return `top:${this.rect.top - 7}px;left:${this.rect.left - 7}px;width:${Math.max(this.rect.width + 14, 24)}px;height:${Math.max(this.rect.height + 14, 24)}px;`;
      },

      get tooltipStyle() {
        if (!this.rect) return '';
        const gap = 14;
        const width = Math.min(390, window.innerWidth - 24);
        let left = Math.max(12, Math.min(this.rect.left, window.innerWidth - width - 12));
        let top = this.rect.top + this.rect.height + gap;
        if (top + 230 > window.innerHeight) top = Math.max(12, this.rect.top - 230 - gap);
        return `left:${left}px;top:${top}px;width:${width}px;`;
      }
    };
  }

  window.pemilikOnboarding = onboarding;
})();
