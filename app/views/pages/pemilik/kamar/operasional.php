<div x-data="operasionalKamarPage()" x-init="init()" class="owner-page">
  <div class="owner-page-header">
    <div>
      <a :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow mt-3">Operasional Kamar</p>
      <h2 class="owner-title" x-text="kamar ? 'Kamar ' + kamar.nomor_kamar : 'Memuat kamar...' "></h2>
      <p class="owner-subtitle" x-text="kamar ? kamar.nama_kos + ' · ' + kamar.tipe_kamar : 'Penghuni dan keuangan aktif dalam satu tempat.'"></p>
    </div>
    <span x-show="kamar" x-cloak class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClass(kamar?.status)" x-text="statusLabel(kamar?.status)"></span>
  </div>

  <div x-show="loading" class="card py-12 text-center text-sm text-slate-500">Memuat operasional kamar...</div>

  <template x-if="!loading && kamar">
    <div class="space-y-5">
      <nav data-help="help-room-operation-tabs" class="owner-segmented" aria-label="Bagian operasional kamar">
        <button type="button" @click="goSection('penghuni')" :class="section === 'penghuni' ? 'is-active' : ''"><?= masterIconSvg('users-round', 'h-4 w-4') ?> Penghuni Aktif</button>
        <button type="button" @click="goSection('keuangan')" :class="section === 'keuangan' ? 'is-active' : ''"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan Aktif</button>
      </nav>

      <section data-help="help-room-operation-occupants" x-show="section === 'penghuni'" x-cloak class="space-y-3">
        <div class="owner-section-heading">
          <div><h3>Penghuni aktif</h3><p x-text="penghuni.length + ' dari ' + kamar.kapasitas + ' kapasitas terisi'"></p></div>
          <a :href="addOccupantUrl" class="btn-primary"><?= masterIconSvg('user-plus', 'h-4 w-4') ?> Tambah</a>
        </div>
        <div x-show="!penghuni.length" class="owner-empty !py-10"><div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"><?= masterIconSvg('users-round','h-6 w-6') ?></div><h3 class="font-semibold text-slate-900">Kamar belum memiliki penghuni aktif</h3><p class="mt-1 text-sm text-slate-500">Tambahkan penghuni ketika unit mulai ditempati.</p></div>
        <div x-show="penghuni.length" class="grid gap-3 md:grid-cols-2">
          <template x-for="item in penghuni" :key="item.id_penghuni">
            <article class="owner-panel">
              <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><?= masterIconSvg('user-round', 'h-5 w-5') ?></span><div class="min-w-0"><h4 class="font-bold text-slate-900" x-text="item.nama"></h4><p class="mt-1 text-xs text-slate-500" x-text="item.no_hp || 'Nomor HP belum tersedia'"></p></div><span class="ml-auto rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Aktif</span></div>
              <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-xs"><div><span class="text-slate-400">Tanggal masuk</span><strong class="mt-1 block text-slate-700" x-text="formatDate(item.tanggal_masuk)"></strong></div><div><span class="text-slate-400">NIK</span><strong class="mt-1 block text-slate-700" x-text="maskNik(item.nik)"></strong></div></div>
              <div class="mt-3 grid grid-cols-2 gap-2"><a :href="BASE_URL + '/pemilik/penghuni/edit?id_penghuni=' + item.id_penghuni" class="owner-action-secondary justify-center"><?= masterIconSvg('pencil', 'h-4 w-4') ?> Data</a><button type="button" @click="openFinance()" class="owner-action-secondary justify-center"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan</button></div>
            </article>
          </template>
        </div>
      </section>

      <section data-help="help-room-operation-finance" x-show="section === 'keuangan'" x-cloak class="space-y-3">
        <div class="owner-section-heading"><div><h3>Tagihan aktif</h3><p>Hanya tagihan yang masih perlu diselesaikan.</p></div></div>
        <div x-show="!tagihan.length" class="owner-empty !py-10"><h3 class="font-semibold text-slate-900">Tidak ada tagihan aktif</h3><p class="mt-1 text-sm text-slate-500">Tagihan lunas dapat dilihat melalui menu Riwayat.</p></div>
        <div x-show="tagihan.length" class="space-y-3">
          <template x-for="item in tagihan" :key="item.id_tagihan">
            <article class="owner-panel">
              <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="item.nama_penghuni || 'Penghuni kamar'"></p><h4 class="mt-1 font-bold text-slate-900" x-text="formatDate(item.tanggal_mulai) + ' – ' + formatDate(item.tanggal_selesai)"></h4></div><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="item.status === 'sebagian' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'" x-text="item.status === 'sebagian' ? 'Sebagian' : 'Perlu ditagih'"></span></div>
              <div class="mt-3 rounded-xl bg-rose-50 p-3"><p class="text-[11px] font-semibold uppercase tracking-wider text-rose-500">Sisa pembayaran</p><p class="mt-1 text-xl font-bold text-rose-700" x-text="rupiah(item.sisa_tagihan)"></p><p class="mt-1 text-xs text-rose-600" x-text="'Jatuh tempo ' + formatDate(item.tanggal_jatuh_tempo)"></p></div>
              <div class="mt-3 grid grid-cols-2 gap-2"><a :href="detailUrl(item.id_tagihan)" class="owner-action-secondary justify-center">Lihat Detail</a><a :href="detailUrl(item.id_tagihan, 'payment')" class="owner-payment-button text-xs"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Catat Bayar</a></div>
            </article>
          </template>
        </div>
      </section>
    </div>
  </template>
