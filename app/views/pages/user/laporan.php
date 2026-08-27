<?php
$statusMeta = [
  'menunggu' => [
    'label' => 'Menunggu',
    'class' => 'bg-amber-50 text-amber-700 border-amber-200',
  ],
  'diproses' => [
    'label' => 'Diproses',
    'class' => 'bg-blue-50 text-blue-700 border-blue-200',
  ],
  'selesai' => [
    'label' => 'Selesai',
    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
  ],
  'ditolak' => [
    'label' => 'Ditolak',
    'class' => 'bg-slate-100 text-slate-600 border-slate-200',
  ],
];

$reasonLabels = [
  'informasi_tidak_sesuai' => 'Informasi tidak sesuai',
  'foto_tidak_sesuai' => 'Foto tidak sesuai',
  'kos_sudah_tidak_tersedia' => 'Kos sudah tidak tersedia',
  'informasi_menyesatkan' => 'Informasi menyesatkan',
  'lainnya' => 'Lainnya',
];
?>

<div x-data="{ openId: null }" class="min-h-[calc(100vh-4rem)] bg-slate-50">
  <section class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
      <a href="<?= BASE_URL ?>/cari-kos" class="text-sm font-semibold text-slate-500 hover:text-primary">← Kembali</a>
      <div class="mt-4">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Laporan Saya</h1>
        <p class="mt-1 text-sm text-slate-500">Pantau laporan kos yang pernah kamu kirim kepada Admin BetaKos.</p>
      </div>
    </div>
  </section>

  <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <?php if (!$laporan): ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-2xl">⚑</div>
        <h2 class="mt-4 font-semibold text-slate-900">Belum ada laporan</h2>
        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
          Kamu belum pernah mengirim laporan kos. Jika menemukan informasi yang bermasalah, kamu dapat melaporkannya dari halaman detail kos.
        </p>
        <a href="<?= BASE_URL ?>/cari-kos" class="mt-5 inline-flex rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">
          Cari Kos
        </a>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($laporan as $item):
          $status = strtolower((string)($item['status'] ?? 'menunggu'));
          $meta = $statusMeta[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'];
          $reason = $reasonLabels[$item['alasan']] ?? ucwords(str_replace('_', ' ', (string)$item['alasan']));
        ?>
          <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <button type="button"
                    @click="openId = openId === <?= (int)$item['id_laporan'] ?> ? null : <?= (int)$item['id_laporan'] ?>"
                    class="w-full p-5 text-left sm:p-6">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-base font-bold text-slate-900"><?= htmlspecialchars($item['nama_kos'] ?? 'Kos tidak ditemukan') ?></h2>
                    <span class="rounded-full border px-2.5 py-1 text-[11px] font-semibold <?= $meta['class'] ?>">
                      <?= htmlspecialchars($meta['label']) ?>
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($reason) ?></p>
                  <p class="mt-2 text-xs text-slate-400">
                    Dilaporkan <?= htmlspecialchars(date('d M Y, H:i', strtotime($item['created_at'] ?? 'now'))) ?>
                  </p>
                </div>
                <span class="shrink-0 text-sm font-semibold text-primary" x-text="openId === <?= (int)$item['id_laporan'] ?> ? 'Tutup ↑' : 'Lihat detail ↓'"></span>
              </div>
            </button>

            <div x-show="openId === <?= (int)$item['id_laporan'] ?>" x-cloak x-transition class="border-t border-slate-100 bg-slate-50 px-5 py-5 sm:px-6">
              <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alasan</dt>
                  <dd class="mt-1 text-sm font-medium text-slate-800"><?= htmlspecialchars($reason) ?></dd>
                </div>
                <div>
                  <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</dt>
                  <dd class="mt-1 text-sm font-medium text-slate-800"><?= htmlspecialchars($meta['label']) ?></dd>
                </div>
              </dl>

              <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Keterangan laporan</p>
                <p class="mt-2 whitespace-pre-line rounded-xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-700"><?= htmlspecialchars($item['deskripsi'] ?? '') ?></p>
              </div>

              <?php if (!empty($item['catatan_admin'])): ?>
                <div class="mt-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan Admin</p>
                  <p class="mt-2 whitespace-pre-line rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-slate-700"><?= htmlspecialchars($item['catatan_admin']) ?></p>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
