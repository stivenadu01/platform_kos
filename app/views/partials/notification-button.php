<?php if (!empty($_SESSION['user'])): ?>
<button type="button" x-data class="bk-notification-bell" @click="$store.notifications.openPanel($event.currentTarget)" :aria-label="'Notifikasi, ' + $store.notifications.unread + ' belum dibaca'" :aria-expanded="$store.notifications.open" aria-haspopup="dialog">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
  <span class="bk-notification-count" x-cloak x-show="$store.notifications.unread > 0" x-text="$store.notifications.unread > 99 ? '99+' : $store.notifications.unread"></span>
</button>
<?php endif; ?>
