<?php if (GOOGLE_CLIENT_ID !== '' && empty($_SESSION['user'])): ?>
<div data-google-option class="bk-google-option">
  <div data-google-signin></div>
  <p data-google-status role="status" class="bk-google-status">Memuat pilihan Google…</p>
</div>
<?php endif; ?>
