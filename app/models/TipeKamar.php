<?php

function getTipeKamarListByPemilik($id_pemilik, $id_kos = '')
{
  $conn = db();
  $where = ['k.id_pemilik = ?'];
  $params = [$id_pemilik];
  $types = 'i';

  if ($id_kos !== '') {
    $where[] = 't.id_kos = ?';
    $params[] = (int) $id_kos;
    $types .= 'i';
  }

  $stmt = $conn->prepare("
    SELECT
      t.id_tipe_kamar,
      t.id_kos,
      t.nama_tipe,
      t.kapasitas,
      t.deskripsi,
      t.created_at,
      t.updated_at,
      k.nama_kos,
      COUNT(km.id_kamar) AS jumlah_kamar,
      SUM(CASE WHEN km.status = 'tersedia' THEN 1 ELSE 0 END) AS kamar_tersedia
    FROM tipe_kamar t
    INNER JOIN kos k ON k.id_kos = t.id_kos
    LEFT JOIN kamar km ON km.id_tipe_kamar = t.id_tipe_kamar
    WHERE " . implode(' AND ', $where) . "
    GROUP BY t.id_tipe_kamar
    ORDER BY k.nama_kos ASC, t.nama_tipe ASC
  ");
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = [];

  while ($row = $result->fetch_assoc()) {
    $row['kapasitas'] = (int) $row['kapasitas'];
    $row['jumlah_kamar'] = (int) $row['jumlah_kamar'];
    $row['kamar_tersedia'] = (int) $row['kamar_tersedia'];
    $data[] = $row;
  }

  $stmt->close();
  return $data;
}

function findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT t.*, k.nama_kos
    FROM tipe_kamar t
    INNER JOIN kos k ON k.id_kos = t.id_kos
    WHERE t.id_tipe_kamar = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");
  $stmt->bind_param('ii', $id_tipe_kamar, $id_pemilik);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$data) {
    return null;
  }

  $data['harga'] = getHargaTipeKamar($id_tipe_kamar);
  $data['fasilitas'] = getFasilitasTipeKamar($id_tipe_kamar);
  $data['foto'] = getFotoTipeKamar($id_tipe_kamar);
  $data['kamar'] = getUnitKamarByTipe($id_tipe_kamar);
  return $data;
}

function createTipeKamar($data, $id_pemilik)
{
  $id_kos = (int) ($data['id_kos'] ?? 0);
  $nama_tipe = trim($data['nama_tipe'] ?? '');
  $kapasitas = (int) ($data['kapasitas'] ?? 0);
  $deskripsi = trim($data['deskripsi'] ?? '');

  if (!$id_kos || $nama_tipe === '' || $kapasitas < 1 || $kapasitas > 255) {
    throw new Exception('Kos, nama tipe, dan kapasitas wajib diisi.', 422);
  }
  if (mb_strlen($nama_tipe) > 100) {
    throw new Exception('Nama tipe maksimal 100 karakter.', 422);
  }

  $conn = db();
  ensureKosOwned($id_kos, $id_pemilik, $conn);
  $stmt = $conn->prepare("
    INSERT INTO tipe_kamar (id_kos, nama_tipe, kapasitas, deskripsi)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->bind_param('isis', $id_kos, $nama_tipe, $kapasitas, $deskripsi);

  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    error_log('Create tipe kamar DB error: ' . $error);
    throw new Exception('Gagal membuat tipe kamar.', 500);
  }

  $id = $stmt->insert_id;
  $stmt->close();
  return $id;
}

