<?php
$idTagihan = (int)($tagihan['id_tagihan'] ?? 0);
$backUrl = BASE_URL . '/pemilik/pembayaran';
?>
<div
  x-data="tagihanDetailPage(<?= $idTagihan ?>)"
  x-init="init()"
  class="space-y-6">

  <div>
    <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
    <h1 class="mt-4 text-xl font-bold text-slate-900 sm:text-2xl">Detail Tagihan</h1>
    <p class="mt-1 text-sm text-slate-500">Lihat status dan catat pembayaran kamar.</p>
  </div>

  <div x-show="loading" class="card border border-slate-200 shadow-sm py-16 text-center text-sm text-slate-500">
    Memuat detail tagihan...
  </div>

  <div x-show="errorMessage" x-cloak class="card border border-red-200 bg-red-50 text-red-700 p-4 text-sm" x-text="errorMessage"></div>

  <template x-if="detail">
    <div class="space-y-5">
      <section data-help="help-tagihan-detail-summary" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/80 p-4 sm:p-5">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-primary">Tagihan kamar</p>
              <h2 class="mt-1 text-xl font-bold text-slate-900" x-text="'Kamar ' + detail.nomor_kamar"></h2>
              <p class="mt-1 text-sm text-slate-500" x-text="detail.nama_kos + (detail.tipe_kamar ? ' · ' + detail.tipe_kamar : '')"></p>
              <p class="mt-1 text-xs text-slate-400" x-text="'Nomor tagihan: ' + detail.nomor_tagihan"></p>
            </div>
            <span class="inline-flex self-start rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClass(detail.status)" x-text="statusLabel(detail.status)"></span>
          </div>
        </div>

        <div class="p-4 sm:p-5">
          <div class="rounded-2xl p-4 sm:p-5" :class="isOverdue ? 'bg-rose-50' : detail.status === 'lunas' ? 'bg-emerald-50' : 'bg-primary-soft'">
            <p class="text-xs font-semibold uppercase tracking-wider" :class="isOverdue ? 'text-rose-600' : detail.status === 'lunas' ? 'text-emerald-600' : 'text-primary'" x-text="detail.status === 'lunas' ? 'Pembayaran selesai' : 'Belum dibayar'"></p>
            <p class="mt-1 text-3xl font-bold tracking-tight text-slate-950" x-text="format(detail.sisa_tagihan)"></p>
            <p class="mt-2 text-xs" :class="isOverdue ? 'text-rose-700' : 'text-slate-500'" x-text="statusMessage"></p>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3"><p class="text-xs text-slate-400">Total tagihan</p><p class="mt-1 text-sm font-bold text-slate-800" x-text="format(detail.total_tagihan)"></p></div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3"><p class="text-xs text-slate-400">Sudah dibayar</p><p class="mt-1 text-sm font-bold text-emerald-600" x-text="format(detail.total_dibayar)"></p></div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3"><p class="text-xs text-slate-400">Jatuh tempo</p><p class="mt-1 text-sm font-bold text-slate-800" x-text="formatDate(detail.tanggal_jatuh_tempo)"></p></div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3"><p class="text-xs text-slate-400">Periode</p><p class="mt-1 text-xs font-semibold leading-5 text-slate-700" x-text="formatDate(detail.tanggal_mulai) + ' – ' + formatDate(detail.tanggal_selesai)"></p></div>
          </div>

          <button data-help="help-tagihan-payment" x-show="canPay" type="button" @click="openPaymentModal()" class="btn-primary mt-4 w-full justify-center sm:w-auto">Catat Pembayaran</button>
          <div x-show="detail.status === 'lunas'" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-medium text-emerald-700">Tagihan ini sudah lunas. Tidak ada pembayaran yang perlu dicatat.</div>
          <div x-show="detail.status === 'dibatalkan'" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">Tagihan telah dibatalkan dan tidak dapat menerima pembayaran.</div>
        </div>
      </section>

      <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-3"><h3 class="font-bold text-slate-900">Riwayat Pembayaran</h3><p class="mt-1 text-xs text-slate-500">Daftar pembayaran yang sudah dicatat untuk tagihan ini.</p></div>
        <div class="overflow-hidden rounded-xl border border-slate-200">
          <template x-if="detail.pembayaran.length === 0"><div class="p-5 text-center text-sm text-slate-500">Belum ada pembayaran yang tercatat.</div></template>
          <div class="divide-y divide-slate-100">
            <template x-for="item in detail.pembayaran" :key="item.id_pembayaran">
              <article class="flex flex-col gap-2 p-3.5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0"><p class="font-semibold text-slate-900" x-text="item.nama_penghuni || 'Penghuni'"></p><p class="mt-1 text-xs text-slate-500" x-text="formatDateTime(item.tanggal_bayar) + ' · ' + paymentMethodLabel(item.metode)"></p><p class="mt-1 text-[11px] text-slate-400" x-text="item.nomor_pembayaran"></p><p x-show="item.catatan" class="mt-1 text-xs text-slate-600" x-text="item.catatan"></p></div>
                <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end"><span class="font-bold text-emerald-600" x-text="format(item.jumlah)"></span><span x-show="item.status" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600" x-text="paymentStatusLabel(item.status)"></span></div>
              </article>
            </template>
          </div>
        </div>
      </section>

      <section data-help="help-tagihan-detail-occupants" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button type="button" @click="showOccupants = !showOccupants" class="flex w-full items-center justify-between gap-3 p-4 text-left sm:p-5">
          <div><h3 class="font-bold text-slate-900">Penghuni Terkait</h3><p class="mt-1 text-xs text-slate-500" x-text="detail.penghuni.length + ' penghuni terhubung dengan tagihan ini'"></p></div>
          <span class="text-lg text-slate-400" x-text="showOccupants ? '−' : '+'"></span>
        </button>
        <div x-show="showOccupants" x-cloak class="border-t border-slate-100 p-4 sm:p-5">
          <div class="divide-y overflow-hidden rounded-xl border border-slate-200">
            <template x-if="detail.penghuni.length === 0"><p class="p-4 text-sm text-slate-500">Belum ada penghuni yang terhubung.</p></template>
            <template x-for="item in detail.penghuni" :key="item.id_penghuni"><div class="flex items-center justify-between gap-3 p-3.5"><div><p class="font-semibold text-slate-900" x-text="item.nama"></p><p class="mt-1 text-xs text-slate-500" x-text="'Masuk ' + formatDate(item.tanggal_masuk) + (item.tanggal_keluar ? ' · Keluar ' + formatDate(item.tanggal_keluar) : '')"></p></div><span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="item.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" x-text="item.status === 'aktif' ? 'Aktif' : 'Keluar'"></span></div></template>
          </div>
        </div>
      </section>

      <section data-help="help-tagihan-adjustment" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button type="button" @click="showAdjustments = !showAdjustments" class="flex w-full items-center justify-between gap-3 p-4 text-left sm:p-5">
          <div><h3 class="font-bold text-slate-900">Biaya Tambahan atau Potongan</h3><p class="mt-1 text-xs text-slate-500" x-text="detail.penyesuaian.length ? detail.penyesuaian.length + ' perubahan tercatat' : 'Tidak ada biaya tambahan atau potongan'"></p></div>
          <span class="text-lg text-slate-400" x-text="showAdjustments ? '−' : '+'"></span>
        </button>
        <div x-show="showAdjustments" x-cloak class="space-y-4 border-t border-slate-100 p-4 sm:p-5">
          <div class="flex justify-end"><button type="button" x-show="canPay" @click="openAdjustmentModal()" class="btn-secondary">+ Tambah atau Potong</button></div>
          <div class="divide-y overflow-hidden rounded-xl border border-slate-200">
            <template x-if="detail.penyesuaian.length === 0"><p class="p-4 text-sm text-slate-500">Belum ada biaya tambahan atau potongan.</p></template>
            <template x-for="item in detail.penyesuaian" :key="item.id_penyesuaian"><div class="flex items-center justify-between gap-3 p-3.5"><div><p class="font-semibold text-slate-900" x-text="item.alasan"></p><p class="mt-1 text-xs text-slate-500" x-text="formatDate(item.tanggal_efektif) + (item.nama_penghuni ? ' · ' + item.nama_penghuni : '')"></p></div><span class="shrink-0 font-bold" :class="item.jenis === 'tambah' ? 'text-rose-600' : 'text-emerald-600'" x-text="(item.jenis === 'tambah' ? '+ ' : '− ') + format(item.jumlah)"></span></div></template>
          </div>
        </div>
      </section>

      <div x-show="showPaymentForm" x-cloak x-transition.opacity @keydown.escape.window="closePaymentModal()" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="closePaymentModal()"></div>
        <div x-show="showPaymentForm" x-transition class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" style="max-height: 92vh;">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-5">
            <div><p class="text-xs font-semibold uppercase tracking-wider text-primary">Kamar <span x-text="detail.nomor_kamar"></span></p><h3 class="mt-1 text-lg font-bold text-slate-900">Catat Pembayaran</h3><p class="mt-1 text-xs text-slate-500">Masukkan uang yang benar-benar sudah diterima.</p></div>
            <button type="button" @click="closePaymentModal()" class="owner-icon-button" aria-label="Tutup modal"><?= masterIconSvg('x', 'h-4 w-4') ?></button>
          </div>
          <form @submit.prevent="submitPayment" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-4 sm:p-5">
              <div class="rounded-xl bg-primary-soft p-3 text-sm"><div class="flex justify-between gap-3"><span class="text-slate-600">Belum dibayar</span><strong class="text-primary" x-text="format(detail.sisa_tagihan)"></strong></div></div>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="form-group"><label class="label">Dibayar oleh</label><select x-model="payment.id_penghuni" class="select" required><option value="">Pilih penghuni</option><template x-for="item in detail.penghuni" :key="item.id_penghuni"><option :value="item.id_penghuni" x-text="item.nama"></option></template></select></div>
                <div class="form-group"><div class="flex items-center justify-between gap-2"><label class="label">Jumlah yang diterima</label><button type="button" @click="payment.jumlah = Number(detail.sisa_tagihan || 0)" class="text-xs font-semibold text-primary hover:underline">Isi seluruh sisa</button></div><input type="number" min="1" step="1" :max="detail.sisa_tagihan" x-model.number="payment.jumlah" class="input-number" required></div>
                <div class="form-group"><label class="label">Metode pembayaran</label><select x-model="payment.metode" class="select" required><option value="tunai">Tunai</option><option value="transfer">Transfer</option><option value="qris">QRIS</option><option value="lainnya">Lainnya</option></select></div>
                <div class="form-group"><label class="label">Tanggal diterima</label><input type="datetime-local" x-model="payment.tanggal_bayar" class="input-datetime" required></div>
              </div>
              <div class="form-group"><label class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label><textarea x-model="payment.catatan" rows="2" class="input resize-none" placeholder="Contoh: Transfer BCA"></textarea></div>
              <div class="rounded-xl bg-slate-50 p-3 text-sm"><div class="flex justify-between gap-3"><span class="text-slate-500">Sisa setelah pembayaran</span><strong class="text-slate-900" x-text="format(paymentRemaining)"></strong></div></div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white p-4 sm:flex-row sm:justify-end sm:px-5"><button type="button" @click="closePaymentModal()" class="btn-secondary justify-center">Batal</button><button type="submit" class="btn-primary justify-center" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Konfirmasi Pembayaran'"></button></div>
          </form>
        </div>
      </div>

      <div x-show="showAdjustmentForm" x-cloak x-transition.opacity @keydown.escape.window="closeAdjustmentModal()" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="closeAdjustmentModal()"></div>
        <div x-show="showAdjustmentForm" x-transition class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" style="max-height: 92vh;">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-5">
            <div><p class="text-xs font-semibold uppercase tracking-wider text-primary">Kamar <span x-text="detail.nomor_kamar"></span></p><h3 class="mt-1 text-lg font-bold text-slate-900">Tambah Biaya atau Potongan</h3><p class="mt-1 text-xs text-slate-500">Gunakan hanya jika nilai tagihan perlu disesuaikan.</p></div>
            <button type="button" @click="closeAdjustmentModal()" class="owner-icon-button" aria-label="Tutup modal"><?= masterIconSvg('x', 'h-4 w-4') ?></button>
          </div>
          <form @submit.prevent="submitAdjustment" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-4 sm:p-5">
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="form-group"><label class="label">Perubahan</label><select x-model="adjustment.jenis" class="select" required><option value="tambah">Tambah biaya</option><option value="kurang">Berikan potongan</option></select></div>
                <div class="form-group"><label class="label">Jumlah</label><input type="number" min="1" step="1" x-model.number="adjustment.jumlah" class="input-number" required></div>
                <div class="form-group"><label class="label">Tanggal berlaku</label><input type="date" x-model="adjustment.tanggal_efektif" class="input-date" :min="detail.tanggal_mulai" :max="detail.tanggal_selesai" required></div>
                <div class="form-group"><label class="label">Alasan</label><input type="text" x-model="adjustment.alasan" maxlength="255" class="input" placeholder="Contoh: Denda keterlambatan" required></div>
              </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white p-4 sm:flex-row sm:justify-end sm:px-5"><button type="button" @click="closeAdjustmentModal()" class="btn-secondary justify-center">Batal</button><button type="submit" class="btn-primary justify-center" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></button></div>
          </form>
        </div>
      </div>
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
      showOccupants: false,
      showAdjustments: false,
      adjustment: { jenis: 'tambah', jumlah: 0, tanggal_efektif: '', alasan: '' },
      payment: { jumlah: 0, id_penghuni: '', metode: 'tunai', tanggal_bayar: '', catatan: '' },

      get canPay() {
        return this.detail && this.detail.status !== 'lunas' && this.detail.status !== 'dibatalkan' && Number(this.detail.sisa_tagihan || 0) > 0;
      },

      get isOverdue() {
        if (!this.canPay || !this.detail?.tanggal_jatuh_tempo) return false;
        return new Date(this.detail.tanggal_jatuh_tempo + 'T23:59:59') < new Date();
      },

      get paymentRemaining() {
        return Math.max(Number(this.detail?.sisa_tagihan || 0) - Number(this.payment.jumlah || 0), 0);
      },

      get statusMessage() {
        if (!this.detail) return '';
        if (this.detail.status === 'lunas') return 'Seluruh pembayaran untuk tagihan ini sudah diterima.';
        if (this.detail.status === 'dibatalkan') return 'Tagihan ini telah dibatalkan.';
        if (this.isOverdue) return 'Jatuh tempo ' + this.formatDate(this.detail.tanggal_jatuh_tempo) + ' sudah lewat.';
        if (this.detail.status === 'sebagian') return 'Sebagian pembayaran sudah diterima. Catat pembayaran berikutnya saat uang diterima.';
        return 'Belum ada pembayaran yang melunasi tagihan ini.';
      },

      async init() {
        await this.load();
        const action = new URLSearchParams(window.location.search).get('action');
        if (action === 'payment' && this.detail?.status !== 'lunas' && this.detail?.status !== 'dibatalkan') {
          this.openPaymentModal();
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

      openPaymentModal() {
        this.setPaymentDefaults();
        this.showPaymentForm = true;
      },

      closePaymentModal() {
        if (!this.saving) this.showPaymentForm = false;
      },

      openAdjustmentModal() {
        const today = new Date().toISOString().slice(0, 10);
        const start = this.detail?.tanggal_mulai || today;
        const end = this.detail?.tanggal_selesai || today;
        const effectiveDate = today < start ? start : today > end ? end : today;
        this.adjustment = { jenis: 'tambah', jumlah: 0, tanggal_efektif: effectiveDate, alasan: '' };
        this.showAdjustmentForm = true;
      },

      closeAdjustmentModal() {
        if (!this.saving) this.showAdjustmentForm = false;
      },

      setPaymentDefaults() {
        this.payment.jumlah = 0;
        this.payment.id_penghuni = this.detail?.penghuni?.length === 1
          ? String(this.detail.penghuni[0].id_penghuni)
          : '';
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        this.payment.tanggal_bayar = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
      },

      async submitPayment() {
        if (!this.payment.id_penghuni) {
          Alpine.store('ui').toast('Penghuni wajib dipilih agar pembayaran tercatat sebagai histori.', 'error');
          return;
        }
        const amount = Number(this.payment.jumlah || 0);
        const remaining = Number(this.detail?.sisa_tagihan || 0);
        if (amount <= 0 || amount > remaining) {
          Alpine.store('ui').toast('Jumlah pembayaran harus lebih dari 0 dan tidak boleh melebihi sisa tagihan.', 'error');
          return;
        }
        const occupant = this.detail.penghuni.find(item => String(item.id_penghuni) === String(this.payment.id_penghuni));
        const confirmed = await Alpine.store('ui').confirm(
          `Catat pembayaran ${this.format(amount)} dari ${occupant?.nama || 'penghuni'}? Sisa setelah pembayaran ${this.format(this.paymentRemaining)}.`
        );
        if (!confirmed) return;
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
      },
      paymentMethodLabel(method) {
        return ({ tunai: 'Tunai', transfer: 'Transfer', qris: 'QRIS', lainnya: 'Lainnya' })[method] || method;
      },
      paymentStatusLabel(status) {
        return ({ berhasil: 'Berhasil', diterima: 'Diterima', pending: 'Menunggu', gagal: 'Gagal', dibatalkan: 'Dibatalkan' })[status] || status;
      }
    };
  }
</script>
