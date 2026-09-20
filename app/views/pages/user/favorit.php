<div x-data="favoritePage()" x-init="init()" class="public-page">
  <section class="public-page-header">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <a href="<?= BASE_URL ?>/cari-kos" class="text-sm font-semibold text-slate-500 hover:text-primary">← Kembali ke pencarian</a>
      <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-primary">Kos tersimpan</p>
          <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Kos Favorit</h1>
          <p class="mt-2 text-sm leading-6 text-slate-500">Simpan kos yang menarik agar mudah dibandingkan dan dihubungi kembali.</p>
        </div>
        <a href="<?= BASE_URL ?>/cari-kos" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary-dark">Cari kos lagi</a>
      </div>
    </div>
  </section>

  <main class="mx-auto max-w-7xl px-4 py-6 pb-10 sm:px-6 lg:px-8">
    <div x-show="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <template x-for="i in 6" :key="i"><div class="h-80 animate-pulse rounded-2xl bg-slate-200"></div></template>
    </div>

    <div x-show="!loading && items.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <template x-for="item in items" :key="item.id_kos">
        <article class="public-card public-card-hover">
          <a :href="'<?= BASE_URL ?>/kos/' + item.id_kos" class="group block">
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img :src="item.foto ? '<?= BASE_URL ?>/uploads' + item.foto : '<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'" :alt="item.nama_kos" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" @error="$event.target.src='<?= BASE_URL ?>/assets/images/placeholder-kos.jpg'">
              <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm" x-text="item.jenis"></span>
            </div>
            <div class="p-4">
              <h2 class="font-semibold text-slate-900" x-text="item.nama_kos"></h2>
              <p class="mt-1 line-clamp-2 text-xs text-slate-500" x-text="item.alamat"></p>
              <p class="mt-3 text-xs font-semibold text-slate-600" x-text="item.kamar_tersedia + ' kamar tersedia'"></p>
              <p class="mt-1 font-bold text-primary" x-text="item.harga_mulai !== null ? formatRupiah(item.harga_mulai) + ' / bulan' : 'Harga belum tersedia'"></p>
            </div>
          </a>
          <div class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
            <span class="text-xs text-slate-400" x-text="formatDate(item.difavorit_at)"></span>
            <button type="button" @click="remove(item.id_kos)" class="text-xs font-bold text-red-600 hover:underline">Hapus favorit</button>
          </div>
        </article>
      </template>
    </div>

    <div x-show="!loading && !items.length" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-500"><?= masterIconSvg('heart', 'h-7 w-7') ?></div>
      <h2 class="mt-3 font-semibold text-slate-900">Belum ada kos favorit</h2>
      <p class="mt-1 text-sm text-slate-500">Saat menemukan kos yang menarik, tekan ikon hati untuk menyimpannya.</p>
      <a href="<?= BASE_URL ?>/cari-kos" class="mt-5 inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-dark">Mulai cari kos</a>
    </div>
  </main>
</div>

<script>
function favoritePage() {
  return {
    items: [],
    loading: false,
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const res = await API.get('/pelanggan/favorit');
        this.items = Array.isArray(res?.data) ? res.data : [];
      } catch (e) {
        console.error('Gagal memuat favorit:', e);
        this.items = [];
      } finally { this.loading = false; }
    },
    async remove(id_kos) {
      try {
        await API.post('/pelanggan/favorit', { id_kos: Number(id_kos) });
        this.items = this.items.filter(item => Number(item.id_kos) !== Number(id_kos));
      } catch (e) { console.error('Gagal menghapus favorit:', e); }
    },
    formatDate(value) {
      if (!value) return '';
      const date = new Date(String(value).replace(' ', 'T'));
      if (Number.isNaN(date.getTime())) return '';
      return 'Disimpan ' + date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }
  };
}
</script>
