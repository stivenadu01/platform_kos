<div
  x-data="pembayaranPage()"
  x-init="init()"
  class="owner-page">

  <div class="owner-page-header">
    <div>
      <a x-show="isScoped" x-cloak :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link mb-3"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow">Keuangan Kos</p>
      <h2 class="owner-title">
        Keuangan Aktif
      </h2>
      <p class="owner-subtitle">
        Tindak lanjuti tagihan yang belum lunas atau baru dibayar sebagian.
      </p>
    </div>
  </div>

  <div x-show="isScoped" x-cloak class="owner-context-panel">
    <div class="owner-context-header">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('wallet', 'h-5 w-5') ?></span>
      <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary" x-text="isOccupantContext ? 'Tagihan Penghuni' : 'Tagihan Tipe Kamar'"></p>
        <h3 class="owner-copy-full mt-1 font-bold text-slate-900" x-text="contextTitle"></h3>
        <p class="mt-1 text-xs text-slate-500" x-text="isOccupantContext ? 'Menampilkan tagihan aktif yang terhubung dengan penghuni ini.' : 'Menampilkan tagihan aktif unit kamar pada konteks yang dipilih.'"></p>
      </div>
    </div>
  </div>

  <div class="owner-segmented" role="tablist" aria-label="Kelompok status tagihan">
    <button type="button" role="tab" @click="setStatus('')" :class="status === '' ? 'is-active' : ''">Semua Aktif</button>
    <button type="button" role="tab" @click="setStatus('belum_lunas')" :class="status === 'belum_lunas' ? 'is-active' : ''"><span class="owner-segment-dot bg-rose-500"></span>Perlu ditagih</button>
    <button type="button" role="tab" @click="setStatus('sebagian')" :class="status === 'sebagian' ? 'is-active' : ''"><span class="owner-segment-dot bg-amber-500"></span>Sebagian</button>
  </div>

  <div x-data="{ filterOpen: window.innerWidth >= 768 }" @resize.window="if (window.innerWidth >= 768) filterOpen = true" data-help="help-tagihan-filter" class="card border border-slate-200 shadow-sm">
    <button type="button" @click="filterOpen = !filterOpen" class="owner-mobile-filter-summary md:hidden">
      <span class="inline-flex items-center gap-2"><?= masterIconSvg('filter', 'h-4 w-4') ?> Cari & Filter Tagihan</span>
      <span class="inline-flex items-center gap-2"><span x-show="search || idKos || idKamar || status" class="owner-mobile-filter-count" x-text="[search,idKos,idKamar,status].filter(Boolean).length"></span><span x-text="filterOpen ? '−' : '+'"></span></span>
    </button>
    <div x-show="filterOpen" x-cloak class="mt-3 grid grid-cols-1 gap-3 md:mt-0 md:grid md:grid-cols-2 lg:grid-cols-4 lg:gap-4">
      <div class="form-group">
        <label class="label">Cari kamar atau penghuni</label>
        <input
          type="search"
          x-model="search"
          @input.debounce.400ms="load()"
          class="input"
          placeholder="Kamar, penghuni, atau nomor tagihan...">
      </div>

      <div x-show="!isScoped" class="form-group">
        <label class="label">Kos</label>
        <select x-model="idKos" @change="applyFilter()" class="select">
          <option value="">Semua kos</option>
          <template x-for="kos in kosList" :key="kos.id_kos">
            <option :value="kos.id_kos" x-text="kos.nama_kos"></option>
          </template>
        </select>
      </div>

      <div x-show="!isOccupantContext" class="form-group">
        <label class="label">Kamar</label>
        <select x-model="idKamar" @change="applyFilter()" class="select">
          <option value="">Semua kamar</option>
          <template x-for="kamar in filteredKamarList" :key="kamar.id_kamar">
            <option :value="kamar.id_kamar" x-text="kamar.nomor_kamar"></option>
          </template>
        </select>
      </div>

    </div>
  </div>

  <div data-help="help-tagihan-list" class="owner-data-container card border border-slate-200 shadow-sm overflow-hidden">
    <div x-show="loading" class="py-12 text-center text-sm text-slate-500">
      Memuat tagihan...
    </div>

    <div x-show="!loading && tagihan.length === 0" x-cloak class="py-14 text-center">
      <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-soft text-primary"><?= masterIconSvg('wallet', 'h-7 w-7') ?></div>
      <h3 class="font-semibold text-slate-900">Belum ada tagihan</h3>
      <p class="mt-1 text-sm text-slate-500">Tagihan akan muncul otomatis setelah penghuni ditambahkan.</p>
    </div>

    <div x-show="!loading && tagihan.length > 0" x-cloak class="!hidden md:!block overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="text-left px-5 py-3 font-semibold">Kamar & Penghuni</th>
            <th class="text-left px-5 py-3 font-semibold">Periode</th>
            <th class="text-left px-5 py-3 font-semibold">Total</th>
            <th class="text-left px-5 py-3 font-semibold">Belum Dibayar</th>
            <th class="text-left px-5 py-3 font-semibold">Status</th>
            <th class="text-right px-5 py-3 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-for="item in tagihan" :key="item.id_tagihan">
            <tr class="hover:bg-slate-50">
              <td class="px-5 py-4">
                <div class="owner-copy-full font-bold text-slate-900" x-text="'Kamar ' + item.nomor_kamar"></div>
                <div class="owner-copy-full mt-1 font-medium text-slate-700" x-text="item.nama_penghuni || 'Belum ada penghuni'"></div>
                <div class="owner-copy-full mt-1 text-xs text-slate-500" x-text="isScoped ? item.tipe_kamar : item.nama_kos + ' · ' + item.tipe_kamar"></div>
                <div class="mt-1 text-[11px] text-slate-400" x-text="item.nomor_tagihan"></div>
              </td>
              <td class="px-5 py-4">
                <div x-text="formatDate(item.tanggal_mulai) + ' - ' + formatDate(item.tanggal_selesai)"></div>
                <div class="text-xs text-slate-500 mt-1" x-text="'Jatuh tempo ' + formatDate(item.tanggal_jatuh_tempo)"></div>
              </td>
              <td class="px-5 py-4 font-semibold" x-text="format(item.total_tagihan)"></td>
              <td class="px-5 py-4 font-bold" :class="Number(item.sisa_tagihan || 0) > 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="format(item.sisa_tagihan)"></td>
              <td class="px-5 py-4">
                <span
                  class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                  :class="statusClass(item.status)"
                  x-text="statusLabel(item.status)"></span>
              </td>
              <td class="px-5 py-4">
                <div class="flex justify-end gap-2">
                  <a data-help="help-tagihan-detail" :href="detailUrl(item.id_tagihan)" class="btn-secondary">Lihat Detail</a>
                  <a
                    x-show="item.status !== 'lunas' && item.status !== 'dibatalkan'"
                    :href="detailUrl(item.id_tagihan, 'payment')"
                    class="owner-payment-button">Catat Bayar</a>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div x-show="!loading && tagihan.length > 0" class="owner-mobile-card-stack !block md:!hidden">
      <template x-for="item in tagihan" :key="'m-' + item.id_tagihan">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1"><div class="owner-copy-full font-bold text-slate-900" x-text="'Kamar ' + item.nomor_kamar"></div><div class="owner-copy-full mt-1 font-medium text-slate-700" x-text="item.nama_penghuni || 'Belum ada penghuni'"></div><div class="owner-copy-full mt-1 text-xs text-slate-500" x-text="isScoped ? item.tipe_kamar : item.nama_kos + ' · ' + item.tipe_kamar"></div><div class="owner-copy-full mt-1 text-[11px] text-slate-400" x-text="item.nomor_tagihan"></div></div>
            <span class="shrink-0 inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>
          <div class="mt-3 flex items-end justify-between gap-3 rounded-xl p-3" :class="Number(item.sisa_tagihan || 0) > 0 ? 'bg-rose-50' : 'bg-emerald-50'">
            <div><div class="text-[11px] font-semibold uppercase tracking-wider" :class="Number(item.sisa_tagihan || 0) > 0 ? 'text-rose-500' : 'text-emerald-500'" x-text="Number(item.sisa_tagihan || 0) > 0 ? 'Sisa yang harus dibayar' : 'Pembayaran selesai'"></div><div class="mt-1 text-xl font-bold tracking-tight" :class="Number(item.sisa_tagihan || 0) > 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="format(item.sisa_tagihan)"></div></div>
            <div class="text-right"><div class="text-[11px] text-slate-400">Jatuh tempo</div><div class="mt-1 text-xs font-semibold text-slate-700" x-text="formatDate(item.tanggal_jatuh_tempo)"></div></div>
          </div>
          <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
            <div><div class="text-slate-400">Periode</div><div class="mt-1 text-slate-700" x-text="formatDate(item.tanggal_mulai) + ' - ' + formatDate(item.tanggal_selesai)"></div></div>
            <div><div class="text-slate-400">Total</div><div class="mt-1 font-semibold text-slate-800" x-text="format(item.total_tagihan)"></div></div>
          </div>
          <div class="mt-3 flex gap-2">
            <a data-help="help-tagihan-detail" :href="detailUrl(item.id_tagihan)" class="btn-secondary flex-1 text-center">Lihat Detail</a>
            <a x-show="item.status !== 'lunas' && item.status !== 'dibatalkan'" :href="detailUrl(item.id_tagihan, 'payment')" class="owner-payment-button flex-1 text-center text-xs">Catat Bayar</a>
          </div>
        </article>
      </template>
    </div>
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
      idTipeKamar: '',
      idPenghuni: '',
      context: '',
      contextInfo: null,
      kosList: [],
      kamarList: [],
      filteredKamarList: [],
      loading: false,
      saving: false,

      get isTypeContext() { return this.context === 'tipe' && !!this.idTipeKamar; },
      get isOccupantContext() { return this.context === 'penghuni' && !!this.idPenghuni; },
      get isScoped() { return this.isTypeContext || this.isOccupantContext; },
      get backUrl() {
        if (this.isTypeContext) {
          return BASE_URL + '/pemilik/kamar?id_kos=' + encodeURIComponent(this.idKos) + '&context=kos';
        }
        return BASE_URL + '/pemilik/penghuni';
      },
      get contextTitle() {
        if (!this.contextInfo) return 'Memuat konteks...';
        if (this.isOccupantContext) return `${this.contextInfo.nama} · ${this.contextInfo.nama_kos} · Kamar ${this.contextInfo.nomor_kamar}`;
        return `${this.contextInfo.nama_kos} · ${this.contextInfo.nama_tipe || this.contextInfo.tipe_kamar}`;
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
        this.status = ['belum_lunas', 'sebagian'].includes(params.get('status')) ? params.get('status') : '';
        this.idKos = params.get('id_kos') || '';
        this.idKamar = params.get('id_kamar') || '';
        this.idTipeKamar = params.get('id_tipe_kamar') || '';
        this.idPenghuni = params.get('id_penghuni') || '';
        this.context = params.get('context') || '';
      },

      async loadFilters() {
        try {
          const [kosRes, kamarRes] = await Promise.all([
            API.get('/pemilik/kos', false),
            API.get('/pemilik/kamar', false)
          ]);

          this.kosList = kosRes.data || [];
          this.kamarList = kamarRes.data || [];

          if (this.isTypeContext) {
            const room = this.kamarList.find(item => String(item.id_tipe_kamar) === String(this.idTipeKamar));
            if (room) this.contextInfo = room;
            else {
              const typeRes = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + encodeURIComponent(this.idTipeKamar), false);
              this.contextInfo = typeRes.data || null;
            }
          } else if (this.isOccupantContext) {
            const occupantRes = await API.get('/pemilik/penghuni/show?id_penghuni=' + encodeURIComponent(this.idPenghuni), false);
            this.contextInfo = occupantRes.data || null;
          }
        } catch (error) {
          console.error('Gagal memuat filter kos/kamar:', error);
          this.kosList = [];
          this.kamarList = [];
        }
      },

      filterKamar() {
        if (this.isTypeContext) {
          this.filteredKamarList = this.kamarList.filter(item => String(item.id_tipe_kamar) === String(this.idTipeKamar));
        } else if (!this.idKos) {
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
        if (this.idTipeKamar) params.set('id_tipe_kamar', this.idTipeKamar);
        if (this.idPenghuni) params.set('id_penghuni', this.idPenghuni);
        if (this.context) params.set('context', this.context);

        const query = params.toString();
        const url = window.location.pathname + (query ? '?' + query : '');
        window.history.replaceState({}, '', url);

        this.load();
      },

      setStatus(value) {
        this.status = value;
        this.applyFilter();
      },

      async load() {
        this.loading = true;
        try {
          const params = new URLSearchParams();
          if (this.search.trim()) params.set('search', this.search.trim());
          if (this.status) params.set('status', this.status);
          if (this.idKos) params.set('id_kos', this.idKos);
          if (this.idKamar) params.set('id_kamar', this.idKamar);
          if (this.idTipeKamar) params.set('id_tipe_kamar', this.idTipeKamar);
          if (this.idPenghuni) params.set('id_penghuni', this.idPenghuni);
          params.set('scope', 'aktif');
          const query = params.toString();
          const res = await API.get('/pemilik/tagihan' + (query ? '?' + query : ''), false);
          this.tagihan = res.data || [];
        } catch (error) {
          console.error(error);
          this.tagihan = [];
        } finally {
          this.loading = false;
        }
      },

      detailUrl(id, action = '') {
        const params = new URLSearchParams({ id_tagihan: id });
        if (action) params.set('action', action);
        return window.BASE_URL + '/pemilik/pembayaran/detail?' + params.toString();
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
