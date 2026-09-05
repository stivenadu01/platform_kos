<div x-data="accountProfilePage()" x-init="init()" class="mx-auto max-w-5xl space-y-6">
  <div>
    <p class="text-sm font-medium text-primary">Akun</p>
    <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Profil & Pengaturan Akun</h1>
    <p class="mt-1 text-sm text-slate-500">Kelola identitas, keamanan, dan sesi akun BetaKos Anda.</p>
  </div>

  <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
    <section class="card border border-slate-200 shadow-sm">
      <div class="flex flex-col items-center text-center">
        <template x-if="user.foto"><img :src="window.BASE_URL + '/uploads' + user.foto" :alt="user.nama || 'Foto profil'" class="h-28 w-28 rounded-full object-cover ring-1 ring-slate-200"></template>
        <template x-if="!user.foto"><div class="flex h-28 w-28 items-center justify-center rounded-full bg-primary-soft text-4xl font-bold text-primary ring-1 ring-blue-100" x-text="initial"></div></template>
        <h2 class="mt-4 text-lg font-bold text-slate-900" x-text="user.nama || 'Pengguna'"></h2>
        <p class="text-sm text-slate-500" x-text="user.email || '-'"></p>
        <span class="mt-3 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold capitalize text-blue-700" x-text="user.role || 'pengguna'"></span>
        <form class="mt-6 w-full" @submit.prevent="uploadFoto">
          <label class="label text-left">Foto profil</label>
          <input x-ref="foto" type="file" accept="image/jpeg,image/png,image/webp" class="input mt-2" required>
          <button class="btn-secondary mt-3 w-full" :disabled="savingFoto" x-text="savingFoto ? 'Mengunggah...' : 'Ubah Foto'"></button>
          <p class="mt-2 text-left text-xs text-slate-400">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
        </form>
      </div>
    </section>

    <div class="space-y-6">
      <section class="card border border-slate-200 shadow-sm">
        <h2 class="font-semibold text-slate-900">Informasi Pribadi</h2>
        <p class="mt-1 text-sm text-slate-500">Nama dan nomor HP dapat diperbarui. Email dan NIK merupakan identitas yang dilindungi.</p>
        <form class="mt-6 grid gap-5 md:grid-cols-2" @submit.prevent="saveProfile">
          <div><label class="label">Nama lengkap</label><input class="input" type="text" x-model.trim="form.nama" maxlength="150" required></div>
          <div><label class="label">Nomor HP</label><input class="input" type="tel" x-model.trim="form.no_hp" maxlength="20" placeholder="081234567890"><p class="mt-1 text-xs text-slate-400">Boleh 08..., 62..., atau +62...; akan dinormalisasi.</p></div>
          <div><label class="label">Email</label><input class="input bg-slate-50" type="email" :value="user.email || '-'" readonly></div>
          <div><label class="label">NIK</label><input class="input bg-slate-50" type="text" :value="user.nik || '-'" readonly></div>
          <div class="md:col-span-2 flex justify-end"><button class="btn-primary w-auto" :disabled="savingProfile" x-text="savingProfile ? 'Menyimpan...' : 'Simpan Perubahan'"></button></div>
        </form>
      </section>

      <section class="card border border-slate-200 shadow-sm">
        <h2 class="font-semibold text-slate-900">Keamanan Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Ubah kata sandi dan kelola akses perangkat yang sedang login.</p>
        <form class="mt-6 space-y-5" @submit.prevent="changePassword">
          <div><label class="label">Kata sandi lama</label><input class="input" type="password" x-model="password.password_lama" autocomplete="current-password" required></div>
          <div class="grid gap-5 md:grid-cols-2"><div><label class="label">Kata sandi baru</label><input class="input" type="password" x-model="password.password_baru" minlength="8" autocomplete="new-password" required></div><div><label class="label">Konfirmasi kata sandi</label><input class="input" type="password" x-model="password.password_konfirmasi" minlength="8" autocomplete="new-password" required></div></div>
          <div class="flex justify-end"><button class="btn-primary w-auto" :disabled="savingPassword" x-text="savingPassword ? 'Mengubah...' : 'Ubah Kata Sandi'"></button></div>
        </form>
        <div class="mt-6 border-t border-slate-100 pt-6">
          <h3 class="font-semibold text-slate-900">Keluar dari semua perangkat</h3>
          <p class="mt-1 text-sm text-slate-500">Mengakhiri semua sesi BetaKos yang sedang aktif, termasuk perangkat ini. Anda perlu login kembali.</p>
          <button type="button" @click="logoutAll" class="mt-4 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50" :disabled="loggingOutAll" x-text="loggingOutAll ? 'Mengakhiri sesi...' : 'Keluar dari Semua Perangkat'"></button>
        </div>
      </section>

      <section class="card border border-slate-200 shadow-sm">
        <h2 class="font-semibold text-slate-900">Pengaturan Akun</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status akun</p><p class="mt-1 font-semibold capitalize text-slate-800" x-text="user.status || '-'"></p></div>
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs font-medium uppercase tracking-wide text-slate-400">Email terverifikasi</p><p class="mt-1 font-semibold text-slate-800" x-text="user.email_verified_at ? 'Ya' : 'Belum'"></p></div>
        </div>
        <p class="mt-4 text-xs text-slate-400">Pengaturan notifikasi akan ditambahkan pada phase berikutnya.</p>
      </section>
    </div>
  </div>
</div>

<script>
function accountProfilePage() {
  return {
    user: <?= json_encode_safe($profile ?? []) ?>,
    form: { nama: '', no_hp: '' },
    password: { password_lama: '', password_baru: '', password_konfirmasi: '' },
    savingProfile: false, savingPassword: false, savingFoto: false, loggingOutAll: false,
    get initial() { return (this.user.nama || 'P').trim().charAt(0).toUpperCase(); },
    init() { this.form = { nama: this.user.nama || '', no_hp: this.user.no_hp || '' }; },
    async saveProfile() { this.savingProfile = true; try { const res = await API.post('/auth/profile', this.form); this.user = res.data; Alpine.store('auth').user = res.data; window.dispatchEvent(new CustomEvent('betakos:onboarding-refresh')); } finally { this.savingProfile = false; } },
    async uploadFoto() { const file = this.$refs.foto?.files?.[0]; if (!file) return; this.savingFoto = true; try { const fd = new FormData(); fd.append('foto', file); const res = await API.post('/auth/profile/foto', fd); this.user = res.data; Alpine.store('auth').user = res.data; this.$refs.foto.value = ''; } finally { this.savingFoto = false; } },
    async changePassword() { if (this.password.password_baru !== this.password.password_konfirmasi) { Alpine.store('ui').toast('Konfirmasi kata sandi tidak cocok', 'error'); return; } this.savingPassword = true; try { await API.post('/auth/password', this.password); this.password = { password_lama: '', password_baru: '', password_konfirmasi: '' }; } finally { this.savingPassword = false; } },
    async logoutAll() { if (!confirm('Keluar dari semua perangkat dan login kembali sekarang?')) return; this.loggingOutAll = true; try { await API.post('/auth/logout-all', {}); window.location.href = window.BASE_URL + '/login'; } finally { this.loggingOutAll = false; } }
  };
}
</script>
