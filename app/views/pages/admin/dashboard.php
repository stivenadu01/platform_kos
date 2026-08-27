<div class="space-y-6">
  <div>
    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Dashboard Admin</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau kualitas platform dan pengajuan verifikasi kos.</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="<?= BASE_URL ?>/admin/verifikasi" class="card border border-amber-200 shadow-sm hover:border-amber-300 transition">
      <div class="p-5"><div class="text-sm text-slate-500">Menunggu Verifikasi</div><div class="mt-2 text-3xl font-bold text-amber-600"><?= (int)$summary['menunggu'] ?></div><div class="mt-2 text-xs text-slate-500">Perlu diperiksa</div></div>
    </a>
    <div class="card border border-emerald-200 shadow-sm"><div class="p-5"><div class="text-sm text-slate-500">Terverifikasi</div><div class="mt-2 text-3xl font-bold text-emerald-600"><?= (int)$summary['disetujui'] ?></div><div class="mt-2 text-xs text-slate-500">Pengajuan disetujui</div></div></div>
    <div class="card border border-red-200 shadow-sm"><div class="p-5"><div class="text-sm text-slate-500">Ditolak</div><div class="mt-2 text-3xl font-bold text-red-600"><?= (int)$summary['ditolak'] ?></div><div class="mt-2 text-xs text-slate-500">Perlu diperbaiki pemilik</div></div></div>
  </div>
  <div class="card border border-slate-200 shadow-sm p-5 sm:p-6">
    <h2 class="font-semibold text-slate-900">Alur Admin MVP</h2>
    <div class="mt-4 flex flex-col md:flex-row md:items-center gap-3 text-sm">
      <span class="rounded-xl bg-slate-100 px-4 py-3">Pemilik mengajukan kos</span><span class="hidden md:block">→</span><span class="rounded-xl bg-amber-50 text-amber-700 px-4 py-3">Admin memeriksa</span><span class="hidden md:block">→</span><span class="rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">Disetujui → Kos aktif</span>
    </div>
  </div>
</div>
