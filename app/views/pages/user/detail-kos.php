<?php
$photos = $kos['foto'] ?? [];
$rooms = $kos['kamar'] ?? [];
$facilities = $kos['fasilitas'] ?? [];
$mainPhoto = $photos[0]['nama_file'] ?? null;
$phone = preg_replace('/\D+/', '', (string)($kos['no_hp_pemilik'] ?? ''));
if ($phone !== '' && str_starts_with($phone, '0')) {
  $phone = '62' . substr($phone, 1);
}
$waText = 'Halo, saya melihat kos ' . ($kos['nama_kos'] ?? '') . ' di BetaKos. Saya ingin menanyakan ketersediaan kamar.';
$waUrl = $phone !== '' ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($waText) : '';
$shareUrl = BASE_URL . '/kos/' . (int)$kos['id_kos'];
$isPelanggan = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pelanggan';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  #detail-kos-map { z-index: 1; }
  .leaflet-pane, .leaflet-control { z-index: 2; }
</style>

<div x-data="kosDetailPage()" class="bg-slate-50">
  <section class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="<?= BASE_URL ?>/cari-kos" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary">
          <span>←</span> Kembali ke pencarian
        </a>
        <div class="flex flex-wrap items-center gap-2">
          <?php if ($isPelanggan): ?>
            <a href="<?= BASE_URL ?>/user/laporan" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary">
              Riwayat Laporan
            </a>
            <button @click="reportOpen = true" type="button" class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
              ⚑ Laporkan Kos
            </button>
          <?php else: ?>
            <a href="<?= BASE_URL ?>/login" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-primary hover:text-primary">
              Login untuk melapor
            </a>
          <?php endif; ?>
          <button @click="share()" type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary">
            ↗ Bagikan
          </button>
        </div>
      </div>
    </div>
  </section>

  <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
      <?php if ($photos): ?>
        <div class="grid min-h-[280px] gap-1 bg-slate-100 lg:grid-cols-[1.65fr_1fr]">
          <button type="button" @click="openGallery(0)" class="group relative min-h-[280px] overflow-hidden lg:min-h-[430px]">
            <img src="<?= BASE_URL ?>/uploads<?= htmlspecialchars($mainPhoto) ?>" alt="<?= htmlspecialchars($kos['nama_kos']) ?>" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
            <span class="absolute bottom-4 left-4 rounded-lg bg-black/60 px-3 py-2 text-xs font-semibold text-white">Lihat semua foto</span>
          </button>
          <div class="hidden grid-cols-2 gap-1 lg:grid">
            <?php foreach (array_slice($photos, 1, 4) as $i => $photo): ?>
              <button type="button" @click="openGallery(<?= $i + 1 ?>)" class="overflow-hidden">
                <img src="<?= BASE_URL ?>/uploads<?= htmlspecialchars($photo['nama_file']) ?>" alt="<?= htmlspecialchars($kos['nama_kos']) ?>" class="h-full min-h-[140px] w-full object-cover transition hover:scale-[1.02]">
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="flex min-h-[280px] items-center justify-center bg-slate-100 text-sm text-slate-400 lg:min-h-[430px]">Foto kos belum tersedia</div>
      <?php endif; ?>

      <div class="grid gap-8 p-5 sm:p-7 lg:grid-cols-[1fr_360px] lg:p-8">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold capitalize text-primary"><?= htmlspecialchars($kos['jenis']) ?></span>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"><?= (int)$kos['kamar_tersedia'] ?> kamar tersedia</span>
          </div>
          <h1 class="mt-3 font-[Poppins] text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"><?= htmlspecialchars($kos['nama_kos']) ?></h1>
          <p class="mt-2 flex items-start gap-2 text-sm leading-6 text-slate-500"><span>⌖</span><span><?= nl2br(htmlspecialchars($kos['alamat'])) ?></span></p>

          <div class="mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Tentang kos</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600"><?= htmlspecialchars(trim($kos['deskripsi'] ?? '') ?: 'Pemilik belum menambahkan deskripsi kos.') ?></p>
          </div>

          <div class="mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Fasilitas</h2>
            <?php if ($facilities): ?>
              <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <?php foreach ($facilities as $facility): ?>
                  <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                    <span class="mr-2 text-primary">✓</span><?= htmlspecialchars($facility['nama_fasilitas']) ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="mt-3 text-sm text-slate-500">Belum ada fasilitas yang dicantumkan.</p>
            <?php endif; ?>
          </div>

          <div class="mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Lokasi</h2>
            <div id="detail-kos-map" class="mt-4 h-[300px] overflow-hidden rounded-2xl border border-slate-200 sm:h-[360px]"></div>
            <a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($kos['latitude'] . ',' . $kos['longitude']) ?>" class="mt-3 inline-flex text-sm font-semibold text-primary hover:text-primary-dark">Buka di Google Maps →</a>
          </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Harga kamar</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">Harga ditampilkan untuk setiap kamar dan berdasarkan jumlah penghuni. Harga adalah total kamar per bulan.</p>

            <div class="mt-5">
              <div class="grid grid-cols-2 gap-2" role="group" aria-label="Filter kamar">
                <button type="button"
                        @click="setRoomFilter('semua')"
                        :class="roomFilter === 'semua' ? 'border-primary bg-primary-soft text-primary' : 'border-slate-200 bg-white text-slate-600 hover:border-primary/40 hover:text-primary'"
                        class="rounded-xl border px-3 py-2 text-xs font-semibold transition">
                  Semua
                </button>
                <button type="button"
                        @click="setRoomFilter('tersedia')"
                        :class="roomFilter === 'tersedia' ? 'border-primary bg-primary-soft text-primary' : 'border-slate-200 bg-white text-slate-600 hover:border-primary/40 hover:text-primary'"
                        class="rounded-xl border px-3 py-2 text-xs font-semibold transition">
                  Tersedia
                </button>
              </div>

              <div class="mt-3 flex items-center justify-between gap-3">
                <p class="text-xs text-slate-500">
                  Menampilkan <span class="font-semibold text-slate-700" x-text="paginatedRooms.length"></span>
                  dari <span class="font-semibold text-slate-700" x-text="filteredRooms.length"></span> kamar
                </p>
                <p class="text-xs text-slate-400" x-show="filteredRooms.length > 0">
                  Halaman <span x-text="currentRoomPage"></span> dari <span x-text="totalRoomPages"></span>
                </p>
              </div>

              <div class="mt-3 space-y-3">
                <template x-for="room in paginatedRooms" :key="room.id_kamar">
                  <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <p class="font-semibold text-slate-900">
                          Kamar <span x-text="room.nomor_kamar"></span>
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                          <span x-text="room.tipe_kamar || 'Kamar'"></span>
                          · kapasitas <span x-text="room.kapasitas"></span> orang
                        </p>
                      </div>
                      <span
                        class="shrink-0 rounded-full px-2 py-1 text-[11px] font-semibold"
                        :class="room.status === 'tersedia'
                          ? 'bg-emerald-50 text-emerald-700'
                          : room.status === 'terisi'
                            ? 'bg-red-50 text-red-700'
                            : room.status === 'perbaikan'
                              ? 'bg-amber-50 text-amber-700'
                              : 'bg-slate-100 text-slate-600'"
                        x-text="room.status_label">
                      </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                      <div>
                        <p class="text-xs text-slate-500">Mulai dari</p>
                        <p class="font-bold text-slate-900" x-show="room.harga.length">
                          Rp <span x-text="formatRupiah(room.harga[0].harga_total)"></span><span class="font-normal text-xs text-slate-500">/bln</span>
                        </p>
                        <p class="text-xs text-slate-500" x-show="!room.harga.length">Harga kamar belum tersedia.</p>
                      </div>

                      <button type="button"
                              @click="toggleRoomPrice(room.id_kamar)"
                              class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-primary hover:text-primary">
                        <span x-text="expandedRooms.includes(room.id_kamar) ? 'Tutup harga ↑' : 'Lihat harga ↓'"></span>
                      </button>
                    </div>

                    <div x-show="expandedRooms.includes(room.id_kamar)" x-cloak x-transition class="mt-3 space-y-2">
                      <template x-for="price in room.harga" :key="price.jumlah_orang">
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                          <span class="text-slate-600" x-text="price.jumlah_orang + ' orang'"></span>
                          <span class="font-bold text-slate-900">
                            Rp <span x-text="formatRupiah(price.harga_total)"></span><span class="font-normal text-xs text-slate-500">/bln</span>
                          </span>
                        </div>
                      </template>
                      <p x-show="!room.harga.length" class="text-xs text-slate-500">Belum ada konfigurasi harga untuk kamar ini.</p>
                    </div>
                  </div>
                </template>

                <div x-show="filteredRooms.length === 0" x-cloak class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center">
                  <p class="text-sm font-semibold text-slate-700">Tidak ada kamar yang sesuai.</p>
                  <p class="mt-1 text-xs text-slate-500">Coba gunakan filter kamar lainnya.</p>
                </div>
              </div>

              <div x-show="totalRoomPages > 1" x-cloak class="mt-5 flex items-center justify-center gap-1" aria-label="Pagination kamar">
                <button type="button"
                        @click="goToRoomPage(currentRoomPage - 1)"
                        :disabled="currentRoomPage === 1"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 hover:border-primary hover:text-primary">
                  ←
                </button>
                <template x-for="page in roomPageNumbers" :key="page">
                  <button type="button"
                          @click="goToRoomPage(page)"
                          :class="currentRoomPage === page ? 'border-primary bg-primary text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-primary hover:text-primary'"
                          class="min-w-9 rounded-lg border px-3 py-2 text-sm font-semibold"
                          x-text="page">
                  </button>
                </template>
                <button type="button"
                        @click="goToRoomPage(currentRoomPage + 1)"
                        :disabled="currentRoomPage === totalRoomPages"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 disabled:cursor-not-allowed disabled:opacity-40 hover:border-primary hover:text-primary">
                  →
                </button>
              </div>
            </div>

            <?php if ($waUrl): ?>
              <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="mt-5 flex w-full items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-dark">Tanya Pemilik via WhatsApp</a>
            <?php else: ?>
              <div class="mt-5 rounded-xl bg-slate-50 p-3 text-center text-xs text-slate-500">Kontak pemilik belum tersedia.</div>
            <?php endif; ?>

            <div class="mt-3 rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-400">Pemilik</p>
              <p class="mt-1 text-sm font-semibold text-slate-800"><?= htmlspecialchars($kos['nama_pemilik'] ?? 'Pemilik kos') ?></p>
            </div>
          </div>
        </aside>
      </div>
    </section>
  </main>

  <?php if ($isPelanggan): ?>
    <div x-show="reportOpen" x-cloak @keydown.escape.window="reportOpen = false" class="fixed inset-0 z-[2100] flex items-end justify-center bg-slate-900/50 p-0 sm:items-center sm:p-5">
      <div class="absolute inset-0" @click="reportOpen = false"></div>
      <div class="relative w-full max-w-lg rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <div>
            <h2 class="font-bold text-slate-900">Laporkan Kos</h2>
            <p class="mt-1 text-xs text-slate-500">Bantu kami menjaga informasi BetaKos tetap akurat.</p>
          </div>
          <button type="button" @click="reportOpen = false" class="h-9 w-9 rounded-lg hover:bg-slate-100">✕</button>
        </div>
        <form @submit.prevent="submitReport" class="space-y-4 p-5 sm:p-6">
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs text-slate-400">Kos yang dilaporkan</p>
            <p class="mt-1 font-semibold text-slate-900"><?= htmlspecialchars($kos['nama_kos']) ?></p>
          </div>
          <div>
            <label class="label">Alasan laporan *</label>
            <select x-model="reportForm.alasan" class="input mt-1 w-full" required>
              <option value="">Pilih alasan</option>
              <option value="informasi_tidak_sesuai">Informasi tidak sesuai</option>
              <option value="foto_tidak_sesuai">Foto tidak sesuai</option>
              <option value="kos_sudah_tidak_tersedia">Kos sudah tidak tersedia</option>
              <option value="informasi_menyesatkan">Informasi menyesatkan</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div>
            <label class="label">Jelaskan masalahnya *</label>
            <textarea x-model="reportForm.deskripsi" class="input mt-1 w-full" rows="5" minlength="10" maxlength="2000" required placeholder="Contoh: Kos sudah tidak menerima penghuni, tetapi masih tampil tersedia..."></textarea>
            <p class="mt-1 text-xs text-slate-400">Minimal 10 karakter, maksimal 2.000 karakter.</p>
          </div>
          <div class="rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs leading-5 text-amber-800">Gunakan laporan hanya untuk informasi kos yang benar-benar bermasalah. Laporan akan diperiksa oleh Admin.</div>
          <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" @click="reportOpen = false" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary" :disabled="reportSaving" x-text="reportSaving ? 'Mengirim...' : 'Kirim Laporan'"></button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div x-show="galleryOpen" x-cloak @keydown.escape.window="galleryOpen = false" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/90 p-4">
    <button @click="galleryOpen = false" type="button" class="absolute right-4 top-4 rounded-full bg-white/10 px-4 py-2 text-white hover:bg-white/20">✕</button>
    <button @click="previousPhoto()" type="button" class="absolute left-3 rounded-full bg-white/10 px-4 py-3 text-2xl text-white hover:bg-white/20 sm:left-8">‹</button>
    <div class="max-h-[90vh] max-w-6xl">
      <img :src="galleryPhotos[galleryIndex]" alt="" class="max-h-[85vh] max-w-full rounded-xl object-contain">
      <p class="mt-3 text-center text-xs text-white/70" x-text="(galleryIndex + 1) + ' / ' + galleryPhotos.length"></p>
    </div>
    <button @click="nextPhoto()" type="button" class="absolute right-3 rounded-full bg-white/10 px-4 py-3 text-2xl text-white hover:bg-white/20 sm:right-8">›</button>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function kosDetailPage() {
  const photos = <?= json_encode(array_map(fn($p) => BASE_URL . '/uploads' . $p['nama_file'], $photos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  const rooms = <?= json_encode(array_map(function ($room) {
    $status = strtolower((string)($room['status'] ?? ''));
    $statusLabels = [
      'tersedia' => 'Tersedia',
      'terisi' => 'Terisi',
      'perbaikan' => 'Perbaikan',
      'nonaktif' => 'Nonaktif',
    ];
    $room['id_kamar'] = (int)($room['id_kamar'] ?? 0);
    $room['kapasitas'] = (int)($room['kapasitas'] ?? 0);
    $room['status'] = $status;
    $room['status_label'] = $statusLabels[$status] ?? ucfirst($status ?: 'Tidak diketahui');
    $room['harga'] = array_map(function ($price) {
      return [
        'jumlah_orang' => (int)($price['jumlah_orang'] ?? 0),
        'harga_total' => (float)($price['harga_total'] ?? 0),
      ];
    }, $room['harga'] ?? []);
    return $room;
  }, $rooms), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  return {
    galleryPhotos: photos,
    galleryIndex: 0,
    galleryOpen: false,
    rooms,
    // Saat pertama membuka detail kos, tampilkan hanya kamar yang tersedia.
    roomFilter: 'tersedia',
    currentRoomPage: 1,
    roomsPerPage: 10,
    expandedRooms: [],
    reportOpen: false,
    reportSaving: false,
    reportSuccess: false,
    reportForm: { alasan: '', deskripsi: '' },
    get filteredRooms() {
      return this.rooms.filter((room) => {
        if (this.roomFilter === 'tersedia') return room.status === 'tersedia';
        return true;
      });
    },
    get totalRoomPages() {
      return Math.max(1, Math.ceil(this.filteredRooms.length / this.roomsPerPage));
    },
    get paginatedRooms() {
      const start = (this.currentRoomPage - 1) * this.roomsPerPage;
      return this.filteredRooms.slice(start, start + this.roomsPerPage);
    },
    get roomPageNumbers() {
      return Array.from({ length: this.totalRoomPages }, (_, index) => index + 1);
    },
    setRoomFilter(filter) {
      this.roomFilter = filter;
      this.currentRoomPage = 1;
      this.expandedRooms = [];
    },
    goToRoomPage(page) {
      const nextPage = Math.min(Math.max(Number(page) || 1, 1), this.totalRoomPages);
      this.currentRoomPage = nextPage;
      this.expandedRooms = [];
    },
    toggleRoomPrice(idKamar) {
      const id = Number(idKamar);
      if (this.expandedRooms.includes(id)) {
        this.expandedRooms = this.expandedRooms.filter((item) => item !== id);
      } else {
        this.expandedRooms = [...this.expandedRooms, id];
      }
    },
    formatRupiah(value) {
      return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0
      }).format(Number(value) || 0);
    },
    openGallery(index) {
      if (!this.galleryPhotos.length) return;
      this.galleryIndex = index;
      this.galleryOpen = true;
    },
    nextPhoto() {
      if (!this.galleryPhotos.length) return;
      this.galleryIndex = (this.galleryIndex + 1) % this.galleryPhotos.length;
    },
    previousPhoto() {
      if (!this.galleryPhotos.length) return;
      this.galleryIndex = (this.galleryIndex - 1 + this.galleryPhotos.length) % this.galleryPhotos.length;
    },
    async submitReport() {
      if (!this.reportForm.alasan || this.reportForm.deskripsi.trim().length < 10) return;
      this.reportSaving = true;
      this.reportSuccess = false;
      try {
        await API.post('/laporan/kos', {
          id_kos: <?= (int)$kos['id_kos'] ?>,
          alasan: this.reportForm.alasan,
          deskripsi: this.reportForm.deskripsi.trim()
        });
        this.reportForm = { alasan: '', deskripsi: '' };
        this.reportSuccess = true;
        setTimeout(() => {
          this.reportOpen = false;
          this.reportSuccess = false;
        }, 1200);
      } catch (e) {
        console.error('Gagal mengirim laporan kos:', e);
      } finally {
        this.reportSaving = false;
      }
    },
    async share() {
      const url = <?= json_encode($shareUrl) ?>;
      const title = <?= json_encode($kos['nama_kos']) ?>;
      try {
        if (navigator.share) {
          await navigator.share({ title, text: 'Lihat kos ini di BetaKos', url });
        } else if (navigator.clipboard) {
          await navigator.clipboard.writeText(url);
          alert('Link detail kos berhasil disalin.');
        } else {
          prompt('Salin link ini:', url);
        }
      } catch (e) {}
    }
  };
}

document.addEventListener('DOMContentLoaded', function () {
  const mapEl = document.getElementById('detail-kos-map');
  if (!mapEl || typeof L === 'undefined') return;
  const lat = <?= json_encode((float)$kos['latitude']) ?>;
  const lng = <?= json_encode((float)$kos['longitude']) ?>;
  const map = L.map(mapEl).setView([lat, lng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
  L.marker([lat, lng]).addTo(map).bindPopup(<?= json_encode($kos['nama_kos']) ?>).openPopup();
  setTimeout(() => map.invalidateSize(), 100);
});
</script>
