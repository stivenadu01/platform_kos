<div x-data="adminMetodePembayaranPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-primary">Konfigurasi Langganan</p>
      <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Metode Pembayaran</h1>
      <p class="mt-1 text-sm text-slate-500">Kelola rekening bank dan e-wallet yang digunakan untuk pembayaran BetaKos Pro.</p>
    </div>
    <button type="button" @click="openCreate()" class="btn-primary">+ Tambah Metode</button>
  </div>

  <div class="card border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-800">
    <strong>Catatan:</strong> perubahan konfigurasi hanya memengaruhi checkout pembayaran baru. Detail tujuan pembayaran pada transaksi yang sudah dikirim tetap disimpan sebagai snapshot untuk histori.
  </div>


  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="card border border-slate-200 p-4"><div class="text-xs text-slate-500">Total Metode</div><div class="mt-1 text-2xl font-bold" x-text="methods.length"></div></div>
    <div class="card border border-emerald-200 p-4"><div class="text-xs text-slate-500">Aktif</div><div class="mt-1 text-2xl font-bold text-emerald-700" x-text="activeCount"></div></div>
    <div class="card border border-blue-200 p-4"><div class="text-xs text-slate-500">Transfer Bank</div><div class="mt-1 text-2xl font-bold text-blue-700" x-text="bankCount"></div></div>
    <div class="card border border-violet-200 p-4"><div class="text-xs text-slate-500">E-Wallet</div><div class="mt-1 text-2xl font-bold text-violet-700" x-text="ewalletCount"></div></div>
  </div>

  <section class="card border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
      <div><h2 class="font-bold text-slate-900">Daftar Rekening & E-Wallet</h2><p class="text-xs text-slate-500 mt-1">Metode aktif tersedia saat pemilik melakukan pembayaran Pro. Riwayat transaksi tetap menggunakan data tujuan saat pembayaran dibuat.</p></div>
      <button type="button" @click="load()" class="btn-secondary text-sm">↻ Refresh</button>
    </div>
    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat konfigurasi...</div>
    <div x-show="!loading && methods.length === 0" class="p-10 text-center text-sm text-slate-500">Belum ada metode pembayaran. Tambahkan minimal satu metode.</div>
    <div x-show="!loading && methods.length" class="!hidden md:!block overflow-x-auto">
      <table class="min-w-[1120px] w-full text-sm">
        <thead class="bg-slate-50 text-slate-500">
          <tr><th class="text-left px-5 py-3">Jenis</th><th class="text-left px-5 py-3">Provider</th><th class="text-left px-5 py-3">Nomor Tujuan</th><th class="text-left px-5 py-3">Atas Nama</th><th class="text-left px-5 py-3">Status</th><th class="text-left px-5 py-3">Penggunaan</th><th class="text-left px-5 py-3">Terakhir Digunakan</th><th class="text-right px-5 py-3">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in methods" :key="item.id_metode_pembayaran">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4" x-text="item.jenis === 'transfer_bank' ? 'Transfer Bank' : 'E-Wallet'"></td>
              <td class="px-5 py-4 font-semibold text-slate-900" x-text="item.nama_provider"></td>
              <td class="px-5 py-4 font-mono" x-text="item.nomor_tujuan"></td>
              <td class="px-5 py-4" x-text="item.nama_penerima"></td>
              <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.is_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'" x-text="item.is_aktif ? 'Aktif' : 'Nonaktif'"></span></td>
              <td class="px-5 py-4 whitespace-nowrap"><div class="font-semibold" x-text="`${item.total_transaksi || 0} transaksi`"></div><div class="text-[11px] text-slate-400" x-text="`${item.transaksi_diverifikasi || 0} terverifikasi`"></div></td>
              <td class="px-5 py-4 whitespace-nowrap text-slate-500" x-text="formatDateTime(item.terakhir_digunakan)"></td>
              <td class="px-5 py-4 text-right whitespace-nowrap">
                <button type="button" @click="openEdit(item)" class="btn-secondary text-xs">Edit</button>
                <button type="button" @click="toggle(item)" class="btn-secondary text-xs ml-1" :class="item.is_aktif ? 'text-red-600' : 'text-emerald-700'" x-text="item.is_aktif ? 'Nonaktifkan' : 'Aktifkan'"></button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && methods.length" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in methods" :key="'mobile-' + item.id_metode_pembayaran">
        <article class="p-4 space-y-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="font-bold text-slate-900 truncate" x-text="item.nama_provider"></h3>
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600" x-text="item.jenis === 'transfer_bank' ? 'Transfer Bank' : 'E-Wallet'"></span>
              </div>
              <p class="mt-1 text-sm font-mono text-slate-700 break-all" x-text="item.nomor_tujuan"></p>
              <p class="mt-1 text-xs text-slate-500" x-text="'Atas nama ' + item.nama_penerima"></p>
            </div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.is_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'" x-text="item.is_aktif ? 'Aktif' : 'Nonaktif'"></span>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">Penggunaan</div><div class="mt-1 font-semibold text-slate-900" x-text="`${item.total_transaksi || 0} transaksi`"></div><div class="text-[11px] text-emerald-700" x-text="`${item.transaksi_diverifikasi || 0} terverifikasi`"></div><div class="text-[11px] text-amber-600" x-show="Number(item.transaksi_menunggu) > 0" x-text="`${item.transaksi_menunggu} menunggu`"></div><div class="text-[11px] text-red-600" x-show="Number(item.transaksi_ditolak) > 0" x-text="`${item.transaksi_ditolak} ditolak`"></div></div>
            <div class="rounded-xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">Terakhir digunakan</div><div class="mt-1 text-sm font-semibold text-slate-800" x-text="formatDateTime(item.terakhir_digunakan)"></div><div class="text-[11px] text-slate-500 mt-1" x-text="item.total_nominal_terverifikasi > 0 ? formatRupiah(item.total_nominal_terverifikasi) + ' terverifikasi' : 'Belum ada pembayaran terverifikasi'"></div></div>
          </div>
          <div x-show="item.keterangan" class="rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-800" x-text="item.keterangan"></div>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="openEdit(item)" class="btn-secondary text-xs w-full">Edit</button>
            <button type="button" @click="toggle(item)" class="btn-secondary text-xs w-full" :class="item.is_aktif ? 'text-red-600' : 'text-emerald-700'" x-text="item.is_aktif ? 'Nonaktifkan' : 'Aktifkan'"></button>
          </div>
        </article>
      </template>
    </div>
  </section>

  <div x-show="formOpen" x-cloak class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeForm()"></div>
    <div class="relative bg-white w-full sm:max-w-xl max-h-[92vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
        <div><h2 class="font-bold text-slate-900" x-text="form.id_metode_pembayaran ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'"></h2><p class="text-xs text-slate-500 mt-1">Data ini akan digunakan pada checkout Pro.</p></div>
        <button type="button" @click="closeForm()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <form @submit.prevent="save()" class="p-5 sm:p-6 space-y-4">
        <div>
          <label class="label">Jenis *</label>
          <select x-model="form.jenis" class="select mt-1" required><option value="transfer_bank">Transfer Bank</option><option value="e_wallet">E-Wallet</option></select>
        </div>
        <div><label class="label">Nama Bank / E-Wallet *</label><input x-model="form.nama_provider" class="input mt-1 w-full" required maxlength="80" placeholder="Contoh: BCA atau DANA"></div>
        <div><label class="label">Nomor Rekening / E-Wallet *</label><input x-model="form.nomor_tujuan" class="input mt-1 w-full" required maxlength="100" placeholder="Nomor tujuan pembayaran"></div>
        <div><label class="label">Atas Nama *</label><input x-model="form.nama_penerima" class="input mt-1 w-full" required maxlength="120" placeholder="Nama pemilik rekening/e-wallet"></div>
        <div><label class="label">Keterangan</label><textarea x-model="form.keterangan" rows="3" class="input mt-1 w-full" maxlength="255" placeholder="Opsional, misalnya: gunakan berita transfer SUB-XXXX"></textarea></div>
        <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-4 cursor-pointer"><input type="checkbox" x-model="form.is_aktif" class="h-4 w-4"><span><span class="block text-sm font-semibold text-slate-900">Aktifkan metode pembayaran</span><span class="block text-xs text-slate-500 mt-1">Metode aktif langsung muncul di checkout pemilik.</span></span></label>
        <div class="flex flex-col sm:flex-row-reverse gap-2 pt-2"><button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan'" ></button><button type="button" @click="closeForm()" class="btn-secondary">Batal</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function adminMetodePembayaranPage() {
  return {
    methods: [], loading: true, saving: false, formOpen: false,
    get activeCount() { return this.methods.filter(x => Number(x.is_aktif) === 1).length; },
    get bankCount() { return this.methods.filter(x => x.jenis === 'transfer_bank').length; },
    get ewalletCount() { return this.methods.filter(x => x.jenis === 'e_wallet').length; },
    form: { id_metode_pembayaran: 0, jenis: 'transfer_bank', nama_provider: '', nomor_tujuan: '', nama_penerima: '', keterangan: '', is_aktif: true },
    async init() { await this.load(); },
    async load() { this.loading = true; try { const res = await API.get('/admin/langganan/metode-pembayaran/ringkasan', false); this.methods = res.data || []; } catch (e) {} finally { this.loading = false; } },
    resetForm() { this.form = { id_metode_pembayaran: 0, jenis: 'transfer_bank', nama_provider: '', nomor_tujuan: '', nama_penerima: '', keterangan: '', is_aktif: true }; },
    openCreate() { this.resetForm(); this.formOpen = true; },
    openEdit(item) { this.form = { id_metode_pembayaran: Number(item.id_metode_pembayaran), jenis: item.jenis, nama_provider: item.nama_provider, nomor_tujuan: item.nomor_tujuan, nama_penerima: item.nama_penerima, keterangan: item.keterangan || '', is_aktif: Number(item.is_aktif) === 1 }; this.formOpen = true; },
    closeForm() { if (!this.saving) this.formOpen = false; },
    async save() { this.saving = true; try { await API.post('/admin/langganan/metode-pembayaran', this.form); this.formOpen = false; await this.load(); } catch (e) {} finally { this.saving = false; } },
    formatDateTime(v) { if (!v) return 'Belum ada'; const d = new Date(String(v).replace(' ', 'T')); return isNaN(d) ? 'Belum ada' : d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }); },
    formatRupiah(v) { return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR', maximumFractionDigits:0 }).format(Number(v || 0)); },
    async toggle(item) { try { await API.post('/admin/langganan/metode-pembayaran/status', { id_metode_pembayaran: item.id_metode_pembayaran, is_aktif: !Number(item.is_aktif) }); await this.load(); } catch (e) {} }
  };
}
</script>
