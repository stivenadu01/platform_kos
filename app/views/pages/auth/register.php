<div class="section-center bg-slate-50 px-4 py-8 sm:py-10">
  <div
    x-data="registerForm()"
    class="w-full max-w-5xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 bg-white rounded-2xl border border-slate-200 shadow-sm">

    <!-- BRAND -->
    <div class="hidden lg:flex flex-center flex-col gap-5 p-12 bg-primary-soft">
      <img :src="BASE_URL + '/assets/icon/logo.png'" alt="BetaKos"
        class="w-24 h-24 object-contain">

      <div class="text-center">
        <h2 class="text-2xl font-bold text-heading" x-text="role === 'pemilik' ? 'Kelola Kos Lebih Mudah & Promosikan Kos Anda' : 'Temukan Kos Lebih Mudah'"></h2>
        <p class="mt-2 text-sm text-muted max-w-sm" x-text="role === 'pemilik' ? 'Daftarkan diri sebagai pemilik kos untuk mengelola properti dan mempromosikan kos Anda melalui BetaKos.' : 'Bergabung untuk mencari kos berdasarkan kebutuhan dan lokasi yang Anda inginkan.'"></p>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="p-6 sm:p-10 lg:p-12">
      <div class="max-w-md mx-auto">

        <!-- STEP 1: ROLE -->
        <template x-if="step === 1">
          <div>
            <div class="text-center sm:text-left">
              <p class="text-xs font-semibold uppercase tracking-wide text-primary">Langkah 1 dari 2</p>
              <h1 class="mt-2 text-2xl font-bold text-heading">Bagaimana Anda ingin menggunakan BetaKos?</h1>
              <p class="mt-2 text-sm text-muted">Pilih akun sesuai kebutuhan Anda.</p>
            </div>

            <div class="mt-7 space-y-4">
              <button
                type="button"
                @click="selectRole('pemilik')"
                class="w-full text-left rounded-2xl border border-slate-200 p-5 transition hover:border-primary hover:bg-primary-soft focus:outline-none focus:ring-2 focus:ring-primary/20">
                <div class="flex items-start gap-4">
                  <div class="h-12 w-12 shrink-0 rounded-xl bg-primary-soft flex-center text-primary">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 10h.01M15 10h.01M9 13h.01M15 13h.01"/></svg>
                  </div>
                  <div>
                    <h2 class="font-bold text-heading">Pemilik Kos</h2>
                    <p class="mt-1 text-sm text-muted">Saya memiliki atau mengelola kos dan ingin mengelolanya melalui BetaKos.</p>
                  </div>
                </div>
              </button>

              <button
                type="button"
                @click="selectRole('pelanggan')"
                class="w-full text-left rounded-2xl border border-slate-200 p-5 transition hover:border-primary hover:bg-primary-soft focus:outline-none focus:ring-2 focus:ring-primary/20">
                <div class="flex items-start gap-4">
                  <div class="h-12 w-12 shrink-0 rounded-xl bg-primary-soft flex-center text-primary">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19a8 8 0 0 1 16 0M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                  </div>
                  <div>
                    <h2 class="font-bold text-heading">Pencari Kos</h2>
                    <p class="mt-1 text-sm text-muted">Saya sedang mencari kos dan ingin menemukan tempat tinggal yang sesuai dengan kebutuhan saya.</p>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </template>

        <!-- STEP 2: ACCOUNT FORM -->
        <template x-if="step === 2">
          <div>
            <div class="flex items-center gap-3">
              <button type="button" @click="step = 1" class="h-9 w-9 rounded-lg border border-slate-200 flex-center text-muted hover:text-heading hover:bg-slate-50" aria-label="Kembali">
                ←
              </button>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Langkah 2 dari 2</p>
                <h1 class="mt-1 text-2xl font-bold text-heading">Buat Akun <span x-text="role === 'pemilik' ? 'Pemilik Kos' : 'Pencari Kos'"></span></h1>
              </div>
            </div>

            <p class="mt-3 text-sm text-muted">
              <span x-show="role === 'pemilik'">Setelah akun aktif, Anda dapat menambahkan kos dari menu Kelola Kos. Verifikasi kos dilakukan melalui proses verifikasi oleh tim BetaKos.</span>
              <span x-show="role === 'pelanggan'">Setelah akun aktif, Anda dapat mencari dan menyimpan kos yang sesuai dengan kebutuhan Anda.</span>
            </p>

            <form @submit.prevent="submit" class="mt-7 space-y-5">
              <div class="form-group">
                <label class="label">Nama Lengkap</label>
                <input type="text" x-model="nama" autocomplete="name" placeholder="Masukkan nama lengkap" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">Email</label>
                <input type="email" x-model="email" autocomplete="email" placeholder="nama@email.com" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">Nomor HP</label>
                <input type="tel" x-model="no_hp" autocomplete="tel" placeholder="08xxxxxxxxxx" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">NIK</label>
                <input type="text" x-model="nik" autocomplete="off" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" placeholder="Masukkan NIK" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">Kata Sandi</label>
                <input type="password" x-model="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">Konfirmasi Kata Sandi</label>
                <input type="password" x-model="konfirmasi_password" autocomplete="new-password" placeholder="Ulangi kata sandi" class="input py-3 px-4" required>
              </div>

              <button type="submit" class="btn-primary w-full py-3" :disabled="loading">
                <span x-show="!loading">Daftar sebagai <span x-text="role === 'pemilik' ? 'Pemilik Kos' : 'Pencari Kos'"></span></span>
                <span x-show="loading" x-cloak>Memproses...</span>
              </button>
            </form>
          </div>
        </template>

        <p class="mt-6 text-center text-xs text-muted">
          Setelah mendaftar, Anda perlu memverifikasi email sebelum dapat masuk.
        </p>

        <div class="mt-4 text-center text-sm text-muted">
          Sudah punya akun?
          <a :href="BASE_URL + '/login'" class="font-semibold text-primary hover:text-primary-dark">Masuk di sini</a>
        </div>

      </div>
    </div>
  </div>
