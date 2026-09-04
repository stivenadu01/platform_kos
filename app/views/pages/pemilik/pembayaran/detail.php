<?php $idTagihan = (int)($tagihan['id_tagihan'] ?? 0); ?>
<div
  x-data="tagihanDetailPage(<?= $idTagihan ?>)"
  x-init="init()"
  class="space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <a href="<?= BASE_URL ?>/pemilik/pembayaran" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-primary">← Kembali ke Tagihan & Pembayaran</a>
      <div class="mt-4">
        <p class="text-sm font-medium text-primary">Detail Tagihan</p>
        <h2 class="mt-1 text-2xl font-bold text-slate-900" x-text="detail?.nomor_tagihan || 'Memuat tagihan...'"> </h2>
        <p class="mt-1 text-sm text-slate-500" x-text="detail ? detail.nama_kos + ' • Kamar ' + detail.nomor_kamar : ''"></p>
      </div>
    </div>
    <span x-show="detail" x-cloak class="inline-flex self-start rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClass(detail?.status)" x-text="statusLabel(detail?.status)"></span>
  </div>

  <div x-show="loading" class="card border border-slate-200 shadow-sm py-16 text-center text-sm text-slate-500">
    Memuat detail tagihan...
  </div>

  <div x-show="errorMessage" x-cloak class="card border border-red-200 bg-red-50 text-red-700 p-4 text-sm" x-text="errorMessage"></div>

  <template x-if="detail">
    <div class="space-y-6">
      <section data-help="help-tagihan-detail-summary" class="card border border-slate-200 shadow-sm">
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-4">
          <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Periode tagihan</p>
          <p class="mt-1 text-base font-semibold text-blue-950" x-text="formatDate(detail.tanggal_mulai) + ' - ' + formatDate(detail.tanggal_selesai)"></p>
          <p class="mt-1 text-xs text-blue-700" x-text="'Jatuh tempo ' + formatDate(detail.tanggal_jatuh_tempo)"></p>
        </div>

        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Harga dasar</p><p class="font-bold mt-1" x-text="format(detail.harga_dasar)"></p></div>
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Penyesuaian</p><p class="font-bold mt-1" x-text="format(detail.total_penyesuaian)"></p></div>
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Total</p><p class="font-bold mt-1" x-text="format(detail.total_tagihan)"></p></div>
          <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Sisa</p><p class="font-bold mt-1" x-text="format(detail.sisa_tagihan)"></p></div>
        </div>
      </section>

      <section data-help="help-tagihan-detail-occupants" class="card border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between gap-3 mb-3">
          <div><h3 class="font-semibold text-slate-900">Penghuni pada Periode Ini</h3><p class="text-xs text-slate-500 mt-1">Penghuni yang terhubung dengan tagihan ini.</p></div>
          <span class="text-xs text-slate-500" x-text="detail.penghuni.length + ' penghuni'"></span>
        </div>
        <div class="divide-y border border-slate-200 rounded-xl">
          <template x-if="detail.penghuni.length === 0"><p class="p-4 text-sm text-slate-500">Belum ada penghuni yang terhubung ke periode ini.</p></template>
          <template x-for="item in detail.penghuni" :key="item.id_penghuni">
            <div class="p-4 flex items-center justify-between gap-4">
              <div><p class="font-medium text-slate-900" x-text="item.nama"></p><p class="text-xs text-slate-500 mt-1" x-text="item.tanggal_masuk + (item.tanggal_keluar ? ' - ' + item.tanggal_keluar : ' - masih tinggal')"></p></div>
              <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : 'Keluar'"></span>
            </div>
          </template>
        </div>
      </section>

      <section data-help="help-tagihan-adjustment" class="card border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
          <div><h3 class="font-semibold text-slate-900">Penyesuaian Tagihan</h3><p class="text-xs text-slate-500 mt-1">Tambahkan biaya atau potongan pada tagihan.</p></div>
          <button type="button" x-show="detail.status !== 'lunas' && detail.status !== 'dibatalkan'" @click="showAdjustmentForm = !showAdjustmentForm" class="btn-secondary" x-text="showAdjustmentForm ? 'Tutup Form' : '+ Tambah Penyesuaian'"></button>
          <span x-show="detail.status === 'lunas'" class="text-xs text-emerald-600">Tagihan lunas — penyesuaian tidak dapat ditambahkan.</span>
        </div>

        <div x-show="showAdjustmentForm" x-cloak class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
          <form @submit.prevent="submitAdjustment" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="form-group"><label class="label">Jenis</label><select x-model="adjustment.jenis" class="input" required><option value="tambah">Tambahan</option><option value="kurang">Pengurangan</option></select></div>
              <div class="form-group"><label class="label">Jumlah</label><input type="number" min="1" step="1" x-model.number="adjustment.jumlah" class="input" required></div>
              <div class="form-group"><label class="label">Tanggal efektif</label><input type="date" x-model="adjustment.tanggal_efektif" class="input" :min="detail.tanggal_mulai" :max="detail.tanggal_selesai" required></div>
              <div class="form-group"><label class="label">Alasan</label><input type="text" x-model="adjustment.alasan" maxlength="255" class="input" placeholder="Contoh: Denda keterlambatan" required></div>
            </div>
            <div class="flex justify-end gap-3"><button type="button" @click="showAdjustmentForm = false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary" :disabled="saving">Simpan Penyesuaian</button></div>
          </form>
        </div>

        <div class="divide-y border border-slate-200 rounded-xl">
          <template x-if="detail.penyesuaian.length === 0"><p class="p-4 text-sm text-slate-500">Belum ada penyesuaian.</p></template>
          <template x-for="item in detail.penyesuaian" :key="item.id_penyesuaian">
            <div class="p-4 flex items-center justify-between gap-4"><div><p class="font-medium" x-text="item.alasan"></p><p class="text-xs text-slate-500 mt-1" x-text="formatDate(item.tanggal_efektif) + (item.nama_penghuni ? ' • ' + item.nama_penghuni : '')"></p></div><span class="font-semibold" :class="item.jenis === 'tambah' ? 'text-red-600' : 'text-emerald-600'" x-text="(item.jenis === 'tambah' ? '+ ' : '- ') + format(item.jumlah)"></span></div>
          </template>
        </div>
      </section>

      <section data-help="help-tagihan-payment" class="card border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
          <div><h3 class="font-semibold text-slate-900">Pembayaran</h3><p class="text-xs text-slate-500 mt-1">Catat pembayaran yang benar-benar diterima dari penghuni.</p></div>
          <button type="button" x-show="detail.status !== 'lunas' && detail.status !== 'dibatalkan'" @click="showPaymentForm = !showPaymentForm; $nextTick(() => showPaymentForm && setPaymentDefaults())" class="btn-primary" x-text="showPaymentForm ? 'Tutup Form' : 'Catat Pembayaran'"></button>
        </div>

        <div x-show="showPaymentForm" x-cloak class="mb-4 rounded-xl border border-primary/20 bg-slate-50 p-4">
          <form @submit.prevent="submitPayment" class="space-y-4">
            <div class="rounded-xl bg-white border border-slate-200 p-4"><div class="flex justify-between text-sm"><span>Total tagihan</span><strong x-text="format(detail.total_tagihan)"></strong></div><div class="flex justify-between text-sm mt-2"><span>Sisa</span><strong x-text="format(detail.sisa_tagihan)"></strong></div></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="form-group"><label class="label">Penghuni</label><select x-model="payment.id_penghuni" class="input" required><option value="">Pilih penghuni</option><template x-for="item in detail.penghuni" :key="item.id_penghuni"><option :value="item.id_penghuni" x-text="item.nama"></option></template></select></div>
              <div class="form-group"><label class="label">Jumlah pembayaran</label><input type="number" min="1" step="1" :max="detail.sisa_tagihan" x-model.number="payment.jumlah" class="input" required></div>
              <div class="form-group"><label class="label">Metode</label><select x-model="payment.metode" class="input" required><option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="lainnya">Lainnya</option></select></div>
              <div class="form-group"><label class="label">Tanggal pembayaran</label><input type="datetime-local" x-model="payment.tanggal_bayar" class="input" required></div>
            </div>
            <div class="form-group"><label class="label">Catatan</label><textarea x-model="payment.catatan" rows="3" class="input resize-none" placeholder="Catatan pembayaran (opsional)"></textarea></div>
            <div class="flex justify-end gap-3"><button type="button" @click="showPaymentForm = false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary" :disabled="saving">Simpan Pembayaran</button></div>
          </form>
        </div>

        <div class="divide-y border border-slate-200 rounded-xl">
          <template x-if="detail.pembayaran.length === 0"><p class="p-4 text-sm text-slate-500">Belum ada pembayaran.</p></template>
          <template x-for="item in detail.pembayaran" :key="item.id_pembayaran">
            <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"><div><p class="font-medium" x-text="item.nomor_pembayaran"></p><p class="text-xs text-slate-500 mt-1" x-text="(item.nama_penghuni || 'Penghuni') + ' • ' + formatDateTime(item.tanggal_bayar) + ' • ' + item.metode"></p></div><span class="font-semibold text-emerald-600" x-text="format(item.jumlah)"></span></div>
          </template>
        </div>
      </section>
    </div>
  </template>
