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
          Cari kos berdasarkan kampus, lokasi, harga, fasilitas, dan ketersediaan kamar.
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
            Masuk untuk melanjutkan ke BetaKos.
          </p>
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