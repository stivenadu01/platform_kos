<?php

/*
|--------------------------------------------------------------------------
| CRON JOB — PENGINGAT TAGIHAN WHATSAPP
|--------------------------------------------------------------------------
| Jalankan satu kali setiap pagi, contoh pukul 08.00 WITA:
|   php cron/process_whatsapp_reminders.php
|
| Simulasi tanggal tertentu:
|   php cron/process_whatsapp_reminders.php --date=2026-09-20
|--------------------------------------------------------------------------
*/

if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  exit("Cron Job hanya boleh dijalankan melalui CLI.\n");
}

$rootPath = dirname(__DIR__);
require_once $rootPath . '/app/config/bootstrap.php';
require_once $rootPath . '/app/services/WhatsAppNotificationService.php';

if (!filter_var($_ENV['WHATSAPP_NOTIFICATIONS_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
  fwrite(STDOUT, "Notifikasi WhatsApp dinonaktifkan.\n");
  exit(0);
}

$lockPath = $rootPath . '/storage/cron-whatsapp-reminders.lock';
$lockDir = dirname($lockPath);
if (!is_dir($lockDir)) mkdir($lockDir, 0775, true);
$lockHandle = fopen($lockPath, 'c');
if (!$lockHandle) {
  fwrite(STDERR, "Gagal membuka file lock Cron WhatsApp.\n");
  exit(1);
}
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
  fwrite(STDOUT, "Cron WhatsApp sedang berjalan. Proses dilewati.\n");
  fclose($lockHandle);
  exit(0);
}

try {
  $date = date('Y-m-d');
  foreach ($argv as $argument) {
    if (str_starts_with($argument, '--date=')) {
      $date = substr($argument, 7);
      break;
    }
  }
  $dateCheck = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
  if (!$dateCheck || $dateCheck->format('Y-m-d') !== $date) {
    throw new InvalidArgumentException('Tanggal Cron tidak valid.');
  }

  $candidates = getAutomaticWhatsAppReminderCandidates($date);
  $service = new WhatsAppNotificationService();
  $total = ['tagihan' => count($candidates), 'queued' => 0, 'failed' => 0, 'skipped' => 0];

  foreach ($candidates as $idTagihan) {
    $recipients = getWhatsAppBillRecipients($idTagihan);
    if (!$recipients) continue;
    $diff = (new DateTimeImmutable($date))->diff(
      new DateTimeImmutable($recipients[0]['tanggal_jatuh_tempo'])
    );
    $days = (int) $diff->format('%r%a');
    $stage = $days === 3 ? 'h_minus_3' : ($days === 0 ? 'hari_h' : 'h_plus_3');

    try {
      $result = $service->sendAutomaticBillReminder($idTagihan, $stage);
      $total['queued'] += $result['queued'];
      $total['failed'] += $result['failed'];
      $total['skipped'] += $result['skipped'];
    } catch (Throwable $e) {
      $total['failed']++;
      error_log("WhatsApp Cron tagihan #{$idTagihan}: " . $e->getMessage());
    }
  }

  fwrite(STDOUT, "Cron WhatsApp {$date}\n");
  fwrite(STDOUT, "Tagihan: {$total['tagihan']} | Queued: {$total['queued']} | Gagal: {$total['failed']} | Dilewati: {$total['skipped']}\n");
  exit($total['failed'] > 0 ? 2 : 0);
} catch (Throwable $e) {
  fwrite(STDERR, 'FATAL: ' . $e->getMessage() . "\n");
  exit(1);
} finally {
  flock($lockHandle, LOCK_UN);
  fclose($lockHandle);
}
