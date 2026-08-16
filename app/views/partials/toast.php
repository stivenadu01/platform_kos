<!-- TOAST -->
<div
  x-cloak
  x-data
  x-show="$store.ui.toastMessage"
  x-transition
  class="fixed top-15 left-1/2 -translate-x-1/2 w-full max-w-lg px-4 text-white z-100">

  <span
    :class="{
      'bg-green-500': $store.ui.toastType === 'success',
      'bg-yellow-500': $store.ui.toastType === 'warning',
      'bg-red-500': $store.ui.toastType === 'error'
    }"
    class="flex w-full py-3 px-2 rounded">

    <span
      x-text="$store.ui.toastMessage">
    </span>

  </span>

</div>


<!-- CONFIRM MODAL -->
<div
  x-cloak
  x-data
  x-show="$store.ui.confirmShow"
  x-transition
  @click.self="$store.ui.confirmNo()"
  class="modal-backdrop z-100 px-5">

  <div class="modal-box">

    <h2 class="text-lg font-semibold mb-2">
      Konfirmasi
    </h2>

    <p
      class="mb-6"
      x-text="$store.ui.confirmMessage">
    </p>

    <div class="flex-end">

      <div class="w-1/2 flex-between gap-2">

        <button
          type="button"
          class="btn"
          @click="$store.ui.confirmNo()">
          Batal
        </button>

        <button
          type="button"
          class="btn bg-red-500 text-white"
          @click="$store.ui.confirmYes()">
          Ya
        </button>

      </div>

    </div>

  </div>

</div>


<!-- GLOBAL LOADING -->
<div
  x-data
  x-show="$store.ui.loading"
  x-transition.opacity
  class="fixed inset-0 z-[999] flex-center">

  <div class="absolute inset-0 bg-white/40 backdrop-blur-sm"></div>

  <div class="relative">
    <div
      class="w-10 h-10 border-4 border-gray-300 border-t-primary rounded-full animate-spin">
    </div>
  </div>

</div>