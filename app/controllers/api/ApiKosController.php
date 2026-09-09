<?php

class ApiKosController
{
  public function __construct()
  {
    model('Kos');
    model('Fasilitas');
    model('Aturan');
  }

  private function owner()
  {
    return $_SESSION['user'];
  }


  public function index()
  {
    $user = $this->owner();

    response([
      'success' => true,
      'data' => getKosByPemilik($user['id_user'])
    ]);
  }


  public function show()
  {
    $id_kos = (int) params('id');
    $user = $this->owner();

    $kos = findKosById(
      $id_kos,
      $user['id_user']
    );

    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    $kos['fasilitas'] = getFasilitasByKos($id_kos, $user['id_user']);
    $kos['aturan'] = getAturanByKos($id_kos, $user['id_user']);

    response([
      'success' => true,
      'data' => $kos
    ]);
  }


  public function store()
  {
    $user = $this->owner();
    $data = input();

    $this->validate($data);

    $conn = db();
    $conn->begin_transaction();

    try {
      $id_kos = createKos($user['id_user'], $data);

      if (!$id_kos) {
        throw new RuntimeException('Gagal menambahkan kos');
      }

      syncFasilitasKos($id_kos, $user['id_user'], $data['fasilitas'] ?? [], false);
      syncAturanKos($id_kos, $user['id_user'], $data['aturan'] ?? [], false);
      $conn->commit();
    } catch (Throwable $e) {
      $conn->rollback();
      $this->databaseFailure($e, 'Gagal menambahkan kos. Silakan coba kembali.');
    }

    response([
      'success' => true,
      'message' => 'Kos berhasil ditambahkan',
      'data' => [
        'id_kos' => $id_kos
      ]
    ], 201);
  }


  public function update()
  {
    $id_kos = (int) params('id');
    $user = $this->owner();
    $data = input();

    $this->validate($data);

    $existingKos = findKosById(
      $id_kos,
      $user['id_user']
    );

    if (!$existingKos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    if (($existingKos['status'] ?? '') === 'menunggu_verifikasi') {
      response([
        'success' => false,
        'message' => 'Kos yang sedang menunggu verifikasi tidak dapat diedit.'
      ], 409);
    }

    $requiresReverification = ($existingKos['status'] ?? '') === 'aktif'
      && $this->hasVerificationCriticalChanges($existingKos, $data);

    $conn = db();
    $conn->begin_transaction();

    try {
      $success = updateKos($id_kos, $user['id_user'], $data, $requiresReverification);

      if (!$success) {
        throw new RuntimeException('Gagal mengubah data kos');
      }

      syncFasilitasKos($id_kos, $user['id_user'], $data['fasilitas'] ?? [], false);
      syncAturanKos($id_kos, $user['id_user'], $data['aturan'] ?? [], false);
      $conn->commit();
    } catch (Throwable $e) {
      $conn->rollback();
      $this->databaseFailure($e, 'Gagal mengubah data kos. Silakan coba kembali.');
    }

    response([
      'success' => true,
      'message' => $requiresReverification
        ? 'Data kos berhasil diperbarui dan perlu diajukan untuk verifikasi ulang.'
        : 'Data kos berhasil diperbarui',
      'data' => [
        'status' => $requiresReverification ? 'draft' : ($existingKos['status'] ?? 'draft'),
        'requires_reverification' => $requiresReverification
      ]
    ]);
  }


  public function destroy()
  {

    $id_kos = (int) params('id');
    $user = $this->owner();

    if (!findKosById($id_kos, $user['id_user'])) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    if (!deleteKos($id_kos, $user['id_user'])) {
      response([
        'success' => false,
        'message' => 'Gagal menghapus kos'
      ], 500);
    }

    response([
      'success' => true,
      'message' => 'Kos berhasil dihapus'
    ]);
  }


  private function validate($data)
  {
    $required = [
      'nama_kos',
      'alamat',
      'latitude',
      'longitude',
      'jenis'
    ];

    foreach ($required as $field) {
      if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
        response([
          'success' => false,
          'message' => "Field {$field} wajib diisi"
        ], 422);
      }
    }

    if (mb_strlen(trim((string)($data['nama_kos'] ?? ''))) > 200) {
      response(['success' => false, 'message' => 'Nama kos maksimal 200 karakter'], 422);
    }

    if (isset($data['aturan']) && !is_array($data['aturan'])) {
      response(['success' => false, 'message' => 'Format aturan kos tidak valid'], 422);
    }

    if (!in_array($data['jenis'], ['putra', 'putri', 'campur'], true)) {
      response([
        'success' => false,
        'message' => 'Jenis kos tidak valid'
      ], 422);
    }

    if (
      !is_numeric($data['latitude']) ||
      $data['latitude'] < -90 ||
      $data['latitude'] > 90
    ) {
      response([
        'success' => false,
        'message' => 'Latitude tidak valid'
      ], 422);
    }

    if (
      !is_numeric($data['longitude']) ||
      $data['longitude'] < -180 ||
      $data['longitude'] > 180
    ) {
      response([
        'success' => false,
        'message' => 'Longitude tidak valid'
      ], 422);
    }

    if (isset($data['google_maps_url']) && trim((string)$data['google_maps_url']) !== '') {
      $googleMapsUrl = trim((string)$data['google_maps_url']);
      if (mb_strlen($googleMapsUrl) > 2048 || !filter_var($googleMapsUrl, FILTER_VALIDATE_URL)) {
        response(['success' => false, 'message' => 'Link Google Maps tidak valid'], 422);
      }
      $host = strtolower((string)parse_url($googleMapsUrl, PHP_URL_HOST));
      $allowed = $host === 'maps.app.goo.gl' || $host === 'goo.gl' || str_ends_with($host, '.google.com') || str_ends_with($host, '.google.co.id') || $host === 'google.com' || $host === 'google.co.id';
      if (!$allowed) {
        response(['success' => false, 'message' => 'Link harus berasal dari Google Maps'], 422);
      }
    }
  }

