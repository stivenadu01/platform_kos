<?php

function getAllAturan($activeOnly = true)
{
  $conn = db();
  $sql = "SELECT id_aturan, nama_aturan, kategori, icon, deskripsi, urutan, status FROM aturan";
  if ($activeOnly) $sql .= " WHERE status = 'aktif'";
  $sql .= " ORDER BY kategori ASC, urutan ASC, nama_aturan ASC";
  $result = $conn->query($sql);
  if (!$result) throw new RuntimeException('Gagal mengambil data aturan.');
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as &$row) {
    $row['id_aturan'] = (int)$row['id_aturan'];
    $row['urutan'] = (int)$row['urutan'];
  }
  unset($row);
  return $rows;
}

function getAturanByKos($id_kos, $id_pemilik = null)
{
  $conn = db();
  $sql = "SELECT a.id_aturan, a.nama_aturan, a.kategori, a.icon, a.deskripsi, a.urutan
          FROM aturan a
          INNER JOIN kos_aturan ka ON ka.id_aturan = a.id_aturan
          INNER JOIN kos k ON k.id_kos = ka.id_kos
          WHERE ka.id_kos = ? AND a.status = 'aktif'";
  $types = 'i';
  $params = [(int)$id_kos];
  if ($id_pemilik !== null) {
    $sql .= ' AND k.id_pemilik = ?';
    $types .= 'i';
    $params[] = (int)$id_pemilik;
  }
  $sql .= ' ORDER BY a.kategori ASC, a.urutan ASC, a.nama_aturan ASC';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  foreach ($rows as &$row) {
    $row['id_aturan'] = (int)$row['id_aturan'];
    $row['urutan'] = (int)$row['urutan'];
  }
  unset($row);
  return $rows;
}

function syncAturanKos($id_kos, $id_pemilik, $aturan)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT id_kos FROM kos WHERE id_kos = ? AND id_pemilik = ? LIMIT 1');
  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();
  $owned = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$owned) throw new Exception('Kos tidak ditemukan atau bukan milik Anda.', 404);

  $ids = [];
  if (is_array($aturan)) {
    foreach ($aturan as $id) {
      $id = (int)$id;
      if ($id > 0) $ids[$id] = $id;
    }
  }
  $ids = array_values($ids);

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('DELETE FROM kos_aturan WHERE id_kos = ?');
    $stmt->bind_param('i', $id_kos);
    if (!$stmt->execute()) throw new Exception('Gagal menghapus aturan lama.', 500);
    $stmt->close();

    if ($ids) {
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $types = str_repeat('i', count($ids));
      $stmt = $conn->prepare("SELECT id_aturan FROM aturan WHERE status = 'aktif' AND id_aturan IN ($placeholders)");
      $stmt->bind_param($types, ...$ids);
      $stmt->execute();
      $valid = [];
      foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) $valid[] = (int)$row['id_aturan'];
      $stmt->close();
      sort($valid); $check = $ids; sort($check);
      if ($valid !== $check) throw new Exception('Terdapat aturan yang tidak valid.', 422);

      $stmt = $conn->prepare('INSERT INTO kos_aturan (id_kos, id_aturan) VALUES (?, ?)');
      foreach ($ids as $id_aturan) {
        $stmt->bind_param('ii', $id_kos, $id_aturan);
        if (!$stmt->execute()) throw new Exception('Gagal menyimpan aturan kos.', 500);
      }
      $stmt->close();
    }
    $conn->commit();
    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function getAturanAdmin($search = '', $kategori = '', $status = '')
{
  $conn = db();
  $where = [];
  $types = '';
  $params = [];
  if ($search !== '') { $where[] = '(nama_aturan LIKE ? OR deskripsi LIKE ?)'; $like = "%$search%"; $types .= 'ss'; $params[] = $like; $params[] = $like; }
  $validCategories = ['penghuni','tamu','jam','kebersihan','hewan','keamanan','umum'];
  if (in_array($kategori, $validCategories, true)) { $where[] = 'kategori = ?'; $types .= 's'; $params[] = $kategori; }
  if (in_array($status, ['aktif','nonaktif'], true)) { $where[] = 'status = ?'; $types .= 's'; $params[] = $status; }
  $sql = 'SELECT id_aturan, nama_aturan, kategori, icon, deskripsi, urutan, status, created_at, updated_at FROM aturan';
  if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
  $sql .= ' ORDER BY kategori ASC, urutan ASC, nama_aturan ASC';
  $stmt = $conn->prepare($sql);
  if ($types) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  foreach ($rows as &$row) { $row['id_aturan']=(int)$row['id_aturan']; $row['urutan']=(int)$row['urutan']; }
  unset($row);
  return $rows;
}

