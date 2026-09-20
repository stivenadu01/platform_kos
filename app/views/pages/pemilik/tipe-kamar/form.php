<div x-data="tipeKamarForm()" x-init="init()" class="mx-auto max-w-4xl space-y-6">
  <div>
    <a :href="backUrl" @click.prevent="utils.goBack($el.href)" class="owner-back-link"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</a>
    <h2 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl" x-text="id ? 'Kelola Tipe Kamar' : 'Tambah Tipe Kamar'"></h2>
  </div>

  <div x-show="loading" class="card p-10 text-center text-sm text-slate-500">Memuat data tipe kamar...</div>
  <form x-show="!loading" x-cloak @submit.prevent="saveType" class="card space-y-6 border border-slate-200 shadow-sm">
    <nav class="grid grid-cols-2 gap-2 border-b border-slate-200 pb-5 sm:grid-cols-4" aria-label="Tahapan tipe kamar">
      <template x-for="(label, index) in steps" :key="label"><div class="flex items-center gap-2 rounded-xl px-3 py-2" :class="step === index + 1 ? 'bg-primary-soft text-primary' : step > index + 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400'"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="step === index + 1 ? 'bg-primary text-white' : step > index + 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500'" x-text="step > index + 1 ? '✓' : index + 1"></span><span class="text-xs font-semibold sm:text-sm" x-text="label"></span></div></template>
    </nav>
    <div x-show="step === 1" x-cloak data-step-panel="1" data-help="help-tipe-form-main" class="grid gap-5 md:grid-cols-2">
      <div class="form-group"><label class="label">Kos <span class="text-red-500">*</span></label><select data-onboarding="tipe-field-kos" x-model="form.id_kos" class="select" required>
          <option value="">Pilih kos</option><template x-for="kos in kosList" :key="kos.id_kos">
            <option :value="kos.id_kos" x-text="kos.nama_kos"></option>
          </template>
        </select></div>
      <div class="form-group"><label class="label">Nama Tipe <span class="text-red-500">*</span></label><input data-onboarding="tipe-field-nama" x-model="form.nama_tipe" class="input" maxlength="100" placeholder="Standard" required></div>
      <div class="form-group"><label class="label">Kapasitas <span class="text-red-500">*</span></label><input data-onboarding="tipe-field-kapasitas" type="number" x-model.number="form.kapasitas" min="1" max="255" class="input-number" required></div>
      <div class="form-group md:col-span-2"><label class="label">Deskripsi</label><textarea x-model="form.deskripsi" class="input resize-none" rows="3"></textarea></div>
    </div>
    <div x-show="step === 2" x-cloak data-step-panel="2" data-help="help-tipe-form-price" data-onboarding="tipe-field-harga">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold">Harga</h3>
          <p class="mt-1 text-sm text-slate-500">Harga penuh tipe kamar berdasarkan jumlah penghuni.</p>
        </div><button type="button" @click="addPrice()" class="btn-secondary">+ Tambah</button>
      </div>
      <div class="mt-4 space-y-3"><template x-for="(item, index) in harga" :key="index">
          <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]"><select x-model.number="item.jumlah_orang" class="select"><template x-for="number in availablePriceNumbers(index)" :key="number">
                <option :value="number" x-text="number + ' orang'"></option>
              </template></select><input type="number" x-model.number="item.harga_total" min="1000" step="1000" class="input-number" placeholder="700000" required><button type="button" @click="harga.splice(index, 1)" class="btn-danger">Hapus</button></div>
        </template></div>
    </div>
    <div x-show="step === 3" x-cloak data-step-panel="3" data-help="help-tipe-form-facility" data-onboarding="tipe-field-fasilitas">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h3 class="font-semibold">Fasilitas Kamar</h3>
          <p class="mt-1 text-sm text-slate-500">Hanya fasilitas berkategori kamar yang dapat dipilih.</p>
        </div>
      </div>
      <div class="mt-4 grid gap-2 sm:grid-cols-2"><template x-for="item in fasilitas" :key="item.id_fasilitas"><label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm cursor-pointer hover:bg-slate-50"><input type="checkbox" :value="Number(item.id_fasilitas)" x-model="fasilitasTerpilih" class="rounded border-slate-300 text-primary focus:ring-primary"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-primary" x-html="iconSvg(item.icon, 'h-4 w-4')"></span><span x-text="item.nama_fasilitas"></span></label></template></div>
    </div>
    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between"><a x-show="step === 1" :href="backUrl" class="btn-secondary text-center">Batal</a><button x-show="step > 1" x-cloak type="button" @click="previousStep()" class="btn-secondary">← Sebelumnya</button><button x-show="step < 3" type="button" @click="nextStep()" class="btn-primary sm:ml-auto">Selanjutnya →</button><button x-show="step === 3" x-cloak type="submit" data-help="help-tipe-form-save" data-onboarding="tipe-save" class="btn-primary" :disabled="saving" x-text="saving ? 'Menyimpan...' : (id ? 'Simpan Tipe' : 'Simpan & Lanjut ke Foto')"></button></div>
  </form>


</div>
<script>
  function tipeKamarForm() {
    return {
      get backUrl() { return BASE_URL + '/pemilik/kamar'; },
      iconPaths: <?= json_encode_safe(array_map(fn($v)=>$v['path'], masterIconCatalog()), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,
      iconSvg(i,c='h-4 w-4') { const p=this.iconPaths[i]||this.iconPaths['map-pin']; return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="${c}" aria-hidden="true">${p}</svg>`; },
      id: utils.getQuery('id_tipe_kamar') || '',
      loading: true,
      saving: false,
      step: 1,
      get steps() {
        return this.id ? ['Informasi', 'Harga', 'Fasilitas'] : ['Informasi', 'Harga', 'Fasilitas', 'Foto'];
      },
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
      validateStep() {
        const panel = this.$root.querySelector(`[data-step-panel="${this.step}"]`);
        const invalid = panel ? panel.querySelector(':invalid') : null;
        if (invalid) { invalid.reportValidity(); invalid.focus(); return false; }
        if (this.step === 2 && this.harga.length === 0) {
          Alpine.store('ui').toast('Tambahkan minimal satu harga tipe kamar.', 'warning');
          return false;
        }
        return true;
      },
      nextStep() {
        if (!this.validateStep()) return;
        this.step = Math.min(3, this.step + 1);
        this.$nextTick(() => this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      },
      previousStep() {
        this.step = Math.max(1, this.step - 1);
        this.$nextTick(() => this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' }));
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
        if (!this.validateStep()) return;
        this.saving = true;
        try {
          const isNew = !this.id;
          const payload = {
            ...this.form,
            id_tipe_kamar: this.id,
            harga: this.harga,
            id_fasilitas: this.fasilitasTerpilih
          };
          const res = this.id ? await API.put('/pemilik/tipe-kamar', payload) : await API.post('/pemilik/tipe-kamar', payload);
          if (isNew) this.id = res.data.id_tipe_kamar;
          if (isNew) {
            window.location.href = BASE_URL + '/pemilik/tipe-kamar/foto?id_tipe_kamar=' + encodeURIComponent(this.id) + '&wizard=1';
          } else if (localStorage.getItem('betakos_owner_onboarding_active_v3') === '1') {
            window.location.href = BASE_URL + '/pemilik/kamar?onboarding=1';
          } else {
            window.location.href = this.backUrl;
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
