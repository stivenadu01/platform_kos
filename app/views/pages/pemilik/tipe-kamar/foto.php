<div x-data="tipeKamarFotoPage()" x-init="init()" class="mx-auto max-w-6xl space-y-6">
  <div>
    <a href="<?= BASE_URL ?>/pemilik/tipe-kamar" class="text-sm text-primary hover:underline">← Kembali ke tipe kamar</a>
    <div class="mt-3">
      <h2 class="text-2xl font-bold text-slate-900">Foto Tipe Kamar</h2>
      <p class="mt-1 text-sm text-slate-500">Kelola foto yang akan ditampilkan pada tipe kamar ini.</p>
    </div>
  </div>

  <div class="card border border-slate-200 p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-lg font-semibold text-slate-900" x-text="tipe.nama_tipe || 'Memuat tipe kamar...'">Memuat tipe kamar...</h3>
        <p class="mt-1 text-sm text-slate-500">
          Kos: <span x-text="tipe.nama_kos || '-'">-</span>
          · Kapasitas: <span x-text="tipe.kapasitas ? tipe.kapasitas + ' orang' : '-'">-</span>
        </p>
      </div>
      <a :href="BASE_URL + '/pemilik/tipe-kamar/edit?id_tipe_kamar=' + id" class="btn-secondary text-center">Edit Tipe Kamar</a>
    </div>
  </div>

  <div class="card border border-slate-200 p-6 shadow-sm">
    <div>
      <h3 class="font-semibold text-slate-900">Tambah Foto</h3>
      <p class="mt-1 text-sm text-slate-500">Upload foto kamar atau bagian kamar yang ingin ditampilkan kepada pencari kos.</p>
    </div>

    <form @submit.prevent="upload" class="mt-5">
      <div class="rounded-xl border-2 border-dashed border-slate-300 p-6 text-center transition hover:border-primary">
        <input type="file" x-ref="file" accept="image/jpeg,image/png,image/webp" @change="previewFile" class="hidden">
        <button type="button" data-onboarding="tipe-foto-pilih" @click="$refs.file.click()" class="btn-secondary">Pilih Foto</button>
        <p class="mt-3 text-sm text-slate-500">JPG, PNG, atau WEBP</p>
        <p class="mt-1 text-xs text-slate-400">Maksimal 10 MB</p>

        <div x-show="selectedFile" x-cloak class="mt-4">
          <p class="text-sm font-medium text-slate-700" x-text="selectedFile ? selectedFile.name : ''"></p>
        </div>
        <div x-show="preview" x-cloak class="mt-5">
          <img :src="preview" alt="Preview foto" class="mx-auto max-h-64 rounded-xl border border-slate-200 object-contain">
        </div>
      </div>

      <div class="mt-5 flex justify-end">
        <button type="submit" data-onboarding="tipe-foto-upload" class="btn-primary" :disabled="loading || !selectedFile">
          <span x-show="!loading">Upload Foto</span>
          <span x-show="loading" x-cloak>Mengupload...</span>
        </button>
      </div>
    </form>
  </div>

  <div class="card border border-slate-200 p-6 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="font-semibold text-slate-900">Daftar Foto</h3>
        <p class="mt-1 text-sm text-slate-500">Pilih satu foto sebagai thumbnail tipe kamar.</p>
      </div>
      <div class="text-sm text-slate-500"><span x-text="foto.length"></span> foto</div>
    </div>

    <div x-show="loadingData" x-cloak class="py-14 text-center text-sm text-slate-500">Memuat foto...</div>
    <div x-show="!loadingData && foto.length === 0" x-cloak class="py-14 text-center">
      <div class="mb-4 text-4xl">🖼️</div>
      <h3 class="font-semibold text-slate-900">Belum ada foto</h3>
      <p class="mt-1 text-sm text-slate-500">Upload foto pertama untuk tipe kamar ini.</p>
    </div>

    <div x-show="!loadingData && foto.length > 0" x-cloak class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <template x-for="item in foto" :key="item.id_foto">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
          <div class="relative aspect-[4/3] bg-slate-100">
            <img :src="BASE_URL + '/uploads/' + item.nama_file" :alt="item.nama_foto || 'Foto tipe kamar'" class="h-full w-full object-cover">
            <div x-show="isThumbnail(item)" x-cloak class="absolute left-3 top-3">
              <span class="inline-flex rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-white">Thumbnail</span>
            </div>
          </div>
          <div class="p-4">
            <button type="button" x-show="!isThumbnail(item)" @click="setThumbnail(item.id_foto)" class="btn-secondary w-full">Jadikan Thumbnail</button>
            <div x-show="isThumbnail(item)" x-cloak class="w-full py-2 text-center text-sm font-medium text-primary">✓ Thumbnail</div>
            <button type="button" @click="hapus(item.id_foto)" class="btn-secondary mt-2 w-full text-red-600">Hapus Foto</button>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

