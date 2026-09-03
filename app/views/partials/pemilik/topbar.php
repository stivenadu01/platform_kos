<?php
$__topbarIsPro = false;
$__topbarSubscriptionLabel = 'Gratis';
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'pemilik') {
  model('Langganan');
  $__topbarStatusLangganan = getStatusLanggananPemilik((int)$_SESSION['user']['id_user']);
  $__topbarIsPro = !empty($__topbarStatusLangganan['is_pro']);
  $__topbarSubscriptionLabel = $__topbarIsPro ? 'BetaKos Pro' : 'Akun Gratis';
}
?>

<header
  class="
    h-16
    bg-white
    border-b border-slate-200
    flex items-center justify-between
    px-4 sm:px-6
    sticky top-0 z-30
  ">

  <!-- LEFT -->
  <div class="flex items-center gap-3">

    <!-- MOBILE MENU -->
    <button
      type="button"
      @click="sidebarOpen = true"
      class="
        lg:hidden
        w-10 h-10
        rounded-lg
        hover:bg-slate-100
        flex items-center justify-center
      ">

      ☰

    </button>


    <div class="lg:hidden">

      <div class="font-bold text-slate-900">
        BetaKos
      </div>

    </div>

  </div>


  <!-- RIGHT -->
  <div class="flex items-center gap-3">

    <!-- CONTEXTUAL HELP -->
    <button
      type="button"
      @click="
        if (localStorage.getItem('betakos_owner_onboarding_complete_v3') !== '1') {
          window.dispatchEvent(new CustomEvent('betakos:onboarding-help'));
        } else {
          window.dispatchEvent(new CustomEvent('betakos:operational-help'));
        }
      "
      class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
      title="Buka bantuan sesuai halaman yang sedang dibuka">
      <span class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-400 text-xs font-bold">?</span>
      Bantuan
    </button>

    <!-- USER -->
    <div class="hidden sm:block text-right">

      <div
        class="text-sm font-semibold text-slate-800"
        x-text="$store.auth.user?.nama || 'Pemilik'">
      </div>

      <div class="mt-0.5 flex items-center justify-end gap-2 text-xs">
        <span class="text-slate-500">Pemilik Kos</span>
        <?php if ($__topbarIsPro): ?>
          <span class="rounded-full bg-primary-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">PRO</span>
        <?php else: ?>
          <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Gratis</span>
        <?php endif; ?>
      </div>

    </div>


    <!-- AVATAR -->
    <template x-if="$store.auth.user?.foto">
      <img
        :src="window.BASE_URL + '/uploads' + $store.auth.user.foto"
        :alt="$store.auth.user?.nama || 'Pemilik'"
        class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200">
    </template>

    <template x-if="!$store.auth.user?.foto">
      <div
        class="
          w-10 h-10
          rounded-full
          bg-primary-soft
          text-primary
          flex items-center justify-center
          font-semibold
        "
        x-text="
          ($store.auth.user?.nama || 'P')
            .charAt(0)
            .toUpperCase()
        ">
      </div>
    </template>

  </div>

</header>