<div x-data="adminLokasiPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-primary">Manajemen Referensi</p>
      <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Kelola Lokasi</h1>
      <p class="mt-1 text-sm text-slate-500">Atur lokasi referensi yang tampil pada pencarian publik dan informasi lingkungan sekitar kos.</p>
    </div>
    <button type="button" @click="openCreate()" class="btn-primary">+ Tambah Lokasi</button>
  </div>

  <div class="card border border-slate-200 p-4 sm:p-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div class="md:col-span-2">
        <label class="label">Cari lokasi</label>
        <input x-model="filters.search" @input.debounce.350ms="load()" type="search" class="input mt-1 w-full" placeholder="Nama lokasi atau alamat">
      </div>
      <div>
        <label class="label">Kategori</label>
        <select x-model="filters.kategori" @change="load()" class="select mt-1">
          <option value="">Semua kategori</option>
          <option value="kampus">Kampus</option>
          <option value="area">Area</option>
          <option value="rumah_sakit">Rumah sakit</option>
          <option value="rumah_makan">Rumah makan</option>
          <option value="toko">Toko</option>
          <option value="pusat_perbelanjaan">Pusat perbelanjaan</option>
          <option value="transportasi">Transportasi</option>
          <option value="tempat_wisata">Tempat wisata</option>
        </select>
      </div>
      <div>
        <label class="label">Status</label>
        <select x-model="filters.status" @change="load()" class="select mt-1">
          <option value="">Semua status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>
  </div>

  <section class="card border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
      <div>
        <h2 class="font-bold text-slate-900">Daftar Lokasi</h2>
        <p class="text-xs text-slate-500 mt-1" x-text="`${items.length} lokasi ditemukan`"></p>
      </div>
      <button type="button" @click="load()" class="btn-secondary text-sm">↻ Refresh</button>
    </div>

    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat lokasi...</div>
    <div x-show="!loading && items.length === 0" class="p-10 text-center text-sm text-slate-500">Belum ada lokasi yang sesuai filter.</div>

    <div x-show="!loading && items.length" class="hidden md:block overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
          <tr>
            <th class="text-left px-5 py-3">Nama</th>
            <th class="text-left px-5 py-3">Kategori</th>
            <th class="text-left px-5 py-3">Koordinat</th>
            <th class="text-left px-5 py-3">Urutan</th>
            <th class="text-left px-5 py-3">Status</th>
            <th class="text-right px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in items" :key="item.id_lokasi">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-900" x-text="item.nama"></div>
                <div class="mt-1 text-xs text-slate-500 max-w-[320px] truncate" x-text="item.alamat || 'Alamat belum diisi'"></div>
              </td>
              <td class="px-5 py-4"><div class="flex items-center gap-2"><span class="w-9 h-9 rounded-xl bg-primary-soft text-primary flex items-center justify-center" x-html="iconSvg(item.icon)"></span><span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium" x-text="categoryLabel(item.kategori)"></span></div></td>
              <td class="px-5 py-4 text-xs text-slate-600 whitespace-nowrap" x-text="Number(item.latitude).toFixed(7) + ', ' + Number(item.longitude).toFixed(7)"></td>
              <td class="px-5 py-4" x-text="item.urutan"></td>
              <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : 'Nonaktif'"></span></td>
              <td class="px-5 py-4">
                <div class="flex justify-end gap-2">
                  <button type="button" @click="openEdit(item)" class="btn-secondary text-xs">Edit</button>
                  <button type="button" @click="remove(item)" class="btn-secondary text-xs text-red-600">Hapus</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && items.length" class="md:hidden divide-y divide-slate-200">
      <template x-for="item in items" :key="'m-' + item.id_lokasi">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-start gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary" x-html="iconSvg(item.icon)"></span>
              <div class="font-semibold text-slate-900 truncate" x-text="item.nama"></div>
              <div class="mt-1 text-xs text-slate-500" x-text="categoryLabel(item.kategori)"></div>
            </div>
            <span class="shrink-0 px-2 py-1 rounded-full text-[11px] font-medium" :class="item.status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : 'Nonaktif'"></span>
          </div>
          <div class="mt-3 text-xs text-slate-500" x-text="item.alamat || 'Alamat belum diisi'"></div>
          <div class="mt-2 text-xs text-slate-500" x-text="Number(item.latitude).toFixed(7) + ', ' + Number(item.longitude).toFixed(7) + ' · urutan ' + item.urutan"></div>
          <div class="mt-3 flex gap-2">
            <button type="button" @click="openEdit(item)" class="btn-secondary text-xs flex-1">Edit</button>
            <button type="button" @click="remove(item)" class="btn-secondary text-xs text-red-600 flex-1">Hapus</button>
          </div>
        </article>
      </template>
    </div>
  </section>

  <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeModal()"></div>
    <div class="relative bg-white w-full sm:max-w-2xl max-h-[94vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
        <div>
          <h2 class="font-bold text-slate-900" x-text="editing ? 'Edit Lokasi' : 'Tambah Lokasi'"></h2>
          <p class="text-xs text-slate-500 mt-1">Data ini langsung menjadi pilihan lokasi pada pencarian publik.</p>
        </div>
        <button type="button" @click="closeModal()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <form @submit.prevent="save()" class="p-5 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2"><label class="label">Nama lokasi *</label><input x-model="form.nama" class="input mt-1 w-full" maxlength="200" required placeholder="Contoh: Politeknik Negeri Kupang"></div>
          <div><label class="label">Kategori *</label><select x-model="form.kategori" class="select mt-1"><option value="kampus">Kampus</option><option value="area">Area</option><option value="rumah_sakit">Rumah sakit</option>
          <option value="rumah_makan">Rumah makan</option>
          <option value="toko">Toko</option>
          <option value="pusat_perbelanjaan">Pusat perbelanjaan</option>
          <option value="transportasi">Transportasi</option>
          <option value="tempat_wisata">Tempat wisata</option></select><div class="mt-2 flex items-center gap-2 text-xs text-slate-500"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-soft text-primary" x-html="iconSvg(form.kategori === 'kampus' ? 'graduation-cap' : form.kategori === 'area' ? 'map-pin' : form.kategori === 'rumah_sakit' ? 'hospital' : form.kategori === 'rumah_makan' ? 'utensils' : form.kategori === 'toko' ? 'shopping-basket' : form.kategori === 'pusat_perbelanjaan' ? 'shopping-bag' : form.kategori === 'transportasi' ? 'plane' : 'palmtree', 'h-4 w-4')"></span><span>Icon mengikuti kategori</span></div></div>
          <div><label class="label">Urutan</label><input x-model.number="form.urutan" type="number" min="0" class="input mt-1 w-full"><p class="mt-1 text-xs text-slate-400">Angka lebih kecil tampil lebih dulu. Contoh 10, 20, 30. Jarak angka memudahkan menyisipkan lokasi baru di tengah.</p></div>
          <div class="sm:col-span-2"><label class="label">Alamat</label><textarea x-model="form.alamat" rows="2" maxlength="500" class="input mt-1 w-full" placeholder="Alamat atau keterangan lokasi"></textarea></div>
          <div><label class="label">Latitude *</label><input x-model="form.latitude" type="number" step="any" min="-90" max="90" class="input mt-1 w-full" required></div>
          <div><label class="label">Longitude *</label><input x-model="form.longitude" type="number" step="any" min="-180" max="180" class="input mt-1 w-full" required></div>
          <div class="sm:col-span-2">
            <label class="label">Google Maps URL <span class="font-normal text-slate-400">(opsional)</span></label>
            <div class="mt-1 flex flex-col sm:flex-row gap-2">
              <input
                x-model="form.google_maps_url"
                @paste="handleGoogleMapsPaste()"
                @change="resolveGoogleMapsLink()"
                type="url"
                maxlength="2048"
                class="input w-full"
                placeholder="Tempel link Google Maps di sini..."
              >
              <button type="button" @click="resolveGoogleMapsLink()" class="btn-secondary shrink-0 justify-center" :disabled="resolvingMaps || !form.google_maps_url.trim()" x-text="resolvingMaps ? 'Mencari...' : 'Ambil Koordinat'"></button>
            </div>
            <p class="mt-1 text-xs text-slate-400">Tempel link Google Maps biasa atau link singkat maps.app.goo.gl. Latitude dan longitude akan dicari otomatis.</p>
            <div x-show="mapsResolved" x-cloak class="mt-2 flex items-center gap-2 text-xs text-emerald-700">
              <span class="font-semibold">✓ Koordinat berhasil ditemukan</span>
              <span x-text="`${Number(form.latitude).toFixed(7)}, ${Number(form.longitude).toFixed(7)}`"></span>
            </div>
          </div>
          <div><label class="label">Status</label><select x-model="form.status" class="select mt-1"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
        </div>
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
          <button type="button" @click="closeModal()" class="btn-secondary justify-center">Batal</button>
          <button type="submit" class="btn-primary justify-center" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Lokasi'"></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function adminLokasiPage() {
  return {
    items: [], loading: false, saving: false, modalOpen: false, editing: null, resolvingMaps: false, mapsResolved: false,
    filters: { search: '', kategori: '', status: '' },
    form: { nama: '', kategori: 'kampus', alamat: '', latitude: '', longitude: '', google_maps_url: '', urutan: 0, status: 'aktif' },

    async init() { await this.load(); },

    iconPaths: <?= json_encode_safe(array_map(fn($v)=>$v['path'], masterIconCatalog()), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
    iconSvg(i,c='h-5 w-5') { const p=this.iconPaths[i]||this.iconPaths['map-pin']; return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="${c}" aria-hidden="true">${p}</svg>`; },

    async load() {
      this.loading = true;
      try {
        const params = new URLSearchParams();
        if (this.filters.search.trim()) params.set('search', this.filters.search.trim());
        if (this.filters.kategori) params.set('kategori', this.filters.kategori);
        if (this.filters.status) params.set('status', this.filters.status);
        const suffix = params.toString() ? '?' + params.toString() : '';
        const res = await API.get('/admin/lokasi' + suffix, false);
        this.items = Array.isArray(res.data) ? res.data : [];
      } catch (e) {
        this.items = [];
      } finally { this.loading = false; }
    },

    resetForm() {
      this.form = { nama: '', kategori: 'kampus', alamat: '', latitude: '', longitude: '', google_maps_url: '', urutan: 0, status: 'aktif' };
      this.mapsResolved = false;
    },

    openCreate() { this.editing = null; this.resetForm(); this.modalOpen = true; },
    openEdit(item) {
      this.editing = item.id_lokasi;
      this.form = {
        nama: item.nama || '', kategori: item.kategori || 'kampus', alamat: item.alamat || '',
        latitude: item.latitude ?? '', longitude: item.longitude ?? '', google_maps_url: item.google_maps_url || '',
        urutan: Number(item.urutan || 0), status: ['aktif', 'active', '1', 1, true].includes(item.status) ? 'aktif' : 'nonaktif'
      };
      this.mapsResolved = Boolean(item.google_maps_url);
      this.modalOpen = true;
    },
    closeModal() { if (!this.saving && !this.resolvingMaps) this.modalOpen = false; },

    handleGoogleMapsPaste() {
      this.mapsResolved = false;
      setTimeout(() => this.resolveGoogleMapsLink(), 80);
    },

    async resolveGoogleMapsLink() {
      const value = String(this.form.google_maps_url || '').trim();
      if (!value || this.resolvingMaps) return;

      this.resolvingMaps = true;
      this.mapsResolved = false;
      try {
        const result = await API.post('/admin/lokasi/resolve-google-maps', { url: value }, false);
        const data = result.data || {};
        this.form.latitude = data.latitude ?? '';
        this.form.longitude = data.longitude ?? '';
        this.mapsResolved = true;
      } catch (e) {
        this.mapsResolved = false;
      } finally {
        this.resolvingMaps = false;
      }
    },

    async save() {
      this.saving = true;
      try {
        const payload = { ...this.form, latitude: Number(this.form.latitude), longitude: Number(this.form.longitude), urutan: Number(this.form.urutan || 0) };
        const path = this.editing ? '/admin/lokasi/' + encodeURIComponent(this.editing) : '/admin/lokasi';
        if (this.editing) await API.put(path, payload);
        else await API.post(path, payload);
        this.modalOpen = false;
        await this.load();
      } catch (e) {} finally { this.saving = false; }
    },

    async remove(item) {
      const ok = await Alpine.store('ui').confirm(`Hapus lokasi "${item.nama}" dari daftar referensi publik?`);
      if (!ok) return;
      try {
        await API.delete('/admin/lokasi/' + encodeURIComponent(item.id_lokasi));
        await this.load();
      } catch (e) {}
    },

    categoryLabel(value) {
      const labels = {
        kampus: 'Kampus',
        area: 'Area',
        rumah_sakit: 'Rumah sakit',
        rumah_makan: 'Rumah makan',
        toko: 'Toko',
        pusat_perbelanjaan: 'Pusat perbelanjaan',
        transportasi: 'Transportasi',
        tempat_wisata: 'Tempat wisata',
      };
      return labels[value] || 'Kategori tidak dikenal';
    }
  };
}
</script>
