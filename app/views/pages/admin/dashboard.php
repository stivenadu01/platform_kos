<?php $dashboard = $dashboard ?? []; ?>
<div x-data="adminDashboardPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-primary">Ringkasan Platform</p>
      <h1 class="mt-1 text-xl sm:text-2xl font-bold text-slate-900">Dashboard Admin</h1>
      <p class="mt-1 text-sm text-slate-500">Pantau pengguna, kos, verifikasi, laporan, langganan, dan aktivitas pembayaran BetaKos dari satu halaman.</p>
    </div>
    <button type="button" @click="load()" class="btn-secondary">↻ Perbarui Data</button>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
    <a href="<?= BASE_URL ?>/admin/pengguna" class="card p-4 sm:p-5 border border-slate-200 hover:border-primary/30 transition"><div class="text-xs text-slate-500">Pengguna Aktif</div><div class="mt-2 text-2xl sm:text-3xl font-bold text-slate-900" x-text="d.pengguna?.aktif ?? 0"></div><div class="mt-1 text-xs text-slate-500" x-text="`${d.pengguna?.total ?? 0} total pengguna`"></div></a>
    <a href="<?= BASE_URL ?>/admin/verifikasi" class="card p-4 sm:p-5 border border-amber-200 bg-amber-50/30 hover:border-amber-300 transition"><div class="text-xs text-slate-500">Kos Menunggu Verifikasi</div><div class="mt-2 text-2xl sm:text-3xl font-bold text-amber-600" x-text="d.kos?.menunggu_verifikasi ?? 0"></div><div class="mt-1 text-xs text-slate-500">Perlu pemeriksaan</div></a>
    <div class="card p-4 sm:p-5 border border-emerald-200"><div class="text-xs text-slate-500">Kos Aktif</div><div class="mt-2 text-2xl sm:text-3xl font-bold text-emerald-600" x-text="d.kos?.aktif ?? 0"></div><div class="mt-1 text-xs text-slate-500" x-text="`${d.kos?.total ?? 0} total kos`"></div></div>
    <div class="card p-4 sm:p-5 border border-blue-200"><div class="text-xs text-slate-500">Pemilik Pro Aktif</div><div class="mt-2 text-2xl sm:text-3xl font-bold text-blue-600" x-text="d.langganan?.aktif ?? 0"></div><div class="mt-1 text-xs text-slate-500" x-text="`${d.langganan?.akan_berakhir_7_hari ?? 0} berakhir ≤ 7 hari`"></div></div>
  </div>

  <section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="card border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Pengguna</h2><p class="text-xs text-slate-500 mt-1">Status dan aktivitas login</p></div><a href="<?= BASE_URL ?>/admin/pengguna" class="text-xs font-semibold text-primary">Kelola →</a></div>
      <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Pemilik</div><div class="mt-1 text-xl font-bold" x-text="d.pengguna?.pemilik ?? 0"></div></div>
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Pelanggan</div><div class="mt-1 text-xl font-bold" x-text="d.pengguna?.pelanggan ?? 0"></div></div>
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Login hari ini</div><div class="mt-1 text-xl font-bold" x-text="d.pengguna?.login_hari_ini ?? 0"></div></div>
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Login 7 hari</div><div class="mt-1 text-xl font-bold" x-text="d.pengguna?.login_7_hari ?? 0"></div></div>
      </div>
    </div>
    <div class="card border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Kos & Kamar</h2><p class="text-xs text-slate-500 mt-1">Kondisi inventaris properti</p></div><a href="<?= BASE_URL ?>/admin/verifikasi" class="text-xs font-semibold text-primary">Verifikasi →</a></div>
      <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-emerald-50 p-3"><div class="text-xs text-slate-500">Kos aktif</div><div class="mt-1 text-xl font-bold text-emerald-700" x-text="d.kos?.aktif ?? 0"></div></div>
        <div class="rounded-xl bg-amber-50 p-3"><div class="text-xs text-slate-500">Menunggu</div><div class="mt-1 text-xl font-bold text-amber-700" x-text="d.kos?.menunggu_verifikasi ?? 0"></div></div>
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Kamar tersedia</div><div class="mt-1 text-xl font-bold" x-text="d.kamar?.tersedia ?? 0"></div></div>
        <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Kamar terisi</div><div class="mt-1 text-xl font-bold" x-text="d.kamar?.terisi ?? 0"></div></div>
      </div>
    </div>
    <div class="card border border-slate-200 p-5">
      <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Langganan & Pembayaran</h2><p class="text-xs text-slate-500 mt-1">Kondisi layanan Pro</p></div><a href="<?= BASE_URL ?>/admin/langganan" class="text-xs font-semibold text-primary">Kelola →</a></div>
      <div class="mt-5 space-y-3">
        <div class="flex justify-between gap-3 text-sm"><span class="text-slate-500">Menunggu pembayaran</span><strong x-text="d.pembayaran_langganan?.menunggu ?? 0"></strong></div>
        <div class="flex justify-between gap-3 text-sm"><span class="text-slate-500">Pro aktif</span><strong x-text="d.langganan?.aktif ?? 0"></strong></div>
        <div class="flex justify-between gap-3 text-sm"><span class="text-slate-500">Berakhir</span><strong x-text="d.langganan?.berakhir ?? 0"></strong></div>
        <div class="flex justify-between gap-3 text-sm"><span class="text-slate-500">Berakhir ≤ 7 hari</span><strong class="text-amber-600" x-text="d.langganan?.akan_berakhir_7_hari ?? 0"></strong></div>
        <div class="border-t border-slate-100 pt-3 flex justify-between gap-3 text-sm"><span class="text-slate-500">Pendapatan Pro bulan ini</span><strong class="text-emerald-700" x-text="formatRupiah(d.pembayaran_langganan?.pendapatan_bulan_ini)"></strong></div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="<?= BASE_URL ?>/admin/laporan" class="card p-4 border border-slate-200"><div class="text-xs text-slate-500">Laporan Menunggu</div><div class="mt-1 text-2xl font-bold" x-text="d.laporan?.menunggu ?? 0"></div></a>
    <a href="<?= BASE_URL ?>/admin/laporan" class="card p-4 border border-slate-200"><div class="text-xs text-slate-500">Laporan Diproses</div><div class="mt-1 text-2xl font-bold" x-text="d.laporan?.diproses ?? 0"></div></a>
    <a href="<?= BASE_URL ?>/admin/langganan/metode-pembayaran" class="card p-4 border border-slate-200"><div class="text-xs text-slate-500">Metode Pembayaran Aktif</div><div class="mt-1 text-2xl font-bold" x-text="d.metode_pembayaran?.aktif ?? 0"></div><div class="mt-1 text-xs text-slate-500" x-text="`${d.metode_pembayaran?.bank_aktif ?? 0} bank · ${d.metode_pembayaran?.ewallet_aktif ?? 0} e-wallet`"></div></a>
    <div class="card p-4 border border-slate-200"><div class="text-xs text-slate-500">Penghuni Aktif</div><div class="mt-1 text-2xl font-bold" x-text="d.penghuni_aktif ?? 0"></div><div class="mt-1 text-xs text-slate-500">Data penghuni pemilik Pro</div></div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="card border border-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><div><h2 class="font-bold text-slate-900">Pengguna Terbaru</h2><p class="text-xs text-slate-500 mt-1">Termasuk login terakhir</p></div><a href="<?= BASE_URL ?>/admin/pengguna" class="text-xs font-semibold text-primary">Lihat semua →</a></div>
      <div class="divide-y divide-slate-100"><template x-for="u in (d.terbaru?.pengguna || [])" :key="u.id_user"><div class="px-5 py-3 flex items-center justify-between gap-4"><div class="min-w-0"><div class="font-semibold text-sm truncate" x-text="u.nama"></div><div class="text-xs text-slate-500 truncate" x-text="u.email"></div></div><div class="text-right shrink-0"><div class="text-xs font-medium" x-text="roleLabel(u.role)"></div><div class="text-[11px] text-slate-400" x-text="u.last_login_at ? 'Login ' + formatDateTime(u.last_login_at) : 'Belum pernah login'"></div></div></div></template><div x-show="!(d.terbaru?.pengguna || []).length" class="p-6 text-center text-sm text-slate-500">Belum ada pengguna.</div></div>
    </div>
    <div class="card border border-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><div><h2 class="font-bold text-slate-900">Kos Terbaru</h2><p class="text-xs text-slate-500 mt-1">Perkembangan listing di platform</p></div><a href="<?= BASE_URL ?>/admin/verifikasi" class="text-xs font-semibold text-primary">Verifikasi →</a></div>
      <div class="divide-y divide-slate-100"><template x-for="k in (d.terbaru?.kos || [])" :key="k.id_kos"><div class="px-5 py-3 flex items-center justify-between gap-4"><div class="min-w-0"><div class="font-semibold text-sm truncate" x-text="k.nama_kos"></div><div class="text-xs text-slate-500 truncate" x-text="'Pemilik: ' + k.nama_pemilik"></div></div><div class="text-right shrink-0"><span class="px-2 py-1 rounded-full text-[11px] font-semibold" :class="kosStatusClass(k.status)" x-text="kosStatusLabel(k.status)"></span><div class="text-[11px] text-slate-400 mt-1" x-text="formatDate(k.created_at)"></div></div></div></template><div x-show="!(d.terbaru?.kos || []).length" class="p-6 text-center text-sm text-slate-500">Belum ada kos.</div></div>
    </div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="card border border-amber-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-amber-100 bg-amber-50/40 flex items-center justify-between"><div><h2 class="font-bold text-slate-900">Pembayaran Perlu Diperiksa</h2><p class="text-xs text-slate-500 mt-1">Pembayaran langganan manual yang masih menunggu</p></div><a href="<?= BASE_URL ?>/admin/langganan" class="text-xs font-semibold text-primary">Periksa →</a></div>
      <div class="divide-y divide-slate-100"><template x-for="p in (d.terbaru?.pembayaran_menunggu || [])" :key="p.id_pembayaran_langganan"><div class="px-5 py-3 flex items-center justify-between gap-4"><div class="min-w-0"><div class="font-semibold text-sm truncate" x-text="p.nama_pemilik"></div><div class="text-xs text-slate-500 truncate" x-text="`${p.nama_paket} · ${p.nomor_order}`"></div></div><div class="text-right shrink-0"><div class="font-semibold text-sm" x-text="formatRupiah(p.nominal)"></div><div class="text-[11px] text-slate-400" x-text="formatDateTime(p.created_at)"></div></div></div></template><div x-show="!(d.terbaru?.pembayaran_menunggu || []).length" class="p-6 text-center text-sm text-slate-500">Tidak ada pembayaran yang menunggu.</div></div>
    </div>
    <div class="card border border-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><div><h2 class="font-bold text-slate-900">Laporan Terbaru</h2><p class="text-xs text-slate-500 mt-1">Laporan kos yang membutuhkan perhatian Admin</p></div><a href="<?= BASE_URL ?>/admin/laporan" class="text-xs font-semibold text-primary">Kelola →</a></div>
      <div class="divide-y divide-slate-100"><template x-for="r in (d.terbaru?.laporan_menunggu || [])" :key="r.id_laporan"><div class="px-5 py-3 flex items-center justify-between gap-4"><div class="min-w-0"><div class="font-semibold text-sm truncate" x-text="r.nama_kos"></div><div class="text-xs text-slate-500 truncate" x-text="`${reasonLabel(r.alasan)} · ${r.nama_pelapor}`"></div></div><div class="text-right shrink-0"><span class="px-2 py-1 rounded-full text-[11px] font-semibold" :class="reportStatusClass(r.status)" x-text="reportStatusLabel(r.status)"></span><div class="text-[11px] text-slate-400 mt-1" x-text="formatDate(r.created_at)"></div></div></div></template><div x-show="!(d.terbaru?.laporan_menunggu || []).length" class="p-6 text-center text-sm text-slate-500">Tidak ada laporan yang perlu ditangani.</div></div>
    </div>
  </section>
