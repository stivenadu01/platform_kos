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
        <select x-model="idKos" @change="applyFilter()" class="select">
          <option value="">Semua kos</option>
          <template x-for="kos in kosList" :key="kos.id_kos">
            <option :value="kos.id_kos" x-text="kos.nama_kos"></option>
          </template>
        </select>
      </div>

      <div class="form-group">
        <label class="label">Kamar</label>
        <select x-model="idKamar" @change="applyFilter()" class="select">
          <option value="">Semua kamar</option>
          <template x-for="kamar in filteredKamarList" :key="kamar.id_kamar">
            <option :value="kamar.id_kamar" x-text="kamar.nomor_kamar"></option>
          </template>
        </select>
      </div>

      <div class="form-group">
        <label class="label">Status</label>
        <select x-model="status" @change="applyFilter()" class="select">
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

    <div x-show="!loading && tagihan.length > 0" x-cloak class="!hidden md:!block overflow-x-auto">
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
                  <a data-help="help-tagihan-detail" :href="detailUrl(item.id_tagihan)" class="btn-secondary">Detail</a>
                  <a
                    x-show="item.status !== 'lunas' && item.status !== 'dibatalkan'"
                    :href="detailUrl(item.id_tagihan, 'payment')"
                    class="btn-primary">Bayar</a>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && tagihan.length > 0" class="!block md:!hidden divide-y divide-slate-200">
      <template x-for="item in tagihan" :key="'m-' + item.id_tagihan">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0"><div class="font-semibold text-slate-900 truncate" x-text="item.nomor_tagihan"></div><div class="mt-1 text-xs text-slate-500" x-text="item.nama_kos + ' · Kamar ' + item.nomor_kamar"></div></div>
            <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div><div class="text-slate-400">Periode</div><div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_mulai) + ' - ' + formatDate(item.tanggal_selesai)"></div></div>
            <div><div class="text-slate-400">Jatuh tempo</div><div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_jatuh_tempo)"></div></div>
            <div><div class="text-slate-400">Total</div><div class="mt-1 font-semibold text-slate-800" x-text="format(item.total_tagihan)"></div></div>
            <div><div class="text-slate-400">Sisa</div><div class="mt-1 font-semibold text-slate-800" x-text="format(item.sisa_tagihan)"></div></div>
          </div>
          <div class="mt-3 flex gap-2">
            <a data-help="help-tagihan-detail" :href="detailUrl(item.id_tagihan)" class="btn-secondary flex-1 text-center">Detail</a>
            <a x-show="item.status !== 'lunas' && item.status !== 'dibatalkan'" :href="detailUrl(item.id_tagihan, 'payment')" class="btn-primary flex-1 text-center">Bayar</a>
          </div>
        </article>
      </template>
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
      summary: {
        belum_lunas: 0,
        sebagian: 0,
        lunas: 0,
        sisa: 0
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

      detailUrl(id, action = '') {
        const params = new URLSearchParams({ id_tagihan: id });
        if (action) params.set('action', action);
        return window.BASE_URL + '/pemilik/pembayaran/detail?' + params.toString() + (action === 'payment' ? '#help-tagihan-payment' : '');
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