</div>


<script>
  function registerForm() {
    return {
      step: 1,
      role: '',
      nama: '',
      email: '',
      password: '',
      konfirmasi_password: '',
      no_hp: '',
      nik: '',
      loading: false,

      selectRole(role) {
        this.role = role;
        this.step = 2;
      },

      async submit() {
        if (!this.role) {
          this.step = 1;
          return;
        }

        if (!this.nama || !this.email || !this.password || !this.konfirmasi_password || !this.no_hp || !this.nik) {
          Alpine.store('ui').toast('Semua field harus diisi', 'error');
          return;
        }

        if (this.password.length < 8) {
          Alpine.store('ui').toast('Kata sandi minimal 8 karakter', 'error');
          return;
        }

        if (!/^\d{16}$/.test(this.nik)) {
          Alpine.store('ui').toast('NIK harus terdiri dari 16 digit', 'error');
          return;
        }

        if (this.password !== this.konfirmasi_password) {
          Alpine.store('ui').toast('Kata sandi tidak cocok', 'error');
          return;
        }

        this.loading = true;

        try {
          const res = await API.post('/auth/register', {
            nama: this.nama,
            email: this.email,
            password: this.password,
            no_hp: this.no_hp,
            nik: this.nik,
            role: this.role,
            konfirmasi_password: this.konfirmasi_password
          });

          if (res.success) {
            Alpine.store('ui').toast('Registrasi berhasil. Cek email untuk verifikasi akun.', 'success');

            setTimeout(() => {
              window.location.href = BASE_URL + '/login';
            }, 1500);
          }
        } catch (error) {
          console.error(error);
        } finally {
          this.loading = false;
        }
      }
    }
  }
</script>