function updateTipeKamar($id_tipe_kamar, $data, $id_pemilik)
{
  $existing = findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik);
  if (!$existing) {
    throw new Exception('Tipe kamar tidak ditemukan.', 404);
  }

  $nama_tipe = trim($data['nama_tipe'] ?? '');
  $kapasitas = (int) ($data['kapasitas'] ?? 0);
  $deskripsi = trim($data['deskripsi'] ?? '');
  if ($nama_tipe === '' || $kapasitas < 1 || $kapasitas > 255) {
    throw new Exception('Nama tipe dan kapasitas wajib diisi.', 422);
  }
  if (mb_strlen($nama_tipe) > 100) {
    throw new Exception('Nama tipe maksimal 100 karakter.', 422);
  }

  $conn = db();

  // Kapasitas tidak boleh diturunkan di bawah konfigurasi harga
  // atau jumlah penghuni aktif yang sudah ada pada unit tipe ini.
  $stmt = $conn->prepare("
    SELECT
      GREATEST(
        COALESCE((SELECT MAX(h.jumlah_orang)
                  FROM harga_kamar h
                  WHERE h.id_tipe_kamar = t.id_tipe_kamar), 0),
        COALESCE((SELECT MAX(p.jumlah_aktif)
                  FROM (
                    SELECT p.id_kamar, COUNT(*) AS jumlah_aktif
                    FROM penghuni p
                    INNER JOIN kamar km2 ON km2.id_kamar = p.id_kamar
                    WHERE km2.id_tipe_kamar = t.id_tipe_kamar
                      AND p.status = 'aktif'
                    GROUP BY p.id_kamar
                  ) p), 0)
      ) AS kapasitas_minimal
    FROM tipe_kamar t
    WHERE t.id_tipe_kamar = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $capacityCheck = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $kapasitasMinimal = (int) ($capacityCheck['kapasitas_minimal'] ?? 0);
  if ($kapasitas < $kapasitasMinimal) {
    throw new Exception(
      "Kapasitas tidak boleh kurang dari {$kapasitasMinimal} orang karena masih digunakan oleh harga atau penghuni aktif.",
      422
    );
  }
  $stmt = $conn->prepare("
    UPDATE tipe_kamar
    SET nama_tipe = ?, kapasitas = ?, deskripsi = ?
    WHERE id_tipe_kamar = ?
  ");
  $stmt->bind_param('sisi', $nama_tipe, $kapasitas, $deskripsi, $id_tipe_kamar);
  $result = $stmt->execute();
  $error = $stmt->error;
  $stmt->close();

  if (!$result) {
    error_log('Update tipe kamar DB error: ' . $error);
    throw new Exception('Gagal memperbarui tipe kamar.', 500);
  }
  return true;
}

function deleteTipeKamar($id_tipe_kamar, $id_pemilik)
{
  $conn = db();
  $fotoPaths = [];
  $conn->begin_transaction();

  try {
    // Kunci tipe kamar selama pemeriksaan agar unit baru tidak dapat masuk
    // di antara validasi dan penghapusan.
    $stmt = $conn->prepare("\n      SELECT t.id_tipe_kamar\n      FROM tipe_kamar t\n      INNER JOIN kos k ON k.id_kos = t.id_kos\n      WHERE t.id_tipe_kamar = ? AND k.id_pemilik = ?\n      LIMIT 1\n      FOR UPDATE\n    ");
    $stmt->bind_param('ii', $id_tipe_kamar, $id_pemilik);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) {
      throw new Exception('Tipe kamar tidak ditemukan.', 404);
    }

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM kamar WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $id_tipe_kamar);
    $stmt->execute();
    $total = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();
    if ($total > 0) {
      throw new Exception('Tipe kamar tidak dapat dihapus karena masih memiliki unit kamar.', 422);
    }

    // Simpan path sebelum ON DELETE CASCADE menghapus metadata foto.
    $stmt = $conn->prepare('SELECT nama_file FROM tipe_kamar_foto WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $id_tipe_kamar);
    $stmt->execute();
    $fotoPaths = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'nama_file');
    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM tipe_kamar WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $id_tipe_kamar);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
      throw new Exception('Gagal menghapus tipe kamar.', 500);
    }
    $stmt->close();
    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }

  foreach ($fotoPaths as $namaFile) {
    $namaFile = str_replace('\\', '/', (string) $namaFile);
    if (!preg_match('#^/tipe-kamar/[A-Za-z0-9_.-]+$#', $namaFile)) {
      error_log('Lewati path foto tipe kamar tidak valid: ' . $namaFile);
      continue;
    }

    $filePath = ROOT_PATH . '/public/uploads' . $namaFile;
    if (is_file($filePath) && !is_link($filePath) && !@unlink($filePath)) {
      error_log('Gagal menghapus file foto tipe kamar: ' . $filePath);
    }
  }

  return true;
}

