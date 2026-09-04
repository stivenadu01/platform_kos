<?php

require_once ROOT_PATH . '/app/models/TipeKamar.php';

function getKosByPemilik($id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      k.id_kos,
      k.nama_kos,
      k.alamat,
      k.latitude,
      k.longitude,
      k.jenis,
      k.deskripsi,
      k.status,
      k.created_at,
      k.updated_at,
      vk.status AS status_verifikasi,
      vk.catatan AS catatan_verifikasi,
      vk.tanggal_verifikasi,
      COUNT(DISTINCT km.id_kamar) AS jumlah_kamar,
      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia,
      COUNT(DISTINCT CASE WHEN km.status = 'terisi' THEN km.id_kamar END) AS kamar_terisi,
      COUNT(DISTINCT CASE WHEN km.status = 'tidak_tersedia' THEN km.id_kamar END) AS kamar_tidak_tersedia
    FROM kos k
    LEFT JOIN kamar km ON km.id_kos = k.id_kos
    LEFT JOIN (
      SELECT v.id_kos, v.status, v.catatan, v.tanggal_verifikasi
      FROM verifikasi_kos v
      INNER JOIN (
        SELECT id_kos, MAX(id_verifikasi) AS id_verifikasi
        FROM verifikasi_kos
        GROUP BY id_kos
      ) latest ON latest.id_verifikasi = v.id_verifikasi
    ) vk ON vk.id_kos = k.id_kos
    WHERE k.id_pemilik = ?
    GROUP BY k.id_kos, vk.status, vk.catatan, vk.tanggal_verifikasi
    ORDER BY k.id_kos DESC
  ");

  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);

  $stmt->close();

  return $data;
}


function findKosById($id_kos, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_kos,
      id_pemilik,
      nama_kos,
      alamat,
      latitude,
      longitude,
      jenis,
      deskripsi,
      status,
      created_at,
      updated_at
    FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();

  $data = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  return $data;
}