</div>

<script>
function adminDashboardPage() {
  return {
    d: <?= json_encode($dashboard, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    loading: false,
    async init() {},
    async load() { this.loading = true; try { const r = await API.get('/admin/dashboard', false); if (r.data) this.d = r.data; } finally { this.loading = false; } },
    roleLabel(v) { return v === 'pemilik' ? 'Pemilik' : v === 'admin' ? 'Admin' : 'Pelanggan'; },
    kosStatusLabel(v) { return ({aktif:'Aktif', menunggu_verifikasi:'Menunggu Verifikasi', draft:'Draft', ditolak:'Ditolak'})[v] || v || '-'; },
    kosStatusClass(v) { return v === 'aktif' ? 'bg-emerald-100 text-emerald-700' : v === 'menunggu_verifikasi' ? 'bg-amber-100 text-amber-700' : v === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600'; },
    reportStatusLabel(v) { return ({menunggu:'Menunggu',diproses:'Diproses'})[v] || v || '-'; },
    reportStatusClass(v) { return v === 'menunggu' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'; },
    reasonLabel(v) { return ({informasi_tidak_sesuai:'Informasi tidak sesuai',foto_tidak_sesuai:'Foto tidak sesuai',kos_sudah_tidak_tersedia:'Kos tidak tersedia',informasi_menyesatkan:'Informasi menyesatkan',lainnya:'Lainnya'})[v] || v || '-'; },
    formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(Number(v || 0)); },
    formatDate(v) { if (!v) return '-'; const d = new Date(String(v).replace(' ','T')); return isNaN(d) ? '-' : d.toLocaleDateString('id-ID', { dateStyle:'medium' }); },
    formatDateTime(v) { if (!v) return ''; const d = new Date(String(v).replace(' ','T')); return isNaN(d) ? '' : d.toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}); }
  }
}
</script>
