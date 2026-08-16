<?php


/**
 * Mengambil semua foto milik kos tertentu.
 *
 * Ownership pemilik ikut diverifikasi melalui tabel kos.
 */
function getFotoByKos($id_kos, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      f.id_foto,
      f.id_kos,
      f.nama_file,
      f.urutan,
      f.is_thumbnail,
      f.created_at
    FROM kos_foto f
    INNER JOIN kos k
      ON k.id_kos = f.id_kos
    WHERE f.id_kos = ?
      AND k.id_pemilik = ?
    ORDER BY
      f.is_thumbnail DESC,
      f.urutan ASC,
      f.id_foto ASC
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


/**
 * Mengambil satu foto sekaligus memastikan
 * foto tersebut milik kos milik pemilik.
 */
function findFotoByIdPemilik($id_foto, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      f.*
    FROM kos_foto f
    INNER JOIN kos k
      ON k.id_kos = f.id_kos
    WHERE f.id_foto = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_foto,
    $id_pemilik
  );

  $stmt->execute();

  $data = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $data;
}


/**
 * Menambahkan foto baru ke sebuah kos.
 *
 * Foto pertama otomatis menjadi thumbnail.
 */
function createFotoKos(
  $id_kos,
  $id_pemilik,
  $file
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
   * Ambil urutan berikutnya.
   */
  $stmt = $conn->prepare("
    SELECT
      COALESCE(MAX(urutan), 0) + 1 AS urutan
    FROM kos_foto
    WHERE id_kos = ?
  ");

  $stmt->bind_param(
    'i',
    $id_kos
  );

  $stmt->execute();

  $urutan = (int) (
    $stmt
      ->get_result()
      ->fetch_assoc()['urutan'] ?? 1
  );

  $stmt->close();


  /*
   * Cek apakah kos sudah mempunyai foto.
   *
   * Jika belum ada foto, foto pertama
   * otomatis menjadi thumbnail.
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM kos_foto
    WHERE id_kos = ?
  ");

  $stmt->bind_param(
    'i',
    $id_kos
  );

  $stmt->execute();

  $totalFoto = (int) (
    $stmt
      ->get_result()
      ->fetch_assoc()['total'] ?? 0
  );

  $stmt->close();


  $isThumbnail = $totalFoto === 0 ? 1 : 0;


  /*
   * Upload file.
   */
  $namaFile = uploadImageGeneral(
    $file,
    'kos'
  );


  /*
   * Simpan data foto.
   */
  $stmt = $conn->prepare("
    INSERT INTO kos_foto (
      id_kos,
      nama_file,
      urutan,
      is_thumbnail
    )
    VALUES (?, ?, ?, ?)
  ");

  $stmt->bind_param(
    'isii',
    $id_kos,
    $namaFile,
    $urutan,
    $isThumbnail
  );

  $success = $stmt->execute();

  $id_foto = $stmt->insert_id;

  $stmt->close();


  /*
   * Jika database gagal menyimpan,
   * hapus file yang sudah terlanjur di-upload.
   */
  if (!$success) {

    $filePath =
      ROOT_PATH .
      '/public/uploads' .
      $namaFile;

    if (file_exists($filePath)) {
      unlink($filePath);
    }

    throw new Exception(
      'Gagal menyimpan data foto.',
      500
    );
  }


  return $id_foto;
}


/**
 * Menjadikan foto sebagai thumbnail.
 *
 * Hanya boleh ada satu thumbnail
 * untuk setiap kos.
 */
function setThumbnailFoto(
  $id_foto,
  $id_pemilik
) {
  $conn = db();


  /*
   * Ambil foto sekaligus validasi ownership.
   */
  $foto = findFotoByIdPemilik(
    $id_foto,
    $id_pemilik
  );

  if (!$foto) {
    throw new Exception(
      'Foto tidak ditemukan atau bukan milik Anda.'
    );
  }


  $id_kos = (int) $foto['id_kos'];


  /*
   * Transaction agar tidak ada kondisi
   * dua thumbnail pada saat proses.
   */
  $conn->begin_transaction();

  try {

    /*
     * Hapus status thumbnail dari
     * semua foto kos.
     */
    $stmt = $conn->prepare("
      UPDATE kos_foto
      SET is_thumbnail = 0
      WHERE id_kos = ?
    ");

    $stmt->bind_param(
      'i',
      $id_kos
    );

    if (!$stmt->execute()) {
      throw new Exception(
        'Gagal mengatur thumbnail.'
      );
    }

    $stmt->close();


    /*
     * Jadikan foto terpilih sebagai thumbnail.
     */
    $stmt = $conn->prepare("
      UPDATE kos_foto
      SET is_thumbnail = 1
      WHERE id_foto = ?
    ");

    $stmt->bind_param(
      'i',
      $id_foto
    );

    if (!$stmt->execute()) {
      throw new Exception(
        'Gagal menetapkan thumbnail.'
      );
    }

    $stmt->close();


    $conn->commit();

    return true;
  } catch (Throwable $e) {

    $conn->rollback();

    throw $e;
  }
}


/**
 * Menghapus foto.
 *
 * Foto thumbnail tidak boleh dihapus
 * jika masih ada foto lain.
 */
function deleteFotoKos(
  $id_foto,
  $id_pemilik
) {
  $conn = db();


  /*
   * Validasi ownership.
   */
  $foto = findFotoByIdPemilik(
    $id_foto,
    $id_pemilik
  );

  if (!$foto) {
    throw new Exception(
      'Foto tidak ditemukan atau bukan milik Anda.'
    );
  }


  $id_kos = (int) $foto['id_kos'];


  /*
   * Jika foto adalah thumbnail,
   * cek apakah ada foto lain.
   */
  if ((int) $foto['is_thumbnail'] === 1) {

    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM kos_foto
      WHERE id_kos = ?
        AND id_foto != ?
    ");

    $stmt->bind_param(
      'ii',
      $id_kos,
      $id_foto
    );

    $stmt->execute();

    $fotoLain = (int) (
      $stmt
        ->get_result()
        ->fetch_assoc()['total'] ?? 0
    );

    $stmt->close();


    if ($fotoLain > 0) {

      throw new Exception(
        'Foto thumbnail tidak dapat dihapus. Jadikan foto lain sebagai thumbnail terlebih dahulu.'
      );
    }
  }


  $namaFile = $foto['nama_file'];


  /*
   * Hapus dari database.
   */
  $stmt = $conn->prepare("
    DELETE FROM kos_foto
    WHERE id_foto = ?
  ");

  $stmt->bind_param(
    'i',
    $id_foto
  );

  $success = $stmt->execute();

  $stmt->close();


  if (!$success) {
    throw new Exception(
      'Gagal menghapus foto.',
      500
    );
  }


  /*
   * Hapus file fisik.
   */
  $filePath =
    ROOT_PATH .
    '/public/uploads' .
    $namaFile;

  if (file_exists($filePath)) {
    unlink($filePath);
  }


  return true;
}
