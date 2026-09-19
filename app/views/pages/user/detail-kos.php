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
$googleMapsUrl = trim((string)($kos['google_maps_url'] ?? ''));
if ($googleMapsUrl === '') {
  $googleMapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string)$kos['latitude'] . ',' . (string)$kos['longitude']);
}
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
$rekomendasi = $kos['rekomendasi'] ?? [];
$selectedLat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
$selectedLng = isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : null;
$selectedPlace = trim((string)($_GET['lokasi'] ?? ''));
$selectedDistance = null;
if ($selectedLat !== null && $selectedLng !== null) {
  $lat1 = deg2rad((float)$kos['latitude']);
  $lat2 = deg2rad($selectedLat);
  $dLat = deg2rad($selectedLat - (float)$kos['latitude']);
  $dLng = deg2rad($selectedLng - (float)$kos['longitude']);
  $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
  $selectedDistance = round(6371 * 2 * asin(min(1, sqrt($a))), 2);
}
$aboutText = trim((string)($kos['deskripsi'] ?? '')) ?: 'Pemilik belum menambahkan deskripsi kos.';
$aboutIsLong = mb_strlen($aboutText) > 420;

$updatedAt = $kos['updated_at'] ?? null;
$updatedLabel = 'Informasi diperbarui';
if ($updatedAt) {
  try {
    $updatedDate = new DateTime($updatedAt);
    $now = new DateTime();
    $days = max(0, (int)$updatedDate->diff($now)->days);
    if ($days === 0) $updatedLabel = 'Diperbarui hari ini';
    elseif ($days === 1) $updatedLabel = 'Diperbarui kemarin';
    elseif ($days < 30) $updatedLabel = 'Diperbarui ' . $days . ' hari lalu';
    else $updatedLabel = 'Diperbarui ' . $updatedDate->format('d M Y');
  } catch (Exception $e) {
  }
}

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

