<?php
$tab = $_GET['tab'] ?? 'lokasi';
if (!in_array($tab, ['lokasi', 'fasilitas', 'aturan'], true)) $tab = 'lokasi';

$masterCards = [
  ['key'=>'lokasi','title'=>'Lokasi Referensi','desc'=>'Kampus, area, dan tempat penting yang menjadi referensi pencarian dan lingkungan sekitar kos.','icon'=>'map-pin'],
  ['key'=>'fasilitas','title'=>'Fasilitas','desc'=>'Fasilitas kos dan kamar yang dapat dipilih pemilik saat mengelola properti.','icon'=>'sparkles'],
  ['key'=>'aturan','title'=>'Aturan','desc'=>'Ketentuan kos yang dapat dipilih pemilik dan ditampilkan secara terstruktur kepada pencari kos.','icon'=>'clipboard-check'],
];
?>
<div class="space-y-6">
  <div>
    <p class="text-sm font-semibold text-primary">Pengaturan Platform</p>
    <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900">Data Master</h1>
    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">Satu pusat data untuk elemen yang digunakan bersama oleh admin, pemilik, dan halaman publik BetaKos.</p>
  </div>

  <div class="grid gap-4 lg:grid-cols-3">
    <?php foreach ($masterCards as $card): ?>
      <a href="<?= BASE_URL ?>/admin/data-master?tab=<?= urlencode($card['key']) ?>"
         class="group rounded-2xl border <?= $tab === $card['key'] ? 'border-primary/30 bg-primary-soft/40' : 'border-slate-200 bg-white' ?> p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md">
        <div class="flex items-start justify-between gap-4">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-primary">
            <?= masterIconSvg($card['icon'], 'h-6 w-6') ?>
          </span>
          <span class="text-slate-400 transition group-hover:translate-x-0.5">→</span>
        </div>
        <h2 class="mt-4 font-[Poppins] text-lg font-bold text-slate-900"><?= htmlspecialchars($card['title']) ?></h2>
        <p class="mt-1 text-sm leading-6 text-slate-500"><?= htmlspecialchars($card['desc']) ?></p>
        <div class="mt-4 text-xs font-semibold text-primary">Kelola data →</div>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
    <div class="flex items-start gap-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
        <?= masterIconSvg('shield-check', 'h-5 w-5') ?>
      </span>
      <div>
        <h2 class="font-semibold text-slate-900">Standar data & icon</h2>
        <p class="mt-1 text-sm leading-6 text-slate-500">Icon master dipilih dari katalog resmi BetaKos. Lokasi menggunakan icon otomatis berdasarkan kategori, sedangkan fasilitas dan aturan menggunakan katalog yang sama di seluruh halaman.</p>
      </div>
    </div>
  </div>

  <div>
    <?php
      if ($tab === 'lokasi') require ROOT_PATH . '/app/views/pages/admin/lokasi.php';
      elseif ($tab === 'fasilitas') require ROOT_PATH . '/app/views/pages/admin/fasilitas.php';
      else require ROOT_PATH . '/app/views/pages/admin/aturan.php';
    ?>
  </div>
</div>