<script>
  function tipeKamarFotoPage() {
    return {
      id: utils.getQuery('id_tipe_kamar') || '',
      loading: false,
      loadingData: true,
      selectedFile: null,
      preview: null,
      tipe: {
        nama_tipe: '',
        nama_kos: '',
        kapasitas: 0
      },
      foto: [],

      async init() {
        if (!this.id) {
          window.location.href = BASE_URL + '/pemilik/tipe-kamar';
          return;
        }
        try {
          const res = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + this.id, false);
          this.tipe = res.data || this.tipe;
          await this.loadFoto();
        } catch (error) {
          console.error(error);
        }
      },

      async loadFoto() {
        this.loadingData = true;
        try {
          const res = await API.get('/pemilik/tipe-kamar/foto?id_tipe_kamar=' + this.id, false);
          this.foto = res.data || [];
        } catch (error) {
          console.error(error);
        } finally {
          this.loadingData = false;
        }
      },

      previewFile(event) {
        const file = event.target.files[0];
        if (!file) {
          this.selectedFile = null;
          this.preview = null;
          return;
        }
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
          this.$refs.file.value = '';
          this.selectedFile = null;
          this.preview = null;
          Alpine.store('ui').toast('Format foto harus JPG, PNG, atau WEBP.', 'warning');
          return;
        }
        if (file.size > 10 * 1024 * 1024) {
          this.$refs.file.value = '';
          this.selectedFile = null;
          this.preview = null;
          Alpine.store('ui').toast('Ukuran foto maksimal 10 MB.', 'warning');
          return;
        }
        this.selectedFile = file;
        const reader = new FileReader();
        reader.onload = (e) => this.preview = e.target.result;
        reader.readAsDataURL(file);
      },

      async upload() {
        if (!this.selectedFile) {
          Alpine.store('ui').toast('Silakan pilih foto terlebih dahulu.', 'warning');
          return;
        }
        this.loading = true;
        try {
          const formData = new FormData();
          formData.append('foto', this.selectedFile);
          const res = await API.post('/pemilik/tipe-kamar/' + this.id + '/foto', formData);
          if (res.success) {
            Alpine.store('ui').toast('Foto berhasil diupload.', 'success');
            this.$refs.file.value = '';
            this.selectedFile = null;
            this.preview = null;
            await this.loadFoto();
            if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
              window.location.href = BASE_URL + '/pemilik/kamar?onboarding=1';
            }
          }
        } catch (error) {
          console.error(error);
        } finally {
          this.loading = false;
        }
      },

      async setThumbnail(id_foto) {
        try {
          const res = await API.put('/pemilik/tipe-kamar/foto/' + id_foto + '/thumbnail', null);
          if (res.success) {
            Alpine.store('ui').toast('Thumbnail berhasil diubah.', 'success');
            await this.loadFoto();
          }
        } catch (error) {
          console.error(error);
        }
      },

      async hapus(id_foto) {
        const ok = await Alpine.store('ui').confirm('Yakin ingin menghapus foto ini?');
        if (!ok) return;
        try {
          const res = await API.delete('/pemilik/tipe-kamar/foto/' + id_foto, null);
          if (res.success) {
            Alpine.store('ui').toast('Foto berhasil dihapus.', 'success');
            await this.loadFoto();
          }
        } catch (error) {
          console.error(error);
        }
      },

      isThumbnail(item) {
        return Number(item.is_thumbnail) === 1;
      }
    };
  }
</script>
