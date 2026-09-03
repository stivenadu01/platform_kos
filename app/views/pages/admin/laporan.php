<div x-data="adminLaporanPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Laporan Kos</h1>
      <p class="mt-1 text-sm text-slate-500">Periksa laporan dari mahasiswa dan tindak lanjuti informasi kos yang bermasalah.</p>
    </div>
    <button @click="load(1)" class="btn-secondary text-sm">↻ Refresh</button>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    <template x-for="card in summaryCards" :key="card.key">
      <div class="card border border-slate-200 shadow-sm p-4">
        <div class="text-xs text-slate-500" x-text="card.label"></div>
        <div class="mt-1 text-xl font-bold text-slate-900" x-text="summary[card.key] ?? 0"></div>
      </div>
    </template>
  </div>

  <div class="card border border-slate-200 shadow-sm p-4 sm:p-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <div>
        <label class="label">Cari laporan</label>
        <input x-model="filters.search" @input.debounce.400ms="applyFilters()" type="search" class="input mt-1 w-full" placeholder="Nama kos atau nama pelapor">
      </div>
      <div>
        <label class="label">Status</label>
        <select x-model="filters.status" @change="applyFilters()" class="input mt-1 w-full">
          <option value="">Semua status</option>
          <option value="menunggu">Menunggu</option>
          <option value="diproses">Diproses</option>
          <option value="selesai">Selesai</option>
          <option value="ditolak">Ditolak</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card border border-slate-200 shadow-sm overflow-hidden">
    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat laporan...</div>
    <div x-show="!loading && !result.items.length" class="p-10 text-center">
      <div class="text-4xl">⚑</div>
      <h3 class="mt-3 font-semibold text-slate-900">Belum ada laporan</h3>
      <p class="mt-1 text-sm text-slate-500">Laporan dari mahasiswa akan muncul di sini.</p>
    </div>
    <div x-show="!loading && result.items.length" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in result.items" :key="'m-' + item.id_laporan">
        <article class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-3"><div class="min-w-0"><div class="font-semibold text-slate-900 truncate" x-text="item.nama_kos"></div><div class="text-xs text-slate-400 mt-1" x-text="'ID Kos #' + item.id_kos"></div></div><span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span></div>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-400">Pelapor</div><div class="mt-1 font-semibold text-slate-800 truncate" x-text="item.nama_pelapor"></div><div class="text-slate-500 truncate" x-text="item.email_pelapor"></div></div>
            <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-400">Alasan</div><div class="mt-1 font-semibold text-slate-700" x-text="reasonLabel(item.alasan)"></div></div>
          </div>
          <div class="flex items-center justify-between gap-3"><span class="text-xs text-slate-500" x-text="formatDate(item.created_at)"></span><button @click="openDetail(item.id_laporan)" class="btn-secondary text-xs">Periksa</button></div>
        </article>
      </template>
    </div>

    <div x-show="!loading && result.items.length" class="!hidden md:!block overflow-x-auto overscroll-x-contain">
      <table class="w-full min-w-[1000px] text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-5 py-3">Kos</th>
            <th class="text-left px-5 py-3">Pelapor</th>
            <th class="text-left px-5 py-3">Alasan</th>
            <th class="text-left px-5 py-3">Status</th>
            <th class="text-left px-5 py-3">Tanggal</th>
            <th class="text-right px-5 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in result.items" :key="item.id_laporan">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-900" x-text="item.nama_kos"></div>
                <div class="text-xs text-slate-400 mt-1" x-text="'ID Kos #' + item.id_kos"></div>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-800" x-text="item.nama_pelapor"></div>
                <div class="text-xs text-slate-500" x-text="item.email_pelapor"></div>
              </td>
              <td class="px-5 py-4"><span class="text-xs font-medium text-slate-700" x-text="reasonLabel(item.alasan)"></span></td>
              <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span></td>
              <td class="px-5 py-4 text-slate-500" x-text="formatDate(item.created_at)"></td>
              <td class="px-5 py-4 text-right"><button @click="openDetail(item.id_laporan)" class="btn-secondary text-xs">Periksa</button></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div x-show="!loading && result.total_pages > 1" class="px-4 sm:px-5 py-4 border-t border-slate-200 flex items-center justify-between gap-3">
      <div class="text-xs text-slate-500" x-text="`Halaman ${result.page} dari ${result.total_pages}`"></div>
      <div class="flex items-center gap-1">
        <button @click="goPage(result.page - 1)" :disabled="result.page <= 1" class="w-9 h-9 rounded-lg border border-slate-200 disabled:opacity-40">←</button>
        <template x-for="page in pages()" :key="page"><button @click="goPage(page)" :class="page === result.page ? 'bg-primary text-white border-primary' : 'border-slate-200 text-slate-700'" class="w-9 h-9 rounded-lg border text-sm" x-text="page"></button></template>
        <button @click="goPage(result.page + 1)" :disabled="result.page >= result.total_pages" class="w-9 h-9 rounded-lg border border-slate-200 disabled:opacity-40">→</button>
      </div>
    </div>
  </div>

  <div x-show="detailOpen" x-cloak class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeDetail()"></div>
    <div class="relative bg-white w-full sm:max-w-2xl max-h-[92vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl shadow-2xl">
      <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
        <div><h2 class="font-bold text-slate-900">Periksa Laporan</h2><p class="text-xs text-slate-500 mt-1" x-text="detail?.nama_kos || ''"></p></div>
        <button @click="closeDetail()" class="w-9 h-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>
      <div class="p-5 sm:p-6 space-y-5" x-show="detail">
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-400">Pelapor</div><div class="mt-1 font-semibold text-slate-900" x-text="detail?.nama_pelapor"></div><div class="text-xs text-slate-500" x-text="detail?.email_pelapor"></div></div>
          <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-400">Alasan</div><div class="mt-1 font-semibold text-slate-900" x-text="reasonLabel(detail?.alasan)"></div></div>
        </div>
        <div><div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Isi laporan</div><div class="mt-2 rounded-xl border border-slate-200 p-4 text-sm leading-6 text-slate-700 whitespace-pre-line" x-text="detail?.deskripsi"></div></div>
        <div x-show="detail?.catatan_admin" class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-400">Catatan Admin sebelumnya</div><div class="mt-1 text-sm text-slate-700 whitespace-pre-line" x-text="detail?.catatan_admin"></div></div>
        <form @submit.prevent="decision" class="space-y-4 border-t border-slate-200 pt-5">
          <div><label class="label">Tindakan</label><select x-model="decisionForm.status" class="input mt-1 w-full"><option value="diproses">Tandai Diproses</option><option value="selesai">Selesaikan Laporan</option><option value="ditolak">Tolak Laporan</option></select></div>
          <div><label class="label">Catatan Admin <span class="font-normal text-slate-400">(wajib jika ditolak)</span></label><textarea x-model="decisionForm.catatan_admin" rows="4" class="input mt-1 w-full" placeholder="Jelaskan tindakan atau alasan penolakan..."></textarea></div>
          <div class="flex flex-col sm:flex-row-reverse gap-2"><button class="btn-primary" :disabled="saving" type="submit" x-text="saving ? 'Menyimpan...' : 'Simpan Tindakan'"></button><button type="button" @click="closeDetail()" class="btn-secondary">Batal</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function adminLaporanPage() {
  return {
    summary: { total: 0, menunggu: 0, diproses: 0, selesai: 0, ditolak: 0 },
    summaryCards: [
      { key: 'total', label: 'Total' }, { key: 'menunggu', label: 'Menunggu' },
      { key: 'diproses', label: 'Diproses' }, { key: 'selesai', label: 'Selesai' }, { key: 'ditolak', label: 'Ditolak' }
    ],
    result: { items: [], total: 0, page: 1, limit: 10, total_pages: 1 },
    filters: { status: '', search: '' }, loading: false, detailOpen: false, detail: null, saving: false,
    decisionForm: { status: 'diproses', catatan_admin: '' },
    async init() { await this.load(1); },
    queryString() { const p = new URLSearchParams({ page: this.result.page || 1, limit: 10 }); Object.entries(this.filters).forEach(([k,v]) => { if (v) p.set(k,v); }); return p.toString(); },
    async load(page=1) { this.loading=true; try { this.result.page=page; const r=await API.get('/admin/laporan?'+this.queryString(), false); this.result=r.data||this.result; this.summary=r.summary||this.summary; } finally { this.loading=false; } },
    async applyFilters(){ await this.load(1); },
    async goPage(page){ if(page<1||page>this.result.total_pages)return; await this.load(page); },
    pages(){ const total=Number(this.result.total_pages||1), cur=Number(this.result.page||1), start=Math.max(1,cur-2), end=Math.min(total,start+4), a=[]; for(let i=start;i<=end;i++)a.push(i); return a; },
    reasonLabel(v){ return ({informasi_tidak_sesuai:'Informasi tidak sesuai',foto_tidak_sesuai:'Foto tidak sesuai',kos_sudah_tidak_tersedia:'Kos sudah tidak tersedia',informasi_menyesatkan:'Informasi menyesatkan',lainnya:'Lainnya'})[v] || v || '-'; },
    statusLabel(v){ return ({menunggu:'Menunggu',diproses:'Diproses',selesai:'Selesai',ditolak:'Ditolak'})[v] || v || '-'; },
    statusClass(v){ return v==='menunggu'?'bg-amber-100 text-amber-700':v==='diproses'?'bg-blue-100 text-blue-700':v==='selesai'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700'; },
    formatDate(v){ if(!v)return '-'; const d=new Date(String(v).replace(' ','T')); return isNaN(d)?'-':d.toLocaleDateString('id-ID',{dateStyle:'medium'}); },
    async openDetail(id){ try { const r=await API.get('/admin/laporan/'+id, false); this.detail=r.data; this.decisionForm={status:['selesai','ditolak'].includes(this.detail.status)?'diproses':this.detail.status||'diproses',catatan_admin:''}; this.detailOpen=true; } catch(e){} },
    closeDetail(){ if(!this.saving)this.detailOpen=false; },
    async decision(){ if(!this.detail)return; if(this.decisionForm.status==='ditolak' && !this.decisionForm.catatan_admin.trim()){ Alpine.store('ui').toast('Catatan wajib diisi ketika laporan ditolak.','error'); return; } this.saving=true; try { await API.post('/admin/laporan/keputusan',{id_laporan:this.detail.id_laporan,...this.decisionForm}); this.detailOpen=false; await this.load(this.result.page); } catch(e){} finally{this.saving=false;} }
  }
}
</script>