function ensureKosOwned($id_kos, $id_pemilik, $conn)
{
  $stmt = $conn->prepare('SELECT id_kos FROM kos WHERE id_kos = ? AND id_pemilik = ? LIMIT 1');
  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();
  $kos = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$kos) {
    throw new Exception('Kos tidak ditemukan atau bukan milik Anda.', 403);
  }
}

function getHargaTipeKamar($id_tipe_kamar)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT id_harga, id_tipe_kamar, jumlah_orang, harga_total FROM harga_kamar WHERE id_tipe_kamar = ? ORDER BY jumlah_orang ASC');
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function saveHargaTipeKamar($id_tipe_kamar, $harga, $kapasitas, $id_pemilik, $manageTransaction = true)
{
  if (!findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik)) {
    throw new Exception('Tipe kamar tidak ditemukan.', 404);
  }
  if (!is_array($harga)) {
    throw new Exception('Format harga tidak valid.', 422);
  }
  if (count($harga) < 1) {
    throw new Exception('Minimal satu harga tipe kamar wajib diisi.', 422);
  }

  $normalized = [];
  foreach ($harga as $item) {
    if (!is_array($item)) {
      throw new Exception('Konfigurasi harga tidak valid.', 422);
    }
    $jumlahRaw = $item['jumlah_orang'] ?? null;
    $jumlah = filter_var($jumlahRaw, FILTER_VALIDATE_INT);
    $nilai = $item['harga_total'] ?? null;
    if ($jumlah === false || $jumlah < 1 || $jumlah > $kapasitas || $nilai === null || $nilai === '' || !is_numeric($nilai) || !is_finite((float) $nilai) || (float) $nilai <= 0) {
      throw new Exception('Konfigurasi harga tidak valid.', 422);
    }
    if (isset($normalized[$jumlah])) {
      throw new Exception('Jumlah orang pada harga tidak boleh duplikat.', 422);
    }
    $normalized[$jumlah] = ['jumlah_orang' => $jumlah, 'harga_total' => (float) $nilai];
  }

  $conn = db();
  if ($manageTransaction) $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('DELETE FROM harga_kamar WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $id_tipe_kamar);
    if (!$stmt->execute()) {
      throw new Exception('Gagal menghapus harga lama.', 422);
    }
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO harga_kamar (id_tipe_kamar, jumlah_orang, harga_total) VALUES (?, ?, ?)');
    foreach ($normalized as $item) {
      $jumlah = $item['jumlah_orang'];
      $nilai = $item['harga_total'];
      $stmt->bind_param('iid', $id_tipe_kamar, $jumlah, $nilai);
      if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan harga.', 422);
      }
    }
    $stmt->close();
    if ($manageTransaction) $conn->commit();
  } catch (Throwable $e) {
    if ($manageTransaction) $conn->rollback();
    throw $e;
  }
  return true;
}

