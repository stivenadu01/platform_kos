<?php

// Admin membutuhkan fungsi user (findUserByEmail, tambahUser).
// Pastikan model User dimuat meskipun Admin dipanggil secara langsung dari API.
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Langganan.php';
require_once ROOT_PATH . '/app/models/MetodePembayaranLangganan.php';

function getAdminVerificationSummary()
{
  $conn = db();

  $sql = "
    SELECT
      SUM(CASE WHEN vk.status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
      SUM(CASE WHEN vk.status = 'disetujui' THEN 1 ELSE 0 END) AS disetujui,
      SUM(CASE WHEN vk.status = 'ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM verifikasi_kos vk
    INNER JOIN (
      SELECT id_kos, MAX(id_verifikasi) AS id_verifikasi
      FROM verifikasi_kos
      GROUP BY id_kos
    ) latest ON latest.id_verifikasi = vk.id_verifikasi
  ";

  $row = $conn->query($sql)->fetch_assoc() ?: [];

  return [
    'menunggu' => (int)($row['menunggu'] ?? 0),
    'disetujui' => (int)($row['disetujui'] ?? 0),
    'ditolak' => (int)($row['ditolak'] ?? 0)
  ];
}

function getAdminVerifikasiList($status = 'menunggu')
{
  $conn = db();

  $allowed = ['menunggu', 'disetujui', 'ditolak', ''];
  if (!in_array($status, $allowed, true)) {
    $status = 'menunggu';
  }

  $where = $status !== '' ? 'WHERE vk.status = ?' : '';

  $sql = "
    SELECT
      vk.id_verifikasi,
      vk.id_kos,
      vk.status AS status_verifikasi,
      vk.catatan,
      vk.tanggal_pengajuan,
      vk.tanggal_verifikasi,
      k.nama_kos,
      k.alamat,
      k.jenis,
      k.status AS status_kos,
      k.latitude,
      k.longitude,
      u.id_user AS id_pemilik,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      u.no_hp AS no_hp_pemilik,
      COUNT(DISTINCT km.id_kamar) AS jumlah_kamar,
      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia
    FROM verifikasi_kos vk
    INNER JOIN kos k ON k.id_kos = vk.id_kos
    INNER JOIN users u ON u.id_user = k.id_pemilik
    LEFT JOIN kamar km ON km.id_kos = k.id_kos
    INNER JOIN (
      SELECT id_kos, MAX(id_verifikasi) AS id_verifikasi
      FROM verifikasi_kos
      GROUP BY id_kos
    ) latest ON latest.id_verifikasi = vk.id_verifikasi
    $where
    GROUP BY
      vk.id_verifikasi, vk.id_kos, vk.status, vk.catatan,
      vk.tanggal_pengajuan, vk.tanggal_verifikasi,
      k.nama_kos, k.alamat, k.jenis, k.status, k.latitude, k.longitude,
      u.id_user, u.nama, u.email, u.no_hp
    ORDER BY vk.id_verifikasi DESC
  ";

  $stmt = $conn->prepare($sql);
  if ($status !== '') {
    $stmt->bind_param('s', $status);
  }
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $data;
}

function getAdminVerifikasiDetail($id_verifikasi)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      vk.id_verifikasi,
      vk.id_kos,
      vk.status AS status_verifikasi,
      vk.catatan,
      vk.tanggal_pengajuan,
      vk.tanggal_verifikasi,
      k.nama_kos,
      k.alamat,
      k.latitude,
      k.longitude,
      k.jenis,
      k.deskripsi,
      k.status AS status_kos,
      u.id_user AS id_pemilik,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      u.no_hp AS no_hp_pemilik,
      u.nik AS nik_pemilik
    FROM verifikasi_kos vk
    INNER JOIN kos k ON k.id_kos = vk.id_kos
    INNER JOIN users u ON u.id_user = k.id_pemilik
    WHERE vk.id_verifikasi = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_verifikasi);
  $stmt->execute();
  $kos = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$kos) return null;

  $stmt = $conn->prepare("
    SELECT km.id_kamar, km.nomor_kamar, km.id_tipe_kamar,
      tk.nama_tipe AS tipe_kamar, tk.kapasitas, km.status, km.deskripsi
    FROM kamar km
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    WHERE km.id_kos = ?
    ORDER BY nomor_kamar ASC, id_kamar ASC
  ");
  $stmt->bind_param('i', $kos['id_kos']);
  $stmt->execute();
  $kamar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  foreach ($kamar as &$item) {
    $stmt = $conn->prepare("SELECT jumlah_orang, harga_total FROM harga_kamar WHERE id_tipe_kamar = ? ORDER BY jumlah_orang ASC");
    $stmt->bind_param('i', $item['id_tipe_kamar']);
    $stmt->execute();
    $item['harga'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  }
  unset($item);

  $stmt = $conn->prepare("
    SELECT id_foto, nama_file, urutan, is_thumbnail
    FROM kos_foto
    WHERE id_kos = ?
    ORDER BY is_thumbnail DESC, urutan ASC, id_foto ASC
  ");
  $stmt->bind_param('i', $kos['id_kos']);
  $stmt->execute();
  $foto = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $kos['kamar'] = $kamar;
  $kos['foto'] = $foto;

  return $kos;
}

function prosesVerifikasiKos($id_verifikasi, $id_admin, $keputusan, $catatan = '')
{
  $conn = db();

  if (!in_array($keputusan, ['disetujui', 'ditolak'], true)) {
    throw new Exception('Keputusan verifikasi tidak valid', 422);
  }

  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("SELECT id_kos, status FROM verifikasi_kos WHERE id_verifikasi = ? LIMIT 1");
    $stmt->bind_param('i', $id_verifikasi);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) throw new Exception('Pengajuan verifikasi tidak ditemukan', 404);
    if ($row['status'] !== 'menunggu') throw new Exception('Pengajuan ini sudah diproses', 409);

    $stmt = $conn->prepare("
      UPDATE verifikasi_kos
      SET status = ?, catatan = ?, id_admin = ?, tanggal_verifikasi = NOW()
      WHERE id_verifikasi = ? AND status = 'menunggu'
    ");
    $stmt->bind_param('ssii', $keputusan, $catatan, $id_admin, $id_verifikasi);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
      $stmt->close();
      throw new Exception('Gagal memperbarui verifikasi', 500);
    }
    $stmt->close();

    $statusKos = $keputusan === 'disetujui' ? 'aktif' : 'ditolak';
    $stmt = $conn->prepare("UPDATE kos SET status = ? WHERE id_kos = ? AND status = 'menunggu_verifikasi'");
    $stmt->bind_param('si', $statusKos, $row['id_kos']);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
      $stmt->close();
      throw new Exception('Status kos tidak dapat diperbarui', 500);
    }
    $stmt->close();

    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function ajukanVerifikasiKos($id_kos, $id_pemilik)
{
  $conn = db();
  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("SELECT status FROM kos WHERE id_kos = ? AND id_pemilik = ? LIMIT 1");
    $stmt->bind_param('ii', $id_kos, $id_pemilik);
    $stmt->execute();
    $kos = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$kos) throw new Exception('Kos tidak ditemukan', 404);
    if (!in_array($kos['status'], ['draft', 'ditolak'], true)) {
      throw new Exception('Kos belum dapat diajukan untuk verifikasi', 409);
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM verifikasi_kos WHERE id_kos = ? AND status = 'menunggu'");
    $stmt->bind_param('i', $id_kos);
    $stmt->execute();
    $pending = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    if ($pending > 0) throw new Exception('Kos sedang menunggu verifikasi admin', 409);

    $stmt = $conn->prepare("INSERT INTO verifikasi_kos (id_kos, status) VALUES (?, 'menunggu')");
    $stmt->bind_param('i', $id_kos);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal membuat pengajuan verifikasi', 500);
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE kos SET status = 'menunggu_verifikasi' WHERE id_kos = ? AND id_pemilik = ?");
    $stmt->bind_param('ii', $id_kos, $id_pemilik);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}


/* =========================================================
   MANAJEMEN PENGGUNA ADMIN
   ========================================================= */

function getAdminUserSummary()
{
  $conn = db();

  $sql = "
    SELECT
      COUNT(*) AS total,
      SUM(CASE WHEN role = 'pemilik' THEN 1 ELSE 0 END) AS pemilik,
      SUM(CASE WHEN role = 'pelanggan' THEN 1 ELSE 0 END) AS pelanggan,
      SUM(CASE WHEN email_verified_at IS NULL THEN 1 ELSE 0 END) AS belum_verifikasi,
      SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS aktif,
      SUM(CASE WHEN status <> 'aktif' THEN 1 ELSE 0 END) AS nonaktif
    FROM users
  ";

  $row = $conn->query($sql)->fetch_assoc() ?: [];

  return [
    'total' => (int)($row['total'] ?? 0),
    'pemilik' => (int)($row['pemilik'] ?? 0),
    'pelanggan' => (int)($row['pelanggan'] ?? 0),
    'belum_verifikasi' => (int)($row['belum_verifikasi'] ?? 0),
    'aktif' => (int)($row['aktif'] ?? 0),
    'nonaktif' => (int)($row['nonaktif'] ?? 0)
  ];
}

function getAdminUserList($page = 1, $limit = 10, $search = '', $role = '', $verification = '', $status = '')
{
  $conn = db();
  $page = max(1, (int)$page);
  $limit = max(1, min(50, (int)$limit));
  $offset = ($page - 1) * $limit;

  $where = [];
  $params = [];
  $types = '';

  if ($search !== '') {
    $where[] = '(u.nama LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ? OR u.nik LIKE ?)';
    $safe = "%{$search}%";
    array_push($params, $safe, $safe, $safe, $safe);
    $types .= 'ssss';
  }

  if (in_array($role, ['pelanggan', 'pemilik', 'admin'], true)) {
    $where[] = 'u.role = ?';
    $params[] = $role;
    $types .= 's';
  }

  if ($verification === 'terverifikasi') {
    $where[] = 'u.email_verified_at IS NOT NULL';
  } elseif ($verification === 'belum') {
    $where[] = 'u.email_verified_at IS NULL';
  }

  if (in_array($status, ['aktif', 'nonaktif', 'ditangguhkan'], true)) {
    $where[] = 'u.status = ?';
    $params[] = $status;
    $types .= 's';
  }

  $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users u $whereSql");
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  $sql = "
    SELECT
      u.id_user,
      u.nama,
      u.nik,
      u.email,
      u.no_hp,
      u.role,
      u.foto,
      u.email_verified_at,
      u.status,
      u.created_at,
      u.updated_at,
      u.last_login_at
    FROM users u
    $whereSql
    ORDER BY u.id_user DESC
    LIMIT ? OFFSET ?
  ";

  $dataParams = $params;
  $dataTypes = $types . 'ii';
  $dataParams[] = $limit;
  $dataParams[] = $offset;

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return [
    'items' => $data,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => max(1, (int)ceil($total / $limit))
  ];
}

function adminVerifyUser($id_user)
{
  $conn = db();
  $id_user = (int)$id_user;
  if ($id_user <= 0) throw new Exception('Pengguna tidak valid', 422);

  $stmt = $conn->prepare("SELECT id_user, email_verified_at FROM users WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$user) throw new Exception('Pengguna tidak ditemukan', 404);
  if (!empty($user['email_verified_at'])) return true;

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE id_user = ? AND email_verified_at IS NULL");
    $stmt->bind_param('i', $id_user);
    if (!$stmt->execute()) throw new Exception('Gagal memverifikasi pengguna', 500);
    $stmt->close();

    // Token lama tidak boleh dipakai lagi setelah diverifikasi oleh Admin.
    $stmt = $conn->prepare("UPDATE user_verification_tokens SET used_at = NOW() WHERE id_user = ? AND used_at IS NULL");
    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function adminUpdateUserStatus($id_user, $status, $admin_id)
{
  $conn = db();
  $id_user = (int)$id_user;
  $admin_id = (int)$admin_id;

  if (!in_array($status, ['aktif', 'nonaktif', 'ditangguhkan'], true)) {
    throw new Exception('Status pengguna tidak valid', 422);
  }
  if ($id_user <= 0) throw new Exception('Pengguna tidak valid', 422);
  if ($id_user === $admin_id) throw new Exception('Admin tidak dapat menonaktifkan atau menangguhkan akun sendiri', 409);

  $stmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $exists = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$exists) throw new Exception('Pengguna tidak ditemukan', 404);

  $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id_user = ?");
  $stmt->bind_param('si', $status, $id_user);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal mengubah status pengguna', 500);
  }
  $stmt->close();
  return true;
}

function adminCreateUser($data)
{
  $conn = db();

  $email = strtolower(trim($data['email'] ?? ''));
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Email tidak valid', 422);
  }
  if (findUserByEmail($email)) {
    throw new Exception('Email sudah digunakan', 409);
  }

  $data['email'] = $email;
  $data['role'] = $data['role'] ?? 'pelanggan';
  if (!in_array($data['role'], ['pelanggan', 'pemilik', 'admin'], true)) {
    throw new Exception('Role pengguna tidak valid', 422);
  }
  if (strlen($data['password'] ?? '') < 8) {
    throw new Exception('Kata sandi minimal 8 karakter', 422);
  }

  try {
    $ok = tambahUser($data, false);
    if (!$ok) throw new Exception('Gagal menambahkan pengguna', 500);
    $created = findUserByEmail($email);
    return (int)($created['id_user'] ?? 0);
  } catch (mysqli_sql_exception $e) {
    throw new Exception('Gagal menambahkan pengguna. Pastikan email/NIK belum digunakan.', 409);
  }
}

