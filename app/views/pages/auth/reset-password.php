<div class="section-center bg-slate-50 px-4">

  <div class="w-full max-w-md">

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">

      <div class="text-center mb-7">

        <img
          :src="BASE_URL + '/assets/icon/logo.png'"
          alt="BetaKos"
          class="w-16 h-16 object-contain mx-auto mb-4">

        <h1 class="text-2xl font-bold text-heading">
          Reset Kata Sandi
        </h1>

        <p class="mt-2 text-sm text-muted">
          Buat kata sandi baru untuk akun Anda.
        </p>

      </div>

      <div
        x-data="resetForm()"
        x-init="init()">

        <!-- TOKEN INVALID -->
        <div
          x-show="!token"
          x-cloak
          class="rounded-xl bg-danger-soft p-4 text-sm text-danger">
          Link reset password tidak valid atau token tidak ditemukan.
        </div>

        <!-- FORM -->
        <form
          x-show="token"
          x-cloak
          @submit.prevent="submit"
          class="space-y-5">

          <div class="form-group">
            <label class="label">Kata Sandi Baru</label>

            <input
              type="password"
              x-model="password"
              autocomplete="new-password"
              placeholder="Minimal 8 karakter"
              class="input py-3 px-4"
              minlength="8"
              required>
          </div>

          <div class="form-group">
            <label class="label">Konfirmasi Kata Sandi</label>

            <input
              type="password"
              x-model="confirmPassword"
              autocomplete="new-password"
              placeholder="Ulangi kata sandi"
              class="input py-3 px-4"
              minlength="8"
              required>
          </div>

          <button
            type="submit"
            class="btn-primary w-full py-3"
            :disabled="loading">

            <span x-show="!loading">
              Reset Kata Sandi
            </span>

            <span x-show="loading" x-cloak>
              Memproses...
            </span>

          </button>

        </form>

        <div class="mt-6 text-center">
          <a
            :href="BASE_URL + '/login'"
            class="text-sm font-medium text-primary hover:text-primary-dark">
            Kembali ke Login
          </a>
        </div>

      </div>

    </div>

  </div>

</div>


<script>
  function resetForm() {
    return {
      token: '',
      password: '',
      confirmPassword: '',
      loading: false,

      init() {
        const params = new URLSearchParams(window.location.search);
        this.token = params.get('token') || '';
      },

      async submit() {
        if (!this.token) {
          Alpine.store('ui').toast(
            'Token reset tidak valid',
            'error'
          );
          return;
        }

        if (this.password.length < 8) {
          Alpine.store('ui').toast(
            'Kata sandi minimal 8 karakter',
            'error'
          );
          return;
        }

        if (this.password !== this.confirmPassword) {
          Alpine.store('ui').toast(
            'Kata sandi tidak cocok',
            'error'
          );
          return;
        }

        this.loading = true;

        try {
          const res = await API.post('/auth/reset-password', {
            token: this.token,
            password: this.password
          });

          if (res.success) {
            Alpine.store('ui').toast(
              'Password berhasil direset. Silakan login.',
              'success'
            );

            setTimeout(() => {
              window.location.href = BASE_URL + '/login';
            }, 1200);
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