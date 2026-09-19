<?php

class ApiWhatsAppController
{
  public function sendBillReminder()
  {
    try {
      require_once ROOT_PATH . '/app/helpers/rate_limit_helper.php';
      require_once ROOT_PATH . '/app/services/WhatsAppNotificationService.php';

      $idPemilik = (int) ($_SESSION['user']['id_user'] ?? 0);
      $idTagihan = (int) input('id_tagihan', 0);
      if ($idTagihan <= 0) {
        throw new Exception('ID tagihan tidak valid.', 422);
      }

      rateLimit('wa_reminder_owner_' . $idPemilik, 30, 3600);

      $service = new WhatsAppNotificationService();
      $summary = $service->sendBillReminderForOwner($idTagihan, $idPemilik);

      response([
        'success' => true,
        'message' => $this->summaryMessage($summary),
        'data' => $summary,
      ]);
    } catch (Throwable $e) {
      $status = (int) $e->getCode();
      if ($status < 400 || $status > 599) $status = 500;
      $message = $status >= 500
        ? 'Notifikasi WhatsApp tidak dapat diproses. Periksa konfigurasi atau koneksi perangkat.'
        : $e->getMessage();
      error_log('WhatsApp reminder error: ' . $e->getMessage());
      response(['success' => false, 'message' => $message], $status);
    }
  }

  private function summaryMessage(array $summary): string
  {
    $parts = [];
    if ($summary['queued'] > 0) $parts[] = $summary['queued'] . ' notifikasi masuk antrean';
    if ($summary['failed'] > 0) $parts[] = $summary['failed'] . ' gagal';
    if ($summary['skipped'] > 0) $parts[] = $summary['skipped'] . ' dilewati atau sudah dikirim hari ini';
    return $parts ? ucfirst(implode(', ', $parts)) . '.' : 'Tidak ada notifikasi yang dikirim.';
  }
}
