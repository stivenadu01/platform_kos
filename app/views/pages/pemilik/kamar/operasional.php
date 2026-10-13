<div x-data="operasionalKamarPage(<?= !empty($canManageOccupancy) ? 'true' : 'false' ?>)"  class="owner-page">
  <div class="owner-page-header">
    <div>
      <a :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
      <p class="owner-eyebrow mt-3">Operasional Kamar</p>
      <div class="owner-context-title"><div class="min-w-0"><h2 class="owner-title" x-text="kamar ? 'Kamar ' + kamar.nomor_kamar : 'Memuat kamar...' "></h2><p class="owner-subtitle" x-text="kamar ? kamar.nama_kos + ' · ' + kamar.tipe_kamar : 'Penghuni dan keuangan aktif dalam satu tempat.'"></p></div>
      <div x-show="kamar" x-cloak class="ui-actions" x-data="uiActionMenu()" @betakos:close-menus.window="closeMenu(false)" @resize.window="closeMenu()"><button type="button" x-ref="menuTrigger" class="ui-more-trigger" aria-label="Tindakan lainnya" aria-haspopup="dialog" :aria-expanded="menuOpen" @click="menuOpen ? closeMenu() : showMenu()"><?= masterIconSvg('ellipsis','h-5 w-5') ?></button><template x-teleport="body"><div x-show="menuOpen" x-cloak class="ui-menu-layer" @keydown.escape.stop.prevent="closeMenu()"><div class="ui-menu-backdrop" @click="closeMenu()"></div><div x-ref="menuPanel" role="dialog" aria-modal="true" aria-label="Tindakan kamar" class="ui-action-menu" :style="menuPosition" @click="if ($event.target.closest('a,button')) closeMenu(false)"><a :href="BASE_URL+'/pemilik/kamar?id_kos='+kamar.id_kos"><?= masterIconSvg('bed','h-4 w-4') ?> Tipe, harga &amp; foto</a><button type="button" @click="removeRoom()" class="owner-photo-action-danger"><?= masterIconSvg('trash-2','h-4 w-4') ?> Hapus kamar</button></div></div></template></div></div>
    </div>
    <span x-show="kamar" x-cloak class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClass(kamar?.status)" x-text="statusLabel(kamar?.status)"></span>
  </div>

  <div x-show="loading" class="card py-12 text-center text-sm text-slate-500">Memuat operasional kamar...</div>

  <template x-if="!loading && kamar">
    <div class="space-y-5">
      <nav data-help="help-room-operation-tabs" class="owner-segmented" aria-label="Bagian operasional kamar">
        <button type="button" x-show="isPro" @click="goSection('penghuni')" :class="section === 'penghuni' ? 'is-active' : ''"><?= masterIconSvg('users-round', 'h-4 w-4') ?> Penghuni Aktif</button>
        <button type="button" x-show="isPro" @click="goSection('keuangan')" :class="section === 'keuangan' ? 'is-active' : ''"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan</button>
        <button type="button" @click="goSection('data')" :class="section==='data'?'is-active':''"><?= masterIconSvg('settings','h-4 w-4') ?> Data kamar</button>
      </nav>
      <section x-show="section==='data'" class="owner-panel">
        <h3 class="font-bold">Data &amp; status kamar</h3>
        <p class="mt-2 text-sm text-slate-500" x-text="kamar.tipe_kamar+' · Kapasitas '+kamar.kapasitas+' orang'"></p>
        <template x-if="kamar.status!=='terisi'"><form @submit.prevent="saveStatus()" class="mt-4 space-y-3"><label for="room-status" class="label">Status operasional</label><select id="room-status" class="select" x-model="statusDraft"><option value="tersedia">Tersedia</option><option value="tidak_tersedia">Tidak tersedia</option><option value="perbaikan">Perbaikan</option><option value="nonaktif">Nonaktif</option></select><button type="submit" class="btn-primary" :disabled="saving" x-text="saving?'Menyimpan…':'Simpan status'"></button></form></template>
        <p x-show="kamar.status==='terisi'" class="mt-3 text-sm text-slate-500">Kamar terisi mengikuti data penghuni aktif. Gunakan proses penghuni keluar untuk mengosongkannya.</p>
        <div x-show="!isPro" class="mt-4 rounded-xl bg-primary-soft p-4"><p class="text-sm">Penghuni dan pencatatan pembayaran tersedia di BetaKos Pro.</p><a :href="BASE_URL+'/pemilik/langganan'" class="btn-secondary mt-2">Lihat BetaKos Pro</a></div>
      </section>

      <section data-help="help-room-operation-occupants" x-show="isPro && section === 'penghuni'" x-cloak class="space-y-3">
        <div class="owner-section-heading">
          <div><h3>Penghuni aktif</h3><p x-text="penghuni.length + ' dari ' + kamar.kapasitas + ' kapasitas terisi'"></p></div>
          <a x-show="penghuni.length < Number(kamar.kapasitas)" :href="addOccupantUrl" class="btn-primary"><?= masterIconSvg('user-plus', 'h-4 w-4') ?> Tambah</a>
        </div>
        <div x-show="!penghuni.length" class="owner-empty !py-10"><div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-soft text-primary"><?= masterIconSvg('users-round','h-6 w-6') ?></div><h3 class="font-semibold text-slate-900">Kamar belum memiliki penghuni aktif</h3><p class="mt-1 text-sm text-slate-500">Tambahkan penghuni ketika unit mulai ditempati.</p></div>
        <div x-show="penghuni.length" class="grid gap-3 md:grid-cols-2">
          <template x-for="item in penghuni" :key="item.id_penghuni">
            <article class="owner-panel">
              <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><?= masterIconSvg('user-round', 'h-5 w-5') ?></span><div class="min-w-0"><a :href="BASE_URL+'/pemilik/penghuni/detail?id_penghuni='+item.id_penghuni" class="font-bold text-slate-900 hover:text-primary" x-text="item.nama"></a><p class="mt-1 text-xs text-slate-500" x-text="item.no_hp || 'Nomor HP belum tersedia'"></p></div><span class="ml-auto rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktif</span></div>
              <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-xs"><div><span class="text-slate-400">Tanggal masuk</span><strong class="mt-1 block text-slate-700" x-text="formatDate(item.tanggal_masuk)"></strong></div><div><span class="text-slate-400">NIK</span><strong class="mt-1 block text-slate-700" x-text="maskNik(item.nik)"></strong></div></div>
              <div class="mt-3 grid grid-cols-2 gap-2"><a :href="BASE_URL + '/pemilik/penghuni/edit?id_penghuni=' + item.id_penghuni" class="owner-action-secondary justify-center"><?= masterIconSvg('pencil', 'h-4 w-4') ?> Data</a><button type="button" @click="openFinance()" class="owner-action-secondary justify-center"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Tagihan</button></div>
            </article>
          </template>
        </div>
      </section>

      <section data-help="help-room-operation-finance" x-show="isPro && section === 'keuangan'" x-cloak class="space-y-3">
        <div class="owner-section-heading"><div><h3>Tagihan belum selesai</h3><p>Termasuk tagihan berjalan, mendatang, dan yang perlu ditagih.</p></div></div>
        <div x-show="tagihan.length" class="owner-segmented" role="tablist" aria-label="Periode tagihan kamar"><button type="button" @click="billGroup='aktif'" :class="billGroup==='aktif'?'is-active':''"><span class="owner-segment-dot bg-rose-500"></span>Perlu ditagih <span x-text="actionBills.length"></span></button><button type="button" @click="billGroup='terjadwal'" :class="billGroup==='terjadwal'?'is-active':''"><span class="owner-segment-dot bg-blue-500"></span>Berjalan &amp; Mendatang <span x-text="scheduledBills.length"></span></button></div>
        <div x-show="!displayedBills.length" class="owner-empty !py-10"><h3 class="font-semibold text-slate-900" x-text="billGroup==='aktif'?'Tidak ada tagihan yang perlu ditagih':'Tidak ada tagihan berjalan atau mendatang'"></h3><p class="mt-1 text-sm text-slate-500" x-text="tagihan.length?'Pilih kelompok periode lainnya untuk melihat tagihan kamar ini.':'Tagihan lunas dapat dilihat melalui menu Riwayat.'"></p></div>
        <div x-show="displayedBills.length" class="space-y-3">
          <template x-for="item in displayedBills" :key="item.id_tagihan">
            <article class="owner-panel">
              <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400" x-text="item.nama_penghuni || 'Penghuni kamar'"></p><h4 class="mt-1 font-bold text-slate-900" x-text="formatDate(item.tanggal_mulai) + ' – ' + formatDate(item.tanggal_selesai)"></h4></div><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="billStatusClass(item)" x-text="billStatusLabel(item)"></span></div>
              <div class="mt-3 rounded-xl p-3" :class="item.perlu_tindakan ? 'bg-rose-50' : 'bg-blue-50'"><p class="text-xs font-semibold uppercase tracking-wider" :class="item.perlu_tindakan ? 'text-rose-500' : 'text-blue-500'" x-text="item.perlu_tindakan ? 'Sisa pembayaran' : 'Dapat dibayar lebih awal'"></p><p class="mt-1 text-xl font-bold" :class="item.perlu_tindakan ? 'text-rose-700' : 'text-blue-700'" x-text="rupiah(item.sisa_tagihan)"></p><p class="mt-1 text-xs" :class="item.perlu_tindakan ? 'text-rose-600' : 'text-blue-600'" x-text="'Jatuh tempo ' + formatDate(item.tanggal_jatuh_tempo)"></p></div>
              <div class="mt-3 grid grid-cols-2 gap-2"><a :href="detailUrl(item.id_tagihan)" class="owner-action-secondary justify-center">Lihat Detail</a><a :href="detailUrl(item.id_tagihan, 'payment')" class="owner-payment-button text-xs"><?= masterIconSvg('wallet', 'h-4 w-4') ?> Catat Bayar</a></div>
            </article>
          </template>
        </div>
      </section>
    </div>
  </template>
