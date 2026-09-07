<?php

function lokasiKategoriIcon($kategori)
{
  return [
    'kampus' => 'graduation-cap',
    'area' => 'map-pin',
    'rumah_sakit' => 'hospital',
    'rumah_makan' => 'utensils',
    'toko' => 'shopping-basket',
    'pusat_perbelanjaan' => 'shopping-bag',
    'transportasi' => 'plane',
    'tempat_wisata' => 'palmtree',
  ][$kategori] ?? 'map-pin';
}

function lokasiKategoriLabel($kategori)
{
  return [
    'kampus' => 'Kampus',
    'area' => 'Area',
    'rumah_sakit' => 'Rumah sakit',
    'rumah_makan' => 'Rumah makan',
    'toko' => 'Toko',
    'pusat_perbelanjaan' => 'Pusat perbelanjaan',
    'transportasi' => 'Transportasi',
    'tempat_wisata' => 'Tempat wisata',
  ][$kategori] ?? $kategori;
}

function getLokasiReferensiPublik()
{
  $conn = db();
  $result = $conn->query("\n    SELECT id_lokasi, nama, kategori, icon, alamat, latitude, longitude, google_maps_url, urutan\n    FROM lokasi_referensi\n    WHERE status = 'aktif'\n    ORDER BY kategori ASC, urutan ASC, nama ASC\n  ");

  if (!$result) throw new RuntimeException('Gagal mengambil lokasi referensi.');

  $groups = ['kampus' => [], 'area' => [], 'penting' => []];
  while ($row = $result->fetch_assoc()) {
    $key = in_array($row['kategori'], ['kampus', 'area'], true) ? $row['kategori'] : 'penting';
    $groups[$key][] = [
      'id_lokasi' => (int)$row['id_lokasi'],
      'kategori' => $row['kategori'],
      'icon' => $row['icon'] ?? lokasiKategoriIcon($row['kategori']),
      'nama' => $row['nama'],
      'label' => $row['nama'],
      'alamat' => $row['alamat'],
      'latitude' => (float)$row['latitude'],
      'longitude' => (float)$row['longitude'],
      'google_maps_url' => $row['google_maps_url'],
      'subtitle' => $row['kategori'] === 'kampus'
        ? 'Cari kos di sekitar kampus'
        : ($row['kategori'] === 'area' ? 'Cari kos di area ini' : 'Cari kos di sekitar tempat ini'),
    ];
  }

  return $groups;
}

function getLokasiReferensiAdmin($search = '', $kategori = '', $status = '')
{
  $conn = db();
  $where = [];
  $types = '';
  $params = [];

  if ($search !== '') {
    $where[] = '(nama LIKE ? OR alamat LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
  }
  if (in_array($kategori, ['kampus', 'area', 'rumah_sakit', 'rumah_makan', 'toko', 'pusat_perbelanjaan', 'transportasi', 'tempat_wisata'], true)) {
    $where[] = 'kategori = ?';
    $types .= 's';
    $params[] = $kategori;
  }
  if (in_array($status, ['aktif', 'nonaktif'], true)) {
    $where[] = 'status = ?';
    $types .= 's';
    $params[] = $status;
  }

  $sql = 'SELECT id_lokasi, nama, kategori, icon, alamat, latitude, longitude, google_maps_url, urutan, status, created_at, updated_at FROM lokasi_referensi';
  if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
  $sql .= ' ORDER BY kategori ASC, urutan ASC, nama ASC';

  $stmt = $conn->prepare($sql);
  if ($types !== '') $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  foreach ($rows as &$row) {
    $row['id_lokasi'] = (int)$row['id_lokasi'];
    $row['status'] = normalizeLokasiReferensiStatus($row['status'] ?? null);
    $row['latitude'] = (float)$row['latitude'];
    $row['longitude'] = (float)$row['longitude'];
    $row['urutan'] = (int)$row['urutan'];
  }
  unset($row);
  return $rows;
}

