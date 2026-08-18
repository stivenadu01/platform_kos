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

    <!-- USER -->
    <div class="hidden sm:block text-right">

      <div
        class="text-sm font-semibold text-slate-800"
        x-text="$store.auth.user?.nama || 'Pemilik'">
      </div>

      <div class="text-xs text-slate-500">
        Pemilik Kos
      </div>

    </div>


    <!-- AVATAR -->
    <template x-if="$store.auth.user?.foto">
      <img
        :src="window.BASE_URL + $store.auth.user.foto"
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