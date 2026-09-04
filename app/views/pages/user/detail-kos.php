<?php
$photos = $kos['foto'] ?? [];
$facilities = $kos['fasilitas'] ?? [];
$roomTypes = $kos['tipe_kamar'] ?? [];
$mainPhoto = $photos[0]['nama_file'] ?? null;
$phone = preg_replace('/\D+/', '', (string)($kos['no_hp_pemilik'] ?? ''));
if ($phone !== '' && str_starts_with($phone, '0')) {
  $phone = '62' . substr($phone, 1);
}
$waText = 'Halo, saya melihat kos ' . ($kos['nama_kos'] ?? '') . ' di BetaKos. Saya ingin menanyakan ketersediaan kamar.';
$waUrl = $phone !== '' ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($waText) : '';
$shareUrl = BASE_URL . '/kos/' . (int)$kos['id_kos'];
$isPelanggan = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pelanggan';

$pemilikNama = trim((string)($kos['nama_pemilik'] ?? 'Pemilik kos'));
$pemilikInisial = '';
foreach (preg_split('/\s+/', $pemilikNama) as $kata) {
  if ($kata !== '') $pemilikInisial .= mb_strtoupper(mb_substr($kata, 0, 1));
  if (mb_strlen($pemilikInisial) >= 2) break;
}
$pemilikInisial = $pemilikInisial ?: 'PK';
$pemilikFoto = $kos['foto_pemilik'] ?? null;
$pemilikPro = !empty($kos['pemilik_pro']);

