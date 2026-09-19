<?php

class FonnteService
{
  private string $token;
  private string $endpoint;

  public function __construct()
  {
    $this->token = trim((string) ($_ENV['FONNTE_TOKEN'] ?? ''));
    $this->endpoint = trim((string) ($_ENV['FONNTE_API_URL'] ?? 'https://api.fonnte.com/send'));

    if ($this->token === '') {
      throw new RuntimeException('Layanan WhatsApp belum dikonfigurasi.', 503);
    }

    $parts = parse_url($this->endpoint);
    if (
      !is_array($parts) ||
      ($parts['scheme'] ?? '') !== 'https' ||
      strtolower((string) ($parts['host'] ?? '')) !== 'api.fonnte.com' ||
      ($parts['path'] ?? '') !== '/send'
    ) {
      throw new RuntimeException('Endpoint layanan WhatsApp tidak valid.', 500);
    }
  }

  public function send(string $target, string $message): array
  {
    if (!preg_match('/^628[1-9][0-9]{7,10}$/', $target)) {
      throw new InvalidArgumentException('Nomor tujuan WhatsApp tidak valid.', 422);
    }

    if ($message === '' || mb_strlen($message) > 4000) {
      throw new InvalidArgumentException('Isi notifikasi WhatsApp tidak valid.', 422);
    }

    $curl = curl_init($this->endpoint);
    if ($curl === false) {
      throw new RuntimeException('Gagal menyiapkan koneksi WhatsApp.', 503);
    }

    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => [
        'target' => $target,
        'message' => $message,
        'countryCode' => '0',
        'connectOnly' => true,
        'preview' => false,
      ],
      CURLOPT_HTTPHEADER => ['Authorization: ' . $this->token],
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_TIMEOUT => 15,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $raw = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($raw === false) {
      throw new RuntimeException('Koneksi ke layanan WhatsApp gagal.', 503);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
      throw new RuntimeException('Respons layanan WhatsApp tidak valid.', 502);
    }

    $success = filter_var(
      $data['status'] ?? $data['Status'] ?? false,
      FILTER_VALIDATE_BOOLEAN
    );
    if (!$success || $httpStatus < 200 || $httpStatus >= 300) {
      $reason = trim((string) ($data['reason'] ?? $data['detail'] ?? 'Pengiriman ditolak.'));
      error_log('Fonnte send failed: HTTP ' . $httpStatus . ' - ' . ($curlError ?: $reason));
      throw new RuntimeException('Notifikasi WhatsApp gagal dimasukkan ke antrean.', 502);
    }

    return [
      'request_id' => isset($data['requestid']) ? (string) $data['requestid'] : null,
      'message_id' => isset($data['id'][0]) ? (string) $data['id'][0] : null,
      'process' => (string) ($data['process'] ?? 'pending'),
      'detail' => (string) ($data['detail'] ?? ''),
    ];
  }
}
