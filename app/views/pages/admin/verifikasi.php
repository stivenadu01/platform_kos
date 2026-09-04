<div x-data="adminVerifikasiPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div><h1 class="text-xl sm:text-2xl font-bold text-slate-900">Verifikasi Kos</h1><p class="mt-1 text-sm text-slate-500">Periksa data kos sebelum dipublikasikan kepada pengguna.</p></div>
    <div class="flex gap-2">
      <button @click="load('menunggu')" :class="tab === 'menunggu' ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200'" class="px-4 py-2 rounded-xl text-sm font-semibold">Menunggu <span x-text="summary.menunggu"></span></button>
      <button @click="load('disetujui')" :class="tab === 'disetujui' ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200'" class="px-4 py-2 rounded-xl text-sm font-semibold">Disetujui</button>
      <button @click="load('ditolak')" :class="tab === 'ditolak' ? 'bg-primary text-white' : 'bg-white text-slate-600 border border-slate-200'" class="px-4 py-2 rounded-xl text-sm font-semibold">Ditolak</button>
    </div>
  </div>

  <div x-show="loading" class="card border border-slate-200 p-8 text-center text-sm text-slate-500">Memuat pengajuan...</div>

  <div x-show="!loading && items.length === 0" class="card border border-slate-200 p-10 text-center">
    <div class="text-4xl">✓</div><h3 class="mt-3 font-semibold text-slate-900">Tidak ada pengajuan</h3><p class="mt-1 text-sm text-slate-500">Belum ada data pada kategori ini.</p>
  </div>

  <div x-show="!loading && items.length" class="grid grid-cols-1 xl:grid-cols-2 gap-5">
    <template x-for="item in items" :key="item.id_verifikasi">
      <article class="card border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5">
          <div class="flex items-start justify-between gap-3">
            <div><h2 class="font-bold text-slate-900" x-text="item.nama_kos"></h2><p class="mt-1 text-sm text-slate-500 line-clamp-2" x-text="item.alamat"></p></div>
            <span class="shrink-0 text-xs px-2.5 py-1 rounded-full" :class="badgeClass(item.status_verifikasi)" x-text="labelStatus(item.status_verifikasi)"></span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-5">
            <div class="bg-slate-50 rounded-xl p-3"><div class="text-xs text-slate-500">Pemilik</div><div class="mt-1 text-sm font-semibold truncate" x-text="item.nama_pemilik"></div></div>
            <div class="bg-slate-50 rounded-xl p-3"><div class="text-xs text-slate-500">Jenis</div><div class="mt-1 text-sm font-semibold capitalize" x-text="item.jenis"></div></div>
            <div class="bg-slate-50 rounded-xl p-3"><div class="text-xs text-slate-500">Kamar</div><div class="mt-1 text-sm font-semibold" x-text="item.jumlah_kamar"></div></div>
            <div class="bg-slate-50 rounded-xl p-3"><div class="text-xs text-slate-500">Tersedia</div><div class="mt-1 text-sm font-semibold" x-text="item.kamar_tersedia"></div></div>
          </div>
          <div class="mt-4 text-xs text-slate-500">Diajukan <span x-text="formatDate(item.tanggal_pengajuan)"></span></div>
          <div class="flex gap-2 mt-5"><button @click="show(item.id_verifikasi)" class="btn-secondary flex-1">Periksa Data</button><button x-show="tab === 'menunggu'" @click="decide(item, 'disetujui')" class="btn-primary flex-1">Setujui</button></div>
        </div>
      </article>
    </template>
  </div>

  <div x-show="detail" x-cloak class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="close()"></div>
    <div class="relative bg-white w-full sm:max-w-4xl max-h-[92vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10"><div><h2 class="font-bold text-slate-900" x-text="detail?.nama_kos || 'Detail Kos'"></h2><p class="text-xs text-slate-500">Pemeriksaan pengajuan</p></div><button @click="close()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button></div>
      <div class="p-5 sm:p-6 space-y-6" x-show="detail">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div><h3 class="font-semibold">Informasi Kos</h3><dl class="mt-3 space-y-2 text-sm"><div><dt class="text-slate-500">Alamat</dt><dd x-text="detail?.alamat"></dd></div><div><dt class="text-slate-500">Jenis</dt><dd class="capitalize" x-text="detail?.jenis"></dd></div><div><dt class="text-slate-500">Koordinat</dt><dd x-text="`${detail?.latitude}, ${detail?.longitude}`"></dd></div><div><dt class="text-slate-500">Deskripsi</dt><dd class="whitespace-pre-line" x-text="detail?.deskripsi || '-'"></dd></div></dl></div>
          <div><h3 class="font-semibold">Pemilik</h3><dl class="mt-3 space-y-2 text-sm"><div><dt class="text-slate-500">Nama</dt><dd x-text="detail?.nama_pemilik"></dd></div><div><dt class="text-slate-500">Email</dt><dd x-text="detail?.email_pemilik"></dd></div><div><dt class="text-slate-500">No. HP</dt><dd x-text="detail?.no_hp_pemilik || '-'"></dd></div><div><dt class="text-slate-500">NIK</dt><dd x-text="detail?.nik_pemilik || '-'"></dd></div></dl></div>
        </div>
        <div><h3 class="font-semibold">Kamar dan Harga</h3><div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3"><template x-for="k in (detail?.kamar || [])" :key="k.id_kamar"><div class="border border-slate-200 rounded-xl p-4"><div class="flex justify-between gap-3"><div><div class="font-semibold" x-text="`Kamar ${k.nomor_kamar}`"></div><div class="text-xs text-slate-500" x-text="`${k.tipe_kamar || 'Kamar'} · kapasitas ${k.kapasitas} orang`"></div></div><span class="text-xs px-2 py-1 rounded-full bg-slate-100" x-text="k.status"></span></div><div class="mt-3 space-y-1 text-sm"><template x-for="h in (k.harga || [])" :key="h.jumlah_orang"><div class="flex justify-between"><span x-text="`${h.jumlah_orang} orang`"></span><strong x-text="formatRupiah(h.harga_total)"></strong></div></template><div x-show="!(k.harga || []).length" class="text-xs text-slate-400">Belum ada konfigurasi harga.</div></div></div></template></div></div>
        <div x-show="(detail?.foto || []).length"><h3 class="font-semibold">Foto Kos</h3><div class="mt-3 flex gap-3 overflow-x-auto"><template x-for="f in (detail?.foto || [])" :key="f.id_foto"><img :src="BASE_URL + '/uploads' + f.nama_file" class="w-40 h-28 object-cover rounded-xl border border-slate-200 shrink-0" alt="Foto kos" @error="$event.target.style.display='none'"></template></div></div>
        <div x-show="tab === 'menunggu'" class="border-t border-slate-200 pt-5"><label class="text-sm font-medium text-slate-700">Catatan untuk pemilik <span class="text-slate-400">(wajib jika ditolak)</span></label><textarea x-model="catatan" rows="3" class="input mt-2 w-full" placeholder="Tulis alasan atau data yang perlu diperbaiki..."></textarea><div class="flex flex-col sm:flex-row gap-2 mt-3"><button @click="decide(detail, 'ditolak')" class="btn-secondary text-red-600 flex-1">Tolak / Minta Perbaikan</button><button @click="decide(detail, 'disetujui')" class="btn-primary flex-1">Setujui & Aktifkan Kos</button></div></div>
      </div>
    </div>
  </div>
