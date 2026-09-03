<div x-data="pemilikLanggananCheckout()" x-init="init()" class="space-y-6">
  <div>
    <a :href="window.BASE_URL + '/pemilik/langganan'" class="text-sm font-medium text-primary hover:underline">← Kembali ke Langganan</a>
    <p class="mt-4 text-sm font-semibold text-primary">Checkout Langganan</p>
    <h2 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">BetaKos Pro</h2>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
      Pembayaran dilakukan secara manual. <span x-text="isRenewal ? 'Perpanjangan akan aktif kembali setelah pembayaran diverifikasi admin dan menggunakan harga perpanjangan.' : 'Langganan baru aktif setelah pembayaran diverifikasi admin dan menggunakan harga awal.'"></span>
    </p>
  </div>

  <div x-show="loading" class="card h-48 animate-pulse border border-slate-200"></div>

  <template x-if="!loading && pendingPayment">
    <div class="card border border-amber-200 bg-amber-50">
      <div class="flex items-start gap-3">
        <span class="text-xl">🟡</span>
        <div class="min-w-0">
          <h3 class="font-bold text-amber-900">Pembayaran masih menunggu verifikasi</h3>
          <p class="mt-1 text-sm leading-6 text-amber-800">
            Order <strong x-text="pendingPayment.nomor_order"></strong> sedang diperiksa admin.
          </p>
          <a :href="window.BASE_URL + '/pemilik/langganan/pembayaran'" class="inline-flex mt-4 btn-primary">Lihat Status Pembayaran</a>
        </div>
      </div>
    </div>
  </template>

  <template x-if="!loading && !pendingPayment && selectedPackage">
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_420px] gap-6">
      <section class="space-y-6">
        <div class="card border-2 border-primary shadow-sm">
          <div>
            <label class="text-sm font-semibold text-slate-700">Durasi Pro</label>
            <select x-model="packageCode" @change="selectPackage()" class="input mt-2 w-full">
              <template x-for="item in packages" :key="item.id_paket_langganan">
                <option :value="item.kode" x-text="item.durasi_bulan + ' bulan — ' + formatRupiah(isRenewal ? item.harga_perpanjangan : item.harga_bulanan)"></option>
              </template>
            </select>
          </div>

          <div class="flex items-start justify-between gap-4 mt-5">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-primary" x-text="isRenewal ? 'Perpanjangan ' + selectedPackage.nama : selectedPackage.nama"></p>
              <h3 class="mt-1 text-xl font-bold text-slate-900" x-text="(isRenewal ? 'Perpanjangan ' : 'Langganan ') + selectedPackage.durasi_bulan + ' bulan'"></h3>
              <p class="mt-2 text-sm text-slate-500" x-text="selectedPackage.deskripsi"></p>
              <p class="mt-2 text-xs text-slate-500" x-text="isRenewal ? 'Harga perpanjangan: ' + formatRupiah(selectedPackage.harga_perpanjangan) + ' untuk ' + selectedPackage.durasi_bulan + ' bulan.' : 'Harga awal: ' + formatRupiah(selectedPackage.harga_bulanan) + ' untuk ' + selectedPackage.durasi_bulan + ' bulan.'"></p>
            </div>
            <div class="text-right whitespace-nowrap">
              <div class="text-xl font-bold text-slate-900" x-text="formatRupiah(displayPrice)"></div>
              <div class="text-xs text-slate-500" x-text="selectedPackage.durasi_bulan + ' bulan'"></div>
            </div>
          </div>

          <div class="mt-5 border-t border-slate-100 pt-5">
            <p class="text-sm font-semibold text-slate-900">Fitur Pro</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">
              <template x-for="fitur in selectedPackage.fitur" :key="fitur">
                <li class="flex gap-2"><span class="text-primary font-bold">✓</span><span x-text="fitur"></span></li>
              </template>
            </ul>
          </div>
        </div>

        <div class="card border border-slate-200">
          <h3 class="font-bold text-slate-900">Metode Pembayaran</h3>
          <p class="mt-1 text-sm text-slate-500">Pilih rekening tujuan yang sudah dikonfigurasi oleh BetaKos.</p>

          <div class="mt-4 grid gap-3">
            <template x-for="method in paymentMethods" :key="method.id_metode_pembayaran">
              <button
                type="button"
                @click="selectedMethod = Number(method.id_metode_pembayaran)"
                class="text-left rounded-xl border p-4 transition"
                :class="selectedMethod === Number(method.id_metode_pembayaran) ? 'border-primary bg-primary-soft ring-1 ring-primary' : 'border-slate-200 hover:border-slate-300'">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <span class="font-semibold text-slate-900" x-text="method.jenis === 'transfer_bank' ? 'Transfer Bank' : 'E-Wallet'"></span>
                    <span class="block text-xs text-slate-500 mt-1" x-text="method.nama_provider"></span>
                  </div>
                  <span class="h-4 w-4 rounded-full border flex items-center justify-center" :class="selectedMethod === Number(method.id_metode_pembayaran) ? 'border-primary' : 'border-slate-300'">
                    <span x-show="selectedMethod === Number(method.id_metode_pembayaran)" class="h-2 w-2 rounded-full bg-primary"></span>
                  </span>
                </div>
                <div class="mt-3 text-sm text-slate-600">
                  <span class="font-semibold" x-text="method.nomor_tujuan"></span>
                  <span class="mx-1">•</span>
                  <span x-text="'a.n. ' + method.nama_penerima"></span>
                </div>
                <div x-show="method.keterangan" class="mt-2 text-xs text-slate-500" x-text="method.keterangan"></div>
              </button>
            </template>
          </div>

          <div x-show="availableMethodCount === 0" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-700">
            Metode pembayaran belum tersedia. Admin perlu mengaktifkan minimal satu metode pembayaran pada menu <b>Metode Pembayaran</b>.
          </div>
        </div>
      </section>

      <aside class="lg:sticky lg:top-20 h-fit card border border-slate-200">
        <h3 class="font-bold text-slate-900">Konfirmasi Pembayaran</h3>

        <div class="mt-4 rounded-xl bg-slate-50 p-4">
          <div class="flex justify-between gap-4 text-sm">
            <span class="text-slate-500" x-text="selectedPackage.nama"></span>
            <strong x-text="formatRupiah(displayPrice)"></strong>
          </div>
          <div class="mt-3 border-t border-slate-200 pt-3 flex justify-between gap-4">
            <span class="font-semibold text-slate-700">Total</span>
            <strong class="text-lg text-slate-900" x-text="formatRupiah(displayPrice)"></strong>
          </div>
        </div>

        <form class="mt-5 space-y-4" @submit.prevent="submit()">
          <div>
            <label class="text-sm font-medium text-slate-700">Bukti pembayaran</label>
            <input
              x-ref="proof"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              class="input mt-2 w-full"
              required>
            <p class="mt-1 text-xs leading-5 text-slate-500">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
          </div>

          <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-800">
            Setelah submit, admin akan memeriksa pembayaran. Jangan melakukan pembayaran/order kedua selama order ini masih menunggu verifikasi.
          </div>

          <button
            type="submit"
            class="btn-primary w-full"
            :disabled="submitting || !selectedMethod || availableMethodCount === 0">
            <span x-text="submitting ? 'Mengirim...' : 'Kirim Pembayaran'"></span>
          </button>
        </form>
      </aside>
    </div>
  </template>