/* =========================================================
   LAPORAN KOS / MODERASI ADMIN
   ========================================================= */

function getAdminLaporanSummary()
{
  $conn = db();
  $row = $conn->query("
    SELECT
      COUNT(*) AS total,
      SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
      SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) AS diproses,
      SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
      SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM laporan_kos
  ")->fetch_assoc() ?: [];

  return [
    'total' => (int)($row['total'] ?? 0),
    'menunggu' => (int)($row['menunggu'] ?? 0),
    'diproses' => (int)($row['diproses'] ?? 0),
    'selesai' => (int)($row['selesai'] ?? 0),
    'ditolak' => (int)($row['ditolak'] ?? 0),
  ];
}

function getAdminLaporanList($page = 1, $limit = 10, $status = '', $search = '')
{
  $conn = db();
  $page = max(1, (int)$page);
  $limit = max(1, min(50, (int)$limit));
  $offset = ($page - 1) * $limit;

  $where = [];
  $params = [];
  $types = '';

  if (in_array($status, ['menunggu', 'diproses', 'selesai', 'ditolak'], true)) {
    $where[] = 'l.status = ?';
    $params[] = $status;
    $types .= 's';
  }

  if ($search !== '') {
    $where[] = '(k.nama_kos LIKE ? OR u.nama LIKE ? OR u.email LIKE ?)';
    $safe = "%{$search}%";
    array_push($params, $safe, $safe, $safe);
    $types .= 'sss';
  }

  $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM laporan_kos l INNER JOIN kos k ON k.id_kos = l.id_kos INNER JOIN users u ON u.id_user = l.id_user $whereSql");
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  $sql = "
    SELECT
      l.id_laporan,
      l.id_kos,
      l.id_user,
      l.alasan,
      l.deskripsi,
      l.status,
      l.catatan_admin,
      l.created_at,
      l.updated_at,
      k.nama_kos,
      k.status AS status_kos,
      u.nama AS nama_pelapor,
      u.email AS email_pelapor,
      a.nama AS nama_admin
    FROM laporan_kos l
    INNER JOIN kos k ON k.id_kos = l.id_kos
    INNER JOIN users u ON u.id_user = l.id_user
    LEFT JOIN users a ON a.id_user = l.id_admin
    $whereSql
    ORDER BY l.id_laporan DESC
    LIMIT ? OFFSET ?
  ";

  $dataParams = $params;
  $dataTypes = $types . 'ii';
  $dataParams[] = $limit;
  $dataParams[] = $offset;

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();
  $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return [
    'items' => $items,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'total_pages' => max(1, (int)ceil($total / $limit))
  ];
}