function getLokasiReferensiById($id)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT id_lokasi, nama, kategori, icon, alamat, latitude, longitude, google_maps_url, urutan, status, created_at, updated_at FROM lokasi_referensi WHERE id_lokasi = ? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) return null;
  $row['id_lokasi'] = (int)$row['id_lokasi'];
  $row['status'] = normalizeLokasiReferensiStatus($row['status'] ?? null);
  $row['latitude'] = (float)$row['latitude'];
  $row['longitude'] = (float)$row['longitude'];
  $row['urutan'] = (int)$row['urutan'];
  return $row;
}

function normalizeLokasiReferensiStatus($status)
{
  $value = strtolower(trim((string)$status));
  return in_array($value, ['aktif', 'active', '1', 'enabled'], true) ? 'aktif' : 'nonaktif';
}

function validateLokasiReferensiData($data)
{
  $nama = trim((string)($data['nama'] ?? ''));
  $kategori = trim((string)($data['kategori'] ?? ''));
  $alamat = trim((string)($data['alamat'] ?? ''));
  $latitude = filter_var($data['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
  $longitude = filter_var($data['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
  $googleMapsUrl = trim((string)($data['google_maps_url'] ?? ''));
  $urutan = (int)($data['urutan'] ?? 0);
  $status = trim((string)($data['status'] ?? 'aktif'));

  if ($nama === '' || mb_strlen($nama) > 200) throw new Exception('Nama lokasi wajib diisi dan maksimal 200 karakter.', 422);
  if (!in_array($kategori, ['kampus', 'area', 'rumah_sakit', 'rumah_makan', 'toko', 'pusat_perbelanjaan', 'transportasi', 'tempat_wisata'], true)) throw new Exception('Kategori lokasi tidak valid.', 422);
  if ($latitude === false || $latitude < -90 || $latitude > 90) throw new Exception('Latitude tidak valid.', 422);
  if ($longitude === false || $longitude < -180 || $longitude > 180) throw new Exception('Longitude tidak valid.', 422);
  if ($alamat !== '' && mb_strlen($alamat) > 500) throw new Exception('Alamat maksimal 500 karakter.', 422);
  if ($googleMapsUrl !== '' && mb_strlen($googleMapsUrl) > 2048) throw new Exception('URL Google Maps terlalu panjang.', 422);
  if ($googleMapsUrl !== '' && !filter_var($googleMapsUrl, FILTER_VALIDATE_URL)) throw new Exception('URL Google Maps tidak valid.', 422);
  if ($googleMapsUrl !== '') {
    $host = strtolower((string)parse_url($googleMapsUrl, PHP_URL_HOST));
    $allowedHosts = $host === 'maps.app.goo.gl' || $host === 'goo.gl' || $host === 'google.com' || $host === 'google.co.id' || str_ends_with($host, '.google.com') || str_ends_with($host, '.google.co.id');
    if (!$allowedHosts) throw new Exception('URL harus berasal dari Google Maps.', 422);
  }
  if (!in_array($status, ['aktif', 'nonaktif'], true)) throw new Exception('Status lokasi tidak valid.', 422);

  return [$nama, $kategori, $alamat !== '' ? $alamat : null, (float)$latitude, (float)$longitude, $googleMapsUrl !== '' ? $googleMapsUrl : null, $urutan, $status];
}

function createLokasiReferensi($data)
{
  [$nama, $kategori, $alamat, $latitude, $longitude, $googleMapsUrl, $urutan, $status] = validateLokasiReferensiData($data);
  $conn = db();
  $icon = lokasiKategoriIcon($kategori);
  $stmt = $conn->prepare('INSERT INTO lokasi_referensi (nama, kategori, icon, alamat, latitude, longitude, google_maps_url, urutan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->bind_param('ssssddsis', $nama, $kategori, $icon, $alamat, $latitude, $longitude, $googleMapsUrl, $urutan, $status);
  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    throw new Exception('Gagal menambahkan lokasi: ' . $error, 500);
  }
  $id = $conn->insert_id;
  $stmt->close();
  return (int)$id;
}

function updateLokasiReferensi($id, $data)
{
  if ($id <= 0) throw new Exception('ID lokasi tidak valid.', 422);
  if (!getLokasiReferensiById($id)) throw new Exception('Lokasi referensi tidak ditemukan.', 404);

  [$nama, $kategori, $alamat, $latitude, $longitude, $googleMapsUrl, $urutan, $status] = validateLokasiReferensiData($data);
  $conn = db();
  $icon = lokasiKategoriIcon($kategori);
  $stmt = $conn->prepare('UPDATE lokasi_referensi SET nama = ?, kategori = ?, icon = ?, alamat = ?, latitude = ?, longitude = ?, google_maps_url = ?, urutan = ?, status = ? WHERE id_lokasi = ?');
  $stmt->bind_param('ssssdddisi', $nama, $kategori, $icon, $alamat, $latitude, $longitude, $googleMapsUrl, $urutan, $status, $id);
  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    throw new Exception('Gagal memperbarui lokasi: ' . $error, 500);
  }
  $stmt->close();
}

function deleteLokasiReferensi($id)
{
  if ($id <= 0) throw new Exception('ID lokasi tidak valid.', 422);
  $conn = db();
  $stmt = $conn->prepare('DELETE FROM lokasi_referensi WHERE id_lokasi = ?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();
  if ($affected === 0) throw new Exception('Lokasi referensi tidak ditemukan.', 404);
}


/**
 * Mengambil beberapa lokasi dan lingkungan terdekat dari koordinat kos.
 * Perhitungan jarak menggunakan Haversine di PHP agar kompatibel lintas versi MySQL.
 */
function getLokasiPopulerSekitar($latitude, $longitude, $limit = 6, $maxDistanceKm = 20)
{
  $latitude = (float)$latitude;
  $longitude = (float)$longitude;
  $limit = max(1, min(12, (int)$limit));
  $maxDistanceKm = max(1, (float)$maxDistanceKm);

  if (!is_finite($latitude) || !is_finite($longitude) ||
      $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    return [];
  }

  $conn = db();
  $result = $conn->query("\n    SELECT id_lokasi, nama, kategori, icon, alamat, latitude, longitude, google_maps_url\n    FROM lokasi_referensi\n    WHERE status = 'aktif' AND kategori <> 'area'\n    ORDER BY urutan ASC, nama ASC\n  ");

  if (!$result) {
    throw new RuntimeException('Gagal mengambil lokasi dan lingkungan sekitar.');
  }

  $items = [];
  while ($row = $result->fetch_assoc()) {
    $targetLat = (float)$row['latitude'];
    $targetLng = (float)$row['longitude'];

    if (!is_finite($targetLat) || !is_finite($targetLng) ||
        $targetLat < -90 || $targetLat > 90 || $targetLng < -180 || $targetLng > 180) {
      continue;
    }

    $lat1 = deg2rad($latitude);
    $lat2 = deg2rad($targetLat);
    $dLat = deg2rad($targetLat - $latitude);
    $dLng = deg2rad($targetLng - $longitude);
    $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;
    $a = min(1, max(0, $a));
    $distanceKm = 6371 * 2 * asin(sqrt($a));

    if ($distanceKm > $maxDistanceKm) {
      continue;
    }

    $items[] = [
      'id_lokasi' => (int)$row['id_lokasi'],
      'kategori' => $row['kategori'],
      'icon' => $row['icon'] ?? lokasiKategoriIcon($row['kategori']),
      'nama' => $row['nama'],
      'alamat' => $row['alamat'],
      'latitude' => $targetLat,
      'longitude' => $targetLng,
      'google_maps_url' => $row['google_maps_url'],
      'jarak_km' => $distanceKm,
    ];
  }
  $result->free();

  usort($items, static function ($a, $b) {
    return $a['jarak_km'] <=> $b['jarak_km'];
  });

  return array_slice($items, 0, $limit);
}
