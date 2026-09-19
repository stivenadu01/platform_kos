<div x-data="adminAturanPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-primary">Data Master · Aturan Kos</p>
      <h2 class="mt-1 text-2xl font-bold text-slate-900">Aturan Kos</h2>
      <p class="mt-1 text-sm text-slate-500">Aturan yang dapat dipilih pemilik dan ditampilkan pada detail kos.</p>
    </div><button class="btn-primary" @click="openCreate()">+ Tambah Aturan</button>
  </div>
  <div class="card p-4 border border-slate-200">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3"><input class="input" x-model="filters.search" @input.debounce.300ms="load()" placeholder="Cari nama aturan..."><select class="select" x-model="filters.status" @change="load()">
        <option value="">Semua status</option>
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
      </select></div>
  </div>
  <section class="card border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex justify-between">
      <div>
        <h3 class="font-bold">Daftar Aturan Kos</h3>
        <p class="text-xs text-slate-500 mt-1" x-text="items.length + ' data'"></p>
      </div><button class="btn-secondary text-sm" @click="load()">↻ Refresh</button>
    </div>
    <div x-show="loading" class="p-10 text-center text-sm text-slate-500">Memuat...</div>
    <div x-show="!loading && items.length===0" class="p-10 text-center text-sm text-slate-500">Belum ada data yang sesuai.</div>
    <div x-show="!loading && items.length" class="divide-y divide-slate-100">
      <template x-for="item in items" :key="item.id_aturan">
        <article class="p-4 sm:p-5 flex items-start gap-3">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary" x-html="iconSvg(item.icon)"></span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h4 class="font-semibold text-slate-900" x-text="item.nama_aturan"></h4><span class="rounded-full px-2.5 py-1 text-[11px] font-medium" :class="item.status==='aktif'?'bg-emerald-100 text-emerald-700':'bg-slate-100 text-slate-500'" x-text="item.status==='aktif'?'Aktif':'Nonaktif'"></span>
            </div>
            <p class="mt-1 text-xs text-slate-500" x-text="iconLabels[item.icon] || item.icon"></p>
          </div>
          <div class="flex shrink-0 gap-2"><button class="btn-secondary text-xs" @click="openEdit(item)">Edit</button><button class="btn-secondary text-xs text-red-600" @click="remove(item)">Hapus</button></div>
        </article>
      </template>
    </div>
  </section>
  <div x-show="modalOpen" x-cloak class="fixed inset-0 z-90 flex items-end sm:items-center justify-center p-0 sm:p-5">
    <div class="absolute inset-0 bg-slate-900/50" @click="close()"></div>
    <div class="relative bg-white w-full sm:max-w-xl max-h-[94vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl p-5 shadow-2xl">
      <div class="flex justify-between gap-3">
        <div>
          <h3 class="font-bold" x-text="editing?'Edit Aturan Kos':'Tambah Aturan Kos'"></h3>
          <p class="mt-1 text-xs text-slate-500">Gunakan katalog icon resmi BetaKos agar tampilan konsisten di seluruh platform.</p>
        </div><button @click="close()">✕</button>
      </div>
      <form class="mt-5 space-y-4" @submit.prevent="save()">
        <div><label class="label">Nama aturan *</label><input class="input mt-1" x-model="form.nama_aturan" required maxlength="200"></div>
        <div><label class="label">Icon *</label>
          <div class="mt-2 grid grid-cols-5 sm:grid-cols-7 gap-2 max-h-60 overflow-y-auto rounded-xl border border-slate-200 p-2">
            <template x-for="key in iconKeys" :key="key"><button type="button" @click="form.icon=key" class="group flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl p-2 transition" :class="form.icon===key?'bg-primary-soft text-primary ring-2 ring-primary/30':'text-slate-500 hover:bg-slate-50'" :title="iconLabels[key]"><span class="flex h-9 w-9 items-center justify-center rounded-lg" :class="form.icon===key?'bg-white':'bg-slate-100'" x-html="iconSvg(key,'h-5 w-5')"></span><span class="w-full truncate text-[9px]" x-text="iconLabels[key]"></span></button></template>
          </div>
          <div class="mt-2 flex items-center gap-2 text-xs text-slate-500"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-soft text-primary" x-html="iconSvg(form.icon,'h-5 w-5')"></span><span>Terpilih: <strong x-text="iconLabels[form.icon] || form.icon"></strong></span></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="label">Urutan</label><input class="input mt-1" type="number" min="0" x-model.number="form.urutan"></div>
          <div><label class="label">Status</label><select class="select mt-1" x-model="form.status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select></div>
        </div>
        <div class="flex justify-end gap-2 pt-2"><button type="button" class="btn-secondary" @click="close()">Batal</button><button class="btn-primary" :disabled="saving" x-text="saving?'Menyimpan...':'Simpan'"></button></div>
      </form>
    </div>
  </div>
</div>
<script>
  function adminAturanPage() {
    return {
      items: [],
      loading: false,
      saving: false,
      modalOpen: false,
      editing: null,
      filters: {
        search: '',
        status: ''
      },
      form: {
        nama_aturan: '',
        icon: 'ban',
        urutan: 0,
        status: 'aktif'
      },
      iconPaths: <?= json_encode_safe(array_map(fn($v) => $v['path'], masterIconCatalog()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      iconLabels: <?= json_encode_safe(array_map(fn($v) => $v['label'], masterIconCatalog()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      iconKeys: <?= json_encode_safe(array_keys(masterIconCatalog()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      init() {
        this.load()
      },
      iconSvg(i, c = 'h-5 w-5') {
        const p = this.iconPaths[i] || this.iconPaths['map-pin'];
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="${c}" aria-hidden="true">${p}</svg>`
      },
      async load() {
        this.loading = true;
        try {
          let q = new URLSearchParams(this.filters).toString();
          let r = await API.get('/admin/aturan?' + q, false);
          this.items = Array.isArray(r.data) ? r.data : []
        } catch (e) {
          console.error(e)
        } finally {
          this.loading = false
        }
      },
      openCreate() {
        this.editing = null;
        this.form = {
          nama_aturan: '',
          icon: 'ban',
          urutan: 0,
          status: 'aktif'
        };
        this.modalOpen = true
      },
      openEdit(i) {
        this.editing = i.id_aturan;
        this.form = {
          nama_aturan: i.nama_aturan || '',
          icon: i.icon || 'ban',
          urutan: Number(i.urutan || 0),
          status: i.status || 'aktif'
        };
        this.modalOpen = true
      },
      close() {
        this.modalOpen = false
      },
      async save() {
        this.saving = true;
        try {
          let r = this.editing ? await API.put('/admin/aturan/' + this.editing, this.form) : await API.post('/admin/aturan', this.form);
          if (r.success) {
            this.close();
            await this.load()
          }
        } catch (e) {
          console.error(e)
        } finally {
          this.saving = false
        }
      },
      async remove(i) {
        if (!await Alpine.store('ui').confirm('Hapus data ini?')) return;
        try {
          await API.delete('/admin/aturan/' + i.id_aturan);
          await this.load()
        } catch (e) {
          console.error(e)
        }
      }
    }
  }
</script>