$lastLoginAt = $kos['last_login_at'] ?? null;
$lastLoginLabel = 'Belum pernah login';
if (!empty($lastLoginAt)) {
  try {
    $lastLogin = new DateTime($lastLoginAt);
    $now = new DateTime();
    $seconds = max(0, $now->getTimestamp() - $lastLogin->getTimestamp());
    if ($seconds < 60) {
      $lastLoginLabel = 'Baru saja';
    } elseif ($seconds < 3600) {
      $lastLoginLabel = 'Terakhir online ' . max(1, (int) floor($seconds / 60)) . ' menit lalu';
    } elseif ($seconds < 86400) {
      $lastLoginLabel = 'Terakhir online ' . max(1, (int) floor($seconds / 3600)) . ' jam lalu';
    } elseif ($seconds < 604800) {
      $lastLoginLabel = 'Terakhir online ' . max(1, (int) floor($seconds / 86400)) . ' hari lalu';
    } else {
      $lastLoginLabel = 'Terakhir online ' . $lastLogin->format('d M Y');
    }
  } catch (Exception $e) {
    $lastLoginLabel = 'Informasi aktivitas tidak tersedia';
  }
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  #detail-kos-map {
    z-index: 1;
  }

  .leaflet-pane,
  .leaflet-control {
    z-index: 2;
  }
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
            <?php if ($pemilikPro): ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">★ Pemilik Pro</span>
            <?php endif; ?>
            <?php if ((int)$kos['kamar_tersedia'] > 0): ?>
              <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"><?= (int)$kos['kamar_tersedia'] ?> kamar tersedia</span>
            <?php else: ?>
              <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Saat ini tidak tersedia</span>
            <?php endif; ?>
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
            <div class="flex items-end justify-between gap-3">
              <div>
                <h2 class="font-[Poppins] text-xl font-bold text-slate-900 sm:text-2xl">Tipe & Harga Kamar</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Pilih tipe kamar berdasarkan kapasitas, fasilitas, harga, dan ketersediaannya.</p>
              </div>
            </div>

            <?php if ($roomTypes): ?>
              <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <?php foreach ($roomTypes as $type): ?>
                  <?php
                    $typePrices = $type['harga'] ?? [];
                    $typeFacilities = $type['fasilitas'] ?? [];
                    $typePhotos = $type['foto'] ?? [];
                  ?>
                  <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <?php if (!empty($typePhotos)): ?>
                      <button
                        type="button"
                        @click='openTypeGallery(<?= json_encode($type['nama_tipe'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode(array_map(fn($photo) => BASE_URL . '/uploads' . $photo['nama_file'], $typePhotos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>)'
                        class="group relative block h-48 w-full overflow-hidden bg-slate-100 text-left sm:h-44"
                        aria-label="Lihat foto <?= htmlspecialchars($type['nama_tipe']) ?>">
                        <img src="<?= BASE_URL ?>/uploads<?= htmlspecialchars($typePhotos[0]['nama_file']) ?>" alt="<?= htmlspecialchars($type['nama_tipe']) ?>" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                        <span class="absolute inset-x-0 bottom-0 flex items-end justify-between bg-gradient-to-t from-black/65 to-transparent px-4 pb-3 pt-10 text-xs font-semibold text-white">
                          <span>Lihat <?= count($typePhotos) ?> foto</span>
                          <span class="rounded-full bg-black/45 px-2.5 py-1 backdrop-blur-sm">⌕</span>
                        </span>
                      </button>
                    <?php else: ?>
                      <div class="flex h-40 items-center justify-center bg-slate-100 text-sm text-slate-400">Foto tipe belum tersedia</div>
                    <?php endif; ?>

                    <div class="p-4 sm:p-5">
                      <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                          <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($type['nama_tipe']) ?></h3>
                          <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span class="rounded-full bg-primary-soft px-2.5 py-1 font-semibold text-primary">Kapasitas <?= (int)$type['kapasitas'] ?> orang</span>
                            <?php if ((int)$type['kamar_tersedia'] > 0): ?>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700"><?= (int)$type['kamar_tersedia'] ?> tersedia</span>
                          <?php else: ?>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600">Saat ini tidak tersedia</span>
                          <?php endif; ?>
                          </div>
                        </div>
                      </div>

                      <?php if (trim((string)($type['deskripsi'] ?? '')) !== ''): ?>
                        <p class="mt-3 text-sm leading-6 text-slate-600"><?= nl2br(htmlspecialchars($type['deskripsi'])) ?></p>
                      <?php endif; ?>

                      <?php if ($typePrices): ?>
                        <div class="mt-4 rounded-xl bg-slate-50 p-3">
                          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Harga per bulan</p>
                          <div class="mt-2 space-y-2">
                            <?php foreach ($typePrices as $price): ?>
                              <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-slate-600"><?= (int)$price['jumlah_orang'] ?> orang</span>
                                <span class="font-bold text-slate-900">Rp <?= number_format((float)$price['harga_total'], 0, ',', '.') ?></span>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      <?php else: ?>
                        <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Harga tipe kamar belum tersedia.</div>
                      <?php endif; ?>

                      <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fasilitas kamar</p>
                        <?php if ($typeFacilities): ?>
                          <div class="mt-2 flex flex-wrap gap-2">
                            <?php foreach ($typeFacilities as $facility): ?>
                              <span class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600">✓ <?= htmlspecialchars($facility['nama_fasilitas']) ?></span>
                            <?php endforeach; ?>
                          </div>
                        <?php else: ?>
                          <p class="mt-2 text-xs text-slate-500">Belum ada fasilitas kamar yang dicantumkan.</p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center">
                <p class="text-sm font-semibold text-slate-700">Belum ada tipe kamar</p>
                <p class="mt-1 text-xs text-slate-500">Pemilik belum menambahkan tipe kamar untuk kos ini.</p>
              </div>
            <?php endif; ?>
          </div>

          <div class="mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Lokasi</h2>
            <div id="detail-kos-map" class="mt-4 h-[300px] overflow-hidden rounded-2xl border border-slate-200 sm:h-[360px]"></div>
            <a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($kos['latitude'] . ',' . $kos['longitude']) ?>" class="mt-3 inline-flex text-sm font-semibold text-primary hover:text-primary-dark">Buka di Google Maps →</a>
          </div>
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hubungi pemilik</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Tertarik dengan kos ini?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Tanyakan ketersediaan tipe kamar dan detail harga langsung kepada pemilik.</p>

            <?php if ($waUrl): ?>
              <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="mt-5 flex min-h-12 w-full items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-dark">Tanya Pemilik via WhatsApp</a>
            <?php else: ?>
              <div class="mt-5 rounded-xl bg-slate-50 p-3 text-center text-xs text-slate-500">Kontak pemilik belum tersedia.</div>
            <?php endif; ?>

            <div class="mt-3 rounded-xl bg-slate-50 p-3">
              <p class="text-xs text-slate-400">Pemilik</p>
              <div class="mt-2 flex items-center gap-3">
                <?php if ($pemilikFoto): ?>
                  <img src="<?= htmlspecialchars(BASE_URL . '/uploads' . $pemilikFoto) ?>" alt="Foto <?= htmlspecialchars($pemilikNama) ?>" class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-slate-200" loading="lazy">
                <?php else: ?>
                  <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-sm font-bold text-primary ring-1 ring-blue-100">
                    <?= htmlspecialchars($pemilikInisial) ?>
                  </div>
                <?php endif; ?>
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <p class="truncate text-sm font-semibold text-slate-800"><?= htmlspecialchars($pemilikNama) ?></p>
                    <?php if ($pemilikPro): ?>
                      <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">PRO</span>
                    <?php endif; ?>
                  </div>
                  <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                    <span class="h-2 w-2 shrink-0 rounded-full <?= !empty($lastLoginAt) ? 'bg-emerald-500' : 'bg-slate-300' ?>"></span>
                    <span><?= htmlspecialchars($lastLoginLabel) ?></span>
                  </div>
                </div>
              </div>
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

  <div x-show="typeGalleryOpen" x-cloak @click.self="typeGalleryOpen = false" @keydown.escape.window="typeGalleryOpen = false" class="fixed inset-0 z-[2050] flex items-center justify-center bg-black/90 p-4">
    <button @click="typeGalleryOpen = false" type="button" class="absolute right-4 top-4 rounded-full bg-white/10 px-4 py-2 text-white hover:bg-white/20">✕</button>
    <button @click="previousTypePhoto()" type="button" class="absolute left-3 rounded-full bg-white/10 px-4 py-3 text-2xl text-white hover:bg-white/20 sm:left-8">‹</button>
    <div class="flex max-h-[90vh] max-w-6xl flex-col items-center">
      <div class="mb-3 rounded-full bg-black/45 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm" x-text="typeGalleryName"></div>
      <img :src="typeGalleryPhotos[typeGalleryIndex]" :alt="typeGalleryName" class="max-h-[78vh] max-w-full rounded-xl object-contain">
      <p class="mt-3 text-center text-xs text-white/70" x-text="(typeGalleryIndex + 1) + ' / ' + typeGalleryPhotos.length"></p>
    </div>
    <button @click="nextTypePhoto()" type="button" class="absolute right-3 rounded-full bg-white/10 px-4 py-3 text-2xl text-white hover:bg-white/20 sm:right-8">›</button>
  </div>

  <div x-show="galleryOpen" x-cloak @click.self="galleryOpen = false" @keydown.escape.window="galleryOpen = false" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/90 p-4">
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


    return {
      galleryPhotos: photos,
      galleryIndex: 0,
      galleryOpen: false,
      typeGalleryPhotos: [],
      typeGalleryIndex: 0,
      typeGalleryName: '',
      typeGalleryOpen: false,
      reportOpen: false,
      reportSaving: false,
      reportSuccess: false,
      reportForm: {
        alasan: '',
        deskripsi: ''
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
      openTypeGallery(name, photos) {
        if (!Array.isArray(photos) || !photos.length) return;
        this.typeGalleryName = name || 'Foto tipe kamar';
        this.typeGalleryPhotos = photos;
        this.typeGalleryIndex = 0;
        this.typeGalleryOpen = true;
      },
      nextTypePhoto() {
        if (!this.typeGalleryPhotos.length) return;
        this.typeGalleryIndex = (this.typeGalleryIndex + 1) % this.typeGalleryPhotos.length;
      },
      previousTypePhoto() {
        if (!this.typeGalleryPhotos.length) return;
        this.typeGalleryIndex = (this.typeGalleryIndex - 1 + this.typeGalleryPhotos.length) % this.typeGalleryPhotos.length;
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
          this.reportForm = {
            alasan: '',
            deskripsi: ''
          };
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
            await navigator.share({
              title,
              text: 'Lihat kos ini di BetaKos',
              url
            });
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

  document.addEventListener('DOMContentLoaded', function() {
    const mapEl = document.getElementById('detail-kos-map');
    if (!mapEl || typeof L === 'undefined') return;
    const lat = <?= json_encode((float)$kos['latitude']) ?>;
    const lng = <?= json_encode((float)$kos['longitude']) ?>;
    const map = L.map(mapEl).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(<?= json_encode($kos['nama_kos']) ?>).openPopup();
    setTimeout(() => map.invalidateSize(), 100);
  });
</script>