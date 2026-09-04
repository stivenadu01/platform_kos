<div x-data="adminLanggananPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-primary">Manajemen Langganan</p>
      <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Langganan</h1>
      <p class="mt-1 text-sm text-slate-500">Pantau status Pro pemilik dan verifikasi pembayaran perpanjangan secara terpusat.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <template x-for="tabItem in tabs" :key="tabItem.key">
        <button type="button" @click="loadSubscriptions(tabItem.key)"
          class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
          :class="subscriptionTab === tabItem.key ? 'bg-primary text-white border-primary' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
          x-text="tabItem.label"></button>
      </template>
    </div>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
    <template x-for="card in summaryCards" :key="card.key">
      <button type="button" @click="loadSubscriptions(card.key === 'pembayaran_menunggu' ? 'menunggu' : card.key === 'akan_berakhir_7_hari' ? 'akan_berakhir' : card.key)"
        class="card border border-slate-200 p-4 text-left hover:border-primary/30 transition">
        <div class="text-xs text-slate-500" x-text="card.label"></div>
        <div class="mt-1 text-2xl font-bold text-slate-900" x-text="summary[card.key] ?? 0"></div>
      </button>
    </template>

    <div class="col-span-2 md:col-span-3 xl:col-span-8 grid grid-cols-2 gap-3">
      <div class="card border border-emerald-200 p-4 text-left">
        <div class="text-xs text-slate-500">Pendapatan Pro Bulan Ini</div>
        <div class="mt-1 text-xl font-bold text-emerald-700" x-text="formatRupiah(summary.pendapatan_bulan_ini)"></div>
      </div>
      <div class="card border border-blue-200 p-4 text-left">
        <div class="text-xs text-slate-500">Total Pendapatan Pro</div>
        <div class="mt-1 text-xl font-bold text-blue-700" x-text="formatRupiah(summary.total_pendapatan)"></div>
      </div>
    </div>
  </div>

  <section class="card border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h2 class="font-bold text-slate-900">Daftar Langganan</h2>
        <p class="text-xs text-slate-500 mt-1">Status berakhir dihitung berdasarkan tanggal berakhir tanpa menghapus data.</p>
      </div>
      <button type="button" @click="refreshSubscriptions()" class="btn-secondary text-sm">Muat ulang</button>
    </div>

    <div x-show="subscriptionLoading" class="p-10 text-center text-sm text-slate-500">Memuat langganan...</div>
    <div x-show="!subscriptionLoading && subscriptions.length === 0" class="p-10 text-center">
      <div class="text-3xl">—</div>
      <p class="mt-2 font-semibold text-slate-900">Tidak ada langganan</p>
      <p class="mt-1 text-sm text-slate-500">Belum ada data pada filter ini.</p>
    </div>

    <div x-show="!subscriptionLoading && subscriptions.length" class="!hidden md:!block admin-scroll-x">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
          <tr>
            <th class="text-left px-5 py-3 font-semibold">Pemilik</th>
            <th class="text-left px-5 py-3 font-semibold">Paket</th>
            <th class="text-left px-5 py-3 font-semibold">Periode</th>
            <th class="text-left px-5 py-3 font-semibold">Langganan</th>
            <th class="text-left px-5 py-3 font-semibold">Pembayaran Terakhir</th>
            <th class="text-right px-5 py-3 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in subscriptions" :key="item.id_langganan">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-900" x-text="item.nama_pemilik"></div>
                <div class="text-xs text-slate-500" x-text="item.email_pemilik"></div>
              </td>
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-900" x-text="item.nama_paket"></div>
                <div class="text-xs text-slate-500" x-text="formatRupiah(item.harga_bulanan) + ' awal • ' + formatRupiah(item.harga_perpanjangan) + ' harga perpanjangan / ' + item.durasi_bulan + ' bulan'"></div>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <div x-text="formatDate(item.tanggal_mulai)"></div>
                <div class="text-xs text-slate-500" x-text="'s/d ' + formatDate(item.tanggal_berakhir)"></div>
                <div x-show="item.status === 'aktif' && daysUntil(item.tanggal_berakhir) <= 7" class="mt-1 text-xs font-semibold text-amber-700" x-text="daysUntil(item.tanggal_berakhir) === 0 ? 'Berakhir hari ini' : 'H-'.concat(daysUntil(item.tanggal_berakhir))"></div>
              </td>
              <td class="px-5 py-4">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
              </td>
              <td class="px-5 py-4">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="paymentStatusClass(item.status_pembayaran_terakhir)" x-text="paymentStatusLabel(item.status_pembayaran_terakhir)"></span>
              </td>
              <td class="px-5 py-4 text-right">
                <button type="button" @click="showSubscription(item.id_langganan)" class="btn-secondary text-xs">Detail</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div x-show="!subscriptionLoading && subscriptions.length" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in subscriptions" :key="'m-' + item.id_langganan">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-semibold text-slate-900 truncate" x-text="item.nama_pemilik"></div>
              <div class="mt-1 text-xs text-slate-500 truncate" x-text="item.nama_paket + ' · ' + item.email_pemilik"></div>
            </div><span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div>
              <div class="text-slate-400">Periode</div>
              <div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_mulai) + ' s/d ' + formatDate(item.tanggal_berakhir)"></div>
            </div>
            <div>
              <div class="text-slate-400">Pembayaran terakhir</div>
              <div class="mt-1 text-slate-700" x-text="paymentStatusLabel(item.status_pembayaran_terakhir)"></div>
            </div>
          </div>
          <div class="mt-3 text-xs text-slate-500" x-text="formatRupiah(item.harga_bulanan) + ' awal · ' + formatRupiah(item.harga_perpanjangan) + ' harga perpanjangan / ' + item.durasi_bulan + ' bulan'"></div>
          <button type="button" @click="showSubscription(item.id_langganan)" class="mt-3 btn-primary text-xs w-full justify-center inline-flex items-center gap-2">Lihat Detail Langganan <span aria-hidden="true">→</span></button>
        </article>
      </template>
    </div>
  </section>

  <section class="card border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
      <h2 class="font-bold text-slate-900">Pembayaran Menunggu Pemeriksaan</h2>
      <p class="text-xs text-slate-500 mt-1">Periksa bukti pembayaran manual sebelum mengaktifkan atau memperpanjang Pro.</p>
    </div>
    <div x-show="paymentLoading" class="p-10 text-center text-sm text-slate-500">Memuat pembayaran...</div>
    <div x-show="!paymentLoading && pendingPayments.length === 0" class="p-8 text-center text-sm text-slate-500">Tidak ada pembayaran yang menunggu verifikasi.</div>
    <div x-show="!paymentLoading && pendingPayments.length" class="!hidden md:!block admin-scroll-x">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
          <tr>
            <th class="text-left px-5 py-3">Order</th>
            <th class="text-left px-5 py-3">Pemilik</th>
            <th class="text-left px-5 py-3">Paket</th>
            <th class="text-left px-5 py-3">Nominal</th>
            <th class="text-left px-5 py-3">Metode</th>
            <th class="text-left px-5 py-3">Tanggal</th>
            <th class="text-right px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in pendingPayments" :key="item.id_pembayaran_langganan">
            <tr>
              <td class="px-5 py-4 font-semibold" x-text="item.nomor_order"></td>
              <td class="px-5 py-4" x-text="item.nama_pemilik"></td>
              <td class="px-5 py-4" x-text="item.nama_paket"></td>
              <td class="px-5 py-4 font-semibold" x-text="formatRupiah(item.nominal)"></td>
              <td class="px-5 py-4" x-text="methodLabel(item.metode_pembayaran)"></td>
              <td class="px-5 py-4 whitespace-nowrap" x-text="formatDateTime(item.tanggal_pembayaran)"></td>
              <td class="px-5 py-4 text-right whitespace-nowrap">
                <button type="button" @click="showPayment(item.id_pembayaran_langganan)" class="btn-secondary text-xs">Detail</button>
                <button type="button" @click="decide(item, 'diverifikasi')" class="btn-primary text-xs ml-1">Approve</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div x-show="!paymentLoading && pendingPayments.length" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in pendingPayments" :key="'m-' + item.id_pembayaran_langganan">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="font-semibold text-slate-900 truncate" x-text="item.nama_pemilik"></div>
              <div class="mt-1 text-xs text-slate-500 truncate" x-text="item.nama_paket + ' · ' + item.nomor_order"></div>
            </div><span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div>
              <div class="text-slate-400">Nominal</div>
              <div class="mt-1 font-semibold text-slate-800" x-text="formatRupiah(item.nominal)"></div>
            </div>
            <div>
              <div class="text-slate-400">Metode</div>
              <div class="mt-1 text-slate-700" x-text="methodLabel(item.metode_pembayaran)"></div>
            </div>
            <div class="col-span-2">
              <div class="text-slate-400">Tanggal</div>
              <div class="mt-1 text-slate-700" x-text="formatDateTime(item.tanggal_pembayaran)"></div>
            </div>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-2"><button type="button" @click="showPayment(item.id_pembayaran_langganan)" class="btn-secondary text-xs">Detail</button><button type="button" @click="decide(item, 'diverifikasi')" class="btn-primary text-xs">Setujui</button></div>
        </article>
      </template>
    </div>
  </section>

  <div x-show="detail" x-cloak class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeDetail()"></div>
    <div class="relative bg-white w-full sm:max-w-4xl max-h-[94vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
        <div>
          <h2 class="font-bold text-slate-900" x-text="detailTitle"></h2>
          <p class="text-xs text-slate-500">Detail langganan dan riwayat pembayaran</p>
        </div>
        <button type="button" @click="closeDetail()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>

      <div class="p-5 sm:p-6 space-y-6">
        <div x-show="detailLoading" class="py-8 text-center text-sm text-slate-500">Memuat detail...</div>
        <template x-if="detail && !detailLoading">
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="rounded-xl bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Pemilik</div>
                <div class="mt-1 font-semibold" x-text="detail.nama_pemilik"></div>
                <div class="text-xs text-slate-500 mt-1" x-text="detail.email_pemilik"></div>
              </div>
              <div class="rounded-xl bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Paket</div>
                <div class="mt-1 font-semibold" x-text="detail.nama_paket"></div>
                <div class="text-xs text-slate-500 mt-1" x-text="formatRupiah(detail.harga_bulanan) + ' awal • ' + formatRupiah(detail.harga_perpanjangan) + ' harga perpanjangan / ' + detail.durasi_bulan + ' bulan'"></div>
              </div>
              <div class="rounded-xl bg-slate-50 p-4">
                <div class="text-xs text-slate-500">Status</div>
                <div class="mt-2"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(detail.status)" x-text="statusLabel(detail.status)"></span></div>
              </div>
            </div>

            <div>
              <h3 class="font-bold text-slate-900">Periode Langganan</h3>
              <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div>
                  <div class="text-xs text-slate-500">Mulai</div>
                  <div class="font-semibold" x-text="formatDate(detail.tanggal_mulai)"></div>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Berakhir</div>
                  <div class="font-semibold" x-text="formatDate(detail.tanggal_berakhir)"></div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="font-bold text-slate-900">Riwayat Pembayaran</h3>
              <p class="mt-1 text-xs text-slate-500">Klik transaksi untuk memeriksa detail dan bukti pembayaran, termasuk transaksi yang sudah diverifikasi atau ditolak.</p>
              <div class="mt-3 admin-scroll-x border border-slate-200 rounded-xl">
                <table class="min-w-full text-sm">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="text-left px-4 py-3">Order</th>
                      <th class="text-left px-4 py-3">Jenis</th>
                      <th class="text-left px-4 py-3">Nominal</th>
                      <th class="text-left px-4 py-3">Status</th>
                      <th class="text-left px-4 py-3">Tanggal</th>
                      <th class="text-left px-4 py-3">Bukti</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <template x-for="payment in detail.pembayaran" :key="payment.id_pembayaran_langganan">
                      <tr class="hover:bg-slate-50 cursor-pointer" @click="showPayment(payment.id_pembayaran_langganan)">
                        <td class="px-4 py-3 font-semibold" x-text="payment.nomor_order"></td>
                        <td class="px-4 py-3" x-text="payment.jenis_pembayaran === 'renewal' ? 'Perpanjangan' : 'Baru'"></td>
                        <td class="px-4 py-3" x-text="formatRupiah(payment.nominal)"></td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold" :class="paymentStatusClass(payment.status)" x-text="paymentStatusLabel(payment.status)"></span></td>
                        <td class="px-4 py-3 whitespace-nowrap" x-text="formatDateTime(payment.tanggal_pembayaran)"></td>
                        <td class="px-4 py-3">
                          <span x-show="payment.bukti_pembayaran" class="text-xs font-semibold text-primary">Lihat bukti</span>
                          <span x-show="!payment.bukti_pembayaran" class="text-xs text-slate-400">Tidak ada</span>
                        </td>
                      </tr>
                    </template>
                    <tr x-show="!detail.pembayaran?.length">
                      <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada riwayat pembayaran.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <h3 class="font-bold text-slate-900">Riwayat Langganan Pemilik</h3>
              <div class="mt-3 space-y-2">
                <template x-for="history in detail.histori_langganan_pemilik" :key="history.id_langganan">
                  <div class="rounded-xl border border-slate-200 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                      <div class="font-semibold" x-text="history.nama_paket"></div>
                      <div class="text-xs text-slate-500" x-text="formatDate(history.tanggal_mulai) + ' s/d ' + formatDate(history.tanggal_berakhir)"></div>
                    </div>
                    <span class="self-start rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(history.status)" x-text="statusLabel(history.status)"></span>
                  </div>
                </template>
              </div>
            </div>

            <div x-show="detail.catatan" class="rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm">
              <div class="font-semibold text-amber-900">Catatan langganan</div>
              <div class="mt-1 text-amber-800" x-text="detail.catatan"></div>
            </div>

            <div x-show="detailPayment" class="rounded-xl border border-slate-200 p-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <h3 class="font-bold text-slate-900">Detail Pembayaran yang Dipilih</h3>
                  <p class="text-xs text-slate-500 mt-1" x-text="detailPayment?.nomor_order"></p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="paymentStatusClass(detailPayment?.status)" x-text="paymentStatusLabel(detailPayment?.status)"></span>
              </div>
              <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                  <div class="text-xs text-slate-500">Nominal</div>
                  <div class="font-semibold" x-text="formatRupiah(detailPayment?.nominal)"></div>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Metode</div>
                  <div class="font-semibold" x-text="methodLabel(detailPayment?.metode_pembayaran)"></div>
                  <div class="text-xs text-slate-500 mt-1" x-text="detailPayment?.provider_pembayaran || '-'"></div>
                  <div class="text-xs text-slate-500" x-text="detailPayment?.nomor_tujuan_pembayaran || ''"></div>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Jenis</div>
                  <div class="font-semibold" x-text="detailPayment?.jenis_pembayaran === 'renewal' ? 'Perpanjangan' : 'Baru'"></div>
                </div>
                <div>
                  <div class="text-xs text-slate-500">Tanggal</div>
                  <div class="font-semibold" x-text="formatDateTime(detailPayment?.tanggal_pembayaran)"></div>
                </div>
              </div>
              <template x-if="detailPayment?.bukti_pembayaran">
                <a :href="window.BASE_URL + '/uploads' + detailPayment.bukti_pembayaran" target="_blank" rel="noopener" class="block mt-4">
                  <img :src="window.BASE_URL + '/uploads' + detailPayment.bukti_pembayaran" alt="Bukti pembayaran" class="max-h-[420px] w-full object-contain rounded-xl border border-slate-200 bg-slate-50">
                  <span class="block mt-2 text-xs text-primary">Buka bukti pembayaran</span>
                </a>
              </template>
              <p x-show="!detailPayment?.bukti_pembayaran" class="mt-4 text-sm text-slate-500">Bukti pembayaran belum tersedia.</p>
              <div x-show="detailPayment?.catatan_admin" class="mt-4 rounded-lg bg-slate-50 p-3 text-sm"><span class="font-semibold">Catatan admin:</span> <span x-text="detailPayment?.catatan_admin"></span></div>
            </div>

            <div x-show="detailPaymentPending" class="border-t border-slate-200 pt-5">
              <h3 class="font-bold text-slate-900">Pemeriksaan Pembayaran</h3>
              <textarea x-model="catatan" rows="3" class="input mt-3 w-full" placeholder="Catatan wajib jika pembayaran ditolak."></textarea>
              <div class="mt-3 flex flex-col sm:flex-row gap-2">
                <button type="button" @click="decide(detailPayment, 'ditolak')" class="btn-secondary text-red-600 flex-1">Reject</button>
                <button type="button" @click="decide(detailPayment, 'diverifikasi')" class="btn-primary flex-1">Approve & Aktifkan</button>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

