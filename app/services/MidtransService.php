<?php

class MidtransService
{
  private string $serverKey;
  private bool $production;
  private string $baseUrl;

  public function __construct()
  {
    $this->serverKey = trim((string)($_ENV['MIDTRANS_SERVER_KEY'] ?? ''));
    $this->production = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $this->baseUrl = rtrim((string)($_ENV['MIDTRANS_API_URL'] ?? ($this->production ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com')), '/');
    if ($this->serverKey === '') {
      throw new RuntimeException('Midtrans Server Key belum dikonfigurasi.');
    }
  }

  public function createQris(string $orderId, int $grossAmount): array
  {
    if ($orderId === '' || $grossAmount < 1 || $grossAmount > 10000000) {
      throw new InvalidArgumentException('Order atau nominal QRIS tidak valid.');
    }

    $response = $this->request('POST', '/v2/charge', [
      'payment_type' => 'qris',
      'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $grossAmount,
      ],
    ]);
    $response['qr_code_url'] = $this->extractQrCodeUrl($response);
    return $response;
  }

  private function extractQrCodeUrl(array $response): string
  {
    if (empty($response['actions']) || !is_array($response['actions'])) return '';

    foreach ($response['actions'] as $action) {
      if (($action['name'] ?? '') === 'generate-qr-code' && !empty($action['url'])) {
        return (string)$action['url'];
      }
    }

    return '';
  }

  public function getStatus(string $orderId): array
  {
    if ($orderId === '') throw new InvalidArgumentException('Order ID tidak valid.');
    return $this->request('GET', '/v2/' . rawurlencode($orderId) . '/status');
  }

  public function getQrisCodeUrl(string $transactionId): string
  {
    $transactionId = trim($transactionId);
    if ($transactionId === '') throw new InvalidArgumentException('Transaction ID QRIS tidak valid.');

    return $this->baseUrl . '/v2/qris/' . rawurlencode($transactionId) . '/qr-code';
  }

  public function verifyNotificationSignature(array $notification): bool
  {
    $orderId = (string)($notification['order_id'] ?? '');
    $statusCode = (string)($notification['status_code'] ?? '');
    $grossAmount = (string)($notification['gross_amount'] ?? '');
    $signature = strtolower((string)($notification['signature_key'] ?? ''));
    if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') return false;

    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    return hash_equals($expected, $signature);
  }

  private function request(string $method, string $path, ?array $payload = null): array
  {
    $ch = curl_init($this->baseUrl . $path);
    if ($ch === false) throw new RuntimeException('Gagal menyiapkan koneksi Midtrans.');

    $headers = [
      'Accept: application/json',
      'Content-Type: application/json',
      'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
    ];

    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_TIMEOUT => 15,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $errno !== 0) {
      throw new RuntimeException('Koneksi ke Midtrans gagal: ' . ($error ?: 'network error'));
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) throw new RuntimeException('Respons Midtrans tidak valid.');
    if ($http < 200 || $http >= 300) {
      $message = $data['status_message'] ?? 'Permintaan ke Midtrans ditolak.';
      throw new RuntimeException($message, $http ?: 502);
    }
    return $data;
  }
}
