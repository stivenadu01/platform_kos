<div x-data="pemilikLanggananPembayaran()" x-init="init()" class="space-y-6">
  <div>
    <a :href="window.BASE_URL + '/pemilik/langganan'" class="text-sm font-medium text-primary hover:underline">← Kembali ke Langganan</a>
    <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-slate-900">Pembayaran Langganan</h2>
    <p class="mt-2 text-sm leading-6 text-slate-500">Lihat status order Pro, tujuan pembayaran, dan catatan verifikasi admin.</p>
  </div>

  <div x-show="loading" class="card h-40 animate-pulse border border-slate-200"></div>

  <template x-if="!loading">
    <div class="space-y-5">
      <template x-if="selected">
        <div class="card border border-slate-200 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Order</p>
              <h3 class="mt-1 text-xl font-bold text-slate-900" x-text="selected.nomor_order"></h3>
              <p class="mt-1 text-sm text-slate-500" x-text="selected.nama_paket + ' • ' + (selected.jenis_pembayaran === 'renewal' ? 'Perpanjangan' : 'Langganan baru')"></p>
            </div>
            <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(selected.status)" x-text="statusLabel(selected.status)"></span>
          </div>

          <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl bg-slate-50 p-4">
              <p class="text-xs text-slate-500">Nominal</p>
              <p class="mt-1 font-bold text-slate-900" x-text="formatRupiah(selected.nominal)"></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <p class="text-xs text-slate-500">Metode</p>
              <p class="mt-1 font-semibold text-slate-900" x-text="methodLabel(selected.metode_pembayaran)"></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
              <p class="text-xs text-slate-500">Tanggal</p>
              <p class="mt-1 font-semibold text-slate-900" x-text="formatDateTime(selected.tanggal_pembayaran)"></p>
            </div>
          </div>

          <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="rounded-xl border border-slate-200 p-4">
              <h4 class="font-semibold text-slate-900">Tujuan Pembayaran</h4>
              <template x-if="paymentMethod">
                <div class="mt-3 text-sm leading-6 text-slate-600">
                  <p><span class="text-slate-500">Provider:</span> <strong x-text="paymentMethod.provider"></strong></p>
                  <p><span class="text-slate-500">Nomor/Rekening:</span> <strong x-text="paymentMethod.account"></strong></p>
                  <p><span class="text-slate-500">Atas nama:</span> <strong x-text="paymentMethod.holder"></strong></p>
                </div>
              </template>
              <p x-show="!paymentMethod" class="mt-3 text-sm text-red-600">Konfigurasi tujuan pembayaran tidak tersedia.</p>
            </div>

            <div class="rounded-xl border border-slate-200 p-4">
              <h4 class="font-semibold text-slate-900">Bukti Pembayaran</h4>
              <template x-if="selected.bukti_pembayaran">
                <a :href="window.BASE_URL + '/uploads' + selected.bukti_pembayaran" target="_blank" rel="noopener" class="block mt-3">
                  <img :src="window.BASE_URL + '/uploads' + selected.bukti_pembayaran" alt="Bukti pembayaran" class="max-h-56 w-full object-contain rounded-xl border border-slate-200 bg-slate-50">
                  <span class="block mt-2 text-xs text-primary">Buka bukti pembayaran</span>
                </a>
              </template>
              <p x-show="!selected.bukti_pembayaran" class="mt-3 text-sm text-slate-500">Belum ada bukti pembayaran.</p>

              <form x-show="selected.status === 'menunggu'" class="mt-4" @submit.prevent="uploadProof()">
                <label class="text-sm font-medium text-slate-700">Ganti/unggah bukti</label>
                <input x-ref="proof" type="file" accept="image/jpeg,image/png,image/webp" class="input mt-2 w-full" required>
                <button type="submit" class="btn-secondary mt-3 w-full" :disabled="uploading" x-text="uploading ? 'Mengunggah...' : 'Kirim Bukti Baru'"></button>
              </form>
            </div>
          </div>

          <div x-show="selected.catatan_admin" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Catatan Admin</p>
            <p class="mt-1 text-sm leading-6 text-red-800 whitespace-pre-line" x-text="selected.catatan_admin"></p>
          </div>
        </div>
      </template>

      <div class="card border border-slate-200">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="font-bold text-slate-900">Riwayat Pembayaran</h3>
            <p class="mt-1 text-sm text-slate-500">Riwayat pembayaran langganan tersimpan terpisah dari tagihan penghuni.</p>
          </div>
        </div>

        <div class="mt-5 hidden md:block overflow-x-auto">
          <table class="w-full min-w-[760px] text-sm">
            <thead>
              <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
                <th class="pb-3 pr-4">Order</th>
                <th class="pb-3 pr-4">Paket</th>
                <th class="pb-3 pr-4">Nominal</th>
                <th class="pb-3 pr-4">Metode</th>
                <th class="pb-3 pr-4">Tanggal</th>
                <th class="pb-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <template x-if="items.length === 0">
                <tr><td colspan="6" class="py-10 text-center text-slate-500">Belum ada pembayaran langganan.</td></tr>
              </template>
              <template x-for="item in items" :key="item.id_pembayaran_langganan">
                <tr
                  class="border-b border-slate-100 last:border-0 cursor-pointer hover:bg-slate-50"
                  @click="select(item.id_pembayaran_langganan)">
                  <td class="py-4 pr-4 font-semibold text-slate-800" x-text="item.nomor_order"></td>
                  <td class="py-4 pr-4" x-text="item.nama_paket"></td>
                  <td class="py-4 pr-4 font-semibold" x-text="formatRupiah(item.nominal)"></td>
                  <td class="py-4 pr-4" x-text="methodLabel(item.metode_pembayaran)"></td>
                  <td class="py-4 pr-4" x-text="formatDateTime(item.tanggal_pembayaran)"></td>
                  <td class="py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="mt-4 md:hidden space-y-3">
          <template x-if="items.length === 0">
            <div class="py-8 text-center text-sm text-slate-500">Belum ada pembayaran langganan.</div>
          </template>
          <template x-for="item in items" :key="'m-' + item.id_pembayaran_langganan">
            <button type="button" @click="select(item.id_pembayaran_langganan)" class="w-full text-left rounded-xl border border-slate-200 bg-slate-50/60 p-4 active:bg-slate-100">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="font-semibold text-slate-900 truncate" x-text="item.nama_paket"></div>
                  <div class="mt-1 text-xs text-slate-500 truncate" x-text="item.nomor_order"></div>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                <div><div class="text-slate-400">Nominal</div><div class="mt-1 font-semibold text-slate-800" x-text="formatRupiah(item.nominal)"></div></div>
                <div><div class="text-slate-400">Metode</div><div class="mt-1 font-medium text-slate-700" x-text="methodLabel(item.metode_pembayaran)"></div></div>
                <div class="col-span-2"><div class="text-slate-400">Tanggal pembayaran</div><div class="mt-1 font-medium text-slate-700" x-text="formatDateTime(item.tanggal_pembayaran)"></div></div>
              </div>
              <div class="mt-3 text-xs font-semibold text-primary">Lihat detail →</div>
            </button>
          </template>
        </div>
      </div>
    </div>
  </template>
