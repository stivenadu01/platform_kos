<div
  x-data="pemilikDashboard()"
  x-init="init()"
  class="owner-page"
>
  <div class="owner-page-header">
    <div>
      <p class="owner-eyebrow">Dashboard Pemilik</p>
      <h2 class="owner-title">
        Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pemilik') ?> 👋
      </h2>
      <p class="owner-subtitle">Pantau kondisi kos, penghuni, dan pembayaran dari satu halaman.</p>
    </div>
    <div class="grid w-full gap-2 sm:flex sm:w-auto">
      <a href="<?= BASE_URL ?>/pemilik/kos" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:min-w-40">
        <?= masterIconSvg('building-2', 'h-4 w-4') ?>
        <span>Kelola Kos</span>
        <span class="ml-auto"><?= masterIconSvg('chevron-right', 'h-4 w-4') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/pemilik/penghuni/tambah" class="btn-primary inline-flex w-full sm:w-auto"><?= masterIconSvg('user-plus', 'h-4 w-4') ?> Tambah Penghuni</a>
    </div>
  </div>

  <div x-show="!loading && subscription.reminder" x-cloak class="card border border-amber-200 bg-amber-50 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><?= masterIconSvg('alert-circle', 'h-5 w-5') ?></span>
        <div>
          <p class="font-semibold text-amber-900" x-text="subscription.reminder"></p>
          <p class="mt-1 text-sm text-amber-800" x-show="subscription.status === 'berakhir'">Fitur Pro terkunci, tetapi data Anda tetap tersimpan.</p>
        </div>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/langganan" class="btn-primary shrink-0">Perpanjang Pro</a>
    </div>
  </div>

  <div x-show="loading" x-cloak class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <template x-for="i in 4" :key="i">
      <div class="card border border-slate-200 h-28 animate-pulse bg-white"></div>
    </template>
  </div>

  <section x-show="!loading && attentionItems.length" x-cloak class="owner-attention-panel">
    <div class="owner-attention-heading">
      <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><?= masterIconSvg('alert-circle', 'h-5 w-5') ?></span>
      <div><h3>Perlu diperhatikan</h3><p>Tindakan yang sebaiknya Anda selesaikan lebih dahulu.</p></div>
    </div>
    <div class="owner-attention-list">
      <template x-for="item in attentionItems" :key="item.label">
        <a :href="item.url" class="owner-attention-item">
          <span><strong x-text="item.value"></strong><span x-text="item.label"></span></span>
          <?= masterIconSvg('chevron-right', 'h-4 w-4') ?>
        </a>
      </template>
    </div>
  </section>

  <div x-show="!loading" x-cloak data-help="dashboard-ringkasan" class="owner-overview-card">
    <div class="owner-overview-primary">
      <div class="flex items-start justify-between gap-3">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Keterisian kamar</p><p class="mt-1 text-3xl font-bold tracking-tight text-slate-950"><span x-text="summary.kamar_terisi"></span><span class="text-lg font-semibold text-slate-400"> / <span x-text="summary.total_kamar"></span></span></p></div>
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><?= masterIconSvg('bed', 'h-5 w-5') ?></span>
      </div>
      <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500 transition-all duration-500" :style="'width:' + occupancyPercent + '%'" role="progressbar" :aria-valuenow="occupancyPercent" aria-valuemin="0" aria-valuemax="100"></div></div>
      <div class="mt-2 flex items-center justify-between text-xs"><span class="text-slate-500" x-text="occupancyPercent + '% terisi'"></span><a href="<?= BASE_URL ?>/pemilik/kamar" class="font-semibold text-primary">Lihat kamar →</a></div>
    </div>
    <div class="owner-overview-metrics">
      <a href="<?= BASE_URL ?>/pemilik/kos"><span class="text-blue-600"><?= masterIconSvg('building-2', 'h-4 w-4') ?></span><span><strong x-text="summary.total_kos"></strong><small>Properti</small></span></a>
      <a href="<?= BASE_URL ?>/pemilik/kos"><span class="text-emerald-600"><?= masterIconSvg('check-circle-2', 'h-4 w-4') ?></span><span><strong x-text="summary.kamar_tersedia"></strong><small>Kamar tersedia</small></span></a>
      <a href="<?= BASE_URL ?>/pemilik/kos"><span class="text-amber-600"><?= masterIconSvg('circle-x', 'h-4 w-4') ?></span><span><strong x-text="summary.kamar_tidak_tersedia"></strong><small>Perlu diperiksa</small></span></a>
      <a href="<?= BASE_URL ?>/pemilik/penghuni"><span class="text-violet-600"><?= masterIconSvg('users-round', 'h-4 w-4') ?></span><span><strong x-text="summary.penghuni_aktif"></strong><small>Penghuni aktif</small></span></a>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
    <div x-show="isPro" data-help="dashboard-keuangan" class="lg:col-span-2 card border border-slate-200 shadow-sm">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="font-semibold text-slate-900">Ringkasan Keuangan</h3>
          <p class="mt-1 text-sm text-slate-500">Kondisi pembayaran dan pemasukan bulan ini.</p>
        </div>
        <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="text-sm font-semibold text-primary hover:underline">Buka tagihan →</a>
      </div>

      <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl bg-slate-50 p-4">
          <p class="text-xs text-slate-500">Perlu ditagih</p>
          <p class="mt-2 text-xl font-bold text-slate-900" x-text="summary.tagihan_belum_lunas"></p>
        </div>
        <div class="rounded-xl bg-amber-50 p-4">
          <p class="text-xs text-amber-700">Total perlu ditagih</p>
          <p class="mt-2 text-xl font-bold text-slate-900" x-text="rupiah(summary.total_piutang)"></p>
        </div>
        <div class="rounded-xl bg-emerald-50 p-4">
          <p class="text-xs text-emerald-700">Pembayaran bulan ini</p>
          <p class="mt-2 text-xl font-bold text-slate-900" x-text="rupiah(summary.pendapatan_bulan)"></p>
        </div>
      </div>
    </div>

    <div x-show="!isPro" x-cloak class="lg:col-span-2 card border border-amber-200 bg-amber-50/60 shadow-sm">
      <div class="flex items-start gap-4">
        <div class="w-11 h-11 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center"><?= masterIconSvg('lock', 'h-5 w-5') ?></div>
        <div>
          <h3 class="font-semibold text-slate-900">Ringkasan Keuangan adalah fitur Pro</h3>
          <p class="mt-1 text-sm leading-6 text-slate-600">Pantau tagihan, piutang, dan pembayaran dari dashboard setelah mengaktifkan BetaKos Pro.</p>
          <a href="<?= BASE_URL ?>/pemilik/langganan" class="mt-4 inline-flex btn-primary">Lihat BetaKos Pro</a>
        </div>
      </div>
    </div>

    <div data-help="dashboard-aksi" class="card hidden border border-slate-200 shadow-sm sm:block">
      <h3 class="font-semibold text-slate-900">Aksi Cepat</h3>
      <div class="mt-4 space-y-2">
        <a href="<?= BASE_URL ?>/pemilik/kos/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-700"><?= masterIconSvg('building-2', 'h-4 w-4') ?></span><span class="text-sm font-medium">Tambah Kos</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/kamar/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-700"><?= masterIconSvg('bed', 'h-4 w-4') ?></span><span class="text-sm font-medium">Tambah Kamar</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/penghuni/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-700"><?= masterIconSvg('user-round', 'h-4 w-4') ?></span><span class="text-sm font-medium">Tambah Penghuni</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><?= masterIconSvg('wallet', 'h-4 w-4') ?></span><span class="text-sm font-medium">Catat Pembayaran</span>
        </a>
      </div>
    </div>
  </div>

  <div x-show="isPro" data-help="dashboard-tagihan" class="card border border-slate-200 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="font-semibold text-slate-900">Tagihan Terdekat</h3>
        <p class="mt-1 text-sm text-slate-500">Tagihan yang masih memiliki sisa pembayaran.</p>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="text-sm font-semibold text-primary hover:underline">Lihat semua →</a>
    </div>

    <div class="mt-5 !hidden md:!block overflow-x-auto">
      <table class="w-full min-w-[680px] text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
            <th class="pb-3 pr-4">Kamar</th>
            <th class="pb-3 pr-4">Kos</th>
            <th class="pb-3 pr-4">Jatuh Tempo</th>
            <th class="pb-3 pr-4">Sisa</th>
            <th class="pb-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="tagihan.length === 0">
            <tr><td colspan="5" class="py-10 text-center text-slate-500">Tidak ada tagihan yang perlu ditindaklanjuti.</td></tr>
          </template>
          <template x-for="item in tagihan" :key="item.id_tagihan">
            <tr class="border-b border-slate-100 last:border-0">
              <td class="py-4 pr-4"><div class="font-bold text-slate-900" x-text="'Kamar ' + item.nomor_kamar"></div><div class="mt-1 text-[11px] text-slate-400" x-text="item.nomor_tagihan"></div></td>
              <td class="py-4 pr-4" x-text="item.nama_kos"></td>
              <td class="py-4 pr-4" x-text="tanggal(item.tanggal_jatuh_tempo)"></td>
              <td class="py-4 pr-4 font-semibold" x-text="rupiah(item.sisa_tagihan)"></td>
              <td class="py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.status === 'sebagian' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'" x-text="item.status === 'sebagian' ? 'Sebagian' : 'Belum lunas'"></span></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="owner-mobile-card-stack mt-4 md:hidden">
      <div x-show="tagihan.length === 0" class="p-5 text-center text-sm text-slate-500">Tidak ada tagihan yang perlu ditindaklanjuti.</div>
      <template x-for="item in tagihan" :key="'mobile-' + item.id_tagihan">
        <a :href="BASE_URL + '/pemilik/pembayaran/detail?id_tagihan=' + item.id_tagihan" class="block p-3.5 active:bg-slate-50">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="owner-copy-full font-bold text-slate-900" x-text="'Kamar ' + item.nomor_kamar"></p>
              <p class="owner-copy-full mt-1 text-xs text-slate-500" x-text="item.nama_kos"></p>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="item.status === 'sebagian' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'" x-text="item.status === 'sebagian' ? 'Sebagian' : 'Belum lunas'"></span>
          </div>
          <div class="mt-3 flex items-end justify-between gap-3">
            <div><p class="text-[11px] text-slate-400">Jatuh tempo</p><p class="mt-0.5 text-xs font-medium text-slate-700" x-text="tanggal(item.tanggal_jatuh_tempo)"></p></div>
            <div class="text-right"><p class="text-[11px] text-slate-400">Sisa pembayaran</p><p class="mt-0.5 font-bold text-rose-600" x-text="rupiah(item.sisa_tagihan)"></p></div>
          </div>
        </a>
      </template>
    </div>
  </div>

  <div x-show="!isPro" x-cloak class="card border border-slate-200 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><?= masterIconSvg('lock', 'h-5 w-5') ?></div>
      <div>
        <h3 class="font-semibold text-slate-900">Tagihan Terdekat</h3>
        <p class="mt-1 text-sm text-slate-500">Kelola tagihan dan pembayaran dengan BetaKos Pro.</p>
      </div>
    </div>
  </div>
