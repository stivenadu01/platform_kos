<div x-data="propertyWorkspace(<?= (int)$kos['id_kos'] ?>)" class="owner-page">
  <header class="owner-page-header">
    <div class="min-w-0 flex-1">
      <a href="<?= BASE_URL ?>/pemilik/kos" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('chevron-left','h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow mt-3">Unit properti</p>
      <div class="owner-context-title">
        <div class="min-w-0"><h1 class="owner-title"><?= htmlspecialchars($kos['nama_kos']) ?></h1><p class="owner-subtitle"><?= htmlspecialchars($kos['alamat']) ?></p></div>
        <div class="ui-actions" x-data="uiActionMenu()" @betakos:close-menus.window="closeMenu(false)" @resize.window="closeMenu()">
          <button type="button" x-ref="menuTrigger" class="ui-more-trigger" aria-label="Tindakan lainnya" aria-haspopup="dialog" :aria-expanded="menuOpen" @click="menuOpen ? closeMenu() : showMenu()"><?= masterIconSvg('ellipsis','h-5 w-5') ?></button>
          <template x-teleport="body"><div x-show="menuOpen" x-cloak class="ui-menu-layer" @keydown.escape.stop.prevent="closeMenu()"><div class="ui-menu-backdrop" @click="closeMenu()"></div><div x-ref="menuPanel" role="dialog" aria-modal="true" aria-label="Tindakan properti" class="ui-action-menu" :style="menuPosition" @click="if ($event.target.closest('a,button')) closeMenu(false)"><a href="<?= BASE_URL ?>/pemilik/kamar?id_kos=<?= (int)$kos['id_kos'] ?>"><?= masterIconSvg('bed','h-4 w-4') ?> Tipe, harga &amp; foto</a><a href="<?= BASE_URL ?>/pemilik/kos/foto?id=<?= (int)$kos['id_kos'] ?>"><?= masterIconSvg('image','h-4 w-4') ?> Foto kos</a><?php if ($kos['status'] !== 'menunggu_verifikasi'): ?><a href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= (int)$kos['id_kos'] ?>"><?= masterIconSvg('pencil','h-4 w-4') ?> Informasi kos</a><?php endif; ?></div></div></template>
        </div>
      </div>
    </div>
    <span class="rounded-full bg-primary-soft px-3 py-2 text-sm text-primary"><?= htmlspecialchars(ucwords(str_replace('_',' ',$kos['status']))) ?></span>
  </header>
  <nav class="bk-context-nav" aria-label="Kelola kos ini">
    <a href="<?= BASE_URL ?>/pemilik/kos/detail?id_kos=<?= (int)$kos['id_kos'] ?>" aria-current="page"><?= masterIconSvg('bed','h-4 w-4') ?> Unit kamar</a>
    <a href="<?= BASE_URL ?>/pemilik/kamar?id_kos=<?= (int)$kos['id_kos'] ?>">Tipe & harga</a>
    <a href="<?= BASE_URL ?>/pemilik/penghuni?id_kos=<?= (int)$kos['id_kos'] ?>">Penghuni</a>
    <a href="<?= BASE_URL ?>/pemilik/pembayaran?id_kos=<?= (int)$kos['id_kos'] ?>">Tagihan sewa</a>
  </nav>
  <section class="owner-panel"><div class="grid gap-3 sm:grid-cols-2"><div><label for="unit-search" class="label">Cari nomor kamar</label><input id="unit-search" type="search" class="input" x-model="search" @input.debounce.250ms="syncUrl()" placeholder="Contoh: 05"></div><div><label for="unit-type" class="label">Tipe kamar</label><select id="unit-type" class="select" x-model="type" @change="syncUrl()"><option value="">Semua tipe</option><template x-for="t in types" :key="t.id_tipe_kamar"><option :value="String(t.id_tipe_kamar)" x-text="t.nama_tipe"></option></template></select></div></div></section>
  <div class="owner-segmented" aria-label="Status unit"><template x-for="s in statuses" :key="s[0]"><button type="button" @click="status=s[0];syncUrl()" :class="status===s[0]?'is-active':''"  :aria-pressed="status===s[0]"><span x-text="s[1]"></span><span class="bk-tab-count" x-text="countStatus(s[0])"></span></button></template></div>
  <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500" x-text="filtered.length + ' unit kamar'"></p><a :href="BASE_URL+'/pemilik/kamar/tambah?id_kos='+id+(type?'&id_tipe_kamar='+type:'')" class="btn-primary">Tambah unit</a></div>
  <p x-show="error" x-text="error" class="text-red-700" role="alert"></p><p x-show="loading">Memuat unit kamar…</p>
  <div x-show="!loading && !error && !filtered.length" x-cloak class="owner-empty"><h2 class="font-bold">Belum ada kamar yang sesuai</h2><p class="mt-2 text-sm">Ubah filter atau tambahkan tipe dan unit kamar.</p><a href="<?= BASE_URL ?>/pemilik/tipe-kamar/tambah?id_kos=<?= (int)$kos['id_kos'] ?>" class="btn-secondary mt-3">Buat tipe kamar</a></div>
  <div class="owner-unit-grid" data-help="help-property-units"><template x-for="room in filtered" :key="room.id_kamar"><a :href="BASE_URL+'/pemilik/kamar/detail?id_kamar='+room.id_kamar" class="owner-panel owner-panel-hover" :data-room-status="room.status"><div class="flex items-center justify-between"><h2 class="text-lg font-bold" x-text="'Kamar '+room.nomor_kamar"></h2><?= masterIconSvg('chevron-right','h-5 w-5') ?></div><p class="mt-1 text-sm text-slate-500" x-text="room.tipe_kamar || room.nama_tipe"></p><p class="mt-3 text-sm font-semibold" :class="room.status==='tersedia'?'text-emerald-700':'text-slate-700'" x-text="label(room.status)"></p></a></template></div>
</div>
<script>
function propertyWorkspace(id){const q=new URLSearchParams(location.search);return{id,rooms:[],types:[],search:q.get('q')||'',type:q.get('id_tipe_kamar')||'',status:q.get('status')||'',loading:true,error:'',statuses:[['','Semua'],['tersedia','Tersedia'],['terisi','Terisi'],['tidak_tersedia','Tidak tersedia'],['perbaikan','Perbaikan'],['nonaktif','Nonaktif']],get filtered(){return this.rooms.filter(r=>(!this.type||String(r.id_tipe_kamar)===this.type)&&(!this.status||r.status===this.status)&&String(r.nomor_kamar).toLowerCase().includes(this.search.toLowerCase()))},countStatus(value){return this.rooms.filter(r=>(!this.type||String(r.id_tipe_kamar)===this.type)&&(!value||r.status===value)&&String(r.nomor_kamar).toLowerCase().includes(this.search.toLowerCase())).length},label(v){return this.statuses.find(s=>s[0]===v)?.[1]||v},syncUrl(){const p=new URLSearchParams();p.set('id_kos',this.id);if(this.type)p.set('id_tipe_kamar',this.type);if(this.status)p.set('status',this.status);if(this.search.trim())p.set('q',this.search.trim());history.replaceState({},'',location.pathname+'?'+p)},async init(){try{const [r,t]=await Promise.all([API.get('/pemilik/kamar?id_kos='+this.id,false),API.get('/pemilik/tipe-kamar?id_kos='+this.id,false)]);this.rooms=(r.data||[]).sort((a,b)=>String(a.nomor_kamar).localeCompare(String(b.nomor_kamar),'id',{numeric:true}));this.types=t.data||[];if(this.type&&!this.types.some(item=>String(item.id_tipe_kamar)===this.type)){this.type='';this.syncUrl()}}catch(e){this.error='Unit kamar belum dapat dimuat. Muat ulang halaman untuk mencoba lagi.'}finally{this.loading=false}}}}
</script>