function createKos($id_pemilik, $data)
{
  $conn = db();

  $nama_kos = trim($data['nama_kos']);
  $alamat = trim($data['alamat']);
  $latitude = (float) $data['latitude'];
  $longitude = (float) $data['longitude'];
  $jenis = $data['jenis'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  $stmt = $conn->prepare("
    INSERT INTO kos (
      id_pemilik,
      nama_kos,
      alamat,
      latitude,
      longitude,
      jenis,
      deskripsi,
      status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')
  ");

  $stmt->bind_param(
    'issddss',
    $id_pemilik,
    $nama_kos,
    $alamat,
    $latitude,
    $longitude,
    $jenis,
    $deskripsi
  );

  $success = $stmt->execute();
  $id = $stmt->insert_id;

  $stmt->close();

  return $success ? $id : false;
}


function updateKos($id_kos, $id_pemilik, $data)
{
  $conn = db();

  $nama_kos = trim($data['nama_kos']);
  $alamat = trim($data['alamat']);
  $latitude = (float) $data['latitude'];
  $longitude = (float) $data['longitude'];
  $jenis = $data['jenis'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  $stmt = $conn->prepare("
    UPDATE kos
    SET
      nama_kos = ?,
      alamat = ?,
      latitude = ?,
      longitude = ?,
      jenis = ?,
      deskripsi = ?
    WHERE id_kos = ?
      AND id_pemilik = ?
  ");

  $stmt->bind_param(
    'ssddssii',
    $nama_kos,
    $alamat,
    $latitude,
    $longitude,
    $jenis,
    $deskripsi,
    $id_kos,
    $id_pemilik
  );

  $success = $stmt->execute();

  $stmt->close();

  return $success;
}


function deleteKos($id_kos, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    DELETE FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);

  $success = $stmt->execute();

  $stmt->close();

  return $success;
}

function getKosUnggulanUntukHome($limit = 6)
{
  $conn = db();
  $limit = max(1, min((int) $limit, 12));

  $sql = "
    SELECT
      k.id_kos,
      k.nama_kos,
      k.alamat,
      k.jenis,
      k.deskripsi,
      k.latitude,
      k.longitude,
      (
        SELECT f.nama_file
        FROM kos_foto f
        WHERE f.id_kos = k.id_kos
        ORDER BY f.is_thumbnail DESC, f.urutan ASC, f.id_foto ASC
        LIMIT 1
      ) AS foto,
      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia,
      MIN(hk.harga_total) AS harga_mulai
    FROM kos k
    LEFT JOIN kamar km ON km.id_kos = k.id_kos
    LEFT JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    LEFT JOIN harga_kamar hk ON hk.id_tipe_kamar = tk.id_tipe_kamar
    WHERE k.status = 'aktif'
    GROUP BY k.id_kos
    HAVING kamar_tersedia > 0
    ORDER BY k.created_at DESC, k.id_kos DESC
    LIMIT {$limit}
  ";

  $result = $conn->query($sql);
  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  return $data;
}

/**
 * Pencarian publik kos.
 *
 * Kos yang ditampilkan hanya kos aktif dan kamar yang tersedia.
 * Filter kampus menggunakan jarak koordinat kampus -> kos.
 */
function searchKosPublik($filters = [])
{
  $conn = db();

  $q = trim($filters['q'] ?? '');
  $jenis = trim($filters['jenis'] ?? '');
  $kapasitas = isset($filters['kapasitas']) && ctype_digit((string)$filters['kapasitas'])
    ? (int)$filters['kapasitas'] : 0;
  $harga_min = is_numeric($filters['harga_min'] ?? '') ? (float)$filters['harga_min'] : null;
  $harga_max = is_numeric($filters['harga_max'] ?? '') ? (float)$filters['harga_max'] : null;

  $fasilitas = $filters['fasilitas'] ?? [];
  if (!is_array($fasilitas)) $fasilitas = [$fasilitas];
  $fasilitas = array_values(array_unique(array_filter(array_map('intval', $fasilitas), fn($id) => $id > 0)));

  $latitude = is_numeric($filters['latitude'] ?? '') ? (float)$filters['latitude'] : null;
  $longitude = is_numeric($filters['longitude'] ?? '') ? (float)$filters['longitude'] : null;
  $jarak_max = is_numeric($filters['jarak_max'] ?? '') ? (float)$filters['jarak_max'] : null;

  if ($latitude !== null && ($latitude < -90 || $latitude > 90)) $latitude = null;
  if ($longitude !== null && ($longitude < -180 || $longitude > 180)) $longitude = null;
  if ($jarak_max !== null && $jarak_max <= 0) $jarak_max = null;

  // Radius only makes sense when both coordinates are present.
  $useLocation = $latitude !== null && $longitude !== null;

  $page = max(1, (int)($filters['page'] ?? 1));
  $per_page = min(24, max(6, (int)($filters['per_page'] ?? 12)));
  $offset = ($page - 1) * $per_page;

  $where = [
    "k.status = 'aktif'",
    "km.status = 'tersedia'"
  ];
  $params = [];
  $types = '';

  $locationJoin = '';
  $distanceSelect = 'NULL AS jarak_km';
  $distanceWhere = '';
  $radiusParam = null;

  if ($useLocation) {
    // Bind the user's coordinates once via a derived table, then reuse them.
    $locationJoin = "CROSS JOIN (
      SELECT ? AS user_lat, ? AS user_lng
    ) AS loc";
    $params[] = $latitude;
    $params[] = $longitude;
    $types .= 'dd';

    $distanceExpression = "(
      6371 * 2 * ASIN(SQRT(
        POWER(SIN(RADIANS(k.latitude - loc.user_lat) / 2), 2) +
        COS(RADIANS(loc.user_lat)) * COS(RADIANS(k.latitude)) *
        POWER(SIN(RADIANS(k.longitude - loc.user_lng) / 2), 2)
      ))
    )";

    $distanceSelect = "$distanceExpression AS jarak_km";

    if ($jarak_max !== null) {
      // IMPORTANT: this placeholder appears after the normal WHERE
      // placeholders in the final SQL, so its value must be appended
      // after q/jenis/kapasitas/harga parameters below.
      $distanceWhere = "AND $distanceExpression <= ?";
      $radiusParam = $jarak_max;
    }
  }

  if ($q !== '') {
    $where[] = '(k.nama_kos LIKE ? OR k.alamat LIKE ? OR k.deskripsi LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
    $types .= 'sss';
  }

  if (in_array($jenis, ['putra', 'putri', 'campur'], true)) {
    $where[] = 'k.jenis = ?';
    $params[] = $jenis;
    $types .= 's';
  }

  if ($kapasitas > 0) {
    // Kapasitas yang dipilih menentukan harga yang relevan.
    // Contoh: filter 2 orang harus mengambil harga jumlah_orang = 2,
    // bukan harga termurah untuk 1 orang.
    $where[] = 'tk.kapasitas >= ?';
    $params[] = $kapasitas;
    $types .= 'i';

    $where[] = 'hk.jumlah_orang = ?';
    $params[] = $kapasitas;
    $types .= 'i';
  }

  if ($harga_min !== null && $harga_min >= 0) {
    $where[] = 'hk.harga_total >= ?';
    $params[] = $harga_min;
    $types .= 'd';
  }

  if ($harga_max !== null && $harga_max >= 0) {
    $where[] = 'hk.harga_total <= ?';
    $params[] = $harga_max;
    $types .= 'd';
  }

  foreach ($fasilitas as $id_fasilitas) {
    $where[] = 'EXISTS (
      SELECT 1
      FROM kos_fasilitas kf_filter
      WHERE kf_filter.id_kos = k.id_kos
        AND kf_filter.id_fasilitas = ?
    )';
    $params[] = $id_fasilitas;
    $types .= 'i';
  }

  // The radius placeholder is physically located after $whereSql in SQL.
  // Append it only after all WHERE parameters so bind order matches SQL order.
  if ($radiusParam !== null) {
    $params[] = $radiusParam;
    $types .= 'd';
  }

  $whereSql = implode(' AND ', $where);

  $countSql = "
    SELECT COUNT(DISTINCT k.id_kos) AS total
    FROM kos k
    JOIN kamar km ON km.id_kos = k.id_kos
    JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    JOIN harga_kamar hk ON hk.id_tipe_kamar = tk.id_tipe_kamar
    $locationJoin
    WHERE $whereSql $distanceWhere
  ";

  $stmt = $conn->prepare($countSql);
  if (!$stmt) {
    throw new Exception('Gagal menyiapkan pencarian kos: ' . $conn->error, 500);
  }
  if ($types !== '') {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  $dataParams = $params;
  $dataTypes = $types;
  $dataParams[] = $per_page;
  $dataParams[] = $offset;
  $dataTypes .= 'ii';

  $order = $useLocation
    ? 'ORDER BY jarak_km ASC, k.nama_kos ASC'
    : 'ORDER BY k.nama_kos ASC';

  $sql = "
    SELECT
      k.id_kos,
      k.nama_kos,
      k.alamat,
      k.latitude,
      k.longitude,
      k.jenis,
      k.deskripsi,
      MIN(hk.harga_total) AS harga_mulai,
      MIN(tk.kapasitas) AS kapasitas_min,
      COUNT(DISTINCT km.id_kamar) AS kamar_tersedia,
      GROUP_CONCAT(DISTINCT tk.nama_tipe ORDER BY tk.nama_tipe SEPARATOR ', ') AS tipe_kamar,
      $distanceSelect,
      (
        SELECT f.nama_file
        FROM kos_foto f
        WHERE f.id_kos = k.id_kos
        ORDER BY f.is_thumbnail DESC, f.urutan ASC, f.id_foto ASC
        LIMIT 1
      ) AS foto
    FROM kos k
    JOIN kamar km ON km.id_kos = k.id_kos
    JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    JOIN harga_kamar hk ON hk.id_tipe_kamar = tk.id_tipe_kamar
    $locationJoin
    WHERE $whereSql $distanceWhere
    GROUP BY k.id_kos, k.nama_kos, k.alamat, k.latitude, k.longitude, k.jenis, k.deskripsi, loc.user_lat, loc.user_lng
    $order
    LIMIT ? OFFSET ?
  ";

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    throw new Exception('Gagal menyiapkan hasil pencarian kos: ' . $conn->error, 500);
  }
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();

  $rows = [];
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $row['id_kos'] = (int)$row['id_kos'];
    $row['harga_mulai'] = (float)$row['harga_mulai'];
    $row['kapasitas_min'] = (int)$row['kapasitas_min'];
    $row['kamar_tersedia'] = (int)$row['kamar_tersedia'];
    $row['jarak_km'] = $row['jarak_km'] !== null ? round((float)$row['jarak_km'], 2) : null;
    $rows[] = $row;
  }
  $stmt->close();

  return [
    'items' => $rows,
    'pagination' => [
      'page' => $page,
      'per_page' => $per_page,
      'total' => $total,
      'total_pages' => $total > 0 ? (int)ceil($total / $per_page) : 0
    ],
    'location' => $useLocation ? [
      'latitude' => $latitude,
      'longitude' => $longitude,
      'jarak_max' => $jarak_max
    ] : null
  ];
}

