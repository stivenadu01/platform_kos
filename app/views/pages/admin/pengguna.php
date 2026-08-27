<div x-data="adminPenggunaPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Manajemen Pengguna</h1>
      <p class="mt-1 text-sm text-slate-500">Kelola akun mahasiswa, pemilik, dan status verifikasi pengguna.</p>
    </div>
    <button @click="openCreate()" class="btn-primary">+ Tambah Pengguna</button>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
    <template x-for="card in summaryCards" :key="card.key">
      <div class="card border border-slate-200 shadow-sm p-4">
        <div class="text-xs text-slate-500" x-text="card.label"></div>
        <div class="mt-1 text-xl font-bold text-slate-900" x-text="summary[card.key] ?? 0"></div>
      </div>
    </template>
  </div>

  <div class="card border border-slate-200 shadow-sm p-4 sm:p-5">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
      <div class="xl:col-span-2">
        <label class="label">Cari pengguna</label>
        <input x-model="filters.search" @input.debounce.400ms="applyFilters()" type="search" class="input mt-1 w-full" placeholder="Nama, email, no. HP, atau NIK">
      </div>
      <div>
        <label class="label">Peran</label>
        <select x-model="filters.role" @change="applyFilters()" class="input mt-1 w-full">
          <option value="">Semua peran</option>
          <option value="pelanggan">Mahasiswa</option>
          <option value="pemilik">Pemilik</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div>
        <label class="label">Verifikasi</label>
        <select x-model="filters.verification" @change="applyFilters()" class="input mt-1 w-full">
          <option value="">Semua</option>
          <option value="terverifikasi">Terverifikasi</option>
          <option value="belum">Belum diverifikasi</option>
        </select>
      </div>
      <div>
        <label class="label">Status</label>
        <select x-model="filters.status" @change="applyFilters()" class="input mt-1 w-full">
          <option value="">Semua status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
          <option value="ditangguhkan">Ditangguhkan</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-4 sm:px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
      <div>
        <h2 class="font-semibold text-slate-900">Daftar Pengguna</h2>
        <p class="text-xs text-slate-500 mt-1" x-text="`${result.total} pengguna ditemukan`"></p>
      </div>
      <button @click="load()" class="btn-secondary text-sm">↻ Refresh</button>
    </div>

    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat pengguna...</div>
    <div x-show="!loading && !result.items.length" class="p-10 text-center">
      <div class="text-4xl">👥</div>
      <h3 class="mt-3 font-semibold text-slate-900">Pengguna tidak ditemukan</h3>
      <p class="mt-1 text-sm text-slate-500">Coba ubah pencarian atau filter.</p>
    </div>

    <div x-show="!loading && result.items.length" class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-5 py-3">Pengguna</th>
            <th class="text-left px-5 py-3">Peran</th>
            <th class="text-left px-5 py-3">Verifikasi</th>
            <th class="text-left px-5 py-3">Status</th>
            <th class="text-left px-5 py-3">Terdaftar</th>
            <th class="text-right px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="user in result.items" :key="user.id_user">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary-soft text-primary flex items-center justify-center font-bold" x-text="initial(user.nama)"></div>
                  <div class="min-w-0">
                    <div class="font-semibold text-slate-900 truncate max-w-[260px]" x-text="user.nama"></div>
                    <div class="text-xs text-slate-500 truncate max-w-[260px]" x-text="user.email"></div>
                    <div class="text-xs text-slate-400" x-text="user.no_hp || 'No. HP belum diisi'"></div>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium" x-text="roleLabel(user.role)"></span></td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="user.email_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" x-text="user.email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi'"></span>
              </td>
              <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="statusClass(user.status)" x-text="statusLabel(user.status)"></span></td>
              <td class="px-5 py-4 text-slate-500" x-text="formatDate(user.created_at)"></td>
              <td class="px-5 py-4">
                <div class="flex justify-end gap-2">
                  <button x-show="!user.email_verified_at" @click="verify(user)" class="btn-secondary text-xs text-emerald-700">Verifikasi</button>
                  <button x-show="user.status === 'aktif' && Number(user.id_user) !== currentUserId" @click="changeStatus(user, 'nonaktif')" class="btn-secondary text-xs text-red-600">Nonaktifkan</button>
                  <button x-show="user.status === 'nonaktif' && Number(user.id_user) !== currentUserId" @click="changeStatus(user, 'aktif')" class="btn-secondary text-xs text-emerald-700">Aktifkan</button>
                  <button x-show="user.status !== 'ditangguhkan' && Number(user.id_user) !== currentUserId" @click="changeStatus(user, 'ditangguhkan')" class="btn-secondary text-xs text-amber-700">Tangguhkan</button>
                  <button x-show="user.status === 'ditangguhkan' && Number(user.id_user) !== currentUserId" @click="changeStatus(user, 'aktif')" class="btn-secondary text-xs text-emerald-700">Pulihkan</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && result.total_pages > 1" class="px-4 sm:px-5 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div class="text-xs text-slate-500" x-text="`Halaman ${result.page} dari ${result.total_pages}`"></div>
      <div class="flex items-center gap-1">
        <button @click="goPage(result.page - 1)" :disabled="result.page <= 1" class="w-9 h-9 rounded-lg border border-slate-200 disabled:opacity-40">←</button>
        <template x-for="page in pages()" :key="page">
          <button @click="goPage(page)" :class="page === result.page ? 'bg-primary text-white border-primary' : 'border-slate-200 text-slate-700'" class="w-9 h-9 rounded-lg border text-sm" x-text="page"></button>
        </template>
        <button @click="goPage(result.page + 1)" :disabled="result.page >= result.total_pages" class="w-9 h-9 rounded-lg border border-slate-200 disabled:opacity-40">→</button>
      </div>
    </div>
  </div>

  <div x-show="createOpen" x-cloak class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeCreate()"></div>
    <div class="relative bg-white w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
        <div><h2 class="font-bold text-slate-900">Tambah Pengguna</h2><p class="text-xs text-slate-500 mt-1">Akun yang dibuat Admin langsung terverifikasi.</p></div>
        <button @click="closeCreate()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <form @submit.prevent="createUser" class="p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="label">Nama Lengkap *</label><input x-model="form.nama" class="input mt-1 w-full" required></div>
          <div><label class="label">Email *</label><input x-model="form.email" type="email" class="input mt-1 w-full" required></div>
          <div><label class="label">Nomor HP *</label><input x-model="form.no_hp" type="tel" class="input mt-1 w-full" required></div>
          <div><label class="label">NIK *</label><input x-model="form.nik" class="input mt-1 w-full" inputmode="numeric" maxlength="16" required></div>
          <div><label class="label">Peran *</label><select x-model="form.role" class="input mt-1 w-full"><option value="pelanggan">Mahasiswa</option><option value="pemilik">Pemilik</option><option value="admin">Admin</option></select></div>
          <div><label class="label">Kata Sandi *</label><input x-model="form.password" type="password" minlength="8" class="input mt-1 w-full" required placeholder="Minimal 8 karakter"></div>
        </div>
        <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-sm text-emerald-800">✓ Pengguna yang ditambahkan melalui Admin langsung memiliki status <b>terverifikasi</b> dan <b>aktif</b>, sehingga tidak perlu klik link verifikasi email.</div>
        <div class="flex flex-col sm:flex-row-reverse gap-2 pt-2"><button type="submit" class="btn-primary" :disabled="saving"><span x-text="saving ? 'Menyimpan...' : 'Tambah Pengguna'"></span></button><button type="button" @click="closeCreate()" class="btn-secondary">Batal</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function adminPenggunaPage() {
  return {
    summary: { total: 0, pemilik: 0, mahasiswa: 0, belum_verifikasi: 0, aktif: 0, nonaktif: 0 },
    summaryCards: [
      { key: 'total', label: 'Total Pengguna' },
      { key: 'mahasiswa', label: 'Mahasiswa' },
      { key: 'pemilik', label: 'Pemilik' },
      { key: 'belum_verifikasi', label: 'Belum Verifikasi' },
      { key: 'aktif', label: 'Aktif' },
      { key: 'nonaktif', label: 'Nonaktif / Tangguh' }
    ],
    result: { items: [], total: 0, page: 1, limit: 10, total_pages: 1 },
    filters: { search: '', role: '', verification: '', status: '' },
    currentUserId: Number(window.__USER__?.id_user || 0),
    loading: false,
    createOpen: false,
    saving: false,
    form: {},

    resetForm() { this.form = { nama: '', email: '', no_hp: '', nik: '', role: 'pelanggan', password: '' }; },
    async init() { this.resetForm(); await this.load(); },
    queryString() {
      const p = new URLSearchParams({ page: this.result.page || 1, limit: 10 });
      Object.entries(this.filters).forEach(([k, v]) => { if (v) p.set(k, v); });
      return p.toString();
    },
    async load(page = this.result.page || 1) {
      this.loading = true;
      try {
        this.result.page = page;
        const res = await API.get('/admin/pengguna?' + this.queryString(), false);
        this.result = res.data || this.result;
        this.summary = res.summary || this.summary;
      } finally { this.loading = false; }
    },
    async applyFilters() { await this.load(1); },
    async goPage(page) { if (page < 1 || page > this.result.total_pages) return; await this.load(page); },
    pages() {
      const total = Number(this.result.total_pages || 1), current = Number(this.result.page || 1);
      const start = Math.max(1, current - 2), end = Math.min(total, start + 4), out = [];
      for (let i = start; i <= end; i++) out.push(i); return out;
    },
    initial(name) { return String(name || '?').trim().charAt(0).toUpperCase(); },
    roleLabel(role) { return role === 'pemilik' ? 'Pemilik' : role === 'admin' ? 'Admin' : 'Mahasiswa'; },
    statusLabel(status) { return status === 'aktif' ? 'Aktif' : status === 'ditangguhkan' ? 'Ditangguhkan' : 'Nonaktif'; },
    statusClass(status) { return status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : status === 'ditangguhkan' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'; },
    formatDate(v) { if (!v) return '-'; const d = new Date(String(v).replace(' ', 'T')); return isNaN(d) ? '-' : d.toLocaleDateString('id-ID', { dateStyle: 'medium' }); },
    openCreate() { this.resetForm(); this.createOpen = true; },
    closeCreate() { if (!this.saving) this.createOpen = false; },
    async createUser() {
      if (this.form.password.length < 8) { Alpine.store('ui').toast('Kata sandi minimal 8 karakter.', 'error'); return; }
      this.saving = true;
      try { await API.post('/admin/pengguna', this.form); this.createOpen = false; await this.load(1); }
      catch (e) {} finally { this.saving = false; }
    },
    async verify(user) {
      const ok = await Alpine.store('ui').confirm(`Verifikasi akun ${user.nama}?`); if (!ok) return;
      try { await API.post('/admin/pengguna/verifikasi', { id_user: user.id_user }); await this.load(); }
      catch (e) {}
    },
    async changeStatus(user, status) {
      const label = this.statusLabel(status).toLowerCase();
      const ok = await Alpine.store('ui').confirm(`Ubah status ${user.nama} menjadi ${label}?`); if (!ok) return;
      try { await API.post('/admin/pengguna/status', { id_user: user.id_user, status }); await this.load(); }
      catch (e) {}
    }
  }
}
</script>
