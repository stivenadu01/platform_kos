<div
  x-data="kosFotoPage()"
  class="max-w-6xl mx-auto space-y-6">

  <!-- HEADER -->
  <div>

    <a
      href="<?= BASE_URL ?>/pemilik/kos"
      class="text-sm text-primary hover:underline">
      ← Kembali ke Kos Saya
    </a>

    <div class="mt-3">

      <h2 class="text-2xl font-bold text-slate-900">
        Foto Kos
      </h2>

      <p class="mt-1 text-sm text-slate-500">
        Kelola foto yang akan ditampilkan pada kos Anda.
      </p>

    </div>

  </div>


  <!-- INFORMASI KOS -->
  <div
    class="card border border-slate-200 shadow-sm p-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

      <div>

        <h3
          class="font-semibold text-lg text-slate-900"
          x-text="kos.nama_kos || 'Memuat data kos...'">
        </h3>

        <p
          class="mt-1 text-sm text-slate-500"
          x-text="kos.alamat || ''">
        </p>

      </div>

      <a
        href="<?= BASE_URL ?>/pemilik/kos/edit?id=<?= (int) $kos['id_kos'] ?>"
        class="btn-secondary text-center">
        Edit Kos
      </a>

    </div>

  </div>


  <!-- UPLOAD -->
  <div
    class="card border border-slate-200 shadow-sm p-6">

    <div>

      <h3 class="font-semibold text-slate-900">
        Tambah Foto
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Upload foto kamar, fasilitas, lingkungan, atau bagian lain dari kos.
      </p>

    </div>


    <form
      @submit.prevent="upload"
      class="mt-5">

      <div
        class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-primary transition">

        <input
          type="file"
          x-ref="file"
          accept="image/jpeg,image/png,image/webp"
          @change="previewFile"
          class="hidden">


        <button
          data-onboarding="kos-foto-pilih"
          type="button"
          @click="$refs.file.click()"
          class="btn-secondary">

          Pilih Foto

        </button>


        <p class="mt-3 text-sm text-slate-500">
          JPG, PNG, atau WEBP
        </p>

        <p class="text-xs text-slate-400 mt-1">
          Maksimal 10 MB
        </p>


        <!-- FILE NAME -->
        <div
          x-show="selectedFile"
          x-cloak
          class="mt-4">

          <p
            class="text-sm font-medium text-slate-700"
            x-text="selectedFile ? selectedFile.name : ''">
          </p>

        </div>


        <!-- PREVIEW -->
        <div
          x-show="preview"
          x-cloak
          class="mt-5">

          <img
            :src="preview"
            alt="Preview foto"
            class="mx-auto max-h-64 rounded-xl object-contain border border-slate-200">

        </div>

      </div>


      <div class="flex justify-end mt-5">

        <button
          data-onboarding="kos-foto-upload"
          type="submit"
          class="btn-primary"
          :disabled="loading || !selectedFile">

          <span x-show="!loading">
            Upload Foto
          </span>

          <span
            x-show="loading"
            x-cloak>
            Mengupload...
          </span>

        </button>

      </div>

    </form>

  </div>


  <!-- DAFTAR FOTO -->
  <div
    class="card border border-slate-200 shadow-sm p-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

      <div>

        <h3 class="font-semibold text-slate-900">
          Foto Kos
        </h3>

        <p class="mt-1 text-sm text-slate-500">
          Foto utama akan digunakan sebagai gambar utama kos.
        </p>

      </div>


      <div
        class="text-sm text-slate-500">

        <span x-text="foto.length"></span>
        foto

      </div>

    </div>


    <!-- LOADING -->
    <div
      x-show="loadingData"
      x-cloak
      class="py-14 text-center">

      <div class="text-sm text-slate-500">
        Memuat foto...
      </div>

    </div>


    <!-- EMPTY -->
    <div
      x-show="!loadingData && foto.length === 0"
      x-cloak
      class="py-14 text-center">

      <div class="text-4xl mb-4">
        🖼️
      </div>

      <h3 class="font-semibold text-slate-900">
        Belum ada foto
      </h3>

      <p class="mt-1 text-sm text-slate-500">
        Upload foto pertama untuk kos ini.
      </p>

    </div>


    <!-- GRID -->
    <div
      x-show="!loadingData && foto.length > 0"
      x-cloak
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-6">

      <template
        x-for="item in foto"
        :key="item.id_foto">

        <div
          class="border border-slate-200 rounded-xl overflow-hidden bg-white">

          <!-- IMAGE -->
          <div class="relative aspect-[4/3] bg-slate-100">

            <img
              :src="BASE_URL + '/uploads/' + item.nama_file"
              :alt="item.nama_foto || 'Foto kos'"
              class="w-full h-full object-cover">


            <!-- BADGE UTAMA -->
            <div
              x-show="isUtama(item)"
              class="absolute top-3 left-3">

              <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary text-white">

                Foto Utama

              </span>

            </div>

          </div>


          <!-- ACTION -->
          <div class="p-4">

            <div
              class="flex flex-col gap-2">

              <!-- FOTO UTAMA -->
              <button
                type="button"
                x-show="!isUtama(item)"
                @click="setUtama(item.id_foto)"
                class="btn-secondary w-full">

                Jadikan Foto Utama

              </button>


              <div
                x-show="isUtama(item)"
                class="w-full text-center text-sm font-medium text-primary py-2">

                ✓ Foto utama

              </div>


              <!-- HAPUS -->
              <button
                type="button"
                @click="hapus(item.id_foto)"
                class="btn-secondary w-full text-red-600">

                Hapus Foto

              </button>

            </div>

          </div>

        </div>

      </template>

    </div>

  </div>

