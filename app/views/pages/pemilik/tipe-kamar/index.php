<div x-data="tipeKamarPage()" x-init="init()" class="space-y-6">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <a href="<?= BASE_URL ?>/pemilik/kamar" class="text-sm text-slate-500 hover:text-primary">← Kembali ke kamar</a>
      <h2 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl">Tipe Kamar</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola tipe, harga, fasilitas, foto, dan unit kamar.</p>
    </div>
    <a href="<?= BASE_URL ?>/pemilik/tipe-kamar/tambah" class="btn-primary">+ Tambah Tipe Kamar</a>
  </div>

  <div x-show="loading" class="card p-10 text-center text-sm text-slate-500">Memuat tipe kamar...</div>
  <div x-show="!loading && !items.length" x-cloak class="card p-10 text-center text-sm text-slate-500">Belum ada tipe kamar.</div>
  <div x-show="!loading && items.length" x-cloak class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    <template x-for="item in items" :key="item.id_tipe_kamar">
      <article class="card border border-slate-200 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="font-semibold text-slate-900" x-text="item.nama_tipe"></h3>
            <p class="mt-1 text-sm text-slate-500" x-text="item.nama_kos"></p>
          </div>
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600" x-text="item.kapasitas + ' orang'"></span>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-lg bg-slate-50 p-3">
            <div class="text-xs text-slate-400">Unit</div>
            <div class="mt-1 font-semibold" x-text="item.jumlah_kamar"></div>
          </div>
          <div class="rounded-lg bg-emerald-50 p-3">
            <div class="text-xs text-emerald-600">Tersedia</div>
            <div class="mt-1 font-semibold text-emerald-800" x-text="item.kamar_tersedia"></div>
          </div>
        </div>
        <div class="mt-5 flex flex-wrap gap-2">
          <a :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + item.id_tipe_kamar" class="btn-secondary">Kelola</a>
          <button type="button" @click="remove(item)" class="btn-danger">Hapus</button>
        </div>
      </article>
    </template>
  </div>
</div>
<script>
  function tipeKamarPage() {
    return {
      items: [],
      loading: false,
      async init() {
        await this.load();
      },
      async load() {
        this.loading = true;
        try {
          const res = await API.get('/pemilik/tipe-kamar', false);
          this.items = res.data || [];
        } catch (error) {
          console.error(error);
          this.items = [];
        } finally {
          this.loading = false;
        }
      },
      async remove(item) {
        if (!await Alpine.store('ui').confirm('Hapus tipe kamar ' + item.nama_tipe + '?')) return;
        try {
          await API.delete('/pemilik/tipe-kamar', {
            id_tipe_kamar: item.id_tipe_kamar
          });
          await this.load();
        } catch (error) {
          console.error(error);
        }
      }
    };
  }
</script>