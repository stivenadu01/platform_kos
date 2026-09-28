<div x-data="riwayatLanggananPage()" x-init="init()" class="owner-page max-w-5xl">
  <div class="owner-form-heading">
    <a :href="window.BASE_URL + '/pemilik/langganan'" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
    <p class="owner-eyebrow mt-3">BetaKos Pro</p>
    <h2 class="owner-title">Riwayat Langganan</h2>
    <p class="owner-subtitle">Semua aktivasi, perpanjangan, nominal, dan status langganan Anda.</p>
  </div>

  <div x-show="loading" class="grid gap-3 sm:grid-cols-2">
    <div class="card h-32 animate-pulse border border-slate-200"></div>
    <div class="card h-32 animate-pulse border border-slate-200"></div>
  </div>

  <div x-show="!loading && !items.length" x-cloak class="card border border-dashed border-slate-300 py-12 text-center">
    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><?= masterIconSvg('history', 'h-6 w-6') ?></span>
    <h3 class="mt-3 font-bold text-slate-900">Belum ada transaksi</h3>
    <p class="mt-1 text-sm text-slate-500">Riwayat akan muncul setelah Anda mengaktifkan BetaKos Pro.</p>
    <a :href="window.BASE_URL + '/pemilik/langganan'" class="btn-subscription mt-4 inline-flex">Lihat paket Pro</a>
  </div>

  <div x-show="!loading && items.length" x-cloak class="grid gap-3 sm:grid-cols-2">
    <template x-for="item in items" :key="item.riwayat_id">
      <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate font-bold text-slate-900" x-text="item.nama_paket"></p>
            <p class="mt-1 text-xs text-slate-500" x-text="(item.jenis_pembayaran === 'renewal' ? 'Perpanjangan' : 'Aktivasi') + ' · ' + item.durasi_bulan + ' bulan'"></p>
          </div>
          <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold" :class="statusClass(item.status_pembayaran)" x-text="statusLabel(item.status_pembayaran)"></span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3">
          <div><p class="text-[11px] text-slate-400">Total</p><p class="mt-1 text-sm font-extrabold text-slate-900" x-text="item.is_gratis ? 'Gratis' : rupiah(item.nominal)"></p></div>
          <div><p class="text-[11px] text-slate-400">Tanggal</p><p class="mt-1 text-sm font-semibold text-slate-700" x-text="tanggal(item.tanggal_transaksi)"></p></div>
        </div>
      </article>
    </template>
  </div>
</div>

<script>
function riwayatLanggananPage() {
  return {
    loading: true,
    items: [],
    async init() {
      try { const res = await API.get('/pemilik/langganan/riwayat', false); this.items = res.data || []; }
      finally { this.loading = false; }
    },
    rupiah(value) { return Alpine.store('utils').formatRupiah(value); },
    tanggal(value) { return Alpine.store('utils').formatDate(value); },
    statusLabel(value) { return ({ aktif:'Aktif', diverifikasi:'Berhasil', menunggu:'Menunggu', berakhir:'Berakhir', dibatalkan:'Dibatalkan', ditolak:'Ditolak' })[value] || value; },
    statusClass(value) {
      if (value === 'aktif' || value === 'diverifikasi') return 'bg-emerald-50 text-emerald-700';
      if (value === 'menunggu') return 'bg-amber-50 text-amber-700';
      if (value === 'berakhir') return 'bg-slate-100 text-slate-600';
      return 'bg-red-50 text-red-700';
    }
  };
}
</script>
