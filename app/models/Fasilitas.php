<?php

function getAllFasilitas($kategori = 'kos')
{
  $conn = db();
  $sql = "SELECT id_fasilitas, nama_fasilitas, kategori, icon FROM fasilitas WHERE status = 'aktif'";
  $params = [];
  $types = '';
  if (in_array($kategori, ['kos', 'kamar'], true)) {
    $sql .= ' AND kategori = ?';
    $params[] = $kategori;
    $types = 's';
  }
  $sql .= ' ORDER BY nama_fasilitas ASC';
  $stmt = $conn->prepare($sql);
  if ($types !== '') $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}


function getFasilitasByKos(
  $id_kos,
  $id_pemilik
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      f.id_fasilitas,
      f.nama_fasilitas,
      f.icon
    FROM fasilitas f

    INNER JOIN kos_fasilitas kf
      ON kf.id_fasilitas = f.id_fasilitas

    INNER JOIN kos k
      ON k.id_kos = kf.id_kos

    WHERE k.id_kos = ?
      AND k.id_pemilik = ?
      AND f.kategori = 'kos'
      AND f.status = 'aktif'

    ORDER BY f.nama_fasilitas ASC
  ");

  $stmt->bind_param(
    'ii',
    $id_kos,
    $id_pemilik
  );

  $stmt->execute();

  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();

  return $data;
}


