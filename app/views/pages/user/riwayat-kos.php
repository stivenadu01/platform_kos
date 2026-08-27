<div x-data="riwayatKosPage()" x-init="init()" class="min-h-[calc(100vh-4rem)] bg-slate-50">
  <section class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Riwayat Kos Saya</h1>
      <p class="mt-1 text-sm text-slate-500">Lihat tempat tinggal yang sudah terhubung dan ajukan claim untuk riwayat yang belum tercatat.</p>
    </div>

    <section>
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900">Riwayat Terhubung</h2>
        <button type="button" @click="load()" class="btn-secondary text-xs">↻ Refresh</button>
      </div>
      <div x-show="loading" class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">Memuat riwayat...</div>
      <div x-show="!loading && !history.length" class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
        <h3 class="font-semibold text-slate-900">Belum ada riwayat terhubung</h3>
        <p class="mt-1 text-sm text-slate-500">Gunakan daftar kandidat di bawah untuk mengajukan claim.</p>
      </div>
      <div x-show="!loading && history.length" class="grid gap-4 md:grid-cols-2">
        <template x-for="item in history" :key="item.id_penghuni">
          <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="font-semibold text-slate-900" x-text="item.nama_kos"></h3>
                <p class="mt-1 text-sm text-slate-500" x-text="`Kamar ${item.nomor_kamar} · ${item.nama_pemilik}`"></p>
              </div>
              <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700" x-text="item.status === 'aktif' ? 'Sedang tinggal' : 'Selesai'"></span>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div>
                <dt class="text-xs text-slate-400">Mulai</dt>
                <dd class="mt-1 text-slate-700" x-text="item.tanggal_masuk"></dd>
              </div>
              <div>
                <dt class="text-xs text-slate-400">Selesai</dt>
                <dd class="mt-1 text-slate-700" x-text="item.tanggal_keluar || 'Masih tinggal'"></dd>
              </div>
            </dl>
          </article>
        </template>
      </div>
    </section>

    <section>
      <div class="mb-3">
        <h2 class="text-lg font-semibold text-slate-900">Tagihan Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Tagihan dan pembayaran yang terkait dengan histori penghuni Anda.</p>
      </div>
      <div x-show="!bills.length" class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Belum ada tagihan yang dapat ditampilkan.</div>
      <div x-show="bills.length" class="grid gap-4 md:grid-cols-2">
        <template x-for="bill in bills" :key="bill.id_tagihan">
          <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="font-semibold text-slate-900" x-text="bill.nama_kos"></h3>
                <p class="mt-1 text-sm text-slate-500" x-text="`${bill.nomor_tagihan} · Kamar ${bill.nomor_kamar}`"></p>
              </div>
              <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="billStatusClass(bill.status)" x-text="billStatusLabel(bill.status)"></span>
            </div>
            <p class="mt-3 text-sm text-slate-600" x-text="`${bill.tanggal_mulai} - ${bill.tanggal_selesai}`"></p>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-xs text-slate-400">Total</div>
                <div class="mt-1 font-semibold text-slate-900" x-text="formatRupiah(bill.total_tagihan)"></div>
              </div>
              <div>
                <div class="text-xs text-slate-400">Sisa</div>
                <div class="mt-1 font-semibold text-red-600" x-text="formatRupiah(bill.sisa_tagihan)"></div>
              </div>
            </div>
            <button type="button" @click.stop="openBill(bill)" class="btn-secondary mt-4 w-full">Lihat detail tagihan</button>
          </article>
        </template>
      </div>
    </section>

    <section x-show="claims.length">
      <div class="mb-3">
        <h2 class="text-lg font-semibold text-slate-900">Pengajuan Claim</h2>
        <p class="mt-1 text-sm text-slate-500">Pantau hasil verifikasi dari pemilik kos.</p>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <template x-for="item in claims" :key="item.id_claim">
          <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="font-semibold text-slate-900" x-text="item.nama_kos"></h3>
                <p class="mt-1 text-sm text-slate-500" x-text="item.nama_pemilik"></p>
              </div>
              <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="claimStatusClass(item.status)" x-text="claimStatusLabel(item.status)"></span>
            </div>
            <p class="mt-3 text-sm text-slate-600" x-text="`${item.tanggal_masuk} - ${item.tanggal_keluar || 'masih tinggal'}`"></p>
            <p x-show="item.catatan_pemilik" class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-600" x-text="item.catatan_pemilik"></p>
          </article>
        </template>
      </div>
    </section>

    <section>
      <div class="mb-3">
        <h2 class="text-lg font-semibold text-slate-900">Temukan Riwayat Lain</h2>
        <p class="mt-1 text-sm text-slate-500">Kandidat ditampilkan berdasarkan NIK akun Anda dan harus diverifikasi pemilik.</p>
      </div>
      <div x-show="!candidates.length" class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Tidak ada kandidat riwayat yang tersedia.</div>
      <div class="grid gap-4 md:grid-cols-2">
        <template x-for="item in candidates" :key="item.id_penghuni">
          <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-slate-900" x-text="item.nama_kos"></h3>
            <p class="mt-1 text-sm text-slate-500" x-text="`Kamar ${item.nomor_kamar} · ${item.nama_pemilik}`"></p>
            <p class="mt-3 text-sm text-slate-600" x-text="`${item.tanggal_masuk} - ${item.tanggal_keluar || 'masih tinggal'}`"></p>
            <p x-show="item.claim_status === 'ditolak'" class="mt-2 text-xs text-red-600">Claim sebelumnya ditolak. Anda dapat mengajukan ulang.</p>
            <button type="button" @click="openClaim(item)" class="btn-primary mt-4 w-full">Ajukan Claim</button>
          </article>
        </template>
      </div>
    </section>
  </section>

  <div x-show="billOpen" x-cloak class="fixed inset-0 z-80 flex items-end justify-center p-0 sm:items-center sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeBill()"></div>
    <div class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white p-5 shadow-2xl sm:max-w-2xl sm:rounded-2xl sm:p-6" @click.stop>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="font-bold text-slate-900">Detail Tagihan</h2>
          <p class="mt-1 text-sm text-slate-500" x-text="billNumber"></p>
        </div>
        <button type="button" @click="closeBill()" class="h-9 w-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <div x-show="billLoading" class="mt-5 rounded-xl bg-slate-50 p-8 text-center text-sm text-slate-500">Memuat detail tagihan...</div>
      <div x-show="billError" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-text="billError"></div>
      <div class="mt-5 space-y-5">
        <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-4">
          <div>
            <div class="text-xs text-slate-400">Kos</div>
            <div class="mt-1 font-medium text-slate-800" x-text="billName"></div>
          </div>
          <div>
            <div class="text-xs text-slate-400">Periode</div>
            <div class="mt-1 text-slate-700" x-text="`${billStart} - ${billEnd}`"></div>
          </div>
          <div>
            <div class="text-xs text-slate-400">Total</div>
            <div class="mt-1 font-semibold text-slate-900" x-text="formatRupiah(billTotal)"></div>
          </div>
          <div>
            <div class="text-xs text-slate-400">Dibayar</div>
            <div class="mt-1 font-semibold text-emerald-700" x-text="formatRupiah(billPaid)"></div>
          </div>
        </div>
        <div>
          <h3 class="font-semibold text-slate-900">Riwayat Pembayaran</h3>
          <div x-show="!billPayments.length" class="mt-3 rounded-xl border border-dashed border-slate-300 p-5 text-sm text-slate-500">Belum ada pembayaran untuk histori Anda.</div>
          <div x-show="billPayments.length" class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200">
            <template x-for="payment in billPayments" :key="payment.id_pembayaran">
              <div class="flex items-center justify-between gap-4 p-4 text-sm">
                <div>
                  <div class="font-medium text-slate-800" x-text="payment.nomor_pembayaran"></div>
                  <div class="mt-1 text-xs text-slate-500" x-text="`${payment.tanggal_bayar} · ${payment.metode}`"></div>
                </div>
                <div class="text-right">
                  <div class="font-semibold text-slate-900" x-text="formatRupiah(payment.jumlah)"></div>
                  <div class="text-xs text-emerald-700" x-text="payment.status"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="claimOpen" x-cloak class="fixed inset-0 z-80 flex items-end justify-center p-0 sm:items-center sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeClaim()"></div>
    <div class="relative w-full rounded-t-2xl bg-white p-5 shadow-2xl sm:max-w-lg sm:rounded-2xl sm:p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="font-bold text-slate-900">Ajukan Claim</h2>
          <p class="mt-1 text-sm text-slate-500" x-text="selected?.nama_kos || ''"></p>
        </div>
        <button type="button" @click="closeClaim()" class="h-9 w-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <form @submit.prevent="submitClaim" class="mt-5 space-y-4">
        <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">NIK akun Anda sudah dicocokkan dengan data penghuni. Pemilik akan memverifikasi claim ini.</div>
        <div><label class="label">Catatan tambahan</label><textarea x-model="form.catatan_mahasiswa" rows="4" maxlength="2000" class="input mt-1 w-full" placeholder="Contoh: saya tinggal di kamar ini selama dua semester"></textarea></div>
        <div class="flex flex-col gap-2 sm:flex-row-reverse"><button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Mengirim...' : 'Kirim Claim'"></button><button type="button" @click="closeClaim()" class="btn-secondary">Batal</button></div>
      </form>
    </div>
  </div>
