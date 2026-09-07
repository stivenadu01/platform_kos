<?php
$pickerMode = $pickerMode ?? 'search';
$pickerGroups = $locationPresets ?? ['kampus' => [], 'area' => [], 'penting' => []];
$pickerTabs = [
  'kampus' => 'Kampus',
  'area' => 'Area',
  'penting' => 'Lokasi populer',
];
$pickerConfig = [
  'baseUrl' => BASE_URL,
  'redirect' => $pickerMode === 'home',
  'groups' => $pickerGroups,
];
?>

<div
  x-data="BetaKosLocationPicker(<?= htmlspecialchars(json_encode_safe($pickerConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>)"
  class="relative"
  x-init="init()"
  @keydown.escape.window="closePicker()"
  @click.outside="closePicker()">
  <form x-show="!open" @submit.prevent="submitQuery()" class="relative flex items-center gap-3">
    <?php if ($pickerMode === 'search'): ?>
      <button
        type="button"
        @click="closePicker()"
        x-show="open"
        x-cloak
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-2xl text-slate-500 hover:bg-slate-100"
        aria-label="Tutup pencarian lokasi">←</button>
    <?php endif; ?>

    <span class="text-xl text-slate-400" aria-hidden="true">⌕</span>

    <input
      x-ref="locationInput"
      x-model="locationQuery"
      @focus="openPicker()"
      @input="onQueryInput()"
      @keydown.enter.prevent="submitQuery()"
      type="search"
      autocomplete="off"
      class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-slate-800 outline-none placeholder:text-slate-400 sm:text-base"
      placeholder="<?= $pickerMode === 'home' ? 'Cari kos berdasarkan lokasi...' : 'Cari kampus, area, atau tempat...' ?>"
      aria-label="Cari lokasi kos">

    <button
      x-show="locationQuery"
      x-cloak
      @click="locationQuery = ''; suggestions = []; $nextTick(() => $refs.locationInput?.focus())"
      type="button"
      class="rounded-lg px-2 py-1 text-xs text-slate-400 hover:bg-slate-100"
      aria-label="Hapus pencarian">Hapus</button>

    <?php if ($pickerMode === 'home'): ?>
      <button
        type="submit"
        class="shrink-0 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-dark">
        Cari Kos
      </button>
    <?php endif; ?>
  </form>

  <div
    x-show="open"
    x-cloak
    x-transition.origin.top
    class="fixed inset-x-0 bottom-0 top-16 z-[120] flex flex-col overflow-hidden border-t border-slate-200 bg-white shadow-2xl">

    <div class="border-b border-slate-200 bg-white px-4 py-3 shadow-sm">
      <form @submit.prevent="submitQuery()" class="flex items-center gap-3">
        <button
          type="button"
          @click="closePicker()"
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-2xl text-slate-500 hover:bg-slate-100"
          aria-label="Tutup pencarian lokasi">←</button>

        <span class="text-xl text-slate-400" aria-hidden="true">⌕</span>

        <input
          x-ref="locationInput"
          x-model="locationQuery"
          @input="onQueryInput()"
          @keydown.enter.prevent="submitQuery()"
          type="search"
          autocomplete="off"
          class="min-w-0 flex-1 bg-transparent py-2.5 text-sm text-slate-800 outline-none placeholder:text-slate-400 sm:text-base"
          placeholder="Cari kampus, area, atau tempat..."
          aria-label="Cari lokasi kos">

        <button
          x-show="locationQuery"
          x-cloak
          @click="locationQuery = ''; suggestions = []; $nextTick(() => $refs.locationInput?.focus())"
          type="button"
          class="shrink-0 rounded-lg px-2 py-1 text-xs text-slate-400 hover:bg-slate-100"
          aria-label="Hapus pencarian">Hapus</button>

        <?php if ($pickerMode === 'home'): ?>
          <button
            type="submit"
            class="shrink-0 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark sm:px-5">
            Cari Kos
          </button>
        <?php endif; ?>
      </form>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
      <button
        type="button"
        @click="useMyLocation()"
        class="flex w-full items-center gap-4 border-b border-slate-100 px-4 py-4 text-left hover:bg-slate-50">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xl">◎</span>
        <span class="min-w-0">
          <span class="block text-sm font-semibold text-slate-800" x-text="searching && !suggestions.length ? 'Mencari lokasi...' : 'Cari di lokasi sekitar saya'"></span>
          <span class="mt-0.5 block text-xs text-slate-500">Gunakan lokasi perangkat untuk mencari kos terdekat</span>
        </span>
      </button>

      <div class="flex overflow-x-auto border-b border-slate-200">
        <?php foreach ($pickerTabs as $tabKey => $tabLabel): ?>
          <button
            type="button"
            @click="setTab('<?= $tabKey ?>')"
            class="shrink-0 border-b-2 px-5 py-3 text-sm font-semibold transition"
            :class="activeTab === '<?= $tabKey ?>' ? 'border-primary text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'">
            <?= htmlspecialchars($tabLabel) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <template x-if="locationQuery.trim().length >= 3">
        <div class="p-4">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hasil pencarian lokasi</p>
            <span x-show="searching" class="text-xs text-slate-400">Mencari...</span>
          </div>

          <div class="mt-2 divide-y divide-slate-100 rounded-xl border border-slate-100">
            <template x-for="item in suggestions" :key="`${item.latitude},${item.longitude},${item.nama}`">
              <button
                type="button"
                @click="choose(item)"
                class="flex w-full items-start gap-3 px-3 py-3 text-left hover:bg-slate-50">
                <span class="mt-0.5 text-primary">⌖</span>
                <span class="min-w-0">
                  <span class="block text-sm font-medium text-slate-800" x-text="item.nama"></span>
                  <span class="mt-0.5 block text-xs text-slate-400">Pilih lokasi ini</span>
                </span>
              </button>
            </template>

            <p x-show="!searching && suggestions.length === 0" class="px-3 py-4 text-xs text-slate-500">
              Tidak ada lokasi yang cocok. Coba nama kampus, area, jalan, atau tempat lain.
            </p>
          </div>
        </div>
      </template>

      <template x-if="locationQuery.trim().length < 3">
        <div class="p-4">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pilihan populer</p>
              <p class="mt-1 text-sm font-semibold text-slate-800" x-text="activeTab === 'kampus' ? 'Kampus di Kupang' : (activeTab === 'area' ? 'Area populer di Kupang' : 'Lokasi populer di Kupang')"></p>
            </div>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="item in activeItems.slice(0, 8)" :key="`${item.latitude},${item.longitude},${item.nama}`">
              <button
                type="button"
                @click="choose(item)"
                class="rounded-full border border-slate-200 bg-white px-3.5 py-2 text-sm text-slate-700 transition hover:border-primary hover:bg-primary-soft hover:text-primary">
                <span x-text="item.label || item.nama"></span>
              </button>
            </template>
          </div>

          <template x-if="activeItems.length === 0">
            <p class="mt-4 text-xs text-slate-400">Belum ada pilihan lokasi pada kategori ini.</p>
          </template>
        </div>
      </template>

      <p x-show="locationError" x-cloak class="border-t border-slate-100 px-4 py-3 text-xs font-medium text-red-600" x-text="locationError"></p>
    </div>
  </div>
</div>
