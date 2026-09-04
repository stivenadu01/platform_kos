<?php

class GoogleAuthService
{
  private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
  private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
  private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

  public function getRedirectUri(): string
  {
    return GOOGLE_REDIRECT_URI;
  }

  public function buildAuthorizationUrl(string $state): string
  {
    if (GOOGLE_CLIENT_ID === '') {
      throw new Exception('Google Client ID belum dikonfigurasi.', 500);
    }

    return self::AUTH_URL . '?' . http_build_query([
      'client_id' => GOOGLE_CLIENT_ID,
      'redirect_uri' => $this->getRedirectUri(),
      'response_type' => 'code',
      'scope' => 'openid email profile',
      'state' => $state,
      'access_type' => 'online',
      'include_granted_scopes' => 'true',
      'prompt' => 'select_account'
    ], '', '&', PHP_QUERY_RFC3986);
  }

  public function exchangeCode(string $code): array
  {
    $clientSecret = trim($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
    if ($clientSecret === '') {
      throw new Exception('Google Client Secret belum dikonfigurasi di server.', 500);
    }

    $body = http_build_query([
      'code' => $code,
      'client_id' => GOOGLE_CLIENT_ID,
      'client_secret' => $clientSecret,
      'redirect_uri' => $this->getRedirectUri(),
      'grant_type' => 'authorization_code'
    ], '', '&', PHP_QUERY_RFC3986);

    $ch = curl_init(self::TOKEN_URL);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2
    ]);
    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $curlError !== '') {
      throw new Exception('Tidak dapat terhubung ke server Google.', 502);
    }

    $data = json_decode($raw, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($data) || empty($data['id_token'])) {
      throw new Exception('Google menolak authorization code. Silakan coba lagi.', 400);
    }

    return $data;
  }

  public function verifyIdToken(string $idToken): array
  {
    $url = self::TOKENINFO_URL . '?' . http_build_query(['id_token' => $idToken]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2
    ]);
    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $curlError !== '' || $httpCode !== 200) {
      throw new Exception('Token Google tidak dapat diverifikasi.', 401);
    }

    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
      throw new Exception('Respons identitas Google tidak valid.', 401);
    }

    $issuer = (string) ($payload['iss'] ?? '');
    $audience = (string) ($payload['aud'] ?? '');
    $sub = trim((string) ($payload['sub'] ?? ''));
    $email = strtolower(trim((string) ($payload['email'] ?? '')));
    $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $exp = (int) ($payload['exp'] ?? 0);

    if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
      throw new Exception('Penerbit token Google tidak valid.', 401);
    }
    if ($audience !== GOOGLE_CLIENT_ID) {
      throw new Exception('Token Google bukan untuk aplikasi BetaKos.', 401);
    }
    if ($sub === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$emailVerified) {
      throw new Exception('Identitas akun Google tidak valid atau email belum terverifikasi.', 401);
    }
    if ($exp <= time()) {
      throw new Exception('Token Google sudah kedaluwarsa.', 401);
    }

    return [
      'sub' => $sub,
      'email' => $email,
      'nama' => trim((string) ($payload['name'] ?? '')),
      'picture' => trim((string) ($payload['picture'] ?? ''))
    ];
  }
}
