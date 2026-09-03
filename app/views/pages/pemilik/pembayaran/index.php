<div
  x-data="pembayaranPage()"
  x-init="init()"
  class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-slate-900">
        Tagihan & Pembayaran
      </h2>
      <p class="mt-1 text-sm text-slate-500">
        Kelola tagihan, penyesuaian, dan pembayaran penghuni.
      </p>
    </div>
  </div>

  <div data-help="help-tagihan-summary" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="card border border-slate-200 shadow-sm">
      <p class="text-xs text-slate-500">Belum lunas</p>
      <p class="mt-2 text-xl font-bold text-slate-900" x-text="summary.belum_lunas"></p>
    </div>
    <div class="card border border-slate-200 shadow-sm">
      <p class="text-xs text-slate-500">Sebagian</p>
      <p class="mt-2 text-xl font-bold text-slate-900" x-text="summary.sebagian"></p>
    </div>
    <div class="card border border-slate-200 shadow-sm">
      <p class="text-xs text-slate-500">Lunas</p>
      <p class="mt-2 text-xl font-bold text-slate-900" x-text="summary.lunas"></p>
    </div>
    <div class="card border border-slate-200 shadow-sm">
      <p class="text-xs text-slate-500">Total sisa</p>
      <p class="mt-2 text-xl font-bold text-slate-900" x-text="format(summary.sisa)"></p>
    </div>
  </div>

  <div data-help="help-tagihan-filter" class="card border border-slate-200 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="form-group">
        <label class="label">Cari tagihan</label>
        <input
          type="search"
          x-model="search"
          @input.debounce.400ms="load()"
          class="input"
          placeholder="Nomor tagihan...">
      </div>

      <div class="form-group">
        <label class="label">Kos</label>
        <select x-model="idKos" @change="applyFilter()" class="input">
          <option value="">Semua kos</option>
          <template x-for="kos in kosList" :key="kos.id_kos">
            <option :value="kos.id_kos" x-text="kos.nama_kos"></option>
          </template>
        </select>
      </div>

      <div class="form-group">
        <label class="label">Kamar</label>
        <select x-model="idKamar" @change="applyFilter()" class="input">
          <option value="">Semua kamar</option>
          <template x-for="kamar in filteredKamarList" :key="kamar.id_kamar">
            <option :value="kamar.id_kamar" x-text="kamar.nomor_kamar"></option>
          </template>
        </select>
      </div>

      <div class="form-group">
        <label class="label">Status</label>
        <select x-model="status" @change="applyFilter()" class="input">
          <option value="">Semua status</option>
          <option value="belum_lunas">Belum lunas</option>
          <option value="sebagian">Sebagian</option>
          <option value="lunas">Lunas</option>
          <option value="dibatalkan">Dibatalkan</option>
        </select>
      </div>
    </div>
  </div>

  <div data-help="help-tagihan-list" class="card border border-slate-200 shadow-sm overflow-hidden">
    <div x-show="loading" class="py-12 text-center text-sm text-slate-500">
      Memuat tagihan...
    </div>

    <div x-show="!loading && tagihan.length === 0" x-cloak class="py-14 text-center">
      <div class="text-4xl mb-4">💳</div>
      <h3 class="font-semibold text-slate-900">Belum ada tagihan</h3>
      <p class="mt-1 text-sm text-slate-500">Tagihan akan muncul otomatis setelah penghuni ditambahkan.</p>
    </div>

    <div x-show="!loading && tagihan.length > 0" x-cloak class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="text-left px-5 py-3 font-semibold">Tagihan</th>
            <th class="text-left px-5 py-3 font-semibold">Kamar</th>
            <th class="text-left px-5 py-3 font-semibold">Periode</th>
            <th class="text-left px-5 py-3 font-semibold">Total</th>
            <th class="text-left px-5 py-3 font-semibold">Sisa</th>
            <th class="text-left px-5 py-3 font-semibold">Status</th>
            <th class="text-right px-5 py-3 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in tagihan" :key="item.id_tagihan">
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900" x-text="item.nomor_tagihan"></div>
                <div class="text-xs text-slate-500 mt-1" x-text="item.nama_kos"></div>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium" x-text="item.nomor_kamar"></div>
                <div class="text-xs text-slate-500" x-text="item.penghuni_aktif + ' penghuni aktif'"></div>
              </td>
              <td class="px-5 py-4">
                <div x-text="formatDate(item.tanggal_mulai) + ' - ' + formatDate(item.tanggal_selesai)"></div>
                <div class="text-xs text-slate-500 mt-1" x-text="'Jatuh tempo ' + formatDate(item.tanggal_jatuh_tempo)"></div>
              </td>
              <td class="px-5 py-4 font-semibold" x-text="format(item.total_tagihan)"></td>
              <td class="px-5 py-4 font-semibold" x-text="format(item.sisa_tagihan)"></td>
              <td class="px-5 py-4">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                  :class="statusClass(item.status)"
                  x-text="statusLabel(item.status)"></span>
              </td>
              <td class="px-5 py-4">
                <div class="flex justify-end gap-2">
                  <button data-help="help-tagihan-detail" type="button" @click="openDetail(item.id_tagihan)" class="btn-secondary">Detail</button>
                  <button
                    type="button"
                    x-show="item.status !== 'lunas' && item.status !== 'dibatalkan'"
                    @click="openPayment(item)"
                    class="btn-primary">Bayar</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- DETAIL MODAL -->
  <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="closeDetail()"></div>
    <div class="relative w-full max-w-3xl max-h-dvh overflow-y-auto bg-white rounded-2xl shadow-xl p-5 sm:p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-slate-900" x-text="detail?.nomor_tagihan || 'Detail Tagihan'"></h3>
          <p class="text-sm text-slate-500" x-text="detail ? detail.nama_kos + ' • Kamar ' + detail.nomor_kamar : ''"></p>
          <div
            x-show="detail"
            class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-600">
              Periode tagihan
            </p>
            <p
              class="mt-1 text-base font-semibold text-blue-950"
              x-text="detail ? formatDate(detail.tanggal_mulai) + ' - ' + formatDate(detail.tanggal_selesai) : ''">
            </p>
            <p
              class="mt-1 text-xs text-blue-700"
              x-text="detail ? 'Jatuh tempo ' + formatDate(detail.tanggal_jatuh_tempo) : ''">
            </p>
          </div>
        </div>
        <button type="button" @click="closeDetail()" class="text-slate-400 hover:text-slate-700 text-xl">×</button>
      </div>

      <template x-if="detail">
        <div class="mt-6 space-y-6">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-500">Harga dasar</p>
              <p class="font-bold mt-1" x-text="format(detail.harga_dasar)"></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-500">Penyesuaian</p>
              <p class="font-bold mt-1" x-text="format(detail.total_penyesuaian)"></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-500">Total</p>
              <p class="font-bold mt-1" x-text="format(detail.total_tagihan)"></p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-500">Sisa</p>
              <p class="font-bold mt-1" x-text="format(detail.sisa_tagihan)"></p>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-semibold text-slate-900">Penghuni pada Periode Ini</h4>
              <span class="text-xs text-slate-500" x-text="detail.penghuni.length + ' penghuni'"></span>
            </div>
            <div class="divide-y border border-slate-200 rounded-xl">
              <template x-if="detail.penghuni.length === 0">
                <p class="p-4 text-sm text-slate-500">Belum ada penghuni yang terhubung ke periode ini.</p>
              </template>
              <template x-for="item in detail.penghuni" :key="item.id_penghuni">
                <div class="p-4 flex items-center justify-between gap-4">
                  <div>
                    <p class="font-medium text-slate-900" x-text="item.nama"></p>
                    <p class="text-xs text-slate-500 mt-1" x-text="item.tanggal_masuk + (item.tanggal_keluar ? ' - ' + item.tanggal_keluar : ' - masih tinggal')"></p>
                  </div>
                  <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : 'Keluar'"></span>
                </div>
              </template>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-semibold text-slate-900">Penyesuaian Tagihan <span class="text-xs text-slate-500">(Tambahkan biaya atau potongan pada tagihan)</span></h4>
              <button
                data-help="help-tagihan-adjustment"
                type="button"
                x-show="detail.status !== 'lunas' && detail.status !== 'dibatalkan'"
                @click="adjustmentOpen = true"
                class="btn-secondary">+ Tambah Penyesuaian</button>
              <span
                x-show="detail.status === 'lunas'"
                class="text-xs text-emerald-600">
                Tagihan lunas — penyesuaian tidak dapat ditambahkan.
              </span>
            </div>
            <div class="divide-y border border-slate-200 rounded-xl">
              <template x-if="detail.penyesuaian.length === 0">
                <p class="p-4 text-sm text-slate-500">Belum ada penyesuaian.</p>
              </template>
              <template x-for="item in detail.penyesuaian" :key="item.id_penyesuaian">
                <div class="p-4 flex items-center justify-between gap-4">
                  <div>
                    <p class="font-medium" x-text="item.alasan"></p>
                    <p class="text-xs text-slate-500 mt-1" x-text="formatDate(item.tanggal_efektif) + (item.nama_penghuni ? ' • ' + item.nama_penghuni : '')"></p>
                  </div>
                  <span class="font-semibold" :class="item.jenis === 'tambah' ? 'text-red-600' : 'text-emerald-600'" x-text="(item.jenis === 'tambah' ? '+ ' : '- ') + format(item.jumlah)"></span>
                </div>
              </template>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-slate-900 mb-3">Pembayaran</h4>
            <div class="divide-y border border-slate-200 rounded-xl">
              <template x-if="detail.pembayaran.length === 0">
                <p class="p-4 text-sm text-slate-500">Belum ada pembayaran.</p>
              </template>
              <template x-for="item in detail.pembayaran" :key="item.id_pembayaran">
                <div class="p-4 flex items-center justify-between gap-4">
                  <div>
                    <p class="font-medium" x-text="item.nomor_pembayaran"></p>
                    <p class="text-xs text-slate-500 mt-1" x-text="(item.nama_penghuni || 'Penghuni') + ' • ' + formatDateTime(item.tanggal_bayar) + ' • ' + item.metode"></p>
                  </div>
                  <span class="font-semibold text-emerald-600" x-text="format(item.jumlah)"></span>
                </div>
              </template>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" @click="closeDetail()" class="btn-secondary">Tutup</button>
            <button data-help="help-tagihan-payment" type="button" x-show="detail.status !== 'lunas' && detail.status !== 'dibatalkan'" @click="openPayment(detail)" class="btn-primary">Catat Pembayaran</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <!-- ADJUSTMENT MODAL -->
  <div x-show="adjustmentOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="adjustmentOpen = false"></div>
    <form @submit.prevent="submitAdjustment" class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-5 sm:p-6 space-y-5">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Tambah Penyesuaian</h3>
        <p class="text-sm text-slate-500 mt-1">Tambahkan biaya atau potongan pada tagihan ini.</p>
      </div>
      <div class="form-group">
        <label class="label">Jenis</label>
        <select x-model="adjustment.jenis" class="input" required>
          <option value="tambah">Tambahan</option>
          <option value="kurang">Pengurangan</option>
        </select>
      </div>
      <div class="form-group">
        <label class="label">Jumlah</label>
        <input type="number" min="1" step="1" x-model.number="adjustment.jumlah" class="input" required>
      </div>
      <div class="form-group">
        <label class="label">Tanggal efektif</label>
        <input type="date" x-model="adjustment.tanggal_efektif" class="input" :min="detail?.tanggal_mulai" :max="detail?.tanggal_selesai" required>
      </div>
      <div class="form-group">
        <label class="label">Alasan</label>
        <input type="text" x-model="adjustment.alasan" class="input" maxlength="255" placeholder="Contoh: Denda keterlambatan" required>
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" @click="adjustmentOpen = false" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" :disabled="saving">Simpan</button>
      </div>
    </form>
  </div>

  <!-- PAYMENT MODAL -->
  <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="paymentOpen = false"></div>
    <form @submit.prevent="submitPayment" class="relative w-full max-w-md bg-white rounded-2xl shadow-xl p-5 sm:p-6 space-y-5">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Catat Pembayaran</h3>
        <p class="text-sm text-slate-500 mt-1" x-text="paymentTarget?.nomor_tagihan || ''"></p>
        <p
          class="text-xs text-slate-500 mt-1"
          x-text="paymentTarget ? 'Periode ' + formatDate(paymentTarget.tanggal_mulai) + ' - ' + formatDate(paymentTarget.tanggal_selesai) : ''">
        </p>
      </div>
      <div class="rounded-xl bg-slate-50 p-4">
        <div class="flex justify-between text-sm"><span>Total tagihan</span><strong x-text="format(paymentTarget?.total_tagihan || 0)"></strong></div>
        <div class="flex justify-between text-sm mt-2"><span>Sisa</span><strong x-text="format(paymentTarget?.sisa_tagihan || 0)"></strong></div>
      </div>
      <div class="form-group">
        <label class="label">Jumlah pembayaran</label>
        <input type="number" min="1" step="1" :max="paymentTarget?.sisa_tagihan" x-model.number="payment.jumlah" class="input" required>
      </div>
      <div class="form-group">
        <label class="label">Penghuni <span class="text-red-500">*</span></label>
        <select x-model="payment.id_penghuni" class="input" required>
          <option value="">Pilih penghuni</option>
          <template x-for="item in (detail?.penghuni || [])" :key="item.id_penghuni">
            <option :value="item.id_penghuni" x-text="item.nama"></option>
          </template>
        </select>
      </div>
      <div class="form-group">
        <label class="label">Metode</label>
        <select x-model="payment.metode" class="input" required>
          <option value="tunai">Tunai</option>
          <option value="transfer">Transfer</option>
          <option value="qris">QRIS</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>
      <div class="form-group">
        <label class="label">Tanggal pembayaran</label>
        <input type="datetime-local" x-model="payment.tanggal_bayar" class="input" required>
      </div>
      <div class="form-group">
        <label class="label">Catatan</label>
        <textarea x-model="payment.catatan" rows="3" class="input resize-none" placeholder="Catatan pembayaran (opsional)"></textarea>
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" @click="paymentOpen = false" class="btn-secondary">Batal</button>
        <button type="submit" class="btn-primary" :disabled="saving">Simpan Pembayaran</button>
      </div>
    </form>
  </div>
