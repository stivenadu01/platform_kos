<?php
$googleError = $_SESSION['google_auth_error'] ?? '';
unset($_SESSION['google_auth_error']);
?>

<div class="section-center bg-slate-50 px-4" x-data="loginForm()">
  <div class="w-full max-w-5xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 bg-white rounded-2xl border border-slate-200 shadow-sm">

    <!-- BRAND -->
    <div class="hidden lg:flex flex-center flex-col gap-5 p-12 bg-primary-soft">
      <img :src="window.BASE_URL + '/assets/icon/logo.png'" alt="BetaKos"
        class="w-24 h-24 object-contain">

      <div class="text-center">
        <h2 class="text-2xl font-bold text-heading">
          Temukan Kos yang Tepat
        </h2>
        <p class="mt-2 text-sm text-muted max-w-sm">
          Cari kos berdasarkan lokasi, harga, fasilitas, kapasitas, dan ketersediaan kamar.
        </p>
      </div>
    </div>

    <!-- FORM -->
    <div class="p-6 sm:p-10 lg:p-12">
      <div class="max-w-md mx-auto">

        <div class="mb-8">
          <h1 class="text-2xl font-bold text-heading">
            Selamat datang kembali
          </h1>

          <p class="mt-2 text-sm text-muted">
            Masuk untuk mengelola akun dan melanjutkan aktivitas di BetaKos.
          </p>

          <?php if ($googleError !== ''): ?>
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              <?= htmlspecialchars($googleError, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <div class="form-group">
            <label class="label">Email</label>
            <input
              type="email"
              x-model="email"
              autocomplete="email"
              placeholder="nama@email.com"
              class="input py-3 px-4"
              required>
          </div>

          <div class="form-group">
            <div class="flex-between">
              <label class="label">Kata Sandi</label>

              <button
                type="button"
                @click="forgotModal = true"
                class="text-xs font-medium text-primary hover:text-primary-dark">
                Lupa kata sandi?
              </button>
            </div>

            <input
              type="password"
              x-model="password"
              autocomplete="current-password"
              placeholder="Masukkan kata sandi"
              class="input py-3 px-4"
              required>
          </div>

          <button
            type="submit"
            class="btn-primary w-full py-3"
            :disabled="loading">

            <span x-show="!loading">Masuk</span>
            <span x-show="loading" x-cloak>Memproses...</span>
          </button>

          <div class="relative py-1">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
              <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center">
              <span class="bg-white px-3 text-xs text-muted">atau</span>
            </div>
          </div>

          <a
            href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/api/auth/google/start?mode=login', ENT_QUOTES, 'UTF-8') ?>"
            class="w-full h-11 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 flex items-center justify-center gap-3 text-sm font-semibold text-slate-700 transition"
            aria-label="Masuk dengan Google">
            <svg viewBox="0 0 24 24" class="w-5 h-5" aria-hidden="true"><path fill="#4285F4" d="M21.35 12.23c0-.72-.06-1.42-.18-2.09H12v3.96h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.26Z"/><path fill="#34A853" d="M12 21.82c2.63 0 4.84-.87 6.46-2.36l-3.14-2.45c-.87.58-1.98.92-3.32.92-2.55 0-4.71-1.72-5.49-5.04H3.27v2.53A9.76 9.76 0 0 0 12 21.82Z"/><path fill="#FBBC05" d="M6.51 13.89A5.87 5.87 0 0 1 6.2 12c0-.66.11-1.3.31-1.89V7.58H3.27A9.82 9.82 0 0 0 2.18 12c0 1.59.38 3.09 1.09 4.42l3.24-2.53Z"/><path fill="#EA4335" d="M12 6.07c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.15 14.63 2.18 12 2.18a9.76 9.76 0 0 0-8.73 5.4l3.24 2.53C7.29 7.79 9.45 6.07 12 6.07Z"/></svg>
            <span>Masuk dengan Google</span>
          </a>

        </form>

        <div class="mt-6 text-center text-sm text-muted">
          Belum punya akun?
          <a
            :href="window.BASE_URL + '/register'"
            class="font-semibold text-primary hover:text-primary-dark">
            Daftar sekarang
          </a>
        </div>

      </div>
    </div>
  </div>


  <!-- FORGOT PASSWORD MODAL -->
  <div
    x-show="forgotModal"
    x-cloak
    class="modal-backdrop"
    @keydown.escape.window="forgotModal = false">

    <div
      @click.away="forgotModal = false"
      class="modal-box max-w-md">

      <div class="flex-between">
        <h3 class="text-lg font-semibold">
          Lupa Kata Sandi
        </h3>

        <button
          type="button"
          @click="forgotModal = false"
          class="icon-btn">
          ✕
        </button>
      </div>

      <p class="mt-1 text-sm text-muted">
        Masukkan email akun Anda. Jika terdaftar, instruksi reset password akan dikirim ke email tersebut.
      </p>

      <form @submit.prevent="submitForgot" class="mt-5 space-y-4">

        <div class="form-group">
          <label class="label">Email</label>
          <input
            type="email"
            x-model="forgotEmail"
            autocomplete="email"
            placeholder="nama@email.com"
            class="input"
            required>
        </div>

        <div class="flex-end gap-2">
          <button
            type="button"
            @click="forgotModal = false"
            class="btn-secondary">
            Batal
          </button>

          <button
            type="submit"
            class="btn-primary"
            :disabled="forgotLoading || !forgotEmail">

            <span x-show="!forgotLoading">Kirim Instruksi</span>
            <span x-show="forgotLoading" x-cloak>Memproses...</span>
          </button>
        </div>

      </form>
    </div>
  </div>
</div>


<script>
  function loginForm() {
    return {
      email: '',
      password: '',

      loading: false,

      forgotModal: false,
      forgotEmail: '',
      forgotLoading: false,


      async submitForgot() {
        if (!this.forgotEmail) return;

        this.forgotLoading = true;

        try {
          const res = await API.post('/auth/request-reset', {
            email: this.forgotEmail
          });

          if (res.success) {
            Alpine.store('ui').toast(
              'Jika email terdaftar, instruksi reset password akan dikirim.',
              'success'
            );

            this.forgotModal = false;
            this.forgotEmail = '';
          }
        } catch (error) {
          console.error(error);
        } finally {
          this.forgotLoading = false;
        }
      },

      async submit() {
        if (!this.email || !this.password) return;

        this.loading = true;
        try {
          const res = await API.post('/auth/login', {
            email: this.email,
            password: this.password
          });

          if (res.success) {
            Alpine.store('ui').toast('Login berhasil', 'success');

            setTimeout(() => {
              const role = res.data?.role || '';
              if (role === 'admin') {
                window.location.href = BASE_URL + '/admin';
              } else if (role === 'pemilik') {
                window.location.href = BASE_URL + '/pemilik';
              } else {
                window.location.href = BASE_URL;
              }
            }, 700);
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