function syncFasilitasKos(
  $id_kos,
  $id_pemilik,
  $fasilitas,
  $manageTransaction = true
) {
  $conn = db();

  /*
   * Pastikan kos benar-benar milik pemilik.
   */
  $stmt = $conn->prepare("
    SELECT id_kos
    FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_kos,
    $id_pemilik
  );

  $stmt->execute();

  $kos = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if (!$kos) {
    throw new Exception(
      'Kos tidak ditemukan atau bukan milik Anda.'
    );
  }


  /*
   * Pastikan fasilitas selalu berupa array.
   */
  if (!is_array($fasilitas)) {
    $fasilitas = [];
  }


  /*
   * Bersihkan ID fasilitas.
   */
  $ids = [];

  foreach ($fasilitas as $id_fasilitas) {

    $id_fasilitas = (int) $id_fasilitas;

    if ($id_fasilitas > 0) {
      $ids[$id_fasilitas] = $id_fasilitas;
    }
  }

  $ids = array_values($ids);


  /*
   * Mulai transaksi supaya proses sinkronisasi
   * dilakukan secara konsisten.
   */
  if ($manageTransaction) {
    $conn->begin_transaction();
  }

  try {

    /*
     * Hapus relasi lama.
     */
    $stmt = $conn->prepare("
      DELETE FROM kos_fasilitas
      WHERE id_kos = ?
    ");

    $stmt->bind_param(
      'i',
      $id_kos
    );

    if (!$stmt->execute()) {
      throw new Exception(
        'Gagal menghapus fasilitas lama.'
      );
    }

    $stmt->close();


    /*
     * Tidak ada fasilitas yang dipilih.
     * Kos tetap valid tanpa fasilitas.
     */
    if (!empty($ids)) {

      /*
       * Pastikan seluruh ID fasilitas memang
       * tersedia pada tabel master fasilitas.
       */
      $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
      );

      $types = str_repeat('i', count($ids));

      $stmt = $conn->prepare("
        SELECT id_fasilitas
        FROM fasilitas
        WHERE kategori = 'kos'
          AND status = 'aktif'
          AND id_fasilitas IN ($placeholders)
      ");

      $stmt->bind_param(
        $types,
        ...$ids
      );

      $stmt->execute();

      $result = $stmt->get_result();

      $validIds = [];

      while ($row = $result->fetch_assoc()) {
        $validIds[] = (int) $row['id_fasilitas'];
      }

      $stmt->close();


      /*
       * Jangan menerima ID fasilitas yang
       * tidak ada di master.
       */
      if (count($validIds) !== count($ids)) {
        throw new Exception(
          'Terdapat fasilitas yang tidak valid.'
        );
      }


      /*
       * Masukkan relasi baru.
       */
      $stmt = $conn->prepare("
        INSERT INTO kos_fasilitas (
          id_kos,
          id_fasilitas
        )
        VALUES (?, ?)
      ");

      foreach ($ids as $id_fasilitas) {

        $stmt->bind_param(
          'ii',
          $id_kos,
          $id_fasilitas
        );

        if (!$stmt->execute()) {
          throw new Exception(
            'Gagal menyimpan fasilitas kos.'
          );
        }
      }

      $stmt->close();
    }


    if ($manageTransaction) {
      $conn->commit();
    }

    return true;
  } catch (Throwable $e) {

    if ($manageTransaction) {
      $conn->rollback();
    }

    throw $e;
  }
}


function getFasilitasAdmin($search = '', $kategori = '', $status = '')
{
  $conn=db(); $where=[]; $types=''; $params=[];
  if ($search!=='') { $where[]='(nama_fasilitas LIKE ? OR icon LIKE ?)'; $like="%$search%"; $types.='ss'; $params[]=$like; $params[]=$like; }
  if (in_array($kategori,['kos','kamar'],true)) { $where[]='kategori=?'; $types.='s'; $params[]=$kategori; }
  if (in_array($status,['aktif','nonaktif'],true)) { $where[]='status=?'; $types.='s'; $params[]=$status; }
  $sql='SELECT id_fasilitas,nama_fasilitas,kategori,icon,status FROM fasilitas';
  if($where) $sql.=' WHERE '.implode(' AND ',$where);
  $sql.=' ORDER BY kategori ASC,nama_fasilitas ASC';
  $stmt=$conn->prepare($sql); if($types) $stmt->bind_param($types,...$params); $stmt->execute(); $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
  foreach($rows as &$r) $r['id_fasilitas']=(int)$r['id_fasilitas']; unset($r); return $rows;
}

function getFasilitasByIdAdmin($id)
{
  $conn=db(); $stmt=$conn->prepare('SELECT id_fasilitas,nama_fasilitas,kategori,icon,status FROM fasilitas WHERE id_fasilitas=? LIMIT 1'); $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close(); if($row) $row['id_fasilitas']=(int)$row['id_fasilitas']; return $row;
}

function validateFasilitasData($data)
{
  $nama=trim((string)($data['nama_fasilitas']??'')); $kategori=trim((string)($data['kategori']??'kos')); $icon=trim((string)($data['icon']??'sparkles')); $status=trim((string)($data['status']??'aktif'));
  if($nama===''||mb_strlen($nama)>150) throw new Exception('Nama fasilitas wajib diisi dan maksimal 150 karakter.',422);
  if(!in_array($kategori,['kos','kamar'],true)) throw new Exception('Kategori fasilitas tidak valid.',422);
  if($icon===''||mb_strlen($icon)>100) throw new Exception('Icon fasilitas tidak valid.',422);
  if (!in_array($icon, masterIconKeys(), true)) throw new Exception('Icon fasilitas tidak tersedia pada katalog BetaKos.', 422);
  if(!in_array($status,['aktif','nonaktif'],true)) throw new Exception('Status fasilitas tidak valid.',422);
  return [$nama,$kategori,$icon,$status];
}

function createFasilitas($data)
{
  [$nama,$kategori,$icon,$status]=validateFasilitasData($data); $conn=db(); $stmt=$conn->prepare('INSERT INTO fasilitas(nama_fasilitas,kategori,icon,status) VALUES(?,?,?,?)'); $stmt->bind_param('ssss',$nama,$kategori,$icon,$status); if(!$stmt->execute()){ $e=$stmt->error;$stmt->close();throw new Exception('Gagal menambahkan fasilitas: '.$e,500);} $id=$stmt->insert_id;$stmt->close();return (int)$id;
}
function updateFasilitas($id,$data)
{
  if($id<=0||!getFasilitasByIdAdmin($id)) throw new Exception('Fasilitas tidak ditemukan.',404); [$nama,$kategori,$icon,$status]=validateFasilitasData($data); $conn=db(); $stmt=$conn->prepare('UPDATE fasilitas SET nama_fasilitas=?,kategori=?,icon=?,status=? WHERE id_fasilitas=?'); $stmt->bind_param('ssssi',$nama,$kategori,$icon,$status,$id); if(!$stmt->execute()){ $e=$stmt->error;$stmt->close();throw new Exception('Gagal memperbarui fasilitas: '.$e,500);} $stmt->close();
}
function deleteFasilitas($id)
{
  if($id<=0) throw new Exception('ID fasilitas tidak valid.',422); $conn=db(); $stmt=$conn->prepare('DELETE FROM fasilitas WHERE id_fasilitas=?'); $stmt->bind_param('i',$id); $stmt->execute(); $affected=$stmt->affected_rows;$stmt->close(); if(!$affected) throw new Exception('Fasilitas tidak ditemukan.',404);
}