<script>
  function adminLanggananPage() {
    return {
      tabs: [{
          key: '',
          label: 'Semua'
        },
        {
          key: 'aktif',
          label: 'Aktif'
        },
        {
          key: 'menunggu',
          label: 'Menunggu'
        },
        {
          key: 'berakhir',
          label: 'Berakhir'
        },
        {
          key: 'akan_berakhir',
          label: 'Akan Berakhir (7 Hari)'
        },
        {
          key: 'dibatalkan',
          label: 'Dibatalkan'
        }
      ],
      summaryCards: [{
          key: 'aktif',
          label: 'Aktif'
        },
        {
          key: 'menunggu',
          label: 'Menunggu'
        },
        {
          key: 'berakhir',
          label: 'Berakhir'
        },
        {
          key: 'akan_berakhir',
          label: 'Akan Berakhir (7 Hari)'
        },
        {
          key: 'dibatalkan',
          label: 'Dibatalkan'
        },
        {
          key: 'pembayaran_menunggu',
          label: 'Pembayaran Menunggu'
        }
      ],
      subscriptionTab: '',
      subscriptions: [],
      pendingPayments: [],
      summary: {},
      subscriptionLoading: true,
      paymentLoading: true,
      detail: null,
      detailLoading: false,
      detailPayment: null,
      catatan: '',

      async init() {
        await Promise.all([this.refreshSubscriptions(), this.refreshPendingPayments()]);
      },

      async refreshSubscriptions() {
        this.subscriptionLoading = true;
        try {
          const qs = this.subscriptionTab ? '?status=' + encodeURIComponent(this.subscriptionTab) : '';
          const res = await API.get('/admin/langganan' + qs, false);
          this.subscriptions = res.data || [];
          this.summary = res.summary || {};
        } catch (e) {
          this.subscriptions = [];
        } finally {
          this.subscriptionLoading = false;
        }
      },

      async loadSubscriptions(status) {
        this.subscriptionTab = status;
        await this.refreshSubscriptions();
      },

      async refreshPendingPayments() {
        this.paymentLoading = true;
        try {
          const res = await API.get('/admin/langganan/pembayaran?status=menunggu', false);
          this.pendingPayments = res.data || [];
        } catch (e) {
          this.pendingPayments = [];
        } finally {
          this.paymentLoading = false;
        }
      },

      async showSubscription(id) {
        this.detail = null;
        this.detailPayment = null;
        this.detailLoading = true;
        try {
          const res = await API.get('/admin/langganan/' + encodeURIComponent(id), false);
          this.detail = res.data;
          this.detailPayment = (res.data?.pembayaran || []).find((payment) => payment.status === 'menunggu') || null;
          this.catatan = this.detailPayment?.catatan_admin || '';
        } catch (e) {} finally {
          this.detailLoading = false;
        }
      },

      async showPayment(id) {
        this.detail = null;
        this.detailPayment = null;
        this.catatan = '';
        this.detailLoading = true;
        try {
          const res = await API.get('/admin/langganan/pembayaran/' + encodeURIComponent(id), false);
          this.detailPayment = res.data;
          this.catatan = res.data?.catatan_admin || '';
          const sub = await API.get('/admin/langganan/' + encodeURIComponent(res.data.id_langganan), false);
          this.detail = sub.data;
        } catch (e) {} finally {
          this.detailLoading = false;
        }
      },

      closeDetail() {
        this.detail = null;
        this.detailPayment = null;
        this.catatan = '';
      },

      get detailTitle() {
        if (this.detail?.nama_paket) return this.detail.nama_paket + ' — ' + (this.detail.nama_pemilik || 'Langganan');
        return 'Detail Langganan';
      },

      get detailPaymentPending() {
        return !!(this.detailPayment && this.detailPayment.status === 'menunggu');
      },

      async decide(item, decision) {
        if (decision === 'ditolak' && !this.catatan.trim()) {
          Alpine.store('ui').toast('Catatan wajib diisi ketika pembayaran ditolak.', 'error');
          return;
        }
        const ok = await Alpine.store('ui').confirm(
          decision === 'diverifikasi' ?
          `Approve pembayaran ${item?.nomor_order || ''} dan aktifkan/perpanjang Pro?` :
          `Reject pembayaran ${item?.nomor_order || ''}?`
        );
        if (!ok) return;
        try {
          await API.post('/admin/langganan/pembayaran/keputusan', {
            id_pembayaran_langganan: item.id_pembayaran_langganan,
            keputusan: decision,
            catatan: this.catatan
          });
          this.closeDetail();
          await Promise.all([this.refreshSubscriptions(), this.refreshPendingPayments()]);
        } catch (e) {}
      },

      statusClass(status) {
        return status === 'aktif' ? 'bg-emerald-100 text-emerald-700' :
          status === 'menunggu' ? 'bg-amber-100 text-amber-700' :
          status === 'berakhir' ? 'bg-slate-200 text-slate-700' :
          'bg-red-100 text-red-700';
      },

      statusLabel(status) {
        return status === 'aktif' ? 'Aktif' :
          status === 'menunggu' ? 'Menunggu' :
          status === 'berakhir' ? 'Berakhir' :
          status === 'dibatalkan' ? 'Dibatalkan' :
          '-';
      },

      paymentStatusClass(status) {
        return status === 'menunggu' ? 'bg-amber-100 text-amber-700' :
          status === 'diverifikasi' ? 'bg-emerald-100 text-emerald-700' :
          status === 'ditolak' ? 'bg-red-100 text-red-700' :
          status === 'dibatalkan' ? 'bg-slate-200 text-slate-700' :
          'bg-slate-100 text-slate-500';
      },

      paymentStatusLabel(status) {
        return status === 'menunggu' ? 'Menunggu verifikasi' :
          status === 'diverifikasi' ? 'Diverifikasi' :
          status === 'ditolak' ? 'Ditolak' :
          status === 'dibatalkan' ? 'Dibatalkan' :
          'Belum ada pembayaran';
      },

      methodLabel(method) {
        return method === 'transfer_bank' ? 'Transfer Bank' : 'E-Wallet';
      },
      formatRupiah(value) {
        return Alpine.store('utils').formatRupiah(value);
      },

      daysUntil(value) {
        if (!value) return 9999;
        const today = new Date();
        const end = new Date(value + 'T00:00:00');
        today.setHours(0, 0, 0, 0);
        return Math.ceil((end - today) / 86400000);
      },
      formatDate(value) {
        if (!value) return '-';
        return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        });
      },
      formatDateTime(value) {
        if (!value) return '-';
        return new Date(String(value).replace(' ', 'T')).toLocaleString('id-ID', {
          dateStyle: 'medium',
          timeStyle: 'short'
        });
      }
    };
  }
</script>