</div>

<script>
function operasionalKamarPage(){return{
 idKamar:utils.getQuery('id_kamar')||'',section:utils.getQuery('section')==='keuangan'?'keuangan':'penghuni',kamar:null,penghuni:[],tagihan:[],loading:true,
 get backUrl(){return this.kamar?BASE_URL+'/pemilik/kamar/kelola?id_tipe_kamar='+encodeURIComponent(this.kamar.id_tipe_kamar):BASE_URL+'/pemilik/kamar'},
 get addOccupantUrl(){return BASE_URL+'/pemilik/penghuni/tambah?id_kos='+encodeURIComponent(this.kamar?.id_kos||'')+'&id_tipe_kamar='+encodeURIComponent(this.kamar?.id_tipe_kamar||'')+'&id_kamar='+encodeURIComponent(this.idKamar)+'&context=kamar'},
 async init(){if(!this.idKamar){location.replace(BASE_URL+'/pemilik/kos');return}try{const [k,p,t]=await Promise.all([API.get('/pemilik/kamar/show?id_kamar='+encodeURIComponent(this.idKamar),false),API.get('/pemilik/penghuni?id_kamar='+encodeURIComponent(this.idKamar)+'&scope=aktif',false),API.get('/pemilik/tagihan?id_kamar='+encodeURIComponent(this.idKamar)+'&scope=aktif',false)]);this.kamar=k.data||null;this.penghuni=p.data||[];this.tagihan=t.data||[]}catch(e){console.error(e);Alpine.store('ui').toast('Operasional kamar gagal dimuat.','error')}finally{this.loading=false}},
 goSection(v){this.section=v;utils.setQuery('section',v==='keuangan'?'keuangan':null)},openFinance(){this.goSection('keuangan');window.scrollTo({top:0,behavior:'smooth'})},
 detailUrl(id,action=''){const p=new URLSearchParams({id_tagihan:id});if(action)p.set('action',action);return BASE_URL+'/pemilik/pembayaran/detail?'+p},
 formatDate(v){return v?new Date(v+'T00:00:00').toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'-'},rupiah(v){return utils.formatRupiah(v)},maskNik(v){v=String(v||'');return v.length>8?v.slice(0,4)+'••••••••'+v.slice(-4):'-'},
 statusLabel(v){return({tersedia:'Tersedia',terisi:'Terisi',tidak_tersedia:'Tidak tersedia',perbaikan:'Perbaikan',nonaktif:'Nonaktif'})[v]||v},statusClass(v){return({tersedia:'bg-emerald-50 text-emerald-700',terisi:'bg-violet-50 text-violet-700',tidak_tersedia:'bg-amber-50 text-amber-700',perbaikan:'bg-blue-50 text-blue-700',nonaktif:'bg-slate-200 text-slate-600'})[v]||'bg-slate-100 text-slate-600'}
}}
</script>