function getUnitKamarByTipe($id_tipe_kamar)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT id_kamar, id_kos, id_tipe_kamar, nomor_kamar, status, deskripsi FROM kamar WHERE id_tipe_kamar = ? ORDER BY nomor_kamar ASC');
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function getFasilitasTipeKamar($id_tipe_kamar)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT f.id_fasilitas, f.nama_fasilitas, f.icon FROM fasilitas f INNER JOIN tipe_kamar_fasilitas tf ON tf.id_fasilitas = f.id_fasilitas WHERE tf.id_tipe_kamar = ? AND f.kategori = 'kamar' AND f.status = 'aktif' ORDER BY f.nama_fasilitas ASC");
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function syncFasilitasTipeKamar($id_tipe_kamar, $ids, $id_pemilik, $manageTransaction = true)
{
  if (!findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik)) {
    throw new Exception('Tipe kamar tidak ditemukan.', 404);
  }
  if (!is_array($ids)) {
    throw new Exception('Format fasilitas tipe kamar tidak valid.', 422);
  }
  $normalizedIds = [];
  foreach ($ids as $id) {
    $validId = filter_var($id, FILTER_VALIDATE_INT);
    if ($validId === false || $validId < 1) {
      throw new Exception('Fasilitas tipe kamar tidak valid.', 422);
    }
    $normalizedIds[] = $validId;
  }
  $ids = array_values(array_unique($normalizedIds));
  $conn = db();
  if ($manageTransaction) $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('DELETE FROM tipe_kamar_fasilitas WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $id_tipe_kamar);
    if (!$stmt->execute()) {
      throw new Exception('Gagal menghapus fasilitas lama.', 500);
    }
    $stmt->close();

    if ($ids) {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $conn->prepare("SELECT id_fasilitas FROM fasilitas WHERE kategori = 'kamar' AND status = 'aktif' AND id_fasilitas IN ($placeholders)");
      $types = str_repeat('i', count($ids));
      $stmt->bind_param($types, ...$ids);
      $stmt->execute();
      $valid = array_map('intval', array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id_fasilitas'));
      $stmt->close();
      if (count($valid) !== count($ids)) {
        throw new Exception('Fasilitas tipe kamar tidak valid.', 422);
      }

      $stmt = $conn->prepare('INSERT INTO tipe_kamar_fasilitas (id_tipe_kamar, id_fasilitas) VALUES (?, ?)');
      foreach ($ids as $id) {
        $stmt->bind_param('ii', $id_tipe_kamar, $id);
        if (!$stmt->execute()) {
          throw new Exception('Gagal menyimpan fasilitas tipe kamar.', 500);
        }
      }
      $stmt->close();
    }
    if ($manageTransaction) $conn->commit();
  } catch (Throwable $e) {
    if ($manageTransaction) $conn->rollback();
    throw $e;
  }
  return true;
}

/**
 * Simpan data utama, harga, dan fasilitas tipe kamar sebagai satu unit kerja.
 * Foto tetap di luar transaksi karena diunggah setelah ID tipe kamar tersedia.
 */
