<?php

class ApiLokasiReferensiController
{
  public function __construct()
  {
    model('LokasiReferensi');
  }

  public function publicIndex()
  {
    try {
      response(['success' => true, 'data' => getLokasiReferensiPublik()]);
    } catch (Throwable $e) {
      error_log('Public location reference error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Data lokasi referensi belum tersedia.'], 500);
    }
  }

  public function index()
  {
    try {
      response(['success' => true, 'data' => getLokasiReferensiAdmin(
        trim((string)query('search', '')),
        trim((string)query('kategori', '')),
        trim((string)query('status', ''))
      )]);
    } catch (Throwable $e) {
      error_log('Admin location reference list error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat lokasi referensi.'], 500);
    }
  }

  public function show()
  {
    $row = getLokasiReferensiById((int)params('id'));
    if (!$row) response(['success' => false, 'message' => 'Lokasi referensi tidak ditemukan.'], 404);
    response(['success' => true, 'data' => $row]);
  }

  public function resolveGoogleMapsLink()
  {
    $data = input();
    $value = trim((string)($data['url'] ?? ''));

    if ($value === '' || mb_strlen($value) > 2048 || !filter_var($value, FILTER_VALIDATE_URL)) {
      response(['success' => false, 'message' => 'Link Google Maps tidak valid'], 422);
    }

    $host = strtolower((string)parse_url($value, PHP_URL_HOST));
    $allowed = $host === 'maps.app.goo.gl' || $host === 'goo.gl' || $host === 'google.com' || $host === 'google.co.id' || str_ends_with($host, '.google.com') || str_ends_with($host, '.google.co.id');
    if (!$allowed) {
      response(['success' => false, 'message' => 'Gunakan link yang berasal dari Google Maps'], 422);
    }

    $ch = curl_init($value);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_CONNECTTIMEOUT => 8,
      CURLOPT_TIMEOUT => 15,
      CURLOPT_USERAGENT => 'BetaKos/1.0 Google Maps Link Resolver',
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    curl_exec($ch);
    $error = curl_error($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $value;
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error !== '' || $httpCode < 200 || $httpCode >= 400) {
      response(['success' => false, 'message' => 'Link Google Maps tidak dapat dibuka. Coba salin ulang link dari Google Maps.'], 422);
    }

    $finalHost = strtolower((string)parse_url($finalUrl, PHP_URL_HOST));
    $finalAllowed = $finalHost === 'google.com' || $finalHost === 'google.co.id' || str_ends_with($finalHost, '.google.com') || str_ends_with($finalHost, '.google.co.id') || $finalHost === 'maps.google.com';
    if (!$finalAllowed) {
      response(['success' => false, 'message' => 'Link tidak mengarah ke Google Maps'], 422);
    }

    $decoded = urldecode($finalUrl);
    $patterns = [
      '/!3d(-?\\d+(?:\\.\\d+)?)!4d(-?\\d+(?:\\.\\d+)?)/i',
      '/@(-?\\d+(?:\\.\\d+)?),(-?\\d+(?:\\.\\d+)?)/i',
      '/[?&](?:query|q|ll|center)=(-?\\d+(?:\\.\\d+)?)[,%20]+(-?\\d+(?:\\.\\d+)?)/i',
    ];

    foreach ($patterns as $pattern) {
      if (!preg_match($pattern, $decoded, $match)) continue;
      $lat = (float)$match[1];
      $lng = (float)$match[2];
      if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
        response([
          'success' => true,
          'data' => [
            'latitude' => $lat,
            'longitude' => $lng,
            'resolved_url' => $finalUrl,
          ],
        ]);
      }
    }

    response(['success' => false, 'message' => 'Koordinat tidak ditemukan setelah link Google Maps dibuka.'], 422);
  }

  public function store()
  {
    try {
      $id = createLokasiReferensi(input());
      response(['success' => true, 'message' => 'Lokasi berhasil ditambahkan.', 'data' => ['id_lokasi' => $id]], 201);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function update()
  {
    try {
      updateLokasiReferensi((int)params('id'), input());
      response(['success' => true, 'message' => 'Lokasi berhasil diperbarui.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function destroy()
  {
    try {
      deleteLokasiReferensi((int)params('id'));
      response(['success' => true, 'message' => 'Lokasi berhasil dihapus.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