</div>

<script>
  function pembayaranPage() {
    return {
      tagihan: [],
      search: '',
      status: '',
      idKos: '',
      idKamar: '',
      kosList: [],
      kamarList: [],
      filteredKamarList: [],
      loading: false,
      saving: false,
      detailOpen: false,
      adjustmentOpen: false,
      paymentOpen: false,
      detail: null,
      paymentTarget: null,
      summary: {
        belum_lunas: 0,
        sebagian: 0,
        lunas: 0,
        sisa: 0
      },

      adjustment: {
        jenis: 'tambah',
        jumlah: 0,
        tanggal_efektif: '',
        alasan: ''
      },

      payment: {
        jumlah: 0,
        id_penghuni: '',
        metode: 'tunai',
        tanggal_bayar: '',
        catatan: ''
      },

      async init() {
        this.restoreFilters();
        await this.loadFilters();
        this.filterKamar();
        await this.load();
      },

      restoreFilters() {
        const params = new URLSearchParams(window.location.search);
        this.search = params.get('search') || '';
        this.status = params.get('status') || '';
        this.idKos = params.get('id_kos') || '';
        this.idKamar = params.get('id_kamar') || '';
      },

      async loadFilters() {
        try {
          const [kosRes, kamarRes] = await Promise.all([
            API.get('/pemilik/kos', false),
            API.get('/pemilik/kamar', false)
          ]);

          this.kosList = kosRes.data || [];
          this.kamarList = kamarRes.data || [];
        } catch (error) {
          console.error('Gagal memuat filter kos/kamar:', error);
          this.kosList = [];
          this.kamarList = [];
        }
      },

      filterKamar() {
        if (!this.idKos) {
          this.filteredKamarList = this.kamarList;
        } else {
          this.filteredKamarList = this.kamarList.filter(
            item => String(item.id_kos) === String(this.idKos)
          );
        }

        const exists = this.filteredKamarList.some(
          item => String(item.id_kamar) === String(this.idKamar)
        );

        if (this.idKamar && !exists) {
          this.idKamar = '';
        }
      },

      applyFilter() {
        this.filterKamar();

        const params = new URLSearchParams();
        if (this.search.trim()) params.set('search', this.search.trim());
        if (this.status) params.set('status', this.status);
        if (this.idKos) params.set('id_kos', this.idKos);
        if (this.idKamar) params.set('id_kamar', this.idKamar);

        const query = params.toString();
        const url = window.location.pathname + (query ? '?' + query : '');
        window.history.replaceState({}, '', url);

        this.load();
      },

      async load() {
        this.loading = true;
        try {
          const params = new URLSearchParams();
          if (this.search.trim()) params.set('search', this.search.trim());
          if (this.status) params.set('status', this.status);
          if (this.idKos) params.set('id_kos', this.idKos);
          if (this.idKamar) params.set('id_kamar', this.idKamar);
          const query = params.toString();
          const res = await API.get('/pemilik/tagihan' + (query ? '?' + query : ''), false);
          this.tagihan = res.data || [];
          this.buildSummary();
        } catch (error) {
          console.error(error);
          this.tagihan = [];
        } finally {
          this.loading = false;
        }
      },

      buildSummary() {
        const all = this.tagihan;
        this.summary.belum_lunas = all.filter(x => x.status === 'belum_lunas').length;
        this.summary.sebagian = all.filter(x => x.status === 'sebagian').length;
        this.summary.lunas = all.filter(x => x.status === 'lunas').length;
        this.summary.sisa = all.reduce((sum, x) => sum + Number(x.sisa_tagihan || 0), 0);
      },

      async openDetail(id) {
        try {
          const res = await API.get('/pemilik/tagihan/show?id_tagihan=' + id, false);
          this.detail = res.data;
          this.detailOpen = true;
          window.dispatchEvent(new CustomEvent('betakos:tagihan-detail-opened', { detail: { id_tagihan: id } }));
        } catch (error) {
          console.error(error);
        }
      },

      closeDetail() {
        this.detailOpen = false;
        this.detail = null;
      },

      async submitAdjustment() {
        this.saving = true;
        try {
          const res = await API.post('/pemilik/tagihan/penyesuaian', {
            id_tagihan: this.detail.id_tagihan,
            jenis: this.adjustment.jenis,
            jumlah: this.adjustment.jumlah,
            tanggal_efektif: this.adjustment.tanggal_efektif,
            alasan: this.adjustment.alasan
          });
          this.detail = res.data;
          this.adjustmentOpen = false;
          this.adjustment = {
            jenis: 'tambah',
            jumlah: 0,
            tanggal_efektif: '',
            alasan: ''
          };
          await this.load();
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },

      openPayment(item) {
        this.paymentTarget = item;
        if (!this.detail || Number(this.detail.id_tagihan) !== Number(item.id_tagihan)) {
          this.openDetail(item.id_tagihan).then(() => this.setPaymentDefaults());
        } else {
          this.setPaymentDefaults();
        }
        this.paymentOpen = true;
      },

      setPaymentDefaults() {
        const target = this.detail || this.paymentTarget;
        this.paymentTarget = target;
        this.payment.jumlah = Number(target?.sisa_tagihan || 0);
        this.payment.id_penghuni = '';
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        this.payment.tanggal_bayar = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
      },

      async submitPayment() {
        if (!this.payment.id_penghuni) {
          Alpine.store('ui').toast('Penghuni wajib dipilih agar pembayaran tercatat sebagai histori.', 'error');
          return;
        }

        this.saving = true;
        try {
          const date = this.payment.tanggal_bayar.replace('T', ' ') + ':00';
          const res = await API.post('/pemilik/tagihan/pembayaran', {
            id_tagihan: this.paymentTarget.id_tagihan,
            id_penghuni: this.payment.id_penghuni || null,
            jumlah: this.payment.jumlah,
            metode: this.payment.metode,
            tanggal_bayar: date,
            catatan: this.payment.catatan
          });
          this.detail = res.data.tagihan;
          this.paymentTarget = res.data.tagihan;
          this.paymentOpen = false;
          await this.load();
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },

      format(value) {
        return new Intl.NumberFormat('id-ID', {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0
        }).format(Number(value || 0));
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
        return new Date(value.replace(' ', 'T')).toLocaleString('id-ID', {
          dateStyle: 'medium',
          timeStyle: 'short'
        });
      },

      statusLabel(status) {
        return ({
          belum_lunas: 'Belum Lunas',
          sebagian: 'Sebagian',
          lunas: 'Lunas',
          dibatalkan: 'Dibatalkan'
        })[status] || status;
      },

      statusClass(status) {
        return ({
          belum_lunas: 'bg-amber-50 text-amber-700',
          sebagian: 'bg-blue-50 text-blue-700',
          lunas: 'bg-emerald-50 text-emerald-700',
          dibatalkan: 'bg-slate-100 text-slate-600'
        })[status] || 'bg-slate-100 text-slate-600';
      }
    };
  }
</script>