</div>

<script>
  function riwayatKosPage() {
    return {
      history: [],
      bills: [],
      claims: [],
      candidates: [],
      loading: false,
      saving: false,
      claimOpen: false,
      billOpen: false,
      bill: null,
      billNumber: '',
      billName: '',
      billStart: '',
      billEnd: '',
      billTotal: 0,
      billPaid: 0,
      billPayments: [],
      billLoading: false,
      billError: '',
      selected: null,
      form: {
        catatan_mahasiswa: ''
      },
      async init() {
        await this.load();
      },
      async load() {
        this.loading = true;
        try {
          const [history, bills, claims, candidates] = await Promise.all([
            API.get('/pelanggan/riwayat-kos', false),
            API.get('/pelanggan/tagihan', false),
            API.get('/pelanggan/claim', false),
            API.get('/pelanggan/claim/candidates', false)
          ]);
          this.history = history.data || [];
          this.bills = bills.data || [];
          this.claims = claims.data || [];
          this.candidates = candidates.data || [];
        } finally {
          this.loading = false;
        }
      },
      async openBill(summary) {
        this.bill = summary;
        this.billNumber = summary.nomor_tagihan || '';
        this.billName = summary.nama_kos || '';
        this.billStart = summary.tanggal_mulai || '';
        this.billEnd = summary.tanggal_selesai || '';
        this.billTotal = summary.total_tagihan || 0;
        this.billPaid = summary.total_dibayar || 0;
        this.billPayments = [];
        this.billError = '';
        this.billLoading = true;
        this.billOpen = true;
        try {
          const response = await API.get('/pelanggan/tagihan/show?id_tagihan=' + encodeURIComponent(summary.id_tagihan), false);
          this.bill = {
            ...summary,
            ...response.data
          };
          this.billNumber = this.bill.nomor_tagihan || '';
          this.billName = this.bill.nama_kos || '';
          this.billStart = this.bill.tanggal_mulai || '';
          this.billEnd = this.bill.tanggal_selesai || '';
          this.billTotal = this.bill.total_tagihan || 0;
          this.billPaid = this.bill.total_dibayar || 0;
          this.billPayments = Array.isArray(this.bill.pembayaran) ? this.bill.pembayaran : [];
        } catch (error) {
          this.billError = error.message || 'Detail tagihan tidak dapat dimuat.';
        } finally {
          this.billLoading = false;
        }
      },
      closeBill() {
        this.billOpen = false;
        this.bill = null;
        this.billNumber = '';
        this.billName = '';
        this.billStart = '';
        this.billEnd = '';
        this.billTotal = 0;
        this.billPaid = 0;
        this.billPayments = [];
        this.billError = '';
      },
      formatRupiah(value) {
        return Alpine.store('utils').formatRupiah(value);
      },
      billStatusLabel(status) {
        return ({
          belum_lunas: 'Belum lunas',
          sebagian: 'Sebagian',
          lunas: 'Lunas',
          dibatalkan: 'Dibatalkan'
        })[status] || status;
      },
      billStatusClass(status) {
        return status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : status === 'dibatalkan' ? 'bg-slate-200 text-slate-600' : status === 'sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700';
      },
      openClaim(item) {
        this.selected = item;
        this.form = {
          catatan_mahasiswa: ''
        };
        this.claimOpen = true;
      },
      closeClaim() {
        if (!this.saving) this.claimOpen = false;
      },
      async submitClaim() {
        if (!this.selected) return;
        this.saving = true;
        try {
          await API.post('/pelanggan/claim', {
            id_penghuni: this.selected.id_penghuni,
            catatan_mahasiswa: this.form.catatan_mahasiswa
          });
          this.claimOpen = false;
          await this.load();
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },
      claimStatusLabel(status) {
        return ({
          menunggu: 'Menunggu',
          disetujui: 'Disetujui',
          ditolak: 'Ditolak'
        })[status] || status;
      },
      claimStatusClass(status) {
        return status === 'menunggu' ? 'bg-amber-100 text-amber-700' : status === 'disetujui' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
      }
    };
  }
</script>