  private function databaseFailure(Throwable $error, string $fallbackMessage): void
  {
    error_log('Kos save error: ' . $error->getMessage());

    $status = (int) $error->getCode();
    if ($status < 400 || $status > 599) $status = 500;

    response([
      'success' => false,
      'message' => $status < 500 ? $error->getMessage() : $fallbackMessage
    ], $status);
  }

  private function hasVerificationCriticalChanges(array $existing, array $data): bool
  {
    $normalizeText = static function ($value): string {
      return preg_replace('/\s+/u', ' ', trim((string) $value)) ?? trim((string) $value);
    };

    if ($normalizeText($existing['nama_kos'] ?? '') !== $normalizeText($data['nama_kos'] ?? '')) return true;
    if ($normalizeText($existing['alamat'] ?? '') !== $normalizeText($data['alamat'] ?? '')) return true;

    $oldLatitude = (float) ($existing['latitude'] ?? 0);
    $newLatitude = (float) ($data['latitude'] ?? 0);
    $oldLongitude = (float) ($existing['longitude'] ?? 0);
    $newLongitude = (float) ($data['longitude'] ?? 0);
    if (abs($oldLatitude - $newLatitude) > 0.00000001) return true;
    if (abs($oldLongitude - $newLongitude) > 0.00000001) return true;

    $oldMapsUrl = trim((string) ($existing['google_maps_url'] ?? ''));
    $newMapsUrl = trim((string) ($data['google_maps_url'] ?? ''));
    return $oldMapsUrl !== $newMapsUrl;
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
        response(['success' => true, 'data' => ['latitude' => $lat, 'longitude' => $lng, 'resolved_url' => $finalUrl]]);
      }
    }

    response(['success' => false, 'message' => 'Koordinat tidak ditemukan setelah link Google Maps dibuka.'], 422);
  }

  public function fasilitas()
  {
    $data = getAllFasilitas();

    response([
      'success' => true,
      'data' => $data
    ]);
  }
}