function saveTipeKamarComplete($data, $id_pemilik, $id_tipe_kamar = 0)
{
  if (!is_array($data)) {
    throw new Exception('Format data tipe kamar tidak valid.', 422);
  }

  $harga = $data['harga'] ?? null;
  $fasilitas = $data['id_fasilitas'] ?? null;
  if (!is_array($harga)) {
    throw new Exception('Format harga tipe kamar tidak valid.', 422);
  }
  if (!is_array($fasilitas)) {
    throw new Exception('Format fasilitas tipe kamar tidak valid.', 422);
  }

  $kapasitas = (int) ($data['kapasitas'] ?? 0);
  $conn = db();
  $conn->begin_transaction();

  try {
    if ($id_tipe_kamar > 0) {
      if (!findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik)) {
        throw new Exception('Tipe kamar tidak ditemukan.', 404);
      }

      // Harga lama diganti lebih dahulu di transaksi yang sama agar kapasitas
      // baru diperiksa terhadap konfigurasi harga baru, bukan data lama.
      saveHargaTipeKamar($id_tipe_kamar, $harga, $kapasitas, $id_pemilik, false);
      updateTipeKamar($id_tipe_kamar, $data, $id_pemilik);
    } else {
      $id_tipe_kamar = createTipeKamar($data, $id_pemilik);
      saveHargaTipeKamar($id_tipe_kamar, $harga, $kapasitas, $id_pemilik, false);
    }

    syncFasilitasTipeKamar($id_tipe_kamar, $fasilitas, $id_pemilik, false);
    $conn->commit();
    return $id_tipe_kamar;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function getFotoTipeKamar($id_tipe_kamar)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT id_foto, id_tipe_kamar, nama_file, urutan, is_thumbnail, created_at FROM tipe_kamar_foto WHERE id_tipe_kamar = ? ORDER BY is_thumbnail DESC, urutan ASC, id_foto ASC');
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function createFotoTipeKamar($id_tipe_kamar, $id_pemilik, $file)
{
  if (!findTipeKamarByIdPemilik($id_tipe_kamar, $id_pemilik)) {
    throw new Exception('Tipe kamar tidak ditemukan.', 404);
  }
  $conn = db();
  $stmt = $conn->prepare('SELECT COALESCE(MAX(urutan), 0) + 1 AS urutan, COUNT(*) AS total FROM tipe_kamar_foto WHERE id_tipe_kamar = ?');
  $stmt->bind_param('i', $id_tipe_kamar);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $nama_file = uploadImageGeneral($file, 'tipe-kamar');
  $urutan = (int) ($row['urutan'] ?? 1);
  $thumbnail = (int) (($row['total'] ?? 0) === 0);
  $stmt = $conn->prepare('INSERT INTO tipe_kamar_foto (id_tipe_kamar, nama_file, urutan, is_thumbnail) VALUES (?, ?, ?, ?)');
  $stmt->bind_param('isii', $id_tipe_kamar, $nama_file, $urutan, $thumbnail);
  if (!$stmt->execute()) {
    $stmt->close();

    $filePath = ROOT_PATH . '/public/uploads' . $nama_file;
    if (is_file($filePath)) {
      @unlink($filePath);
    }

    throw new Exception('Gagal menyimpan foto tipe kamar.', 500);
  }
  $id = $stmt->insert_id;
  $stmt->close();
  return $id;
}

function setThumbnailFotoTipeKamar($id_foto, $id_pemilik)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT f.id_tipe_kamar FROM tipe_kamar_foto f INNER JOIN tipe_kamar t ON t.id_tipe_kamar = f.id_tipe_kamar INNER JOIN kos k ON k.id_kos = t.id_kos WHERE f.id_foto = ? AND k.id_pemilik = ?');
  $stmt->bind_param('ii', $id_foto, $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) {
    throw new Exception('Foto tipe kamar tidak ditemukan.', 404);
  }
  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('UPDATE tipe_kamar_foto SET is_thumbnail = 0 WHERE id_tipe_kamar = ?');
    $stmt->bind_param('i', $row['id_tipe_kamar']);
    $stmt->execute();
    $stmt->close();
    $stmt = $conn->prepare('UPDATE tipe_kamar_foto SET is_thumbnail = 1 WHERE id_foto = ?');
    $stmt->bind_param('i', $id_foto);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function deleteFotoTipeKamar($id_foto, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      f.id_foto,
      f.id_tipe_kamar,
      f.nama_file,
      f.is_thumbnail
    FROM tipe_kamar_foto f
    INNER JOIN tipe_kamar t ON t.id_tipe_kamar = f.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = t.id_kos
    WHERE f.id_foto = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");
  $stmt->bind_param('ii', $id_foto, $id_pemilik);
  $stmt->execute();
  $foto = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$foto) {
    throw new Exception('Foto tipe kamar tidak ditemukan.', 404);
  }

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('DELETE FROM tipe_kamar_foto WHERE id_foto = ?');
    $stmt->bind_param('i', $id_foto);
    if (!$stmt->execute()) {
      throw new Exception('Gagal menghapus foto tipe kamar.', 500);
    }
    $stmt->close();

    // Jika thumbnail dihapus, promosikan foto berikutnya.
    if ((int) $foto['is_thumbnail'] === 1) {
      $stmt = $conn->prepare("
        SELECT id_foto
        FROM tipe_kamar_foto
        WHERE id_tipe_kamar = ?
        ORDER BY urutan ASC, id_foto ASC
        LIMIT 1
      ");
      $stmt->bind_param('i', $foto['id_tipe_kamar']);
      $stmt->execute();
      $next = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($next) {
        $stmt = $conn->prepare('UPDATE tipe_kamar_foto SET is_thumbnail = 1 WHERE id_foto = ?');
        $stmt->bind_param('i', $next['id_foto']);
        if (!$stmt->execute()) {
          throw new Exception('Gagal menetapkan thumbnail pengganti.', 500);
        }
        $stmt->close();
      }
    }

    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }

  $filePath = ROOT_PATH . '/public/uploads' . $foto['nama_file'];
  if (is_file($filePath)) {
    @unlink($filePath);
  }

  return true;
}
