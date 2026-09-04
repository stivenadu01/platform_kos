<div x-data="claimPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Klaim Riwayat Kos</h1>
      <p class="mt-1 text-sm text-slate-500">Periksa permintaan penghuni yang mengaku pernah tinggal di kos Anda.</p>
    </div>
    <button type="button" @click="load()" class="btn-secondary">↻ Refresh</button>
  </div>

  <div class="flex flex-wrap gap-2">
    <template x-for="tab in tabs" :key="tab.value">
      <button type="button" @click="changeStatus(tab.value)" class="rounded-lg px-4 py-2 text-sm font-medium" :class="status === tab.value ? 'bg-primary text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50'" x-text="tab.label"></button>
    </template>
  </div>

  <div class="card overflow-hidden border border-slate-200 shadow-sm">
    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat claim...</div>
    <div x-show="!loading && !items.length" class="p-10 text-center">
      <div class="text-4xl">✓</div>
      <h2 class="mt-3 font-semibold text-slate-900">Belum ada claim</h2>
      <p class="mt-1 text-sm text-slate-500">Permintaan penghuni akan muncul di halaman ini.</p>
    </div>

    <div x-show="!loading && items.length" class="!hidden md:!block overflow-x-auto">
      <table class="w-full min-w-[950px] text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-5 py-3">Penghuni</th>
            <th class="px-5 py-3">Riwayat</th>
            <th class="px-5 py-3">NIK</th>
            <th class="px-5 py-3">Status</th>
            <th class="px-5 py-3">Diajukan</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in items" :key="item.id_claim">
            <tr class="hover:bg-slate-50/70">
              <td class="px-5 py-4">
                <div class="font-semibold text-slate-900" x-text="item.nama_mahasiswa"></div>
                <div class="mt-1 text-xs text-slate-500" x-text="item.email_mahasiswa"></div>
                <div class="text-xs text-slate-500" x-text="item.no_hp_mahasiswa || '-' "></div>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-800" x-text="item.nama_kos"></div>
                <div class="mt-1 text-xs text-slate-500" x-text="`Kamar ${item.nomor_kamar} · ${item.tanggal_masuk} - ${item.tanggal_keluar || 'masih tinggal'}`"></div>
              </td>
              <td class="px-5 py-4 font-mono text-xs text-slate-600" x-text="item.nik_penghuni || item.nik_diajukan"></td>
              <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span></td>
              <td class="px-5 py-4 text-slate-500" x-text="formatDate(item.tanggal_pengajuan)"></td>
              <td class="px-5 py-4 text-right"><button type="button" @click="openDetail(item)" class="btn-secondary text-xs">Periksa</button></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && items.length" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in items" :key="'m-' + item.id_claim">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><div class="font-semibold text-slate-900 truncate" x-text="item.nama_mahasiswa"></div><div class="mt-1 text-xs text-slate-500 truncate" x-text="item.nama_kos + ' · Kamar ' + item.nomor_kamar"></div></div>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div><div class="text-slate-400">NIK</div><div class="mt-1 font-mono text-slate-700 break-all" x-text="item.nik_penghuni || item.nik_diajukan || '-' "></div></div>
            <div><div class="text-slate-400">Diajukan</div><div class="mt-1 font-medium text-slate-700" x-text="formatDate(item.tanggal_pengajuan)"></div></div>
            <div class="col-span-2"><div class="text-slate-400">Riwayat tinggal</div><div class="mt-1 text-slate-700" x-text="`Kamar ${item.nomor_kamar} · ${item.tanggal_masuk} - ${item.tanggal_keluar || 'masih tinggal'}`"></div></div>
          </div>
          <button type="button" @click="openDetail(item)" class="mt-3 btn-secondary text-xs w-full sm:w-auto">Periksa Klaim</button>
        </article>
      </template>
    </div>
  </div>

  <div x-show="detailOpen" x-cloak class="fixed inset-0 z-[80] flex items-end justify-center p-0 sm:items-center sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeDetail()"></div>
    <div class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:max-w-2xl sm:rounded-2xl">
      <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
        <div>
          <h2 class="font-bold text-slate-900">Periksa Klaim</h2>
          <p class="mt-1 text-xs text-slate-500" x-text="detail?.nama_kos || ''"></p>
        </div>
        <button type="button" @click="closeDetail()" class="h-9 w-9 rounded-lg hover:bg-slate-100">✕</button>
      </div>

      <div class="space-y-5 p-5 sm:p-6" x-show="detail">
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-slate-50 p-4">
            <div class="text-xs text-slate-400">Akun penghuni</div>
            <div class="mt-1 font-semibold text-slate-900" x-text="detail?.nama_mahasiswa"></div>
            <div class="mt-1 text-sm text-slate-600" x-text="detail?.email_mahasiswa"></div>
            <div class="text-sm text-slate-600" x-text="detail?.no_hp_mahasiswa || '-' "></div>
          </div>
          <div class="rounded-xl bg-slate-50 p-4">
            <div class="text-xs text-slate-400">Data penghuni</div>
            <div class="mt-1 font-semibold text-slate-900" x-text="detail?.nama_penghuni"></div>
            <div class="mt-1 font-mono text-sm text-slate-600" x-text="detail?.nik_penghuni || '-' "></div>
            <div class="text-sm text-slate-600" x-text="`Kamar ${detail?.nomor_kamar || '-'} · ${detail?.tanggal_masuk || '-'} - ${detail?.tanggal_keluar || 'masih tinggal'}`"></div>
          </div>
        </div>

        <div x-show="detail?.catatan_mahasiswa">
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan penghuni</div>
          <div class="mt-2 whitespace-pre-line rounded-xl border border-slate-200 p-4 text-sm leading-6 text-slate-700" x-text="detail?.catatan_mahasiswa"></div>
        </div>

        <div x-show="detail?.status === 'menunggu'" class="border-t border-slate-200 pt-5">
          <form @submit.prevent="decision" class="space-y-4">
            <div>
              <label class="label">Keputusan</label>
              <select x-model="form.keputusan" class="input mt-1 w-full">
                <option value="disetujui">Setujui claim</option>
                <option value="ditolak">Tolak claim</option>
              </select>
            </div>
            <div>
              <label class="label">Catatan <span class="font-normal text-slate-400">(wajib jika ditolak)</span></label>
              <textarea x-model="form.catatan_pemilik" rows="4" class="input mt-1 w-full" placeholder="Tambahkan catatan verifikasi..."></textarea>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row-reverse">
              <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Keputusan'"></button>
              <button type="button" @click="closeDetail()" class="btn-secondary">Batal</button>
            </div>
          </form>
        </div>

        <div x-show="detail?.status !== 'menunggu'" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
          Klaim ini sudah diproses.
          <span x-show="detail?.catatan_pemilik" x-text="detail?.catatan_pemilik"></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function claimPage() {
    return {
      items: [],
      status: 'menunggu',
      loading: false,
      detailOpen: false,
      detail: null,
      saving: false,
      form: {
        keputusan: 'disetujui',
        catatan_pemilik: ''
      },
      tabs: [{
          value: 'menunggu',
          label: 'Menunggu'
        },
        {
          value: 'disetujui',
          label: 'Disetujui'
        },
        {
          value: 'ditolak',
          label: 'Ditolak'
        },
        {
          value: '',
          label: 'Semua'
        }
      ],
      async init() {
        await this.load();
      },
      async load() {
        this.loading = true;
        try {
          const query = this.status ? '?status=' + encodeURIComponent(this.status) : '';
          const response = await API.get('/pemilik/claim' + query, false);
          this.items = response.data || [];
        } finally {
          this.loading = false;
        }
      },
      async changeStatus(status) {
        this.status = status;
        await this.load();
      },
      openDetail(item) {
        this.detail = item;
        this.form = {
          keputusan: 'disetujui',
          catatan_pemilik: ''
        };
        this.detailOpen = true;
      },
      closeDetail() {
        if (!this.saving) this.detailOpen = false;
      },
      async decision() {
        if (!this.detail) return;
        if (this.form.keputusan === 'ditolak' && !this.form.catatan_pemilik.trim()) {
          Alpine.store('ui').toast('Catatan wajib diisi ketika claim ditolak.', 'error');
          return;
        }
        this.saving = true;
        try {
          await API.post('/pemilik/claim/keputusan', {
            id_claim: this.detail.id_claim,
            keputusan: this.form.keputusan,
            catatan_pemilik: this.form.catatan_pemilik
          });
          this.detailOpen = false;
          await this.load();
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },
      statusLabel(status) {
        return ({
          menunggu: 'Menunggu',
          disetujui: 'Disetujui',
          ditolak: 'Ditolak'
        })[status] || status;
      },
      statusClass(status) {
        return status === 'menunggu' ?
          'bg-amber-100 text-amber-700' :
          status === 'disetujui' ?
          'bg-emerald-100 text-emerald-700' :
          'bg-red-100 text-red-700';
      },
      formatDate(value) {
        if (!value) return '-';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? '-' : date.toLocaleDateString('id-ID', {
          dateStyle: 'medium'
        });
      }
    };
  }
</script>