</div>

<script>
function pemilikLanggananPembayaran() {
  return {
    loading: true,
    uploading: false,
    items: [],
    selected: null,
    paymentMethods: [],

    get paymentMethod() {
      if (this.selected?.provider_pembayaran || this.selected?.nomor_tujuan_pembayaran || this.selected?.nama_penerima_pembayaran) {
        return { provider: this.selected.provider_pembayaran, account: this.selected.nomor_tujuan_pembayaran, holder: this.selected.nama_penerima_pembayaran };
      }
      const current = this.paymentMethods.find(item => Number(item.id_metode_pembayaran) === Number(this.selected?.id_metode_pembayaran));
      return current ? { provider: current.nama_provider, account: current.nomor_tujuan, holder: current.nama_penerima } : null;
    },

    async init() {
      try {
        const [payments, checkout] = await Promise.all([
          API.get('/pemilik/langganan/pembayaran', false),
          API.get('/pemilik/langganan/checkout', false)
        ]);
        this.items = payments.data || [];
        this.paymentMethods = checkout.data?.payment_methods || [];

        const id = new URLSearchParams(window.location.search).get('id');
        if (id) {
          await this.select(Number(id));
        } else if (this.items.length) {
          await this.select(this.items[0].id_pembayaran_langganan);
        }
      } catch (e) {
      } finally {
        this.loading = false;
      }
    },

    async select(id) {
      try {
        const res = await API.get('/pemilik/langganan/pembayaran/' + encodeURIComponent(id), false);
        this.selected = res.data;
      } catch (e) {}
    },

    async uploadProof() {
      const file = this.$refs.proof?.files?.[0];
      if (!file || !this.selected) return;

      const form = new FormData();
      form.append('id_pembayaran_langganan', this.selected.id_pembayaran_langganan);
      form.append('bukti_pembayaran', file);

      this.uploading = true;
      try {
        await API.post('/pemilik/langganan/pembayaran/bukti', form);
        await this.select(this.selected.id_pembayaran_langganan);
        const res = await API.get('/pemilik/langganan/pembayaran', false);
        this.items = res.data || [];
      } catch (e) {
      } finally {
        this.uploading = false;
      }
    },

    statusClass(status) {
      return status === 'menunggu'
        ? 'bg-amber-100 text-amber-700'
        : status === 'diverifikasi'
          ? 'bg-emerald-100 text-emerald-700'
          : status === 'ditolak'
            ? 'bg-red-100 text-red-700'
            : 'bg-slate-100 text-slate-600';
    },

    statusLabel(status) {
      return status === 'menunggu' ? 'Menunggu verifikasi'
        : status === 'diverifikasi' ? 'Pembayaran diterima'
        : status === 'ditolak' ? 'Pembayaran ditolak'
        : 'Dibatalkan';
    },

    methodLabel(method) {
      return method === 'transfer_bank' ? 'Transfer Bank' : 'E-Wallet';
    },

    formatRupiah(value) {
      return Alpine.store('utils').formatRupiah(value);
    },

    formatDateTime(value) {
      if (!value) return '-';
      return new Date(value.replace(' ', 'T')).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
      });
    }
  };
}
</script>
