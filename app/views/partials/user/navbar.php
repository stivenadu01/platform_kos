<header
  x-data="{ open: false }"
  class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <a href="<?= BASE_URL ?>/" class="flex items-center gap-3 shrink-0">
      <img
        src="<?= BASE_URL ?>/assets/icon/logo.png"
        alt="BetaKos"
        class="h-9 w-9 object-contain">
      <div class="leading-tight">
        <div class="font-bold text-slate-900">BetaKos</div>
        <div class="hidden text-[11px] font-medium text-slate-500 sm:block">Kupang</div>
      </div>
    </a>

    <nav class="hidden items-center gap-1 md:flex">
      <a href="<?= BASE_URL ?>/" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
        Beranda
      </a>
      <a href="<?= BASE_URL ?>/cari-kos" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
        Cari Kos
      </a>
      <template x-if="$store.auth.isLoggedIn">
        <a href="<?= BASE_URL ?>/user/favorit" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
          Favorit
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/laporan" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
          Laporan Saya
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a href="<?= BASE_URL ?>/user/riwayat-kos" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-primary">
          Riwayat Kos
        </a>
      </template>
    </nav>

    <div class="hidden items-center gap-2 md:flex">
      <button
        type="button"
        data-pwa-install
        hidden
        class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary-soft px-4 py-2 text-sm font-semibold text-primary hover:bg-blue-100"
        title="Pasang BetaKos di perangkat">
        <span aria-hidden="true">📱</span>
        Unduh Aplikasi
      </button>
      <template x-if="!$store.auth.isLoggedIn">
        <div class="flex items-center gap-2">
          <a href="<?= BASE_URL ?>/login" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
            Masuk
          </a>
          <a href="<?= BASE_URL ?>/register" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark">
            Daftar
          </a>
        </div>
      </template>

      <template x-if="$store.auth.isLoggedIn">
        <div class="flex items-center gap-2">
          <template x-if="$store.auth.user?.role === 'pemilik'">
            <a href="<?= BASE_URL ?>/pemilik" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
              Dashboard
            </a>
          </template>
          <template x-if="$store.auth.user?.role === 'admin'">
            <a href="<?= BASE_URL ?>/admin" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
              Dashboard
            </a>
          </template>
          <button type="button" @click="$store.auth.logout()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Keluar
          </button>
        </div>
      </template>
    </div>

    <button
      type="button"
      class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 hover:bg-slate-100 md:hidden"
      @click="open = !open"
      :aria-expanded="open"
      aria-label="Buka menu">
      <span x-show="!open" class="text-xl">☰</span>
      <span x-show="open" x-cloak class="text-xl">✕</span>
    </button>
  </div>

  <div x-show="open" x-cloak x-transition class="border-t border-slate-100 bg-white md:hidden">
    <nav class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
      <a @click="open = false" href="<?= BASE_URL ?>/" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Beranda
      </a>
      <a @click="open = false" href="<?= BASE_URL ?>/cari-kos" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cari Kos
      </a>
      <template x-if="$store.auth.isLoggedIn">
        <a @click="open = false" href="<?= BASE_URL ?>/user/favorit" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Favorit
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a @click="open = false" href="<?= BASE_URL ?>/user/laporan" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Laporan Saya
        </a>
      </template>
      <template x-if="$store.auth.user?.role === 'pelanggan'">
        <a @click="open = false" href="<?= BASE_URL ?>/user/riwayat-kos" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Riwayat Kos Saya
        </a>
      </template>

      <button
        type="button"
        data-pwa-install
        hidden
        @click="open = false"
        class="flex w-full items-center gap-3 rounded-xl bg-primary-soft px-4 py-3 text-left text-sm font-semibold text-primary hover:bg-blue-100"
        title="Pasang BetaKos di perangkat">
        <span aria-hidden="true">📱</span>
        Unduh Aplikasi
      </button>

      <div class="border-t border-slate-100 pt-3">
        <template x-if="!$store.auth.isLoggedIn">
          <div class="grid grid-cols-2 gap-2">
            <a @click="open = false" href="<?= BASE_URL ?>/login" class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700">
              Masuk
            </a>
            <a @click="open = false" href="<?= BASE_URL ?>/register" class="rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-white">
              Daftar
            </a>
          </div>
        </template>

        <template x-if="$store.auth.isLoggedIn">
          <div class="space-y-2">
            <template x-if="$store.auth.user?.role === 'pemilik'">
              <a @click="open = false" href="<?= BASE_URL ?>/pemilik" class="block rounded-xl bg-primary-soft px-4 py-3 text-sm font-semibold text-primary">
                Dashboard Pemilik
              </a>
            </template>
            <button @click="open = false; $store.auth.logout()" type="button" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-left text-sm font-semibold text-slate-700">
              Keluar
            </button>
          </div>
        </template>
      </div>
    </nav>
  </div>
</header>