function getAdminLaporanDetail($id_laporan)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT
      l.*, k.nama_kos, k.alamat, k.status AS status_kos,
      u.nama AS nama_pelapor, u.email AS email_pelapor, u.no_hp AS no_hp_pelapor,
      a.nama AS nama_admin
    FROM laporan_kos l
    INNER JOIN kos k ON k.id_kos = l.id_kos
    INNER JOIN users u ON u.id_user = l.id_user
    LEFT JOIN users a ON a.id_user = l.id_admin
    WHERE l.id_laporan = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_laporan);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $row ?: null;
}

function prosesLaporanKos($id_laporan, $id_admin, $status, $catatan_admin = '')
{
  $conn = db();
  if (!in_array($status, ['diproses', 'selesai', 'ditolak'], true)) {
    throw new Exception('Status laporan tidak valid.', 422);
  }
  if ($id_laporan <= 0) throw new Exception('Laporan tidak valid.', 422);

  $stmt = $conn->prepare("SELECT id_laporan, status FROM laporan_kos WHERE id_laporan = ? LIMIT 1");
  $stmt->bind_param('i', $id_laporan);
  $stmt->execute();
  $laporan = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$laporan) throw new Exception('Laporan tidak ditemukan.', 404);
  if (in_array($laporan['status'], ['selesai', 'ditolak'], true)) {
    throw new Exception('Laporan yang sudah selesai atau ditolak tidak dapat diproses kembali.', 409);
  }
  if ($status === 'ditolak' && $catatan_admin === '') {
    throw new Exception('Catatan wajib diisi ketika laporan ditolak.', 422);
  }

  $stmt = $conn->prepare("UPDATE laporan_kos SET status = ?, catatan_admin = ?, id_admin = ? WHERE id_laporan = ?");
  $stmt->bind_param('ssii', $status, $catatan_admin, $id_admin, $id_laporan);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal memperbarui laporan.', 500);
  }
  $stmt->close();
  return true;
}

