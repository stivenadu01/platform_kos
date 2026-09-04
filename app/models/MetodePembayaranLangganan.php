<?php

function getMetodePembayaranLangganan($onlyActive = false)
{
  $conn = db();
  $where = $onlyActive ? 'WHERE is_aktif = 1' : '';
  $sql = "
    SELECT id_metode_pembayaran, jenis, nama_provider, nomor_tujuan,
           nama_penerima, keterangan, is_aktif, created_at, updated_at
    FROM metode_pembayaran_langganan
    {$where}
    ORDER BY is_aktif DESC, jenis ASC, nama_provider ASC, id_metode_pembayaran ASC
  ";
  $result = $conn->query($sql);
  if (!$result) throw new RuntimeException('Gagal mengambil metode pembayaran.');
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as &$row) $row['is_aktif'] = (int)$row['is_aktif'];
  unset($row);
  return $rows;
}

function getMetodePembayaranLanggananById($id, $onlyActive = false)
{
  $conn = db();
  $id = (int)$id;
  if ($id <= 0) return null;
  $sql = "
    SELECT id_metode_pembayaran, jenis, nama_provider, nomor_tujuan,
           nama_penerima, keterangan, is_aktif
    FROM metode_pembayaran_langganan
    WHERE id_metode_pembayaran = ?
  " . ($onlyActive ? " AND is_aktif = 1" : '') . " LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) return null;
  $row['is_aktif'] = (int)$row['is_aktif'];
  return $row;
}

function simpanMetodePembayaranLangganan($data, $adminId)
{
  $conn = db();
  $id = (int)($data['id_metode_pembayaran'] ?? 0);
  $jenis = trim((string)($data['jenis'] ?? ''));
  $provider = trim((string)($data['nama_provider'] ?? ''));
  $nomor = trim((string)($data['nomor_tujuan'] ?? ''));
  $penerima = trim((string)($data['nama_penerima'] ?? ''));
  $keterangan = trim((string)($data['keterangan'] ?? ''));
  $aktif = !empty($data['is_aktif']) ? 1 : 0;

  if (!in_array($jenis, ['transfer_bank', 'e_wallet'], true)) throw new Exception('Jenis metode pembayaran tidak valid.', 422);
  if ($provider === '' || $nomor === '' || $penerima === '') throw new Exception('Provider, nomor rekening/nomor e-wallet, dan nama penerima wajib diisi.', 422);
  if (mb_strlen($provider) > 80 || mb_strlen($nomor) > 100 || mb_strlen($penerima) > 120 || mb_strlen($keterangan) > 255) throw new Exception('Data metode pembayaran terlalu panjang.', 422);

  if ($id > 0) {
    $stmt = $conn->prepare("UPDATE metode_pembayaran_langganan SET jenis=?, nama_provider=?, nomor_tujuan=?, nama_penerima=?, keterangan=?, is_aktif=?, updated_at=CURRENT_TIMESTAMP WHERE id_metode_pembayaran=?");
    $stmt->bind_param('sssssii', $jenis, $provider, $nomor, $penerima, $keterangan, $aktif, $id);
    if (!$stmt->execute()) throw new Exception('Gagal memperbarui metode pembayaran.', 500);
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected === 0 && !getMetodePembayaranLanggananById($id)) throw new Exception('Metode pembayaran tidak ditemukan.', 404);
    return $id;
  }

  $stmt = $conn->prepare("INSERT INTO metode_pembayaran_langganan (jenis, nama_provider, nomor_tujuan, nama_penerima, keterangan, is_aktif) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('sssssi', $jenis, $provider, $nomor, $penerima, $keterangan, $aktif);
  if (!$stmt->execute()) throw new Exception('Gagal menambahkan metode pembayaran.', 500);
  $newId = (int)$conn->insert_id;
  $stmt->close();
  return $newId;
}

function ubahStatusMetodePembayaranLangganan($id, $aktif)
{
  $conn = db();
  $id = (int)$id;
  $aktif = $aktif ? 1 : 0;
  if ($id <= 0) throw new Exception('Metode pembayaran tidak valid.', 422);
  $stmt = $conn->prepare("UPDATE metode_pembayaran_langganan SET is_aktif=?, updated_at=CURRENT_TIMESTAMP WHERE id_metode_pembayaran=?");
  $stmt->bind_param('ii', $aktif, $id);
  $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();
  if ($affected === 0 && !getMetodePembayaranLanggananById($id)) throw new Exception('Metode pembayaran tidak ditemukan.', 404);
}

function getAdminMetodePembayaranSummary()
{
  $conn = db();
  $sql = "
    SELECT
      m.id_metode_pembayaran,
      m.jenis,
      m.nama_provider,
      m.nomor_tujuan,
      m.nama_penerima,
      m.keterangan,
      m.is_aktif,
      m.created_at,
      m.updated_at,
      COUNT(pl.id_pembayaran_langganan) AS total_transaksi,
      SUM(CASE WHEN pl.status = 'menunggu' THEN 1 ELSE 0 END) AS transaksi_menunggu,
      SUM(CASE WHEN pl.status = 'diverifikasi' THEN 1 ELSE 0 END) AS transaksi_diverifikasi,
      SUM(CASE WHEN pl.status = 'ditolak' THEN 1 ELSE 0 END) AS transaksi_ditolak,
      COALESCE(SUM(CASE WHEN pl.status = 'diverifikasi' THEN pl.nominal ELSE 0 END), 0) AS total_nominal_terverifikasi,
      MAX(CASE WHEN pl.status = 'diverifikasi' THEN pl.tanggal_pembayaran ELSE NULL END) AS terakhir_digunakan
    FROM metode_pembayaran_langganan m
    LEFT JOIN pembayaran_langganan pl ON pl.id_metode_pembayaran = m.id_metode_pembayaran
    GROUP BY m.id_metode_pembayaran, m.jenis, m.nama_provider, m.nomor_tujuan, m.nama_penerima, m.keterangan, m.is_aktif, m.created_at, m.updated_at
    ORDER BY m.is_aktif DESC, m.jenis ASC, m.nama_provider ASC, m.id_metode_pembayaran ASC
  ";
  $result = $conn->query($sql);
  if (!$result) throw new RuntimeException('Gagal mengambil ringkasan metode pembayaran.');
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as &$row) {
    $row['is_aktif'] = (int)$row['is_aktif'];
    $row['total_transaksi'] = (int)$row['total_transaksi'];
    $row['transaksi_menunggu'] = (int)$row['transaksi_menunggu'];
    $row['transaksi_diverifikasi'] = (int)$row['transaksi_diverifikasi'];
    $row['transaksi_ditolak'] = (int)$row['transaksi_ditolak'];
    $row['total_nominal_terverifikasi'] = (float)$row['total_nominal_terverifikasi'];
  }
  unset($row);
  return $rows;
}