</div>

<script>
function pemilikDashboard() {
  return {
    loading: true,
    isPro: false,
    subscription: { status: 'gratis', days_remaining: 0, reminder: null },
    summary: {
      total_kos: 0, total_kamar: 0, kamar_terisi: 0, kamar_tersedia: 0, kamar_tidak_tersedia: 0,
      penghuni_aktif: 0, tagihan_belum_lunas: 0, total_piutang: 0, pendapatan_bulan: 0
    },
    tagihan: [],

    get occupancyPercent() {
      const total = Number(this.summary.total_kamar || 0);
      return total ? Math.min(100, Math.round((Number(this.summary.kamar_terisi || 0) / total) * 100)) : 0;
    },

    get attentionItems() {
      const items = [];
      if (!Number(this.summary.total_kos || 0)) {
        items.push({ value: 'Mulai', label: 'Tambahkan kos pertama Anda', url: BASE_URL + '/pemilik/kos/tambah' });
        return items;
      }
      if (this.isPro && Number(this.summary.tagihan_belum_lunas || 0) > 0) {
        items.push({ value: this.summary.tagihan_belum_lunas, label: 'tagihan perlu ditindaklanjuti', url: BASE_URL + '/pemilik/pembayaran' });
      }
      if (Number(this.summary.kamar_tidak_tersedia || 0) > 0) {
        items.push({ value: this.summary.kamar_tidak_tersedia, label: 'kamar tidak tersedia atau bermasalah', url: BASE_URL + '/pemilik/kos' });
      }
      return items;
    },

    async init() {
      try {
        const res = await API.get('/pemilik/dashboard', false);
        this.isPro = res.data.is_pro === true;
        this.subscription = res.data.subscription || this.subscription;
        this.summary = res.data.summary;
        this.tagihan = res.data.tagihan_terdekat || [];
      } finally {
        this.loading = false;
      }
    },

    rupiah(value) {
      return Alpine.store('utils').formatRupiah(value);
    },

    tanggal(value) {
      if (!value) return '-';
      return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
      });
    }
  };
}
</script>
