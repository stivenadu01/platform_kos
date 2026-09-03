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

    <!-- ONBOARDING HELP -->
    <button
      type="button"
      x-data="{ onboardingAvailable: localStorage.getItem('betakos_owner_onboarding_welcome_v3') === '1' && localStorage.getItem('betakos_owner_onboarding_complete_v3') !== '1' }"
      x-init="const syncOnboardingHelp = () => onboardingAvailable = localStorage.getItem('betakos_owner_onboarding_welcome_v3') === '1' && localStorage.getItem('betakos_owner_onboarding_complete_v3') !== '1'; window.addEventListener('storage', syncOnboardingHelp); window.addEventListener('betakos:onboarding-skipped', syncOnboardingHelp); window.addEventListener('betakos:onboarding-help-closed', syncOnboardingHelp); window.addEventListener('betakos:onboarding-completed', syncOnboardingHelp)"
      x-show="onboardingAvailable"
      @click="window.dispatchEvent(new CustomEvent('betakos:onboarding-help'))"
      class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
      title="Buka kembali panduan pengaturan awal">
      <span class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-400 text-xs font-bold">?</span>
      Bantuan
    </button>

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