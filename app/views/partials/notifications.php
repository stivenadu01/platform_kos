<?php if (!empty($_SESSION['user'])): ?>
<div x-data x-cloak x-show="$store.notifications.open" class="bk-notification-overlay modal-backdrop" @click.self="$store.notifications.closePanel()" @keydown.escape.window="$store.notifications.closePanel()" x-effect="if ($store.notifications.open) $nextTick(() => $refs.closeNotifications.focus())">
  <section role="dialog" aria-modal="true" aria-labelledby="bk-notification-title" class="bk-notification-panel">
    <header class="bk-notification-heading">
      <div><h2 id="bk-notification-title">Notifikasi</h2><p><span x-text="$store.notifications.unread"></span> belum dibaca</p></div>
      <button type="button" x-ref="closeNotifications" @click="$store.notifications.closePanel()" class="bk-notification-bell" aria-label="Tutup notifikasi">&times;</button>
    </header>
    <div class="bk-notification-preferences">
      <p x-text="$store.notifications.browserStatus"></p>
      <button type="button" x-show="$store.notifications.canEnableBrowser" :disabled="$store.notifications.enabling" @click="$store.notifications.enableBrowser()" x-text="$store.notifications.enabling ? 'Mengaktifkan…' : 'Aktifkan notifikasi browser'"></button>
      <button type="button" x-show="$store.notifications.browserActive" @click="$store.notifications.disableBrowser()">Nonaktifkan di perangkat ini</button>
    </div>
    <div class="bk-notification-toolbar"><span>Pemberitahuan terbaru</span><button type="button" :disabled="!$store.notifications.unread || $store.notifications.busy" @click="$store.notifications.readAll()">Tandai semua dibaca</button></div>
    <div class="bk-notification-list" aria-live="polite">
      <p class="bk-notification-empty" x-show="$store.notifications.loading">Memuat notifikasi…</p>
      <p class="bk-notification-empty" role="status" x-show="$store.notifications.error" x-text="$store.notifications.error"></p>
      <button type="button" class="bk-notification-more" x-show="$store.notifications.error" @click="$store.notifications.refresh()">Coba lagi</button>
      <p class="bk-notification-empty" x-show="!$store.notifications.loading && !$store.notifications.items.length && !$store.notifications.error">Belum ada notifikasi. Pembaruan akun dan aktivitas kos akan muncul di sini.</p>
      <template x-for="item in $store.notifications.items" :key="item.id">
        <button type="button" class="bk-notification-item" :class="{'is-unread': !item.dibaca_pada}" :disabled="$store.notifications.busy" @click="$store.notifications.visit(item)">
          <span class="bk-notification-dot" aria-hidden="true"></span>
          <span><strong x-text="item.judul"></strong><span x-text="item.pesan"></span><small x-text="$store.notifications.time(item.tanggal)"></small><span class="sr-only" x-show="!item.dibaca_pada">Belum dibaca</span></span>
        </button>
      </template>
      <button type="button" class="bk-notification-more" x-show="$store.notifications.more" :disabled="$store.notifications.busy" @click="$store.notifications.loadMore()">Muat lebih banyak</button>
    </div>
  </section>
</div>
<?php endif; ?>