function getDetailKosPublik($id_kos)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      k.id_kos,
      k.nama_kos,
      k.alamat,
      k.latitude,
      k.longitude,
      k.jenis,
      k.deskripsi,
      u.nama AS nama_pemilik,
      u.no_hp AS no_hp_pemilik,
      u.foto AS foto_pemilik,
      u.last_login_at AS last_login_at,
      CASE WHEN COUNT(DISTINCT lpro.id_langganan) > 0 THEN 1 ELSE 0 END AS pemilik_pro,
      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia,
      COUNT(DISTINCT CASE WHEN km.status = 'tidak_tersedia' THEN km.id_kamar END) AS kamar_tidak_tersedia,
      COUNT(DISTINCT CASE WHEN km.status = 'terisi' THEN km.id_kamar END) AS kamar_terisi,
      MIN(CASE WHEN km.status = 'tersedia' THEN hk.harga_total END) AS harga_mulai
    FROM kos k
    INNER JOIN users u ON u.id_user = k.id_pemilik
    LEFT JOIN kamar km ON km.id_kos = k.id_kos
    LEFT JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    LEFT JOIN harga_kamar hk ON hk.id_tipe_kamar = tk.id_tipe_kamar
    LEFT JOIN langganan lpro ON lpro.id_pemilik = k.id_pemilik
      AND lpro.status = 'aktif'
      AND lpro.tanggal_mulai <= CURDATE()
      AND lpro.tanggal_berakhir >= CURDATE()
    WHERE k.id_kos = ? AND k.status = 'aktif'
    GROUP BY k.id_kos, k.nama_kos, k.alamat, k.latitude, k.longitude, k.jenis, k.deskripsi, u.nama, u.no_hp, u.foto, u.last_login_at
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_kos);
  $stmt->execute();
  $kos = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$kos) {
    return null;
  }

  $stmt = $conn->prepare("
    SELECT
      tk.id_tipe_kamar,
      tk.nama_tipe,
      tk.kapasitas,
      tk.deskripsi,
      COUNT(km.id_kamar) AS jumlah_kamar,
      SUM(CASE WHEN km.status = 'tersedia' THEN 1 ELSE 0 END) AS kamar_tersedia,
      SUM(CASE WHEN km.status = 'tidak_tersedia' THEN 1 ELSE 0 END) AS kamar_tidak_tersedia,
      SUM(CASE WHEN km.status = 'terisi' THEN 1 ELSE 0 END) AS kamar_terisi
    FROM tipe_kamar tk
    LEFT JOIN kamar km ON km.id_tipe_kamar = tk.id_tipe_kamar
    WHERE tk.id_kos = ?
    GROUP BY tk.id_tipe_kamar
    ORDER BY tk.nama_tipe ASC
  ");
  $stmt->bind_param('i', $id_kos);
  $stmt->execute();
  $tipeKamar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  foreach ($tipeKamar as &$tipe) {
    $tipe['harga'] = getHargaTipeKamar((int) $tipe['id_tipe_kamar']);
    $tipe['fasilitas'] = getFasilitasTipeKamar((int) $tipe['id_tipe_kamar']);
    $tipe['foto'] = getFotoTipeKamar((int) $tipe['id_tipe_kamar']);
  }
  unset($tipe);
  $kos['tipe_kamar'] = $tipeKamar;

  $stmt = $conn->prepare("
    SELECT f.id_fasilitas, f.nama_fasilitas
    FROM fasilitas f
    JOIN kos_fasilitas kf ON kf.id_fasilitas = f.id_fasilitas
    WHERE kf.id_kos = ?
    ORDER BY f.nama_fasilitas ASC
  ");
  $stmt->bind_param('i', $id_kos);
  $stmt->execute();
  $kos['fasilitas'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $stmt = $conn->prepare("
    SELECT id_foto, nama_file, urutan, is_thumbnail
    FROM kos_foto
    WHERE id_kos = ?
    ORDER BY is_thumbnail DESC, urutan ASC, id_foto ASC
  ");
  $stmt->bind_param('i', $id_kos);
  $stmt->execute();
  $kos['foto'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $kos;
}

/* =========================================================
   LAPORAN KOS - PELANGGAN
   ========================================================= */

function buatLaporanKos($id_user, $id_kos, $alasan, $deskripsi)
{
  $conn = db();
  $id_user = (int)$id_user;
  $id_kos = (int)$id_kos;
  $deskripsi = trim($deskripsi);

  $allowed = [
    'informasi_tidak_sesuai',
    'foto_tidak_sesuai',
    'kos_sudah_tidak_tersedia',
    'informasi_menyesatkan',
    'lainnya'
  ];

  if ($id_user <= 0 || $id_kos <= 0) throw new Exception('Data laporan tidak valid.', 422);
  if (!in_array($alasan, $allowed, true)) throw new Exception('Alasan laporan tidak valid.', 422);
  if (mb_strlen($deskripsi) < 10) throw new Exception('Jelaskan laporan minimal 10 karakter.', 422);
  if (mb_strlen($deskripsi) > 2000) throw new Exception('Laporan maksimal 2000 karakter.', 422);

  $stmt = $conn->prepare("SELECT id_kos FROM kos WHERE id_kos = ? LIMIT 1");
  $stmt->bind_param('i', $id_kos);
  $stmt->execute();
  $kos = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$kos) throw new Exception('Kos tidak ditemukan.', 404);

  // Hindari spam laporan identik yang masih menunggu/diproses.
  $stmt = $conn->prepare("SELECT id_laporan FROM laporan_kos WHERE id_user = ? AND id_kos = ? AND status IN ('menunggu', 'diproses') LIMIT 1");
  $stmt->bind_param('ii', $id_user, $id_kos);
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if ($existing) throw new Exception('Anda sudah memiliki laporan yang sedang diproses untuk kos ini.', 409);

  $stmt = $conn->prepare("INSERT INTO laporan_kos (id_user, id_kos, alasan, deskripsi) VALUES (?, ?, ?, ?)");
  $stmt->bind_param('iiss', $id_user, $id_kos, $alasan, $deskripsi);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal mengirim laporan.', 500);
  }
  $id = (int)$stmt->insert_id;
  $stmt->close();
  return $id;
}

function getLaporanKosByUser($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT id_laporan, id_kos, alasan, deskripsi, status, catatan_admin, created_at, updated_at,
           (SELECT nama_kos FROM kos WHERE kos.id_kos = laporan_kos.id_kos LIMIT 1) AS nama_kos
    FROM laporan_kos
    WHERE id_user = ?
    ORDER BY id_laporan DESC
  ");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}
