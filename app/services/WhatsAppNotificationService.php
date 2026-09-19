<?php

require_once ROOT_PATH . '/app/services/FonnteService.php';
require_once ROOT_PATH . '/app/models/WhatsAppNotification.php';

class WhatsAppNotificationService
{
  private FonnteService $provider;

  public function __construct()
  {
    if (!filter_var($_ENV['WHATSAPP_NOTIFICATIONS_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
      throw new RuntimeException('Notifikasi WhatsApp sedang dinonaktifkan.', 503);
    }
    $this->provider = new FonnteService();
  }

  public function sendAutomaticBillReminder(int $idTagihan, string $stage): array
  {
    if (!in_array($stage, ['h_minus_3', 'hari_h', 'h_plus_3'], true)) {
      throw new InvalidArgumentException('Tahap pengingat tidak valid.', 422);
    }
    return $this->sendBillReminder($idTagihan, $stage);
  }

  private function sendBillReminder(int $idTagihan, string $stage): array
  {
    $recipients = getWhatsAppBillRecipients($idTagihan);
    if (!$recipients) {
      throw new Exception('Tagihan atau penghuni tagihan tidak ditemukan.', 404);
    }

    $bill = $recipients[0];
    if (!in_array($bill['status'], ['belum_lunas', 'sebagian'], true) || (float) $bill['sisa_tagihan'] <= 0) {
      throw new Exception('Pengingat hanya dapat dikirim untuk tagihan yang masih memiliki sisa.', 422);
    }

    $summary = ['total_penghuni' => count($recipients), 'queued' => 0, 'failed' => 0, 'skipped' => 0];
    foreach ($recipients as $recipient) {
      try {
        $target = formatPhoneForWhatsApp($recipient['no_hp'] ?? '');
      } catch (Throwable $e) {
        $summary['skipped']++;
        continue;
      }

      $message = $this->billReminderMessage($recipient);
      $idempotencyKey = "tagihan:{$idTagihan}:penghuni:{$recipient['id_penghuni']}:pengingat:{$stage}";
      $notificationId = createWhatsAppNotification([
        'id_pemilik' => (int) $recipient['id_pemilik'],
        'id_penghuni' => (int) $recipient['id_penghuni'],
        'id_tagihan' => $idTagihan,
        'id_pembayaran' => null,
        'jenis' => 'pengingat_' . $stage,
        'nomor_tujuan' => $target,
        'isi_pesan' => $message,
        'idempotency_key' => $idempotencyKey,
      ]);

      if ($notificationId === null) {
        $summary['skipped']++;
        continue;
      }

      try {
        $result = $this->provider->send($target, $message);
        markWhatsAppNotificationQueued($notificationId, $result);
        $summary['queued']++;
      } catch (Throwable $e) {
        markWhatsAppNotificationFailed($notificationId, $e->getMessage());
        $summary['failed']++;
      }
    }

    return $summary;
  }

  private function billReminderMessage(array $data): string
  {
    return "🏠 *PENGINGAT TAGIHAN BETAKOS*\n\n" .
      "Halo {$data['nama_penghuni']},\n\n" .
      "Tagihan untuk kamar yang Anda tempati masih memiliki sisa pembayaran.\n\n" .
      "Kos: {$data['nama_kos']}\n" .
      "Kamar: {$data['nomor_kamar']}\n" .
      'Periode: ' . $this->date($data['tanggal_mulai']) . ' - ' . $this->date($data['tanggal_selesai']) . "\n" .
      'Total tagihan kamar: ' . $this->rupiah($data['total_tagihan']) . "\n" .
      'Sudah dibayar: ' . $this->rupiah($data['total_dibayar']) . "\n" .
      'Sisa tagihan kamar: ' . $this->rupiah($data['sisa_tagihan']) . "\n" .
      'Jatuh tempo: ' . $this->date($data['tanggal_jatuh_tempo']) . "\n\n" .
      "Tagihan ini dapat terhubung dengan lebih dari satu penghuni dalam kamar yang sama. Silakan koordinasikan pembayaran dengan penghuni lain atau pemilik kos.\n\n" .
      'Pesan otomatis dari BetaKos.';
  }

  private function rupiah($value): string
  {
    return 'Rp' . number_format((float) $value, 0, ',', '.');
  }

  private function date(string $value): string
  {
    return (new DateTimeImmutable($value))->format('d-m-Y');
  }
}
