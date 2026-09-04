<div
  x-data="hargaKamarPage()"
  x-init="init()"
  class="max-w-3xl mx-auto space-y-6">

  <div>
    <a
      href="<?= BASE_URL ?>/pemilik/kamar"
      class="text-sm text-primary hover:underline">
      ← Kembali ke kamar
    </a>

    <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900">
      Atur Harga Kamar
    </h2>

    <p
      x-show="found"
      class="mt-1 text-sm text-slate-500"
      x-text="roomLabel">
    </p>
  </div>

  <div
    x-show="loadingData"
    class="card text-center py-12 text-sm text-slate-500">
    Memuat konfigurasi harga...
  </div>

  <form
    x-show="!loadingData && found"
    x-cloak
    @submit.prevent="submit"
    class="card border border-slate-200 shadow-sm space-y-6">

    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="font-semibold text-slate-900">
          Harga berdasarkan jumlah penghuni
        </h3>
        <p class="mt-1 text-sm text-slate-500">
          Konfigurasi ini digunakan saat sistem membuat tagihan otomatis.
        </p>
      </div>

      <button
        type="button"
        @click="addHarga()"
        class="btn-secondary whitespace-nowrap"
        :disabled="harga.length >= kapasitas">
        + Tambah Harga
      </button>
    </div>

    <div
      x-show="harga.length === 0"
      class="rounded-lg border border-dashed border-slate-300 p-6 text-center">
      <p class="text-sm text-slate-500">
        Belum ada konfigurasi harga.
      </p>
      <button
        type="button"
        @click="addHarga()"
        class="btn-primary mt-4">
        Tambahkan Harga
      </button>
    </div>

    <div
      x-show="harga.length > 0"
      class="space-y-3">
      <template x-for="(item, index) in harga" :key="index">
        <div
          class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-3 items-end rounded-lg border border-slate-200 p-4">
          <div class="form-group">
            <label class="label">Jumlah Orang</label>
            <select
              x-model.number="item.jumlah_orang"
              class="select">
              <template x-for="jumlah in availableJumlahOrang(index)" :key="jumlah">
                <option :value="jumlah" x-text="jumlah + ' orang'"></option>
              </template>
            </select>
          </div>

          <div class="form-group">
            <label class="label">Harga Total</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
              <input
                type="number"
                x-model.number="item.harga_total"
                min="0"
                step="1000"
                class="input-number pl-11"
                placeholder="700000"
                required>
            </div>
          </div>

          <button
            type="button"
            @click="removeHarga(index)"
            class="btn-danger">
            Hapus
          </button>
        </div>
      </template>
    </div>

    <p class="text-xs text-slate-400">
      Kapasitas kamar:
      <span x-text="kapasitas + ' orang'"></span>.
    </p>

    <div class="flex justify-end gap-3 pt-2">
      <a
        href="<?= BASE_URL ?>/pemilik/kamar"
        class="btn-secondary">
        Batal
      </a>
      <button
        type="submit"
        class="btn-primary"
        :disabled="saving">
        <span x-show="!saving">Simpan Harga</span>
        <span x-show="saving" x-cloak>Menyimpan...</span>
      </button>
    </div>
  </form>

  <div
    x-show="!loadingData && !found"
    x-cloak
    class="card text-center py-12">
    <div class="text-4xl mb-4">🔍</div>
    <h3 class="font-semibold">Kamar tidak ditemukan</h3>
    <a
      href="<?= BASE_URL ?>/pemilik/kamar"
      class="btn-primary inline-flex mt-5">
      Kembali
    </a>
  </div>
</div>

<script>
  function hargaKamarPage() {
    return {
      idKamar: null,
      roomLabel: '',
      kapasitas: 1,
      harga: [],
      loadingData: true,
      saving: false,
      found: false,

      async init() {
        this.idKamar = utils.getQuery('id_kamar');

        if (!this.idKamar) {
          this.loadingData = false;
          return;
        }

        try {
          const res = await API.get(
            '/pemilik/kamar/show?id_kamar=' + encodeURIComponent(this.idKamar),
            false
          );

          if (res.success && res.data) {
            this.kapasitas = Number(res.data.kapasitas) || 1;
            this.roomLabel = res.data.nama_kos + ' · Kamar ' + res.data.nomor_kamar;

            this.harga = Array.isArray(res.data.harga) ?
              res.data.harga.map(item => ({
                jumlah_orang: Number(item.jumlah_orang),
                harga_total: Number(item.harga_total)
              })) :
              [];

            this.found = true;
          }
        } catch (error) {
          console.error('Gagal memuat harga kamar:', error);
        } finally {
          this.loadingData = false;
        }
      },

      availableJumlahOrang(index) {
        const used = this.harga
          .filter((_, currentIndex) => currentIndex !== index)
          .map(item => Number(item.jumlah_orang));
        const result = [];

        for (let jumlah = 1; jumlah <= this.kapasitas; jumlah++) {
          if (!used.includes(jumlah)) {
            result.push(jumlah);
          }
        }

        const current = Number(this.harga[index]?.jumlah_orang);
        if (current >= 1 && current <= this.kapasitas && !result.includes(current)) {
          result.push(current);
          result.sort((first, second) => first - second);
        }

        return result;
      },

      addHarga() {
        if (this.harga.length >= this.kapasitas) {
          Alpine.store('ui').toast('Jumlah harga sudah mencapai kapasitas kamar.', 'warning');
          return;
        }

        const used = this.harga.map(item => Number(item.jumlah_orang));
        let jumlah = 1;
        while (used.includes(jumlah) && jumlah <= this.kapasitas) {
          jumlah++;
        }

        this.harga.push({
          jumlah_orang: jumlah,
          harga_total: ''
        });
      },

      removeHarga(index) {
        this.harga.splice(index, 1);
      },

      async submit() {
        for (const item of this.harga) {
          const jumlah = Number(item.jumlah_orang);
          const harga = Number(item.harga_total);

          if (jumlah < 1 || jumlah > this.kapasitas || !Number.isFinite(harga) || harga < 0) {
            Alpine.store('ui').toast('Harga kamar harus berupa angka yang valid.', 'warning');
            return;
          }
        }

        this.saving = true;

        try {
          await API.put('/pemilik/kamar/harga', {
            id_kamar: this.idKamar,
            harga: this.harga.map(item => ({
              jumlah_orang: Number(item.jumlah_orang),
              harga_total: Number(item.harga_total)
            }))
          });

          Alpine.store('ui').toast('Harga kamar berhasil disimpan.', 'success');
          setTimeout(() => {
            window.location.href = BASE_URL + '/pemilik/kamar';
          }, 500);
        } catch (error) {
          console.error('Gagal menyimpan harga kamar:', error);
        } finally {
          this.saving = false;
        }
      }
    };
  }
</script>