</div>


<script>
  function kosFotoPage() {

    return {

      loading: false,
      loadingData: true,

      selectedFile: null,
      preview: null,

      kos: {
        id_kos: <?= (int) $kos['id_kos'] ?>,
        nama_kos: <?= json_encode($kos['nama_kos'] ?? '') ?>,
        alamat: <?= json_encode($kos['alamat'] ?? '') ?>
      },

      foto: [],


      init() {

        this.loadFoto();

      },


      /*
       * Ambil daftar foto kos
       */
      async loadFoto() {

        this.loadingData = true;

        try {

          const res = await API.get(
            '/pemilik/kos/foto/<?= (int) $kos['id_kos'] ?>'
          );


          if (res.success) {
            this.foto = res.data.foto || [];
          }

        } catch (error) {

          console.error(error);

        } finally {
          this.loadingData = false;

        }

      },


      /*
       * Preview file sebelum upload
       */
      previewFile(event) {

        const file =
          event.target.files[0];


        if (!file) {

          this.selectedFile = null;
          this.preview = null;

          return;

        }


        /*
         * Validasi format
         */
        const allowed = [
          'image/jpeg',
          'image/png',
          'image/webp'
        ];


        if (!allowed.includes(file.type)) {

          this.$refs.file.value = '';
          this.selectedFile = null;
          this.preview = null;

          Alpine.store('ui').toast(
            'Format foto harus JPG, PNG, atau WEBP.',
            'warning'
          );

          return;

        }


        /*
         * Maksimal 10 MB
         */
        if (file.size > 10 * 1024 * 1024) {

          this.$refs.file.value = '';
          this.selectedFile = null;
          this.preview = null;

          Alpine.store('ui').toast(
            'Ukuran foto maksimal 10 MB.',
            'warning'
          );

          return;

        }


        this.selectedFile = file;


        /*
         * Preview
         */
        const reader = new FileReader();

        reader.onload = (e) => {

          this.preview = e.target.result;

        };

        reader.readAsDataURL(file);

      },


      /*
       * Upload foto
       */
      async upload() {

        if (!this.selectedFile) {

          Alpine.store('ui').toast(
            'Silakan pilih foto terlebih dahulu.',
            'warning'
          );

          return;

        }


        this.loading = true;


        try {

          const formData =
            new FormData();


          formData.append(
            'foto',
            this.selectedFile
          );


          const res = await API.post(
            '/pemilik/kos/foto/<?= (int) $kos['id_kos'] ?>',
            formData
          );


          if (res.success) {

            Alpine.store('ui').toast(
              'Foto berhasil diupload.',
              'success'
            );


            /*
             * Reset input
             */
            this.$refs.file.value = '';
            this.selectedFile = null;
            this.preview = null;


            /*
             * Refresh daftar foto
             */
            await this.loadFoto();

            if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
              window.location.href = BASE_URL + '/pemilik/kos?onboarding=1';
            }

          }

        } catch (error) {

          console.error(error);

        } finally {

          this.loading = false;

        }

      },


      /*
       * Jadikan foto utama
       */
      async setUtama(id_foto) {

        try {

          const res = await API.put(
            '/pemilik/kos/foto/<?= (int) $kos['id_kos'] ?>/' +
            id_foto +
            '/thumbnail',
            null
          );


          if (res.success) {

            Alpine.store('ui').toast(
              'Foto utama berhasil diubah.',
              'success'
            );


            await this.loadFoto();

          }

        } catch (error) {

          console.error(error);

        }

      },


      /*
       * Hapus foto
       */
      async hapus(id_foto) {

        const ok =
          await Alpine.store('ui').confirm(
            'Yakin ingin menghapus foto ini?'
          );


        if (!ok) return;


        try {

          const res = await API.delete(
            '/pemilik/kos/foto/<?= (int) $kos['id_kos'] ?>/' +
            id_foto,
            null
          );


          if (res.success) {

            Alpine.store('ui').toast(
              'Foto berhasil dihapus.',
              'success'
            );


            await this.loadFoto();

          }

        } catch (error) {

          console.error(error);

        }

      },


      /*
       * Cek foto utama
       */
      isUtama(item) {

        return Number(item.is_thumbnail) === 1;

      },

    }

  }
</script>