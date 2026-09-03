<?php
$__pemilikIsPro = false;
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pemilik') {
  model('Langganan');
  $__pemilikStatusLangganan = getStatusLanggananPemilik((int)$_SESSION['user']['id_user']);
  $__pemilikIsPro = !empty($__pemilikStatusLangganan['is_pro']);
}
?>

<aside
  class="
    fixed inset-y-0 left-0 z-50
    w-64
    bg-white
    border-r border-slate-200
    flex flex-col
    transform transition-transform duration-300
    lg:translate-x-0
  "
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

  <!-- LOGO -->
  <div class="h-16 shrink-0 px-5 flex items-center border-b border-slate-200">

    <a
      :href="window.BASE_URL + '/pemilik'"
      class="flex items-center gap-3">

      <img
        :src="window.BASE_URL + '/assets/icon/logo.png'"
        alt="BetaKos"
        class="w-9 h-9 object-contain">

      <div>
        <div class="font-bold text-slate-900">
          BetaKos
        </div>

        <div class="text-xs text-slate-500">
          Panel Pemilik
        </div>
      </div>

    </a>

  </div>


  <!-- NAVIGATION -->
  <nav class="min-h-0 flex-1 overflow-y-auto p-4 pb-6 space-y-1">

    <!-- DASHBOARD -->
    <a
      :href="window.BASE_URL + '/pemilik'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        🏠
      </span>

      <span>
        Dashboard
      </span>

    </a>


    <!-- PROFIL -->
    <a
      :href="window.BASE_URL + '/pemilik/profil'"
      data-onboarding="sidebar-profil"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        👤
      </span>

      <span>
        Profil Saya
      </span>

    </a>


    <!-- SECTION KOS -->
    <div class="pt-5 pb-2 px-4">

      <span
        class="text-xs font-semibold uppercase tracking-wider text-slate-400">
        Manajemen Kos
      </span>

    </div>


    <!-- KOS -->
    <a
      :href="window.BASE_URL + '/pemilik/kos'"
      data-onboarding="sidebar-kos"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        🏢
      </span>

      <span>
        Kos Saya
      </span>

    </a>


    <!-- KELOLA KAMAR -->
    <a
      :href="window.BASE_URL + '/pemilik/kamar'"
      data-onboarding="sidebar-kamar"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        🚪
      </span>

      <span>
        Kelola Kamar
      </span>

    </a>


    <!-- PENGHUNI -->
    <a
      :href="window.BASE_URL + '/pemilik/penghuni'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        👥
      </span>

      <span>
        Penghuni
      </span>
      <?php if (!$__pemilikIsPro): ?>
        <span class="ml-auto text-[10px] font-bold text-primary">PRO</span>
      <?php endif; ?>

    </a>


    <!-- PEMBAYARAN -->
    <a
      :href="window.BASE_URL + '/pemilik/pembayaran'"
      class="
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-slate-700
        hover:bg-slate-100
        hover:text-primary
      ">

      <span class="text-lg">
        💳
      </span>

      <span>
        Tagihan & Pembayaran
      </span>
      <?php if (!$__pemilikIsPro): ?>
        <span class="ml-auto text-[10px] font-bold text-primary">PRO</span>
      <?php endif; ?>

    </a>


    <!-- SECTION LAYANAN -->
    <div class="pt-5 pb-2 px-4">
      <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
        Layanan
      </span>
    </div>


    <!-- CLAIM RIWAYAT -->
    <a
      :href="window.BASE_URL + '/pemilik/claim'"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">✓</span>
      <span>Klaim Riwayat</span>
    </a>


    <!-- LANGGANAN -->
    <a
      :href="window.BASE_URL + '/pemilik/langganan'"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
      <span class="text-lg">⭐</span>
      <span>Langganan</span>
    </a>




  </nav>


  <!-- BOTTOM -->
  <div class="shrink-0 p-4 border-t border-slate-200 bg-white">

    <button
      type="button"
      @click="$store.auth.logout()"
      class="
        w-full
        flex items-center gap-3
        px-4 py-3
        rounded-xl
        text-sm font-medium
        text-red-600
        hover:bg-red-50
      ">

      <span class="text-lg">
        ⇥
      </span>

      <span>
        Keluar
      </span>

    </button>

  </div>

</aside>