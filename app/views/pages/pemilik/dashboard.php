<div
  x-data="pemilikDashboard()"
  x-init="init()"
  class="space-y-6"
>
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
      <p class="text-sm font-medium text-primary">Dashboard Pemilik</p>
      <h2 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">
        Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pemilik') ?> 👋
      </h2>
      <p class="mt-1 text-sm text-slate-500">Pantau kondisi kos, penghuni, dan pembayaran dari satu halaman.</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= BASE_URL ?>/pemilik/kos" class="btn-secondary inline-flex w-auto">Kelola Kos</a>
      <a href="<?= BASE_URL ?>/pemilik/penghuni/tambah" class="btn-primary inline-flex w-auto">+ Tambah Penghuni</a>
    </div>
  </div>

  <div x-show="!loading && subscription.reminder" x-cloak class="card border border-amber-200 bg-amber-50 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div class="flex items-start gap-3">
        <span class="text-xl" x-text="subscription.status === 'berakhir' ? '🔒' : '⚠️'"></span>
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

  <div x-show="!loading" x-cloak data-help="dashboard-ringkasan" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="card border border-slate-200 shadow-sm">
      <p class="text-sm text-slate-500">Total Kos</p>
      <div class="mt-3 flex items-end justify-between">
        <p class="text-3xl font-bold text-slate-900" x-text="summary.total_kos"></p>
        <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl">🏠</span>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/kos" class="mt-3 inline-block text-xs font-semibold text-primary hover:underline">Lihat kos →</a>
    </div>

    <div class="card border border-slate-200 shadow-sm">
      <p class="text-sm text-slate-500">Kamar Terisi</p>
      <div class="mt-3 flex items-end justify-between">
        <p class="text-3xl font-bold text-slate-900" x-text="summary.kamar_terisi"></p>
        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl">👤</span>
      </div>
      <p class="mt-3 text-xs text-slate-500"><span x-text="summary.total_kamar"></span> total kamar</p>
    </div>

    <div class="card border border-slate-200 shadow-sm">
      <p class="text-sm text-slate-500">Kamar Tersedia</p>
      <div class="mt-3 flex items-end justify-between">
        <p class="text-3xl font-bold text-slate-900" x-text="summary.kamar_tersedia"></p>
        <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-xl">✓</span>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/kamar" class="mt-3 inline-block text-xs font-semibold text-primary hover:underline">Kelola kamar →</a>
    </div>

    <div class="card border border-slate-200 shadow-sm">
      <p class="text-sm text-slate-500">Kamar Tidak Tersedia</p>
      <div class="mt-3 flex items-end justify-between">
        <p class="text-3xl font-bold text-slate-900" x-text="summary.kamar_tidak_tersedia"></p>
        <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl">—</span>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/kamar" class="mt-3 inline-block text-xs font-semibold text-primary hover:underline">Kelola kamar →</a>
    </div>

    <div class="card border border-slate-200 shadow-sm">
      <p class="text-sm text-slate-500">Penghuni Aktif</p>
      <div class="mt-3 flex items-end justify-between">
        <p class="text-3xl font-bold text-slate-900" x-text="summary.penghuni_aktif"></p>
        <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-700 flex items-center justify-center text-xl">👥</span>
      </div>
      <a href="<?= BASE_URL ?>/pemilik/penghuni" class="mt-3 inline-block text-xs font-semibold text-primary hover:underline">Lihat penghuni →</a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
          <p class="text-xs text-slate-500">Tagihan belum lunas</p>
          <p class="mt-2 text-xl font-bold text-slate-900" x-text="summary.tagihan_belum_lunas"></p>
        </div>
        <div class="rounded-xl bg-amber-50 p-4">
          <p class="text-xs text-amber-700">Total piutang</p>
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
        <div class="w-11 h-11 shrink-0 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl">🔒</div>
        <div>
          <h3 class="font-semibold text-slate-900">Ringkasan Keuangan adalah fitur Pro</h3>
          <p class="mt-1 text-sm leading-6 text-slate-600">Pantau tagihan, piutang, dan pembayaran dari dashboard setelah mengaktifkan BetaKos Pro.</p>
          <a href="<?= BASE_URL ?>/pemilik/langganan" class="mt-4 inline-flex btn-primary">Lihat BetaKos Pro</a>
        </div>
      </div>
    </div>

    <div data-help="dashboard-aksi" class="card border border-slate-200 shadow-sm">
      <h3 class="font-semibold text-slate-900">Aksi Cepat</h3>
      <div class="mt-4 space-y-2">
        <a href="<?= BASE_URL ?>/pemilik/kos/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span>🏠</span><span class="text-sm font-medium">Tambah Kos</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/kamar/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span>🚪</span><span class="text-sm font-medium">Tambah Kamar</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/penghuni/tambah" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span>👤</span><span class="text-sm font-medium">Tambah Penghuni</span>
        </a>
        <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:bg-slate-50">
          <span>💳</span><span class="text-sm font-medium">Catat Pembayaran</span>
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
            <th class="pb-3 pr-4">Tagihan</th>
            <th class="pb-3 pr-4">Kos / Kamar</th>
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
              <td class="py-4 pr-4 font-semibold text-slate-800" x-text="item.nomor_tagihan"></td>
              <td class="py-4 pr-4"><span x-text="item.nama_kos"></span><span class="text-slate-400"> · </span><span x-text="item.nomor_kamar"></span></td>
              <td class="py-4 pr-4" x-text="tanggal(item.tanggal_jatuh_tempo)"></td>
              <td class="py-4 pr-4 font-semibold" x-text="rupiah(item.sisa_tagihan)"></td>
              <td class="py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.status === 'sebagian' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'" x-text="item.status === 'sebagian' ? 'Sebagian' : 'Belum lunas'"></span></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <div x-show="!isPro" x-cloak class="card border border-slate-200 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">🔒</div>
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
