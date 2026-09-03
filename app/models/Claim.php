<?php

function createClaimRiwayat($id_user, $id_penghuni, $nik, $catatan)
{
  $conn = db();
  $id_user = (int)$id_user;
  $id_penghuni = (int)$id_penghuni;
  $nik = trim($nik);
  $catatan = trim($catatan);

  if ($id_user <= 0 || $id_penghuni <= 0) {
    throw new Exception('Data claim tidak valid.', 422);
  }

  if (!preg_match('/^\d{16}$/', $nik)) {
    throw new Exception('NIK harus terdiri dari 16 digit.', 422);
  }

  $stmt = $conn->prepare("SELECT nik FROM users WHERE id_user = ? AND role = 'pelanggan' AND status = 'aktif' LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$user || $user['nik'] !== $nik) {
    throw new Exception('NIK claim harus sesuai dengan akun yang sedang login.', 422);
  }

  $stmt = $conn->prepare("
    SELECT p.id_penghuni, p.id_user, p.nik, p.nama, p.tanggal_masuk,
           p.tanggal_keluar, p.status, km.nomor_kamar, k.id_kos,
           k.nama_kos, k.id_pemilik, u.nama AS nama_pemilik, u.foto AS foto_pemilik
    FROM penghuni p
    INNER JOIN kamar km ON km.id_kamar = p.id_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    INNER JOIN users u ON u.id_user = k.id_pemilik
    WHERE p.id_penghuni = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_penghuni);
  $stmt->execute();
  $penghuni = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$penghuni) {
    throw new Exception('Riwayat penghuni tidak ditemukan.', 404);
  }

  if ((int)$penghuni['id_user'] > 0) {
    throw new Exception('Riwayat ini sudah terhubung ke akun.', 409);
  }

  if ($penghuni['nik'] !== $nik) {
    throw new Exception('NIK tidak cocok dengan data riwayat penghuni.', 422);
  }

  $stmt = $conn->prepare('SELECT id_claim, status FROM claim_riwayat WHERE id_penghuni = ? LIMIT 1');
  $stmt->bind_param('i', $id_penghuni);
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($existing && $existing['status'] !== 'ditolak') {
    throw new Exception('Riwayat ini sudah memiliki claim yang sedang diproses atau disetujui.', 409);
  }

  if ($existing) {
    $stmt = $conn->prepare("UPDATE claim_riwayat SET id_user = ?, nik_diajukan = ?, status = 'menunggu', catatan_mahasiswa = ?, catatan_pemilik = NULL, tanggal_pengajuan = NOW(), tanggal_keputusan = NULL WHERE id_claim = ?");
    $stmt->bind_param('issi', $id_user, $nik, $catatan, $existing['id_claim']);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal mengajukan claim.', 500);
    }
    $id = (int)$existing['id_claim'];
    $stmt->close();
    return $id;
  }

  $stmt = $conn->prepare('INSERT INTO claim_riwayat (id_penghuni, id_user, nik_diajukan, catatan_mahasiswa) VALUES (?, ?, ?, ?)');
  $stmt->bind_param('iiss', $id_penghuni, $id_user, $nik, $catatan);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal mengajukan claim.', 500);
  }
  $id = (int)$stmt->insert_id;
  $stmt->close();
  return $id;
}