</div>
<script>
function adminVerifikasiPage() {
  return {
    items: <?= json_encode($pengajuan ?? []) ?>,
    summary: { menunggu: 0, disetujui: 0, ditolak: 0 },
    tab: 'menunggu', loading: false, detail: null, catatan: '',
    async init() { await this.refresh(false); },
    async refresh(withLoading = true) {
      this.loading = withLoading;
      try { const res = await API.get('/admin/verifikasi?status=' + encodeURIComponent(this.tab), false); this.items = res.data || []; this.summary = res.summary || this.summary; } finally { this.loading = false; }
    },
    async load(tab) { this.tab = tab; this.detail = null; await this.refresh(true); },
    async show(id) { try { const res = await API.get('/admin/verifikasi/' + id); this.detail = res.data; this.catatan = res.data.catatan || ''; } catch(e) {} },
    close() { this.detail = null; this.catatan = ''; },
    async decide(item, keputusan) {
      if (keputusan === 'ditolak' && !this.catatan.trim()) { Alpine.store('ui').toast('Catatan wajib diisi ketika pengajuan ditolak.', 'error'); return; }
      const nama = item?.nama_kos || 'kos ini';
      const ok = await Alpine.store('ui').confirm(keputusan === 'disetujui' ? `Setujui ${nama} dan aktifkan listing?` : `Tolak ${nama}?`);
      if (!ok) return;
      try { await API.post('/admin/verifikasi/keputusan', { id_verifikasi: item.id_verifikasi, keputusan, catatan: this.catatan }); this.close(); await this.refresh(true); } catch(e) {}
    },
    badgeClass(s) { return s === 'menunggu' ? 'bg-amber-100 text-amber-700' : s === 'disetujui' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; },
    labelStatus(s) { return s === 'menunggu' ? 'Menunggu' : s === 'disetujui' ? 'Disetujui' : 'Ditolak'; },
    formatDate(v) { if (!v) return '-'; return new Date(v.replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }); },
    formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(v || 0)); }
  }
}
</script>
