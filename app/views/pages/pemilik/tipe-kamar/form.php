<div x-data="tipeKamarForm()" x-init="init()" class="mx-auto max-w-4xl space-y-6">
  <div>
    <a href="<?= BASE_URL ?>/pemilik/tipe-kamar" class="text-sm text-slate-500 hover:text-primary">← Kembali ke tipe kamar</a>
    <h2 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl" x-text="id ? 'Kelola Tipe Kamar' : 'Tambah Tipe Kamar'"></h2>
  </div>

  <div x-show="loading" class="card p-10 text-center text-sm text-slate-500">Memuat data tipe kamar...</div>
  <form x-show="!loading" x-cloak @submit.prevent="saveType" class="card space-y-6 border border-slate-200 shadow-sm">
    <div class="grid gap-5 md:grid-cols-2">
      <div class="form-group"><label class="label">Kos <span class="text-red-500">*</span></label><select data-onboarding="tipe-field-kos" x-model="form.id_kos" class="input" required>
          <option value="">Pilih kos</option><template x-for="kos in kosList" :key="kos.id_kos">
            <option :value="kos.id_kos" x-text="kos.nama_kos"></option>
          </template>
        </select></div>
      <div class="form-group"><label class="label">Nama Tipe <span class="text-red-500">*</span></label><input data-onboarding="tipe-field-nama" x-model="form.nama_tipe" class="input" maxlength="100" placeholder="Standard" required></div>
      <div class="form-group"><label class="label">Kapasitas <span class="text-red-500">*</span></label><input data-onboarding="tipe-field-kapasitas" type="number" x-model.number="form.kapasitas" min="1" max="255" class="input" required></div>
      <div class="form-group md:col-span-2"><label class="label">Deskripsi</label><textarea x-model="form.deskripsi" class="input resize-none" rows="3"></textarea></div>
    </div>
    <div data-onboarding="tipe-field-harga" class="border-t border-slate-200 pt-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold">Harga</h3>
          <p class="mt-1 text-sm text-slate-500">Harga penuh tipe kamar berdasarkan jumlah penghuni.</p>
        </div><button type="button" @click="addPrice()" class="btn-secondary">+ Tambah</button>
      </div>
      <div class="mt-4 space-y-3"><template x-for="(item, index) in harga" :key="index">
          <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]"><select x-model.number="item.jumlah_orang" class="input"><template x-for="number in availablePriceNumbers(index)" :key="number">
                <option :value="number" x-text="number + ' orang'"></option>
              </template></select><input type="number" x-model.number="item.harga_total" min="0" step="1000" class="input" placeholder="700000" required><button type="button" @click="harga.splice(index, 1)" class="btn-danger">Hapus</button></div>
        </template></div>
    </div>
    <div data-onboarding="tipe-field-fasilitas" class="border-t border-slate-200 pt-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold">Fasilitas Kamar</h3>
          <p class="mt-1 text-sm text-slate-500">Hanya fasilitas berkategori kamar yang dapat dipilih.</p>
        </div>
      </div>
      <div class="mt-4 grid gap-2 sm:grid-cols-2"><template x-for="item in fasilitas" :key="item.id_fasilitas"><label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm"><input type="checkbox" :value="Number(item.id_fasilitas)" x-model="fasilitasTerpilih"><span x-text="item.nama_fasilitas"></span></label></template></div>
    </div>
    <div class="flex justify-end gap-3 border-t border-slate-200 pt-5"><a href="<?= BASE_URL ?>/pemilik/tipe-kamar" class="btn-secondary">Batal</a><button type="submit" data-onboarding="tipe-save" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : 'Simpan Tipe'"></button></div>
  </form>


</div>
<script>
  function tipeKamarForm() {
    return {
      id: utils.getQuery('id_tipe_kamar') || '',
      loading: true,
      saving: false,
      kosList: [],
      fasilitas: [],
      fasilitasTerpilih: [],
      harga: [],
      kamar: [],
      form: {
        id_kos: '',
        nama_tipe: '',
        kapasitas: 1,
        deskripsi: ''
      },
      get availableCount() {
        return this.kamar.filter(item => item.status === 'tersedia').length;
      },
      async init() {
        try {
          const kos = await API.get('/pemilik/kamar/kos', false);
          this.kosList = kos.data || [];
          const facilities = await API.get('/fasilitas?kategori=kamar', false);
          this.fasilitas = (facilities.data || []).filter(item => item.kategori === 'kamar');
          if (this.id) {
            const res = await API.get('/pemilik/tipe-kamar/show?id_tipe_kamar=' + this.id, false);
            const data = res.data;
            this.form = {
              id_kos: String(data.id_kos),
              nama_tipe: data.nama_tipe,
              kapasitas: Number(data.kapasitas),
              deskripsi: data.deskripsi || ''
            };
            this.harga = data.harga || [];
            this.fasilitasTerpilih = (data.fasilitas || []).map(item => Number(item.id_fasilitas));
            this.kamar = data.kamar || [];
          }
        } catch (error) {
          console.error(error);
        } finally {
          this.loading = false;
        }
      },
      availablePriceNumbers(index) {
        const used = this.harga.filter((_, i) => i !== index).map(item => Number(item.jumlah_orang));
        return Array.from({
          length: this.form.kapasitas
        }, (_, i) => i + 1).filter(number => !used.includes(number) || number === Number(this.harga[index]?.jumlah_orang));
      },
      addPrice() {
        if (this.harga.length >= this.form.kapasitas) return;
        const used = this.harga.map(item => Number(item.jumlah_orang));
        const number = Array.from({
          length: this.form.kapasitas
        }, (_, i) => i + 1).find(value => !used.includes(value));
        this.harga.push({
          jumlah_orang: number,
          harga_total: ''
        });
      },
      async saveType() {
        this.saving = true;
        try {
          const payload = {
            ...this.form,
            id_tipe_kamar: this.id
          };
          const res = this.id ? await API.put('/pemilik/tipe-kamar', payload) : await API.post('/pemilik/tipe-kamar', payload);
          if (!this.id) this.id = res.data.id_tipe_kamar;
          await API.put('/pemilik/tipe-kamar/harga', {
            id_tipe_kamar: this.id,
            harga: this.harga
          });
          await API.put('/pemilik/tipe-kamar/fasilitas', {
            id_tipe_kamar: this.id,
            id_fasilitas: this.fasilitasTerpilih
          });
          if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
            window.location.href = BASE_URL + '/pemilik/tipe-kamar?onboarding=1';
          } else {
            window.location.href = BASE_URL + '/pemilik/tipe-kamar';
          }
        } catch (error) {
          console.error(error);
        } finally {
          this.saving = false;
        }
      },
    };
  }
</script>