function getClaimRiwayatByUser($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT c.id_claim, c.id_penghuni, c.status, c.catatan_mahasiswa, c.catatan_pemilik, c.tanggal_pengajuan, c.tanggal_keputusan, p.nama, p.nik, p.tanggal_masuk, p.tanggal_keluar, km.nomor_kamar, k.id_kos, k.nama_kos, u.nama AS nama_pemilik, u.foto AS foto_pemilik FROM claim_riwayat c INNER JOIN penghuni p ON p.id_penghuni = c.id_penghuni INNER JOIN kamar km ON km.id_kamar = p.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users u ON u.id_user = k.id_pemilik WHERE c.id_user = ? ORDER BY c.id_claim DESC");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function getRiwayatKosByUser($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT p.id_penghuni, p.nama, p.nik, p.tanggal_masuk, p.tanggal_keluar, p.status, km.id_kamar, km.nomor_kamar, k.id_kos, k.nama_kos, k.alamat, u.nama AS nama_pemilik, u.foto AS foto_pemilik FROM penghuni p INNER JOIN kamar km ON km.id_kamar = p.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users u ON u.id_user = k.id_pemilik WHERE p.id_user = ? ORDER BY p.tanggal_masuk DESC, p.id_penghuni DESC");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function getClaimCandidatesByUser($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT p.id_penghuni, p.nama, p.nik, p.tanggal_masuk, p.tanggal_keluar, p.status, km.nomor_kamar, k.id_kos, k.nama_kos, k.alamat, u.nama AS nama_pemilik, c.id_claim, c.status AS claim_status, u.foto AS foto_pemilik FROM penghuni p INNER JOIN kamar km ON km.id_kamar = p.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users me ON me.id_user = ? INNER JOIN users u ON u.id_user = k.id_pemilik LEFT JOIN claim_riwayat c ON c.id_penghuni = p.id_penghuni WHERE p.id_user IS NULL AND p.nik = me.nik AND (c.id_claim IS NULL OR c.status = 'ditolak') ORDER BY p.tanggal_masuk DESC, p.id_penghuni DESC");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function getClaimRiwayatByPemilik($id_pemilik, $status = '')
{
  $conn = db();
  $where = 'WHERE k.id_pemilik = ?';
  $params = [(int)$id_pemilik];
  $types = 'i';

  if (in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
    $where .= ' AND c.status = ?';
    $params[] = $status;
    $types .= 's';
  }

  $stmt = $conn->prepare("SELECT c.id_claim, c.id_penghuni, c.id_user, c.nik_diajukan, c.status, c.catatan_mahasiswa, c.catatan_pemilik, c.tanggal_pengajuan, c.tanggal_keputusan, p.nama AS nama_penghuni, p.nik AS nik_penghuni, p.tanggal_masuk, p.tanggal_keluar, km.nomor_kamar, k.id_kos, k.nama_kos, u.nama AS nama_mahasiswa, u.email AS email_mahasiswa, u.no_hp AS no_hp_mahasiswa FROM claim_riwayat c INNER JOIN penghuni p ON p.id_penghuni = c.id_penghuni INNER JOIN kamar km ON km.id_kamar = p.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users u ON u.id_user = c.id_user {$where} ORDER BY c.id_claim DESC");
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function decideClaimRiwayat($id_claim, $id_pemilik, $keputusan, $catatan)
{
  if (!in_array($keputusan, ['disetujui', 'ditolak'], true)) {
    throw new Exception('Keputusan claim tidak valid.', 422);
  }

  if ($keputusan === 'ditolak' && trim($catatan) === '') {
    throw new Exception('Catatan wajib diisi ketika claim ditolak.', 422);
  }

  $conn = db();
  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("SELECT c.id_claim, c.id_penghuni, c.id_user, c.status, p.id_user AS penghuni_user FROM claim_riwayat c INNER JOIN penghuni p ON p.id_penghuni = c.id_penghuni INNER JOIN kamar km ON km.id_kamar = p.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos WHERE c.id_claim = ? AND k.id_pemilik = ? LIMIT 1 FOR UPDATE");
    $stmt->bind_param('ii', $id_claim, $id_pemilik);
    $stmt->execute();
    $claim = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$claim) throw new Exception('Claim tidak ditemukan atau bukan untuk kos Anda.', 404);
    if ($claim['status'] !== 'menunggu') throw new Exception('Claim ini sudah diproses.', 409);

    if ($keputusan === 'disetujui') {
      if ((int)$claim['penghuni_user'] > 0) {
        throw new Exception('Riwayat ini sudah terhubung ke akun lain.', 409);
      }

      $stmt = $conn->prepare("UPDATE penghuni SET id_user = ? WHERE id_penghuni = ? AND id_user IS NULL");
      $stmt->bind_param('ii', $claim['id_user'], $claim['id_penghuni']);
      if (!$stmt->execute() || $stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception('Riwayat tidak dapat diklaim.', 409);
      }
      $stmt->close();
    }

    $stmt = $conn->prepare('UPDATE claim_riwayat SET status = ?, catatan_pemilik = ?, tanggal_keputusan = NOW() WHERE id_claim = ? AND status = \'menunggu\'');
    $stmt->bind_param('ssi', $keputusan, $catatan, $id_claim);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
      $stmt->close();
      throw new Exception('Claim gagal diperbarui.', 500);
    }
    $stmt->close();
    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}
