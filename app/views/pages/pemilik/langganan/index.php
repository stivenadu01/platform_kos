<div
  x-data="pemilikLanggananPage()"
  x-init="init()"
  class="space-y-6">

  <div>
    <p class="text-sm font-semibold text-primary">Monetisasi BetaKos</p>
    <h2 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Langganan BetaKos</h2>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
      Gunakan fitur dasar BetaKos secara gratis. BetaKos Pro membuka fitur manajemen penghuni dan keuangan.
    </p>
  </div>

  <div x-show="upgradeRequested" x-cloak class="card border border-amber-200 bg-amber-50 shadow-sm">
    <div class="flex items-start gap-3">
      <span class="text-xl">🔒</span>
      <div>
        <p class="font-semibold text-amber-900">Fitur tersebut membutuhkan BetaKos Pro</p>
        <p class="mt-1 text-sm leading-6 text-amber-800">Data dan kos Anda tetap aman. Aktifkan Pro untuk membuka kembali fitur manajemen penghuni dan keuangan.</p>
      </div>
    </div>
  </div>

  <div x-show="loading" class="card border border-slate-200 animate-pulse h-40"></div>

  <template x-if="!loading">
    <div class="space-y-6">
      <div class="card border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status saat ini</p>
            <div class="mt-2 flex items-center gap-3">
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="status.is_pro ? 'bg-emerald-50 text-emerald-700' : status.status === 'berakhir' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600'"
                x-text="status.is_pro ? 'BetaKos Pro Aktif' : status.status === 'berakhir' ? 'BetaKos Pro Telah Berakhir' : 'Paket Gratis'"></span>
              <span x-show="status.is_pro" class="text-sm text-slate-500" x-text="status.days_remaining + ' hari tersisa'"></span>
            </div>
          </div>

          <template x-if="status.is_pro || status.status === 'berakhir'">
            <div class="text-left sm:text-right">
              <p class="text-sm font-medium text-slate-700" x-text="status.package?.nama || 'BetaKos Pro'"></p>
              <p class="mt-1 text-xs text-slate-500" x-text="formatDate(status.subscription?.tanggal_mulai) + ' – ' + formatDate(status.subscription?.tanggal_berakhir)"></p>
            </div>
          </template>

          <template x-if="!status.is_pro && status.status !== 'berakhir'">
            <div class="text-left sm:text-right">
              <p class="text-sm font-semibold text-slate-900">Belum berlangganan Pro</p>
              <p class="mt-1 text-xs text-slate-500">Anda dapat mengajukan langganan Pro dan membayar secara manual.</p>
            </div>
          </template>
        </div>
      </div>

      <div x-show="status.reminder" x-cloak class="card border border-amber-200 bg-amber-50 shadow-sm">
        <div class="flex items-start gap-3">
          <span class="text-xl" x-text="status.status === 'berakhir' ? '🔒' : '⚠️'"></span>
          <div>
            <p class="font-semibold text-amber-900" x-text="status.reminder"></p>
            <p class="mt-1 text-sm leading-6 text-amber-800" x-show="status.status === 'berakhir'">Data kos, kamar, penghuni, tagihan, pembayaran, dan riwayat langganan tetap tersimpan.</p>
          </div>
        </div>
      </div>

      <div>
        <div class="mb-4">
          <h3 class="text-lg font-bold text-slate-900">Paket yang tersedia</h3>
          <p class="mt-1 text-sm text-slate-500">Pilih durasi Pro. Harga awal berlaku saat pertama berlangganan, sedangkan harga perpanjangan berlaku untuk pembelian berikutnya.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="card border border-slate-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gratis</p>
                <h4 class="mt-1 text-xl font-bold text-slate-900">BetaKos Free</h4>
              </div>
              <span class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700">Rp0</span>
            </div>
            <p class="mt-4 text-sm leading-6 text-slate-500">Untuk membangun profil dan mempublikasikan kos tanpa biaya.</p>
            <ul class="mt-5 space-y-3 text-sm text-slate-700">
              <li>✓ Profil pemilik</li><li>✓ Kelola kos</li><li>✓ Tipe dan kamar</li><li>✓ Foto dan listing publik</li><li>✓ Kos dapat ditemukan calon penghuni</li>
            </ul>
          </div>

          <div class="card border-2 border-primary bg-white shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 rounded-bl-xl bg-primary px-3 py-1 text-xs font-bold text-white">PRO</div>
            <div class="pr-10">
              <p class="text-xs font-semibold uppercase tracking-wider text-primary">BetaKos Pro</p>
              <h4 class="mt-1 text-xl font-bold text-slate-900">Manajemen Kos Lebih Lengkap</h4>
              <p class="mt-3 text-sm leading-6 text-slate-500">Satu paket Pro dengan pilihan durasi pembayaran. Pilih durasi yang paling sesuai tanpa membuat kartu paket terpisah.</p>
            </div>

            <div class="mt-5">
              <label class="text-sm font-semibold text-slate-700">Pilih durasi</label>
              <select x-model="selectedPackageCode" class="input mt-2 w-full">
                <template x-for="item in paket" :key="item.id_paket_langganan">
                  <option :value="item.kode" x-text="item.durasi_bulan === 1 ? '1 Bulan — ' + formatRupiah(status.is_pro || status.status === 'berakhir' ? item.harga_perpanjangan : item.harga_bulanan) : item.durasi_bulan + ' Bulan — ' + formatRupiah(status.is_pro || status.status === 'berakhir' ? item.harga_perpanjangan : item.harga_bulanan)"></option>
                </template>
              </select>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="text-xs text-slate-500">Harga awal</div>
                <div class="mt-1 font-bold text-slate-900" x-text="formatRupiah(selectedPackage?.harga_bulanan)"></div>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="text-xs text-slate-500">Harga perpanjangan</div>
                <div class="mt-1 font-bold text-slate-900" x-text="formatRupiah(selectedPackage?.harga_perpanjangan)"></div>
              </div>
            </div>

            <div class="mt-4 rounded-xl bg-primary-soft px-4 py-3">
              <div class="flex items-end justify-between gap-4">
                <div>
                  <p class="text-xs text-primary" x-text="selectedPackage ? selectedPackage.durasi_bulan + ' bulan' : '-' "></p>
                  <p class="mt-1 text-sm font-medium text-primary" x-text="status.is_pro || status.status === 'berakhir' ? 'Harga perpanjangan' : 'Harga awal'"></p>
                </div>
                <strong class="text-2xl text-primary" x-text="formatRupiah(selectedPrice)"></strong>
              </div>
            </div>

            <ul class="mt-5 space-y-3 text-sm text-slate-700">
              <template x-if="selectedPackage">
                <template x-for="fitur in selectedPackage.fitur" :key="fitur">
                  <li class="flex items-start gap-2"><span class="text-primary font-bold">✓</span><span x-text="fitur"></span></li>
                </template>
              </template>
            </ul>

            <div class="mt-6 rounded-xl bg-primary-soft px-4 py-3 text-sm text-primary">
              <span x-show="status.status === 'berakhir'">Langganan Pro sebelumnya telah berakhir. Lakukan perpanjangan untuk mengaktifkan kembali Pro.</span>
              <span x-show="!status.is_pro && status.status !== 'berakhir'">Aktifkan Pro dengan pembayaran manual, lalu tunggu verifikasi admin.</span>
              <span x-show="status.is_pro">Langganan Pro Anda sedang aktif dan seluruh fitur Pro dapat digunakan.</span>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
              <a x-show="!pendingPayment && !status.is_pro" :href="checkoutUrl" class="btn-primary text-center">Pilih Pro — <span x-text="formatRupiah(selectedPrice)"></span></a>
              <a x-show="pendingPayment" :href="window.BASE_URL + '/pemilik/langganan/pembayaran'" class="btn-secondary text-center">Lihat Pembayaran Menunggu</a>
              <a x-show="!pendingPayment && (status.is_pro || status.status === 'berakhir')" :href="checkoutUrl" class="btn-secondary text-center">Bayar Perpanjangan</a>
            </div>
          </div>
        </div>

      <div class="card border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h3 class="font-bold text-slate-900">Riwayat Langganan</h3>
            <p class="mt-1 text-sm text-slate-500">Riwayat paket akan tersimpan agar masa berlangganan dapat ditelusuri.</p>
          </div>
        </div>

        <div class="mt-5 hidden md:block overflow-x-auto">
          <table class="w-full min-w-[620px] text-sm">
            <thead>
              <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
                <th class="pb-3 pr-4">Paket</th>
                <th class="pb-3 pr-4">Mulai</th>
                <th class="pb-3 pr-4">Berakhir</th>
                <th class="pb-3 pr-4">Harga</th>
                <th class="pb-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <template x-if="history.length === 0">
                <tr><td colspan="5" class="py-10 text-center text-slate-500">Belum ada riwayat langganan.</td></tr>
              </template>
              <template x-for="item in history" :key="item.id_langganan">
                <tr class="border-b border-slate-100 last:border-0">
                  <td class="py-4 pr-4 font-semibold text-slate-800" x-text="item.nama_paket"></td>
                  <td class="py-4 pr-4" x-text="formatDate(item.tanggal_mulai)"></td>
                  <td class="py-4 pr-4" x-text="formatDate(item.tanggal_berakhir)"></td>
                  <td class="py-4 pr-4" x-text="formatRupiah(item.harga_bulanan) + ' / ' + item.durasi_bulan + ' bulan'"></td>
                  <td class="py-4">
                    <span
                      class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="item.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : item.status === 'menunggu' ? 'bg-amber-50 text-amber-700' : item.status === 'berakhir' ? 'bg-slate-100 text-slate-600' : 'bg-red-50 text-red-700'"
                      x-text="item.status === 'aktif' ? 'Aktif' : item.status === 'menunggu' ? 'Menunggu verifikasi' : item.status === 'berakhir' ? 'Berakhir' : item.status === 'dibatalkan' ? 'Dibatalkan' : item.status"></span>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="mt-4 md:hidden space-y-3">
          <template x-if="history.length === 0">
            <div class="py-8 text-center text-sm text-slate-500">Belum ada riwayat langganan.</div>
          </template>
          <template x-for="item in history" :key="'m-' + item.id_langganan">
            <article class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold text-slate-900 truncate" x-text="item.nama_paket"></div>
                  <div class="mt-1 text-xs text-slate-500" x-text="item.durasi_bulan + ' bulan' + ' · ' + formatRupiah(item.harga_bulanan)"></div>
                </div>
                <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : item.status === 'menunggu' ? 'bg-amber-50 text-amber-700' : item.status === 'berakhir' ? 'bg-slate-100 text-slate-600' : 'bg-red-50 text-red-700'" x-text="item.status === 'aktif' ? 'Aktif' : item.status === 'menunggu' ? 'Menunggu verifikasi' : item.status === 'berakhir' ? 'Berakhir' : item.status === 'dibatalkan' ? 'Dibatalkan' : item.status"></span>
              </div>
              <dl class="mt-3 grid grid-cols-2 gap-3 text-xs">
                <div><dt class="text-slate-400">Mulai</dt><dd class="mt-1 font-medium text-slate-700" x-text="formatDate(item.tanggal_mulai)"></dd></div>
                <div><dt class="text-slate-400">Berakhir</dt><dd class="mt-1 font-medium text-slate-700" x-text="formatDate(item.tanggal_berakhir)"></dd></div>
              </dl>
            </article>
          </template>
        </div>
      </div>
    </div>
  </template>