</div>

<script>
  function tagihanDetailPage(idTagihan) {
    return {
      idTagihan,
      detail: null,
      loading: true,
      saving: false,
      errorMessage: '',
      showAdjustmentForm: false,
      showPaymentForm: false,
      adjustment: { jenis: 'tambah', jumlah: 0, tanggal_efektif: '', alasan: '' },
      payment: { jumlah: 0, id_penghuni: '', metode: 'tunai', tanggal_bayar: '', catatan: '' },

      async init() {
        await this.load();
        const action = new URLSearchParams(window.location.search).get('action');
        if (action === 'payment' && this.detail?.status !== 'lunas' && this.detail?.status !== 'dibatalkan') {
          this.showPaymentForm = true;
          this.$nextTick(() => {
            this.setPaymentDefaults();
            requestAnimationFrame(() => {
              document.getElementById('help-tagihan-payment')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
          });
        }
      },

      async load() {
        this.loading = true;
        this.errorMessage = '';
        try {
          const res = await API.get('/pemilik/tagihan/show?id_tagihan=' + encodeURIComponent(this.idTagihan), false);
          this.detail = res.data;
        } catch (error) {
          console.error(error);
          this.errorMessage = error?.message || 'Detail tagihan tidak dapat dimuat.';
        } finally {
          this.loading = false;
        }
      },

      async submitAdjustment() {
        this.saving = true;
        try {
          const res = await API.post('/pemilik/tagihan/penyesuaian', {
            id_tagihan: this.idTagihan,
            jenis: this.adjustment.jenis,
            jumlah: this.adjustment.jumlah,
            tanggal_efektif: this.adjustment.tanggal_efektif,
            alasan: this.adjustment.alasan
          });
          this.detail = res.data;
          this.showAdjustmentForm = false;
          this.adjustment = { jenis: 'tambah', jumlah: 0, tanggal_efektif: '', alasan: '' };
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },

      setPaymentDefaults() {
        this.payment.jumlah = Number(this.detail?.sisa_tagihan || 0);
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
            id_tagihan: this.idTagihan,
            id_penghuni: this.payment.id_penghuni,
            jumlah: this.payment.jumlah,
            metode: this.payment.metode,
            tanggal_bayar: date,
            catatan: this.payment.catatan
          });
          this.detail = res.data.tagihan;
          this.showPaymentForm = false;
          this.payment = { jumlah: 0, id_penghuni: '', metode: 'tunai', tanggal_bayar: '', catatan: '' };
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },

      format(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
      },
      formatDate(value) {
        if (!value) return '-';
        return new Date(value + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
      },
      formatDateTime(value) {
        if (!value) return '-';
        return new Date(value.replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
      },
      statusLabel(status) {
        return ({ belum_lunas: 'Belum Lunas', sebagian: 'Sebagian', lunas: 'Lunas', dibatalkan: 'Dibatalkan' })[status] || status;
      },
      statusClass(status) {
        return ({ belum_lunas: 'bg-amber-50 text-amber-700', sebagian: 'bg-blue-50 text-blue-700', lunas: 'bg-emerald-50 text-emerald-700', dibatalkan: 'bg-slate-100 text-slate-600' })[status] || 'bg-slate-100 text-slate-600';
      }
    };
  }
</script>
