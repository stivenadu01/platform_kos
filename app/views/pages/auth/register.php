<?php
$googlePending = $_SESSION['google_pending_registration'] ?? null;
$googleModeInitial = isset($_GET['google']) && is_array($googlePending);
$googleError = $_SESSION['google_auth_error'] ?? '';
unset($_SESSION['google_auth_error']);
?>

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

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm font-semibold text-heading">Daftar lebih cepat dengan Google</p>
              <p class="mt-1 text-xs text-muted">Pilihan akun tetap mengikuti role yang Anda pilih di langkah sebelumnya.</p>
              <a
                :href="BASE_URL + '/api/auth/google/start?mode=register&role=' + encodeURIComponent(role)"
                class="w-full h-11 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 flex items-center justify-center gap-3 text-sm font-semibold text-slate-700 transition"
                :class="!role || googleMode ? 'pointer-events-none opacity-50' : ''"
                aria-label="Daftar dengan Google">
                <svg viewBox="0 0 24 24" class="w-5 h-5" aria-hidden="true"><path fill="#4285F4" d="M21.35 12.23c0-.72-.06-1.42-.18-2.09H12v3.96h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.26Z"/><path fill="#34A853" d="M12 21.82c2.63 0 4.84-.87 6.46-2.36l-3.14-2.45c-.87.58-1.98.92-3.32.92-2.55 0-4.71-1.72-5.49-5.04H3.27v2.53A9.76 9.76 0 0 0 12 21.82Z"/><path fill="#FBBC05" d="M6.51 13.89A5.87 5.87 0 0 1 6.2 12c0-.66.11-1.3.31-1.89V7.58H3.27A9.82 9.82 0 0 0 2.18 12c0 1.59.38 3.09 1.09 4.42l3.24-2.53Z"/><path fill="#EA4335" d="M12 6.07c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.15 14.63 2.18 12 2.18a9.76 9.76 0 0 0-8.73 5.4l3.24 2.53C7.29 7.79 9.45 6.07 12 6.07Z"/></svg>
                <span>Daftar dengan Google</span>
              </a>
            </div>

            <div class="my-5 relative">
              <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-slate-200"></div></div>
              <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-muted">atau daftar dengan email</span></div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
              <div class="form-group">
                <label class="label">Nama Lengkap</label>
                <input type="text" x-model="nama" autocomplete="name" placeholder="Masukkan nama lengkap" class="input py-3 px-4" :readonly="googleMode" required>
              </div>

              <div class="form-group">
                <label class="label">Email</label>
                <input type="email" x-model="email" autocomplete="email" placeholder="nama@email.com" class="input py-3 px-4" :readonly="googleMode" required>
              </div>

              <div class="form-group">
                <label class="label">Nomor HP</label>
                <input type="tel" x-model="no_hp" autocomplete="tel" placeholder="08xxxxxxxxxx" class="input py-3 px-4" required>
              </div>

              <div class="form-group">
                <label class="label">NIK</label>
                <input type="text" x-model="nik" autocomplete="off" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" placeholder="Masukkan NIK" class="input py-3 px-4" required>
              </div>

              <template x-if="!googleMode">
                <div>
                  <div class="form-group">
                    <label class="label">Kata Sandi</label>
                    <input type="password" x-model="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="input py-3 px-4" required>
                  </div>

                  <div class="form-group mt-5">
                    <label class="label">Konfirmasi Kata Sandi</label>
                    <input type="password" x-model="konfirmasi_password" autocomplete="new-password" placeholder="Ulangi kata sandi" class="input py-3 px-4" required>
                  </div>
                </div>
              </template>

              <div x-show="googleMode" x-cloak class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-xs text-emerald-800">
                Identitas Google terverifikasi. Anda hanya perlu melengkapi nomor HP dan NIK untuk menyelesaikan pendaftaran.
              </div>

              <button type="submit" class="btn-primary w-full py-3" :disabled="loading">
                <span x-show="!loading" x-text="googleMode ? 'Selesaikan Pendaftaran dengan Google' : 'Daftar sebagai ' + (role === 'pemilik' ? 'Pemilik Kos' : 'Pencari Kos')"></span>
                <span x-show="loading" x-cloak>Memproses...</span>
              </button>
            </form>
          </div>
        </template>

        <p class="mt-6 text-center text-xs text-muted">
          <span x-show="!googleMode">Setelah mendaftar dengan email, Anda perlu memverifikasi email sebelum dapat masuk.</span>
          <span x-show="googleMode" x-cloak>Email Google sudah diverifikasi oleh Google.</span>
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
      step: <?= ($googleModeInitial && !empty($googlePending['role'])) ? '2' : '1' ?>,
      role: <?= json_encode($googlePending['role'] ?? '') ?>,
      nama: <?= json_encode($googlePending['nama'] ?? '') ?>,
      email: <?= json_encode($googlePending['email'] ?? '') ?>,
      password: '',
      konfirmasi_password: '',
      no_hp: '',
      nik: '',
      loading: false,
      googleMode: <?= $googleModeInitial ? 'true' : 'false' ?>,

      selectRole(role) {
        this.role = role;
        this.step = 2;
      },

      async submit() {
        if (!this.role) { this.step = 1; return; }
        if (!this.nama || !this.email || !this.no_hp || !this.nik || (!this.googleMode && (!this.password || !this.konfirmasi_password))) {
          Alpine.store('ui').toast('Semua field harus diisi', 'error'); return;
        }
        if (!this.googleMode && this.password.length < 8) {
          Alpine.store('ui').toast('Kata sandi minimal 8 karakter', 'error'); return;
        }
        if (!/^\d{16}$/.test(this.nik)) {
          Alpine.store('ui').toast('NIK harus terdiri dari 16 digit', 'error'); return;
        }
        if (!this.googleMode && this.password !== this.konfirmasi_password) {
          Alpine.store('ui').toast('Kata sandi tidak cocok', 'error'); return;
        }

        this.loading = true;
        try {
          const res = this.googleMode
            ? await API.post('/auth/google/complete', { role: this.role, no_hp: this.no_hp, nik: this.nik })
            : await API.post('/auth/register', {
                nama: this.nama, email: this.email, password: this.password,
                no_hp: this.no_hp, nik: this.nik, role: this.role,
                konfirmasi_password: this.konfirmasi_password
              });

          if (res.success) {
            if (this.googleMode && res.data) {
              Alpine.store('ui').toast('Pendaftaran dengan Google berhasil', 'success');
              setTimeout(() => { window.location.href = this.role === 'pemilik' ? BASE_URL + '/pemilik' : BASE_URL; }, 700);
            } else {
              Alpine.store('ui').toast('Registrasi berhasil. Cek email untuk verifikasi akun.', 'success');
              setTimeout(() => { window.location.href = BASE_URL + '/login'; }, 1500);
            }
          }
        } catch (error) { console.error(error); }
        finally { this.loading = false; }
      }
    }
  }
</script>