</div>

<script>
function pemilikLanggananPage() {
  return {
    loading: true,
    upgradeRequested: <?= !empty($upgrade_requested) ? 'true' : 'false' ?>,
    status: {
      is_pro: false,
      status: 'gratis',
      package: null,
      subscription: null,
      days_remaining: 0,
      reminder: null
    },
    paket: [],
    history: [],
    pendingPayment: null,
    selectedPackageCode: 'pro',

    get selectedPackage() {
      return this.paket.find(item => item.kode === this.selectedPackageCode) || this.paket[0] || null;
    },
    get selectedPrice() {
      const item = this.selectedPackage;
      if (!item) return 0;
      return this.status.is_pro || this.status.status === 'berakhir' ? Number(item.harga_perpanjangan || 0) : Number(item.harga_bulanan || 0);
    },
    get checkoutUrl() {
      return window.BASE_URL + '/pemilik/langganan/checkout?paket=' + encodeURIComponent(this.selectedPackageCode);
    },

    async init() {
      try {
        const [subscriptionRes, historyRes] = await Promise.all([
          API.get('/pemilik/langganan', false),
          API.get('/pemilik/langganan/riwayat', false)
        ]);

        this.status = subscriptionRes.data.status || this.status;
        this.paket = subscriptionRes.data.paket || [];
        const preferred = new URLSearchParams(window.location.search).get('paket');
        if (preferred && this.paket.some(item => item.kode === preferred)) this.selectedPackageCode = preferred;
        else if (this.paket.some(item => item.kode === 'pro')) this.selectedPackageCode = 'pro';
        else if (this.paket[0]) this.selectedPackageCode = this.paket[0].kode;
        this.pendingPayment = subscriptionRes.data.pending_payment || null;
        this.history = historyRes.data || [];
      } finally {
        this.loading = false;
      }
    },

    formatRupiah(value) {
      return Alpine.store('utils').formatRupiah(value);
    },

    formatDate(value) {
      if (!value) return '-';
      return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
      });
    }
  };
}
</script>
