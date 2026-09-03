(function () {
  const route = () => {
    const base = String(window.BASE_URL || '').replace(/\/$/, '');
    const path = window.location.pathname;
    return base && path.indexOf(base) === 0 ? (path.slice(base.length) || '/') : path;
  };

  const GUIDE = {
    '/pemilik': {
      title: 'Dashboard Pemilik',
      intro: 'Gunakan Dashboard untuk melihat kondisi kos dan menentukan apa yang perlu ditindaklanjuti.',
      steps: [
        ['dashboard-ringkasan', 'Ringkasan kondisi kos', 'Angka di sini membantu Anda melihat jumlah kos, kamar terisi, kamar tersedia, dan penghuni aktif secara cepat.'],
        ['dashboard-keuangan', 'Ringkasan keuangan', 'Pantau tagihan yang belum lunas, total piutang, dan pembayaran bulan ini.'],
        ['dashboard-aksi', 'Aksi cepat', 'Gunakan aksi cepat untuk langsung menambah kos, kamar, penghuni, atau membuka halaman pembayaran.'],
        ['dashboard-tagihan', 'Tagihan terdekat', 'Bagian ini membantu Anda menemukan tagihan yang masih memiliki sisa pembayaran.']
      ]
    },
    '/pemilik/profil': {
      title: 'Profil Pemilik',
      intro: 'Pastikan data kontak pemilik tetap benar karena digunakan sebagai identitas dan komunikasi akun.',
      steps: [
        ['help-profil-data', 'Data profil', 'Perbarui nama dan nomor HP jika ada perubahan. Gunakan nomor yang aktif.'],
        ['help-profil-foto', 'Foto profil', 'Foto profil dapat membantu membedakan akun pemilik saat ditampilkan di aplikasi.'],
        ['help-profil-password', 'Kata sandi', 'Gunakan bagian ini jika Anda ingin mengganti kata sandi akun.']
      ]
    },
    '/pemilik/kos': {
      title: 'Kos Saya',
      intro: 'Halaman ini adalah pusat pengelolaan properti. Dari sini Anda dapat melihat kos dan masuk ke pengaturan detailnya.',
      steps: [
        ['help-kos-list', 'Daftar kos', 'Setiap kartu mewakili satu properti yang Anda miliki. Gunakan aksi pada kartu untuk mengelolanya.'],
        ['help-kos-action', 'Aksi kos', 'Gunakan aksi yang tersedia untuk melihat, mengubah data, mengelola foto, atau melanjutkan proses verifikasi.'],
        ['help-kos-add', 'Tambah kos', 'Jika Anda memiliki properti lain, buat kos baru dari tombol ini.']
      ]
    },
    '/pemilik/tipe-kamar': {
      title: 'Tipe Kamar',
      intro: 'Tipe kamar adalah kategori harga dan karakteristik kamar, misalnya Standard atau Deluxe. Satu tipe dapat digunakan oleh beberapa unit kamar.',
      steps: [
        ['help-tipe-list', 'Daftar tipe kamar', 'Periksa nama tipe, kapasitas, dan harga yang berlaku.'],
        ['help-tipe-action', 'Kelola tipe', 'Gunakan Kelola untuk mengubah data tipe dan Foto untuk mengatur foto khusus tipe tersebut.'],
        ['help-tipe-add', 'Tambah tipe kamar', 'Buat tipe baru jika kos memiliki kategori kamar dengan kapasitas atau harga berbeda.']
      ]
    },
    '/pemilik/kamar': {
      title: 'Kelola Kamar',
      intro: 'Kamar adalah unit fisik yang benar-benar ditempati penghuni. Tipe kamar menentukan karakteristik dan harga unit tersebut.',
      steps: [
        ['help-kamar-summary', 'Daftar kamar', 'Gunakan daftar ini untuk melihat kos, tipe kamar, nomor kamar, kapasitas, dan status ketersediaan.'],
        ['help-kamar-filter', 'Filter kamar', 'Gunakan filter saat memiliki banyak unit agar pencarian kamar lebih cepat.'],
        ['help-kamar-add', 'Tambah kamar', 'Gunakan Tambah Satu Kamar untuk satu unit atau Tambah Banyak Kamar jika ingin membuat beberapa nomor sekaligus.'],
        ['help-kamar-type', 'Tipe kamar', 'Kelola Tipe Kamar digunakan untuk mengatur kategori, kapasitas, harga, fasilitas, dan foto tipe.']
      ]
    },
    '/pemilik/penghuni': {
      title: 'Kelola Penghuni',
      intro: 'Gunakan halaman ini untuk mencatat siapa yang sedang menempati kamar dan menjaga riwayat penghuni tetap rapi.',
      steps: [
        ['help-penghuni-add', 'Tambah penghuni', 'Saat penghuni baru masuk, gunakan Tambah Penghuni. Sistem akan mencari akun berdasarkan NIK dan menghubungkannya dengan kamar.'],
        ['help-penghuni-filter', 'Cari dan filter', 'Gunakan pencarian, kos, kamar, dan status untuk menemukan penghuni tertentu.'],
        ['help-penghuni-table', 'Data penghuni', 'Periksa kamar, tanggal masuk, dan status penghuni dari tabel.'],
        ['help-penghuni-actions', 'Keluar atau edit', 'Edit digunakan untuk perubahan identitas. Jika penghuni berhenti tinggal, gunakan Catat Penghuni Keluar agar riwayat dan tagihan tetap konsisten.']
      ]
    },
    '/pemilik/penghuni/tambah': {
      title: 'Tambah Penghuni',
      intro: 'Pencatatan penghuni juga memengaruhi kamar dan tagihan. Ikuti urutan ini agar data operasional tetap konsisten.',
      steps: [
        ['help-penghuni-nik', '1. Cari berdasarkan NIK', 'Masukkan NIK 16 digit. Jika akun pelanggan sudah terdaftar, data identitas akan diambil dari akun tersebut.'],
        ['help-penghuni-room', '2. Pilih kamar', 'Pilih kamar yang akan ditempati. Sistem menampilkan kapasitas dan jumlah penghuni saat ini.'],
        ['help-penghuni-date', '3. Tentukan tanggal masuk', 'Tanggal masuk menjadi dasar periode sewa dan perhitungan tagihan.'],
        ['help-penghuni-save', '4. Simpan penghuni', 'Setelah disimpan, sistem membuat atau menyesuaikan tagihan sesuai kondisi penghuni dan kamar.']
      ]
    },
    '/pemilik/penghuni/edit': {
      title: 'Edit Penghuni',
      intro: 'Halaman edit sengaja berfokus pada identitas agar riwayat kamar dan tagihan tidak berubah tanpa proses operasional yang benar.',
      steps: [
        ['help-penghuni-edit-data', 'Data identitas', 'Perbarui data identitas yang memang perlu dikoreksi.'],
        ['help-penghuni-save', 'Simpan perubahan', 'Simpan setelah koreksi selesai. Perubahan kamar dan tanggal masuk tidak dilakukan dari halaman ini.']
      ]
    },
    '/pemilik/pembayaran': {
      title: 'Tagihan & Pembayaran',
      intro: 'Ini adalah pusat keuangan operasional penghuni: melihat tagihan, menambahkan penyesuaian, dan mencatat pembayaran.',
      steps: [
        ['help-tagihan-summary', 'Ringkasan tagihan', 'Lihat jumlah tagihan belum lunas, sebagian, lunas, dan total sisa pembayaran.'],
        ['help-tagihan-filter', 'Cari dan filter', 'Gunakan nomor tagihan, kos, kamar, atau status untuk menemukan tagihan tertentu.'],
        ['help-tagihan-list', 'Daftar tagihan', 'Periksa periode, total, sisa, jatuh tempo, dan status sebelum melakukan tindakan.'],
        ['help-tagihan-detail', 'Detail tagihan', 'Buka Detail untuk melihat penghuni, penyesuaian, dan riwayat pembayaran dalam satu periode.', 'open-tagihan-detail'],
        ['help-tagihan-payment', 'Catat pembayaran', 'Catat pembayaran yang benar-benar diterima. Pilih penghuni, jumlah, metode, waktu, dan catatan bila diperlukan.'],
        ['help-tagihan-adjustment', 'Penyesuaian', 'Gunakan penyesuaian untuk menambah atau mengurangi nilai tagihan dengan tanggal efektif dan alasan yang jelas.']
      ]
    },
    '/pemilik/kos/tambah': {
      title: 'Tambah Kos',
      intro: 'Isi data properti dengan informasi yang benar agar kos mudah dikelola dan nantinya siap ditampilkan.',
      steps: [
        ['help-kos-form-main', 'Informasi kos', 'Isi nama, alamat, jenis, dan deskripsi kos.'],
        ['help-kos-form-facility', 'Fasilitas', 'Pilih fasilitas yang memang tersedia di properti.'],
        ['help-kos-form-location', 'Lokasi', 'Tentukan lokasi kos pada bagian lokasi yang tersedia.'],
        ['help-kos-form-save', 'Simpan', 'Simpan setelah data utama selesai. Setelah kos dibuat, Anda dapat mengelola foto dan bagian lainnya.']
      ]
    },
    '/pemilik/kamar/tambah': {
      title: 'Tambah Kamar',
      intro: 'Tambahkan unit fisik yang akan dihuni. Pastikan tipe kamar sudah tersedia sebelum membuat unit.',
      steps: [
        ['help-kamar-form-kos', 'Pilih kos', 'Tentukan properti tempat unit kamar berada.'],
        ['help-kamar-form-type', 'Pilih tipe kamar', 'Hubungkan unit dengan tipe kamar agar kapasitas dan harga memiliki dasar yang jelas.'],
        ['help-kamar-form-number', 'Nomor kamar', 'Isi nomor kamar. Pada mode banyak kamar, tentukan nomor awal dan jumlah unit.'],
        ['help-kamar-form-save', 'Buat kamar', 'Simpan untuk membuat unit kamar.']
      ]
    },
    '/pemilik/tipe-kamar/tambah': {
      title: 'Tambah Tipe Kamar',
      intro: 'Buat kategori kamar yang menjadi dasar kapasitas, harga, fasilitas, dan foto kamar.',
      steps: [
        ['help-tipe-form-main', 'Data tipe', 'Pilih kos, beri nama tipe, dan tentukan kapasitas.'],
        ['help-tipe-form-price', 'Harga', 'Atur harga sesuai jumlah penghuni yang didukung oleh tipe kamar.'],
        ['help-tipe-form-facility', 'Fasilitas kamar', 'Pilih fasilitas yang memang tersedia pada tipe tersebut.'],
        ['help-tipe-form-save', 'Simpan', 'Simpan tipe kamar. Foto tipe dikelola pada halaman Foto setelah tipe dibuat.']
      ]
    }
  };

  function getGuide() {
    const current = route();
    if (GUIDE[current]) return GUIDE[current];
    if (current === '/pemilik/kos/edit') return GUIDE['/pemilik/kos/tambah'];
    if (current === '/pemilik/kamar/edit' || current === '/pemilik/kamar/harga') return GUIDE['/pemilik/kamar'];
    if (current === '/pemilik/tipe-kamar/edit') return GUIDE['/pemilik/tipe-kamar/tambah'];
    if (current === '/pemilik/kos/foto') return {
      title: 'Foto Kos', intro: 'Kelola foto utama properti. Foto kos berbeda dari foto tipe kamar.',
      steps: [['help-kos-photo-picker', 'Pilih foto', 'Pilih foto properti yang jelas dan mewakili kondisi kos.'], ['help-kos-photo-upload', 'Upload foto', 'Upload foto properti. Gunakan foto yang relevan untuk membantu pencari memahami kos.']]
    };
    if (current === '/pemilik/tipe-kamar/foto') return {
      title: 'Foto Tipe Kamar', intro: 'Foto di sini khusus untuk kategori kamar tertentu, bukan foto keseluruhan properti.',
      steps: [['help-tipe-photo-list', 'Foto tipe kamar', 'Foto yang tampil di sini hanya milik tipe kamar yang sedang dikelola.'], ['help-tipe-photo-upload', 'Upload foto', 'Tambahkan foto kamar yang jelas agar pencari dapat memahami kondisi tipe tersebut.']]
    };
    return { title: 'Bantuan BetaKos', intro: 'Panduan kontekstual untuk halaman ini belum tersedia.', steps: [] };
  }

  function operationalHelp() {
    return {
      open: false,
      guide: null,
      current: 0,
      target: null,
      rect: null,
      resizeHandler: null,
      scrollHandler: null,
      tooltipPosition: null,
      advancing: false,

      init() {
        this.resizeHandler = () => this.refreshTarget();
        this.scrollHandler = () => this.refreshTarget();
        window.addEventListener('betakos:tagihan-detail-opened', () => {
          if (!this.open || this.advancing) return;
          const step = this.guide?.steps?.[this.current];
          if (step?.[0] !== 'help-tagihan-detail') return;
          this.waitForElement('[data-help="help-tagihan-payment"]', () => {
            this.current += 1;
            this.$nextTick(() => this.resolveTarget());
          });
        });
        window.addEventListener('resize', this.resizeHandler);
        window.addEventListener('scroll', this.scrollHandler, true);
        window.addEventListener('betakos:operational-help', () => this.start());
        this.guide = getGuide();
      },

      start() {
        this.guide = getGuide();
        this.current = 0;
        this.open = true;
        this.$nextTick(() => this.resolveTarget());
      },

      close() {
        this.open = false;
        this.target = null;
        this.rect = null;
        this.tooltipPosition = null;
        this.advancing = false;
      },

      next() {
        if (!this.guide) return;
        const step = this.guide.steps?.[this.current];
        if (!step) {
          this.close();
          return;
        }

        // Beberapa bantuan operasional perlu menjalankan aksi nyata sebelum
        // pindah ke langkah berikutnya. Contoh: membuka Detail Tagihan.
        if (step[3] === 'open-tagihan-detail' && this.target) {
          this.advancing = true;
          this.target.click();
          this.waitForElement('[data-help="help-tagihan-payment"]', () => {
            this.advancing = false;
            if (this.current >= this.guide.steps.length - 1) {
              this.close();
              return;
            }
            this.current += 1;
            this.$nextTick(() => this.resolveTarget());
          });
          return;
        }

        if (this.current >= this.guide.steps.length - 1) {
          this.close();
          return;
        }
        this.current += 1;
        this.$nextTick(() => this.resolveTarget());
      },

      prev() {
        if (this.current <= 0) return;
        this.current -= 1;
        this.$nextTick(() => this.resolveTarget());
      },

      resolveTarget() {
        const step = this.guide?.steps?.[this.current];
        this.target = null;
        this.rect = null;
        this.tooltipPosition = null;
        if (!step) return;

        const find = () => {
          const el = document.querySelector('[data-help="' + step[0] + '"]');
          if (!el || !this.isVisible(el)) return false;
          this.target = el;
          el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
          setTimeout(() => this.refreshTarget(), 180);
          return true;
        };

        if (!find()) {
          // Modal/detail dapat muncul setelah Alpine menyelesaikan render.
          this.waitForElement('[data-help="' + step[0] + '"]', () => this.resolveTarget(), 2500);
        }
      },

      isVisible(el) {
        if (!el || !document.body.contains(el)) return false;
        const style = window.getComputedStyle(el);
        const r = el.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && r.width > 0 && r.height > 0;
      },

      waitForElement(selector, callback, timeout = 3000) {
        const started = Date.now();
        const check = () => {
          const el = document.querySelector(selector);
          if (this.isVisible(el)) {
            callback(el);
            return;
          }
          if (Date.now() - started >= timeout) {
            this.advancing = false;
            return;
          }
          setTimeout(check, 80);
        };
        check();
      },

      refreshTarget() {
        if (!this.open || !this.target || !document.body.contains(this.target)) return;
        const r = this.target.getBoundingClientRect();
        this.rect = { top: r.top, left: r.left, width: r.width, height: r.height };
        this.$nextTick(() => this.positionTooltip());
      },

      get highlightStyle() {
        if (!this.rect) return '';
        return `top:${this.rect.top - 7}px;left:${this.rect.left - 7}px;width:${Math.max(this.rect.width + 14, 24)}px;height:${Math.max(this.rect.height + 14, 24)}px;`;
      },

      positionTooltip() {
        if (!this.open || !this.rect || !this.$refs.tooltip) return;

        // Tooltip WAJIB berada di luar area target yang sedang dijelaskan.
        // Jika ruang tidak cukup, tinggi tooltip dipangkas sesuai ruang yang
        // benar-benar tersedia di atas/bawah target dan isi tooltip dapat di-scroll.
        const gap = 14;
        const margin = 12;
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        const width = Math.min(390, viewportWidth - margin * 2);
        const targetTop = Math.max(margin, this.rect.top);
        const targetBottom = Math.min(viewportHeight - margin, this.rect.top + this.rect.height);
        const spaceBelow = Math.max(0, viewportHeight - margin - (this.rect.top + this.rect.height) - gap);
        const spaceAbove = Math.max(0, this.rect.top - margin - gap);
        const measuredHeight = this.$refs.tooltip.getBoundingClientRect().height || 0;

        // Utamakan sisi yang mampu menampung tooltip paling banyak.
        // Bila salah satu sisi cukup untuk tinggi penuh, gunakan sisi tersebut.
        const placeBelow = spaceBelow >= measuredHeight || spaceBelow >= spaceAbove;
        const availableHeight = Math.max(0, placeBelow ? spaceBelow : spaceAbove);

        let top;
        if (placeBelow) {
          top = this.rect.top + this.rect.height + gap;
        } else {
          top = this.rect.top - gap - availableHeight;
        }

        // Jangan pernah menggeser tooltip ke dalam target hanya demi menjaga
        // tooltip tetap berada di viewport. Jika ruang sedikit, tooltip dibuat
        // lebih pendek dan scrollable.
        top = Math.max(margin, Math.min(top, viewportHeight - margin - availableHeight));

        // Koreksi kecil agar batas tooltip tetap terpisah dari target.
        if (placeBelow) {
          top = Math.max(top, this.rect.top + this.rect.height + gap);
        } else {
          top = Math.min(top, this.rect.top - gap - availableHeight);
        }

        const left = Math.max(
          margin,
          Math.min(this.rect.left, viewportWidth - width - margin)
        );

        this.tooltipPosition = {
          left,
          top,
          width,
          maxHeight: availableHeight,
          placement: placeBelow ? 'below' : 'above',
          targetTop,
          targetBottom
        };
      },

      get tooltipStyle() {
        if (!this.tooltipPosition) return '';
        const p = this.tooltipPosition;
        return `left:${p.left}px;top:${p.top}px;width:${p.width}px;max-height:${Math.max(p.maxHeight, 80)}px;overflow-y:auto;`;
      }
    };
  }

  window.pemilikOperationalHelp = operationalHelp;
})();
