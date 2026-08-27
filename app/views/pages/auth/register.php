<div class="section-center bg-slate-50 px-4">
  <div class="w-full max-w-5xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 bg-white rounded-2xl border border-slate-200 shadow-sm">

    <!-- BRAND -->
    <div class="hidden lg:flex flex-center flex-col gap-5 p-12 bg-primary-soft">
      <img :src="BASE_URL + '/assets/icon/logo.png'" alt="BetaKos"
        class="w-24 h-24 object-contain">

      <div class="text-center">
        <h2 class="text-2xl font-bold text-heading">
          Temukan Kos Lebih Mudah
        </h2>
        <p class="mt-2 text-sm text-muted max-w-sm">
          Bergabung untuk mencari kos di Kupang berdasarkan kebutuhan dan lokasi kampus Anda.
        </p>
      </div>
    </div>

    <!-- FORM -->
    <div class="p-6 sm:p-10 lg:p-12">
      <div class="max-w-md mx-auto">

        <h1 class="text-2xl font-bold text-heading">
          Buat Akun
        </h1>

        <p class="mt-2 text-sm text-muted">
          Daftar untuk mulai menggunakan BetaKos.
        </p>

        <form
          x-data="registerForm()"
          @submit.prevent="submit"
          class="mt-7 space-y-5">

          <div class="form-group">
            <label class="label">Nama Lengkap</label>
            <input
              type="text"
              x-model="nama"
              autocomplete="name"
              placeholder="Masukkan nama lengkap"
              class="input py-3 px-4"
              required>
          </div>

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
            <label class="label">Nomor HP</label>
            <input
              type="tel"
              x-model="no_hp"
              autocomplete="tel"
              placeholder="08xxxxxxxxxx"
              class="input py-3 px-4"
              required>
          </div>

          <div class="form-group">
            <label class="label">NIK</label>
            <input
              type="text"
              x-model="nik"
              autocomplete="off"
              inputmode="numeric"
              maxlength="16"
              pattern="[0-9]{16}"
              placeholder="Masukkan NIK"
              class="input py-3 px-4"
              required>
          </div>

          <div class="form-group">
            <label class="label">Kata Sandi</label>
            <input
              type="password"
              x-model="password"
              autocomplete="new-password"
              placeholder="Minimal 8 karakter"
              class="input py-3 px-4"
              required>
          </div>

          <div class="form-group">
            <label class="label">Konfirmasi Kata Sandi</label>
            <input
              type="password"
              x-model="konfirmasi_password"
              autocomplete="new-password"
              placeholder="Ulangi kata sandi"
              class="input py-3 px-4"
              required>
          </div>

          <button
            type="submit"
            class="btn-primary w-full py-3"
            :disabled="loading">

            <span x-show="!loading">Daftar</span>
            <span x-show="loading" x-cloak>Memproses...</span>
          </button>

        </form>

        <p class="mt-6 text-center text-xs text-muted">
          Setelah mendaftar, Anda perlu memverifikasi email sebelum dapat masuk.
        </p>

        <div class="mt-4 text-center text-sm text-muted">
          Sudah punya akun?
          <a
            :href="BASE_URL + '/login'"
            class="font-semibold text-primary hover:text-primary-dark">
            Masuk di sini
          </a>
        </div>

      </div>
    </div>

  </div>
</div>


<script>
  function registerForm() {
    return {
      nama: '',
      email: '',
      password: '',
      konfirmasi_password: '',
      no_hp: '',
      nik: '',
      loading: false,

      async submit() {
        if (
          !this.nama ||
          !this.email ||
          !this.password ||
          !this.konfirmasi_password ||
          !this.no_hp ||
          !this.nik
        ) {
          Alpine.store('ui').toast('Semua field harus diisi', 'error');
          return;
        }

        if (this.password.length < 8) {
          Alpine.store('ui').toast(
            'Kata sandi minimal 8 karakter',
            'error'
          );
          return;
        }

        if (!/^\d{16}$/.test(this.nik)) {
          Alpine.store('ui').toast(
            'NIK harus terdiri dari 16 digit',
            'error'
          );
          return;
        }

        if (this.password !== this.konfirmasi_password) {
          Alpine.store('ui').toast(
            'Kata sandi tidak cocok',
            'error'
          );
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
            konfirmasi_password: this.konfirmasi_password
          });

          if (res.success) {
            Alpine.store('ui').toast(
              'Registrasi berhasil. Cek email untuk verifikasi akun.',
              'success'
            );

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