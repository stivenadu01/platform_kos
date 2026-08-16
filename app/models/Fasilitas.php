<?php

function getAllFasilitas()
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_fasilitas,
      nama_fasilitas
    FROM fasilitas
    ORDER BY nama_fasilitas ASC
  ");

  $stmt->execute();

  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

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
      f.nama_fasilitas
    FROM fasilitas f

    INNER JOIN kos_fasilitas kf
      ON kf.id_fasilitas = f.id_fasilitas

    INNER JOIN kos k
      ON k.id_kos = kf.id_kos

    WHERE k.id_kos = ?
      AND k.id_pemilik = ?

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
  $fasilitas
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
  $conn->begin_transaction();

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
        WHERE id_fasilitas IN ($placeholders)
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


    $conn->commit();

    return true;
  } catch (Throwable $e) {

    $conn->rollback();

    throw $e;
  }
}