function validateAturanData($data)
{
  $nama = trim((string)($data['nama_aturan'] ?? ''));
  $kategori = trim((string)($data['kategori'] ?? 'umum'));
  $icon = trim((string)($data['icon'] ?? 'ban'));
  $deskripsi = trim((string)($data['deskripsi'] ?? ''));
  $urutan = (int)($data['urutan'] ?? 0);
  $status = trim((string)($data['status'] ?? 'aktif'));
  if ($nama === '' || mb_strlen($nama) > 200) throw new Exception('Nama aturan wajib diisi dan maksimal 200 karakter.', 422);
  if (!in_array($kategori, ['penghuni','tamu','jam','kebersihan','hewan','keamanan','umum'], true)) throw new Exception('Kategori aturan tidak valid.', 422);
  if ($icon === '' || mb_strlen($icon) > 100) throw new Exception('Icon aturan tidak valid.', 422);
  if (!in_array($icon, masterIconKeys(), true)) throw new Exception('Icon aturan tidak tersedia pada katalog BetaKos.', 422);
  if (mb_strlen($deskripsi) > 500) throw new Exception('Deskripsi maksimal 500 karakter.', 422);
  if (!in_array($status, ['aktif','nonaktif'], true)) throw new Exception('Status aturan tidak valid.', 422);
  return [$nama,$kategori,$icon,$deskripsi !== '' ? $deskripsi : null,$urutan,$status];
}

function createAturan($data)
{
  [$nama,$kategori,$icon,$deskripsi,$urutan,$status] = validateAturanData($data);
  $conn = db();
  $stmt = $conn->prepare('INSERT INTO aturan (nama_aturan,kategori,icon,deskripsi,urutan,status) VALUES (?,?,?,?,?,?)');
  $stmt->bind_param('ssssis', $nama,$kategori,$icon,$deskripsi,$urutan,$status);
  if (!$stmt->execute()) { $error=$stmt->error; $stmt->close(); throw new Exception('Gagal menambahkan aturan: '.$error,500); }
  $id=$stmt->insert_id; $stmt->close(); return (int)$id;
}

function getAturanById($id)
{
  $conn=db(); $stmt=$conn->prepare('SELECT id_aturan,nama_aturan,kategori,icon,deskripsi,urutan,status FROM aturan WHERE id_aturan=? LIMIT 1');
  $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
  if ($row) { $row['id_aturan']=(int)$row['id_aturan']; $row['urutan']=(int)$row['urutan']; }
  return $row;
}

function updateAturan($id,$data)
{
  if ($id<=0 || !getAturanById($id)) throw new Exception('Aturan tidak ditemukan.',404);
  [$nama,$kategori,$icon,$deskripsi,$urutan,$status] = validateAturanData($data);
  $conn=db();
  $stmt=$conn->prepare('UPDATE aturan SET nama_aturan=?,kategori=?,icon=?,deskripsi=?,urutan=?,status=? WHERE id_aturan=?');
  $stmt->bind_param('ssssisi', $nama,$kategori,$icon,$deskripsi,$urutan,$status,$id);
  if (!$stmt->execute()) { $error = $stmt->error; $stmt->close(); throw new Exception('Gagal memperbarui aturan: '.$error, 500); }
  $stmt->close();
  return true;
}

function deleteAturan($id)
{
  if ($id<=0) throw new Exception('ID aturan tidak valid.',422);
  $conn=db(); $stmt=$conn->prepare('DELETE FROM aturan WHERE id_aturan=?'); $stmt->bind_param('i',$id); $stmt->execute(); $affected=$stmt->affected_rows; $stmt->close();
  if ($affected===0) throw new Exception('Aturan tidak ditemukan.',404);
}