<style>
  html {
    scroll-behavior: smooth;
  }

  .detail-section {
    scroll-margin-top: 7rem;
  }

  .about-collapsed {
    max-height: 10.5rem;
    overflow: hidden;
    position: relative;
  }

  @media (min-width: 640px) {
    .about-collapsed {
      max-height: 12.25rem;
    }
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
          <?php if ($isPelanggan): ?>
            <button @click="toggleFavorite()" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary" :aria-pressed="favorited">
              <span class="text-lg leading-none" x-text="favorited ? '♥' : '♡'"></span>
              <span x-text="favorited ? 'Favorit tersimpan' : 'Simpan favorit'"></span>
            </button>
          <?php endif; ?>
          <button @click="share()" type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary">
            ↗ Bagikan
          </button>
        </div>
      </div>
    </div>
  </section>

  <div
    x-show="showSectionTabs"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="sticky top-16 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur lg:hidden">
    <nav class="mx-auto flex max-w-7xl overflow-x-auto px-2 scrollbar-none" aria-label="Navigasi detail kos">
      <template x-for="tab in sectionTabs" :key="tab.id">
        <button
          type="button"
          @click="scrollToSection(tab.id)"
          :class="activeSection === tab.id ? 'border-primary text-primary' : 'border-transparent text-slate-500'"
          class="shrink-0 border-b-2 px-3 py-3 text-xs font-bold transition sm:px-4 sm:text-sm"
          x-text="tab.label"></button>
      </template>
    </nav>
  </div>

  <div id="detail-tabs-sentinel" class="h-px w-full"></div>

  <main class="mx-auto max-w-7xl px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:py-8 lg:pb-8">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
      <section id="foto" class="detail-section">
        <?php if ($photos): ?>
          <div class="grid min-h-[250px] gap-1 bg-slate-100 sm:min-h-[300px] lg:grid-cols-[1.65fr_1fr]">
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
          <div class="flex min-h-[250px] items-center justify-center bg-slate-100 text-sm text-slate-400 sm:min-h-[300px] lg:min-h-[380px]">Foto kos belum tersedia</div>
        <?php endif; ?>
      </section>

      <div class="grid gap-6 p-4 sm:gap-8 sm:p-7 lg:grid-cols-[1fr_360px] lg:p-8">
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
          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="rounded-full bg-slate-100 px-3 py-1.5 font-medium"><?= htmlspecialchars($updatedLabel) ?></span>
            <?php if ($selectedDistance !== null): ?>
              <span class="rounded-full bg-primary-soft px-3 py-1.5 font-semibold text-primary">
                <?= htmlspecialchars($selectedDistance . ' km') ?> dari <?= htmlspecialchars($selectedPlace !== '' ? $selectedPlace : 'lokasi pilihanmu') ?>
              </span>
            <?php endif; ?>
          </div>

          <section id="tentang-kos" class="detail-section mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Tentang kos</h2>
            <div x-data="{ expanded: false }" class="mt-3">
              <div class="text-sm leading-7 text-slate-600" :class="<?= $aboutIsLong ? "expanded ? '' : 'about-collapsed'" : "''" ?>">
                <?= nl2br(htmlspecialchars($aboutText)) ?>
              </div>
              <?php if ($aboutIsLong): ?>
                <button
                  type="button"
                  @click="expanded = !expanded"
                  class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-primary hover:text-primary-dark">
                  <span x-text="expanded ? 'Sembunyikan' : 'Lihat selengkapnya'"></span>
                  <span aria-hidden="true" x-text="expanded ? '↑' : '↓'"></span>
                </button>
              <?php endif; ?>
            </div>
          </section>

          <section id="fasilitas" class="detail-section mt-8">
            <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Fasilitas</h2>
            <?php if ($facilities): ?>
              <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                <?php foreach ($facilities as $facility): ?>
                  <?php
                  $facilityIcon = trim((string)($facility['icon'] ?? ''));
                  if (!in_array($facilityIcon, masterIconKeys(), true)) {
                    $facilityIcon = 'info';
                  }
                  $facilityName = (string)($facility['nama_fasilitas'] ?? 'Fasilitas');
                  ?>
                  <div class="group flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md sm:min-h-12 sm:px-3 sm:py-2">
                    <span
                      class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-primary-soft text-primary sm:h-8 sm:w-8"
                      title="<?= htmlspecialchars(masterIconLabel($facilityIcon), ENT_QUOTES, 'UTF-8') ?>"
                      aria-hidden="true">
                      <?= masterIconSvg($facilityIcon, 'h-3.5 w-3.5 sm:h-4 sm:w-4') ?>
                    </span>
                    <span class="min-w-0 text-xs font-semibold leading-4 text-slate-700 sm:text-sm sm:leading-5">
                      <?= htmlspecialchars($facilityName) ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="mt-3 text-sm text-slate-500">Belum ada fasilitas yang dicantumkan.</p>
            <?php endif; ?>
          </section>

          <section id="aturan" class="detail-section mt-7">
            <div>
              <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Aturan / Ketentuan</h2>

            </div>
            <?php $aturanKos = is_array($kos['aturan'] ?? null) ? $kos['aturan'] : []; ?>
            <?php if ($aturanKos): ?>
              <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($aturanKos as $aturan): ?>
                  <div class="flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 sm:min-h-12 sm:px-3 sm:py-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-primary-soft text-primary sm:h-8 sm:w-8" aria-hidden="true"><?= masterIconSvg($aturan['icon'] ?? 'ban', 'h-3.5 w-3.5 sm:h-4 sm:w-4') ?></span>
                    <p class="min-w-0 text-xs font-semibold leading-4 text-slate-800 sm:text-sm sm:leading-5"><?= htmlspecialchars($aturan['nama_aturan']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">Pemilik belum mencantumkan aturan khusus untuk kos ini. Silakan tanyakan langsung kepada pemilik.</div>
            <?php endif; ?>
          </section>

          <section id="tipe-harga" class="detail-section mt-8">
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
                        @click='openTypeGallery(<?= json_encode_safe($type['nama_tipe'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode_safe(array_map(fn($photo) => BASE_URL . '/uploads' . $photo['nama_file'], $typePhotos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>)'
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
                              <?php
                              $roomFacilityIcon = trim((string)($facility['icon'] ?? ''));
                              if (!in_array($roomFacilityIcon, masterIconKeys(), true)) {
                                $roomFacilityIcon = 'info';
                              }
                              ?>
                              <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600">
                                <?= masterIconSvg($roomFacilityIcon, 'h-3.5 w-3.5 text-primary') ?>
                                <?= htmlspecialchars($facility['nama_fasilitas'] ?? 'Fasilitas') ?>
                              </span>
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
          </section>

          <?php
          $mapLat = (float)($kos['latitude'] ?? 0);
          $mapLng = (float)($kos['longitude'] ?? 0);
          $hasCoordinates = is_finite($mapLat) && is_finite($mapLng) && $mapLat >= -90 && $mapLat <= 90 && $mapLng >= -180 && $mapLng <= 180;
          $googleEmbedUrl = $hasCoordinates
            ? 'https://www.google.com/maps?q=' . rawurlencode($mapLat . ',' . $mapLng) . '&z=17&output=embed'
            : '';
          ?>
          <section id="lokasi" class="detail-section mt-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <div class="flex items-center gap-2">
                  <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-600">📍</span>
                  <div>
                    <h2 class="font-[Poppins] text-xl font-bold text-slate-900">Lokasi dan lingkungan sekitar</h2>
                  </div>
                </div>
              </div>
              <a target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($googleMapsUrl) ?>" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-dark">
                Buka di Google Maps <span aria-hidden="true">↗</span>
              </a>
            </div>

            <?php $lokasiPopulerSekitar = $lokasiPopulerSekitar ?? []; ?>

            <?php if ($hasCoordinates): ?>
              <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm">
                <iframe
                  src="<?= htmlspecialchars($googleEmbedUrl) ?>"
                  title="Peta lokasi <?= htmlspecialchars($kos['nama_kos'] ?? 'kos') ?>"
                  class="block aspect-[16/8] w-full sm:h-[300px] sm:aspect-auto lg:h-[340px]"
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                  allowfullscreen>
                </iframe>
              </div>
            <?php else: ?>
              <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center">
                <p class="font-semibold text-slate-700">Lokasi belum tersedia</p>
                <p class="mt-1 text-sm text-slate-500">Kos ini belum memiliki koordinat yang dapat ditampilkan.</p>
              </div>
            <?php endif; ?>

            <?php if ($lokasiPopulerSekitar): ?>
              <div class="mt-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tempat terdekat</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  <?php foreach ($lokasiPopulerSekitar as $lokasi): ?>
                    <?php
                    $kategori = $lokasi['kategori'] ?? '';
                    $kategoriIcon = $lokasi['icon'] ?? lokasiKategoriIcon($kategori);
                    $kategoriLabel = lokasiKategoriLabel($kategori);
                    $jarak = (float)$lokasi['jarak_km'];
                    $jarakText = $jarak < 1
                      ? number_format(max(1, round($jarak * 1000)), 0, ',', '.') . ' m'
                      : number_format($jarak, 1, ',', '.') . ' km';
                    ?>
                    <a
                      href="<?= htmlspecialchars($lokasi['google_maps_url'] ?: ('https://www.google.com/maps/search/?api=1&query=' . rawurlencode($lokasi['latitude'] . ',' . $lokasi['longitude']))) ?>"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="group rounded-xl border border-slate-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                      <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary" title="<?= htmlspecialchars($kategoriLabel) ?>"><?= masterIconSvg($kategoriIcon, 'h-4 w-4') ?></span>
                        <div class="min-w-0 flex-1">
                          <p class="line-clamp-2 text-sm font-bold text-slate-800 group-hover:text-primary"><?= htmlspecialchars($lokasi['nama']) ?></p>
                          <p class="mt-2 text-xs font-bold text-primary sm:hidden"><?= htmlspecialchars($jarakText) ?></p>
                        </div>
                        <span class="hidden shrink-0 rounded-full bg-primary-soft px-2.5 py-1 text-xs font-bold text-primary sm:inline-flex">
                          <?= htmlspecialchars($jarakText) ?>
                        </span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>





          </section>

        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hubungi pemilik</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Tertarik dengan kos ini?</h2>

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
                      <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold tracking-wide text-amber-700">Pemilik Pro</span>
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

  <div class="fixed inset-x-0 bottom-0 z-[1000] border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_30px_rgba(15,23,42,0.10)] backdrop-blur lg:hidden">
    <div class="mx-auto flex max-w-7xl items-center gap-2">
      <?php if ($isPelanggan): ?>
        <button @click="toggleFavorite()" type="button" class="inline-flex min-h-12 w-14 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-2xl text-slate-700" :aria-label="favorited ? 'Hapus dari favorit' : 'Simpan ke favorit'" x-text="favorited ? '♥' : '♡'"></button>
      <?php endif; ?>
      <?php if ($waUrl): ?>
        <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="flex min-h-12 flex-1 items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-dark">Hubungi Pemilik</a>
      <?php else: ?>
        <div class="flex min-h-12 flex-1 items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-500">Kontak belum tersedia</div>
      <?php endif; ?>
    </div>
  </div>

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
            <select x-model="reportForm.alasan" class="select mt-1" required>
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

<script>
  function kosDetailPage() {
    const photos = <?= json_encode_safe(array_map(fn($p) => BASE_URL . '/uploads' . $p['nama_file'], $photos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;


    return {
      galleryPhotos: photos,
      galleryIndex: 0,
      showSectionTabs: false,
      activeSection: 'tentang-kos',
      sectionTabs: [{
          id: 'foto',
          label: 'Foto'
        },
        {
          id: 'tentang-kos',
          label: 'Tentang kos'
        },
        {
          id: 'fasilitas',
          label: 'Fasilitas'
        },
        {
          id: 'aturan',
          label: 'Aturan'
        },
        {
          id: 'tipe-harga',
          label: 'Tipe & harga'
        },
        {
          id: 'lokasi',
          label: 'Lokasi'
        }
      ],
      galleryOpen: false,
      typeGalleryPhotos: [],
      typeGalleryIndex: 0,
      typeGalleryName: '',
      typeGalleryOpen: false,
      reportOpen: false,
      reportSaving: false,
      favorited: <?= $kos['is_favorited'] ? 'true' : 'false' ?>,
      favoriteSaving: false,
      reportSuccess: false,
      reportForm: {
        alasan: '',
        deskripsi: ''
      },
      init() {
        this.$nextTick(() => this.initSectionNavigation());
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
      scrollToSection(id) {
        const target = document.getElementById(id);
        if (!target) return;
        this.activeSection = id;
        const navbarOffset = 64;
        const tabsOffset = window.innerWidth < 1024 ? 49 : 0;
        const top = target.getBoundingClientRect().top + window.scrollY - navbarOffset - tabsOffset - 8;
        window.scrollTo({
          top,
          behavior: 'smooth'
        });
      },
      initSectionNavigation() {
        const sentinel = document.getElementById('detail-tabs-sentinel');
        if (sentinel && 'IntersectionObserver' in window) {
          const observer = new IntersectionObserver(([entry]) => {
            this.showSectionTabs = !entry.isIntersecting;
          }, {
            threshold: 0
          });
          observer.observe(sentinel);
        } else {
          this.showSectionTabs = window.scrollY > 120;
        }

        const sections = this.sectionTabs
          .map(tab => document.getElementById(tab.id))
          .filter(Boolean);
        if ('IntersectionObserver' in window && sections.length) {
          const sectionObserver = new IntersectionObserver((entries) => {
            const visible = entries
              .filter(entry => entry.isIntersecting)
              .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            if (visible.length) this.activeSection = visible[0].target.id;
          }, {
            rootMargin: '-120px 0px -60% 0px',
            threshold: 0.01
          });
          sections.forEach(section => sectionObserver.observe(section));
        }
      },
      async toggleFavorite() {
        if (this.favoriteSaving) return;
        this.favoriteSaving = true;
        try {
          const res = await API.post('/pelanggan/favorit', {
            id_kos: <?= (int)$kos['id_kos'] ?>
          });
          if (res?.data?.favorited !== undefined) this.favorited = !!res.data.favorited;
        } catch (e) {
          console.error('Gagal memperbarui favorit:', e);
        } finally {
          this.favoriteSaving = false;
        }
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
        const url = <?= json_encode_safe($shareUrl) ?>;
        const title = <?= json_encode_safe($kos['nama_kos']) ?>;
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
</script>