</div>

<script>
function operasionalKamarPage(isPro){return{
 isPro,saving:false,statusDraft:'',
 idKamar:utils.getQuery('id_kamar')||'',section:isPro?(['keuangan','data'].includes(utils.getQuery('section'))?utils.getQuery('section'):'penghuni'):'data',billGroup:'aktif',kamar:null,penghuni:[],tagihan:[],loading:true,
 get actionBills(){return this.tagihan.filter(item=>item.perlu_tindakan===true||item.perlu_tindakan===1)},get scheduledBills(){return this.tagihan.filter(item=>item.perlu_tindakan!==true&&item.perlu_tindakan!==1)},get displayedBills(){return this.billGroup==='terjadwal'?this.scheduledBills:this.actionBills},
 get backUrl(){return this.kamar?BASE_URL+'/pemilik/kos/detail?id_kos='+encodeURIComponent(this.kamar.id_kos):BASE_URL+'/pemilik/kamar'},
 get addOccupantUrl(){return BASE_URL+'/pemilik/penghuni/tambah?id_kos='+encodeURIComponent(this.kamar?.id_kos||'')+'&id_tipe_kamar='+encodeURIComponent(this.kamar?.id_tipe_kamar||'')+'&id_kamar='+encodeURIComponent(this.idKamar)+'&context=kamar'},
 async init(){if(!this.idKamar){location.replace(BASE_URL+'/pemilik/kos');return}try{const k=await API.get('/pemilik/kamar/show?id_kamar='+encodeURIComponent(this.idKamar),false);this.kamar=k.data||null;this.statusDraft=this.kamar?.status||'';if(this.isPro){const [p,t]=await Promise.all([API.get('/pemilik/penghuni?id_kamar='+encodeURIComponent(this.idKamar)+'&scope=aktif',false),API.get('/pemilik/tagihan?id_kamar='+encodeURIComponent(this.idKamar)+'&scope=belum_selesai',false)]);this.penghuni=p.data||[];this.tagihan=t.data||[];if(!this.actionBills.length&&this.scheduledBills.length)this.billGroup='terjadwal'}}catch(e){Alpine.store('ui').toast('Detail kamar gagal dimuat. Muat ulang untuk mencoba lagi.','error')}finally{this.loading=false}},
 async saveStatus(){this.saving=true;try{await API.put('/pemilik/kamar/status',{id_kamar:Number(this.idKamar),status:this.statusDraft});this.kamar.status=this.statusDraft}finally{this.saving=false}},
 async removeRoom(){if(!await Alpine.store('ui').confirm('Hapus Kamar '+(this.kamar?.nomor_kamar||'')+'?'))return;try{await API.delete('/pemilik/kamar',{id_kamar:Number(this.idKamar)});location.href=this.backUrl}catch(e){console.error(e)}},

 goSection(v){this.section=v;utils.setQuery('section',v==='penghuni'?null:v)},openFinance(){this.goSection('keuangan');window.scrollTo({top:0,behavior:'smooth'})},
 detailUrl(id,action=''){const p=new URLSearchParams({id_tagihan:id});if(action)p.set('action',action);return BASE_URL+'/pemilik/pembayaran/detail?'+p},
 formatDate(v){return v?new Date(v+'T00:00:00').toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}):'-'},rupiah(v){return utils.formatRupiah(v)},maskNik(v){v=String(v||'');return v.length>8?v.slice(0,4)+'••••••••'+v.slice(-4):'-'},
 statusLabel(v){return({tersedia:'Tersedia',terisi:'Terisi',tidak_tersedia:'Tidak tersedia',perbaikan:'Perbaikan',nonaktif:'Nonaktif'})[v]||v},statusClass(v){return({tersedia:'bg-emerald-50 text-emerald-700',terisi:'bg-primary-soft text-primary',tidak_tersedia:'bg-amber-50 text-amber-700',perbaikan:'bg-blue-50 text-blue-700',nonaktif:'bg-slate-200 text-slate-600'})[v]||'bg-slate-100 text-slate-600'},
 billStatusLabel(item){return item.status==='sebagian'?'Sebagian · '+item.label_waktu:item.label_waktu},billStatusClass(item){if(item.perlu_tindakan)return item.status==='sebagian'?'bg-amber-50 text-amber-700':'bg-rose-50 text-rose-700';return'bg-blue-50 text-blue-700'}
}}
</script>