</div>

<script>
function pemilikLanggananCheckout() {
  return {
    loading: true,
    submitting: false,
    packageCode: <?= json_encode((string)query('paket', 'pro')) ?>,
    packages: [],
    selectedPackage: null,
    pendingPayment: null,
    isRenewal: false,
    paymentMethods: [],
    selectedMethod: 0,
    get availableMethodCount() { return this.paymentMethods.length; },
    get displayPrice() { return this.isRenewal ? Number(this.selectedPackage?.harga_perpanjangan || 0) : Number(this.selectedPackage?.harga_bulanan || 0); },

    async init() {
      try {
        const res = await API.get('/pemilik/langganan/checkout', false);
        this.packages = res.data.paket || [];
        this.pendingPayment = res.data.pending_payment || null;
        this.isRenewal = res.data.is_renewal === true;
        this.paymentMethods = res.data.payment_methods || [];
        this.selectedPackage = this.packages.find(item => item.kode === this.packageCode) || this.packages[0] || null;
        if (this.selectedPackage) this.packageCode = this.selectedPackage.kode;

        const first = this.paymentMethods[0];
        this.selectedMethod = first ? Number(first.id_metode_pembayaran) : 0;
      } catch (e) {
        // API already displays the error toast.
      } finally {
        this.loading = false;
      }
    },

    selectPackage() {
      this.selectedPackage = this.packages.find(item => item.kode === this.packageCode) || this.packages[0] || null;
    },

    async submit() {
      if (!this.selectedPackage || !this.selectedMethod) return;

      const file = this.$refs.proof?.files?.[0];
      if (!file) {
        Alpine.store('ui').toast('Bukti pembayaran wajib diunggah.', 'error');
        return;
      }

      const form = new FormData();
      form.append('kode_paket', this.selectedPackage.kode);
      form.append('metode_pembayaran', this.selectedMethod);
      form.append('bukti_pembayaran', file);

      this.submitting = true;
      try {
        const res = await API.post('/pemilik/langganan/pembayaran', form);
        const id = res.data?.id_pembayaran_langganan;
        window.location.href = window.BASE_URL + '/pemilik/langganan/pembayaran' + (id ? '?id=' + encodeURIComponent(id) : '');
      } catch (e) {
        // API already displays the error toast.
      } finally {
        this.submitting = false;
      }
    },

    formatRupiah(value) {
      return Alpine.store('utils').formatRupiah(value);
    }
  };
}
</script>
