<div
  x-data="profilPemilikPage()"
  x-init="init()"
  class="space-y-6"
>
  <div>
    <p class="text-sm font-medium text-primary">Akun</p>
    <h2 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Profil Saya</h2>
    <p class="mt-1 text-sm text-slate-500">Kelola informasi akun dan keamanan akses Anda.</p>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="card border border-slate-200 shadow-sm">
      <div class="flex flex-col items-center text-center">
        <div class="relative">
          <template x-if="user.foto">
            <img :src="window.BASE_URL + user.foto" class="w-28 h-28 rounded-full object-cover border-4 border-white shadow ring-1 ring-slate-200" alt="Foto profil">
          </template>
          <template x-if="!user.foto">
            <div class="w-28 h-28 rounded-full bg-primary-soft text-primary flex items-center justify-center text-4xl font-bold ring-1 ring-blue-100" x-text="initial"></div>
          </template>
        </div>

        <h3 class="mt-4 text-lg font-bold text-slate-900" x-text="user.nama || 'Pemilik'"></h3>
        <p class="text-sm text-slate-500" x-text="user.email || '-' "></p>
        <span class="mt-3 inline-flex rounded-full bg-blue-50 text-blue-700 px-3 py-1 text-xs font-semibold">Pemilik Kos</span>

        <form class="mt-6 w-full" @submit.prevent="uploadFoto">
          <label class="block text-left text-sm font-medium text-slate-700">Foto profil</label>
          <input type="file" x-ref="foto" accept="image/jpeg,image/png,image/webp" class="mt-2 input" required>
          <button class="mt-3 btn-secondary w-full" :disabled="savingFoto">
            <span x-text="savingFoto ? 'Mengunggah...' : 'Ubah Foto'"></span>
          </button>
          <p class="mt-2 text-xs text-slate-400 text-left">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
        </form>
      </div>
    </div>

    <div class="xl:col-span-2 space-y-6">
      <div class="card border border-slate-200 shadow-sm">
        <div>
          <h3 class="font-semibold text-slate-900">Informasi Pribadi</h3>
          <p class="mt-1 text-sm text-slate-500">Informasi ini digunakan untuk identitas pemilik pada aplikasi.</p>
        </div>

        <form class="mt-6 space-y-5" @submit.prevent="saveProfile">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="label">Nama lengkap</label>
              <input class="input" type="text" x-model.trim="form.nama" required maxlength="150">
            </div>
            <div>
              <label class="label">Nomor HP</label>
              <input class="input" type="tel" x-model.trim="form.no_hp" maxlength="30" placeholder="08xxxxxxxxxx">
            </div>
            <div>
              <label class="label">Email</label>
              <input class="input bg-slate-50" type="email" x-model.trim="form.email" readonly>
              <p class="mt-1 text-xs text-slate-400">Email akun belum diubah dari halaman profil.</p>
            </div>
            <div>
              <label class="label">NIK</label>
              <input class="input bg-slate-50" type="text" :value="user.nik || '- '" readonly>
            </div>
          </div>

          <div class="flex justify-end">
            <button class="btn-primary w-auto" :disabled="savingProfile" x-text="savingProfile ? 'Menyimpan...' : 'Simpan Perubahan'"></button>
          </div>
        </form>
      </div>

      <div class="card border border-slate-200 shadow-sm">
        <div>
          <h3 class="font-semibold text-slate-900">Keamanan Akun</h3>
          <p class="mt-1 text-sm text-slate-500">Gunakan kata sandi yang kuat dan jangan membagikannya kepada orang lain.</p>
        </div>

        <form class="mt-6 space-y-5" @submit.prevent="changePassword">
          <div>
            <label class="label">Kata sandi lama</label>
            <input class="input" type="password" x-model="password.password_lama" autocomplete="current-password" required>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="label">Kata sandi baru</label>
              <input class="input" type="password" x-model="password.password_baru" minlength="8" autocomplete="new-password" required>
            </div>
            <div>
              <label class="label">Konfirmasi kata sandi</label>
              <input class="input" type="password" x-model="password.password_konfirmasi" minlength="8" autocomplete="new-password" required>
            </div>
          </div>
          <div class="flex justify-end">
            <button class="btn-primary w-auto" :disabled="savingPassword" x-text="savingPassword ? 'Mengubah...' : 'Ubah Kata Sandi'"></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function profilPemilikPage() {
  return {
    user: <?= json_encode($profile ?? []) ?>,
    form: { nama: '', email: '', no_hp: '' },
    password: { password_lama: '', password_baru: '', password_konfirmasi: '' },
    savingProfile: false,
    savingPassword: false,
    savingFoto: false,

    get initial() {
      return (this.user.nama || 'P').trim().charAt(0).toUpperCase();
    },

    init() {
      this.form = {
        nama: this.user.nama || '',
        email: this.user.email || '',
        no_hp: this.user.no_hp || ''
      };
    },

    async saveProfile() {
      this.savingProfile = true;
      try {
        const res = await API.post('/auth/profile', this.form);
        this.user = res.data;
        Alpine.store('auth').user = res.data;
      } finally {
        this.savingProfile = false;
      }
    },

    async uploadFoto() {
      const file = this.$refs.foto?.files?.[0];
      if (!file) return;

      this.savingFoto = true;
      try {
        const fd = new FormData();
        fd.append('foto', file);
        const res = await API.post('/auth/profile/foto', fd);
        this.user = res.data;
        Alpine.store('auth').user = res.data;
        this.$refs.foto.value = '';
      } finally {
        this.savingFoto = false;
      }
    },

    async changePassword() {
      if (this.password.password_baru !== this.password.password_konfirmasi) {
        Alpine.store('ui').toast('Konfirmasi kata sandi tidak cocok', 'error');
        return;
      }

      this.savingPassword = true;
      try {
        await API.post('/auth/password', this.password);
        this.password = { password_lama: '', password_baru: '', password_konfirmasi: '' };
      } finally {
        this.savingPassword = false;
      }
    }
  };
}
</script>