/* =========================================================
   DASHBOARD ADMIN LENGKAP
   ========================================================= */

function getAdminDashboardData()
{
  $conn = db();

  $scalar = function ($sql) use ($conn) {
    $result = $conn->query($sql);
    if (!$result) throw new RuntimeException('Gagal mengambil data dashboard.');
    $row = $result->fetch_assoc() ?: [];
    return $row;
  };

  $users = $scalar("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS aktif,
    SUM(CASE WHEN status <> 'aktif' THEN 1 ELSE 0 END) AS nonaktif,
    SUM(CASE WHEN role = 'pemilik' THEN 1 ELSE 0 END) AS pemilik,
    SUM(CASE WHEN role = 'pelanggan' THEN 1 ELSE 0 END) AS pelanggan,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin,
    SUM(CASE WHEN email_verified_at IS NULL THEN 1 ELSE 0 END) AS belum_verifikasi,
    SUM(CASE WHEN last_login_at >= CURRENT_DATE THEN 1 ELSE 0 END) AS login_hari_ini,
    SUM(CASE WHEN last_login_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS login_7_hari
    FROM users");

  $kos = $scalar("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS aktif,
    SUM(CASE WHEN status = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS menunggu_verifikasi,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS ditolak
    FROM kos");

  $kamar = $scalar("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'terisi' THEN 1 ELSE 0 END) AS terisi,
    SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) AS tersedia
    FROM kamar");

  $penghuni = $scalar("SELECT COUNT(*) AS aktif FROM penghuni WHERE status = 'aktif'");

  $verification = getAdminVerificationSummary();
  $subscriptions = getAdminSubscriptionSummary();
  $laporan = getAdminLaporanSummary();

  $payment = $scalar("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
    SUM(CASE WHEN status = 'diverifikasi' THEN 1 ELSE 0 END) AS diverifikasi,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) AS ditolak,
    COALESCE(SUM(CASE WHEN status = 'diverifikasi' THEN nominal ELSE 0 END), 0) AS total_terverifikasi,
    COALESCE(SUM(CASE WHEN status = 'diverifikasi' AND tanggal_verifikasi >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01') THEN nominal ELSE 0 END), 0) AS pendapatan_bulan_ini
    FROM pembayaran_langganan");

  $paymentMethods = $scalar("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN is_aktif = 1 THEN 1 ELSE 0 END) AS aktif,
    SUM(CASE WHEN is_aktif = 0 THEN 1 ELSE 0 END) AS nonaktif,
    SUM(CASE WHEN jenis = 'transfer_bank' AND is_aktif = 1 THEN 1 ELSE 0 END) AS bank_aktif,
    SUM(CASE WHEN jenis = 'e_wallet' AND is_aktif = 1 THEN 1 ELSE 0 END) AS ewallet_aktif
    FROM metode_pembayaran_langganan");

  $recentUsersResult = $conn->query("SELECT id_user, nama, email, role, status, email_verified_at, last_login_at, created_at FROM users ORDER BY id_user DESC LIMIT 8");
  if (!$recentUsersResult) throw new RuntimeException('Gagal mengambil pengguna terbaru.');
  $recentUsers = $recentUsersResult->fetch_all(MYSQLI_ASSOC);

  $recentKosResult = $conn->query("SELECT k.id_kos, k.nama_kos, k.status, k.jenis, k.created_at, u.nama AS nama_pemilik FROM kos k INNER JOIN users u ON u.id_user = k.id_pemilik ORDER BY k.id_kos DESC LIMIT 8");
  if (!$recentKosResult) throw new RuntimeException('Gagal mengambil kos terbaru.');
  $recentKos = $recentKosResult->fetch_all(MYSQLI_ASSOC);

  $pendingPaymentResult = $conn->query("SELECT pl.id_pembayaran_langganan, pl.nomor_order, pl.nominal, pl.jenis_pembayaran, pl.metode_pembayaran, pl.created_at, u.nama AS nama_pemilik, p.nama AS nama_paket FROM pembayaran_langganan pl INNER JOIN users u ON u.id_user = pl.id_pemilik INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan WHERE pl.status = 'menunggu' ORDER BY pl.id_pembayaran_langganan DESC LIMIT 6");
  if (!$pendingPaymentResult) throw new RuntimeException('Gagal mengambil pembayaran terbaru.');
  $pendingPayments = $pendingPaymentResult->fetch_all(MYSQLI_ASSOC);
  foreach ($pendingPayments as &$item) $item['nominal'] = (float)$item['nominal'];
  unset($item);

  $pendingReportResult = $conn->query("SELECT l.id_laporan, l.alasan, l.status, l.created_at, k.nama_kos, u.nama AS nama_pelapor FROM laporan_kos l INNER JOIN kos k ON k.id_kos = l.id_kos INNER JOIN users u ON u.id_user = l.id_user WHERE l.status IN ('menunggu','diproses') ORDER BY l.id_laporan DESC LIMIT 6");
  if (!$pendingReportResult) throw new RuntimeException('Gagal mengambil laporan terbaru.');
  $pendingReports = $pendingReportResult->fetch_all(MYSQLI_ASSOC);

  $pendingVerification = getAdminVerifikasiList('menunggu');
  $pendingVerification = array_slice($pendingVerification, 0, 6);

  return [
    'pengguna' => array_map('intval', [
      'total' => $users['total'] ?? 0,
      'aktif' => $users['aktif'] ?? 0,
      'nonaktif' => $users['nonaktif'] ?? 0,
      'pemilik' => $users['pemilik'] ?? 0,
      'pelanggan' => $users['pelanggan'] ?? 0,
      'admin' => $users['admin'] ?? 0,
      'belum_verifikasi' => $users['belum_verifikasi'] ?? 0,
      'login_hari_ini' => $users['login_hari_ini'] ?? 0,
      'login_7_hari' => $users['login_7_hari'] ?? 0,
    ]),
    'kos' => array_map('intval', [
      'total' => $kos['total'] ?? 0,
      'aktif' => $kos['aktif'] ?? 0,
      'menunggu_verifikasi' => $kos['menunggu_verifikasi'] ?? 0,
      'draft' => $kos['draft'] ?? 0,
      'ditolak' => $kos['ditolak'] ?? 0,
    ]),
    'kamar' => array_map('intval', [
      'total' => $kamar['total'] ?? 0,
      'terisi' => $kamar['terisi'] ?? 0,
      'tersedia' => $kamar['tersedia'] ?? 0,
    ]),
    'penghuni_aktif' => (int)($penghuni['aktif'] ?? 0),
    'verifikasi' => $verification,
    'langganan' => $subscriptions,
    'laporan' => $laporan,
    'pembayaran_langganan' => [
      'total' => (int)($payment['total'] ?? 0),
      'menunggu' => (int)($payment['menunggu'] ?? 0),
      'diverifikasi' => (int)($payment['diverifikasi'] ?? 0),
      'ditolak' => (int)($payment['ditolak'] ?? 0),
      'total_terverifikasi' => (float)($payment['total_terverifikasi'] ?? 0),
      'pendapatan_bulan_ini' => (float)($payment['pendapatan_bulan_ini'] ?? 0),
    ],
    'metode_pembayaran' => array_map('intval', [
      'total' => $paymentMethods['total'] ?? 0,
      'aktif' => $paymentMethods['aktif'] ?? 0,
      'nonaktif' => $paymentMethods['nonaktif'] ?? 0,
      'bank_aktif' => $paymentMethods['bank_aktif'] ?? 0,
      'ewallet_aktif' => $paymentMethods['ewallet_aktif'] ?? 0,
    ]),
    'terbaru' => [
      'pengguna' => $recentUsers,
      'kos' => $recentKos,
      'pembayaran_menunggu' => $pendingPayments,
      'laporan_menunggu' => $pendingReports,
      'verifikasi_menunggu' => $pendingVerification,
    ],
  ];
}
