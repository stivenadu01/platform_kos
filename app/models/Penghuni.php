<?php

function getPenghuniListByPemilik(
  $id_pemilik,
  $search = '',
  $id_kos = '',
  $id_kamar = '',
  $status = ''
) {
  $conn = db();

  $where = [
    'k.id_pemilik = ?'
  ];

  $params = [$id_pemilik];
  $types = 'i';

  if ($search !== '') {
    $where[] = '(
      p.nama LIKE ?
      OR p.no_hp LIKE ?
      OR p.nik LIKE ?
      OR km.nomor_kamar LIKE ?
    )';

    $keyword = "%{$search}%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= 'ssss';
  }

  if ($id_kos !== '') {
    $where[] = 'km.id_kos = ?';

    $params[] = (int) $id_kos;
    $types .= 'i';
  }

  if ($id_kamar !== '') {
    $where[] = 'p.id_kamar = ?';

    $params[] = (int) $id_kamar;
    $types .= 'i';
  }

  if ($status !== '') {
    $where[] = 'p.status = ?';

    $params[] = $status;
    $types .= 's';
  }

  $whereSql = 'WHERE ' . implode(' AND ', $where);

  $sql = "
    SELECT
      p.id_penghuni,
      p.id_kamar,
      p.id_user,
      p.nama,
      p.no_hp,
      p.nik,
      p.tanggal_masuk,
      p.tanggal_keluar,
      p.status,
      p.created_at,
      p.updated_at,

      km.nomor_kamar,
      km.tipe_kamar,

      k.id_kos,
      k.nama_kos

    FROM penghuni p

    INNER JOIN kamar km
      ON km.id_kamar = p.id_kamar

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    {$whereSql}

    ORDER BY
      CASE
        WHEN p.status = 'aktif' THEN 0
        ELSE 1
      END,
      p.nama ASC
  ";

  $stmt = $conn->prepare($sql);

  $stmt->bind_param(
    $types,
    ...$params
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


function findPenghuniByIdPemilik(
  $id_penghuni,
  $id_pemilik
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      p.*,

      km.nomor_kamar,
      km.tipe_kamar,
      km.kapasitas,

      k.id_kos,
      k.nama_kos

    FROM penghuni p

    INNER JOIN kamar km
      ON km.id_kamar = p.id_kamar

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    WHERE p.id_penghuni = ?
      AND k.id_pemilik = ?

    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_penghuni,
    $id_pemilik
  );

  $stmt->execute();

  $data = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $data;
}


function getKamarListForPenghuni($id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      km.id_kos,
      km.nomor_kamar,
      km.tipe_kamar,
      km.kapasitas,
      km.status,
      k.nama_kos,

      (
        SELECT COUNT(*)
        FROM penghuni p
        WHERE p.id_kamar = km.id_kamar
          AND p.status = 'aktif'
      ) AS jumlah_penghuni

    FROM kamar km

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    WHERE k.id_pemilik = ?
      AND km.status != 'nonaktif'
      AND km.status != 'perbaikan'

    ORDER BY
      k.nama_kos ASC,
      km.nomor_kamar ASC
  ");

  $stmt->bind_param(
    'i',
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


function createPenghuni(
  $data,
  $id_pemilik
) {
  $conn = db();

  $id_kamar = (int) ($data['id_kamar'] ?? 0);
  $id_user = !empty($data['id_user'])
    ? (int) $data['id_user']
    : null;

  $nama = trim($data['nama'] ?? '');
  $no_hp = trim($data['no_hp'] ?? '');
  $nik = trim($data['nik'] ?? '');

  $tanggal_masuk =
    trim($data['tanggal_masuk'] ?? '');

  if (!$id_kamar) {
    throw new Exception(
      'Kamar wajib dipilih.'
    );
  }

  if ($nama === '') {
    throw new Exception(
      'Nama penghuni wajib diisi.'
    );
  }

  if ($tanggal_masuk === '') {
    throw new Exception(
      'Tanggal masuk wajib diisi.'
    );
  }

  /*
   * Pastikan kamar milik pemilik.
   */
  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      km.kapasitas,
      km.status,
      k.nama_kos,
      km.nomor_kamar

    FROM kamar km

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    WHERE km.id_kamar = ?
      AND k.id_pemilik = ?

    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_kamar,
    $id_pemilik
  );

  $stmt->execute();

  $kamar = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if (!$kamar) {
    throw new Exception(
      'Kamar tidak ditemukan atau bukan milik Anda.'
    );
  }

  if (
    $kamar['status'] === 'nonaktif' ||
    $kamar['status'] === 'perbaikan'
  ) {
    throw new Exception(
      'Kamar tidak dapat digunakan untuk penghuni.'
    );
  }

  /*
   * Cek kapasitas.
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param(
    'i',
    $id_kamar
  );

  $stmt->execute();

  $jumlahAktif = (int) (
    $stmt
      ->get_result()
      ->fetch_assoc()['total'] ?? 0
  );

  $stmt->close();

  if (
    $jumlahAktif >= (int) $kamar['kapasitas']
  ) {
    throw new Exception(
      'Kapasitas kamar sudah penuh.'
    );
  }

  /*
   * NIK harus unik jika diisi.
   */
  if ($nik !== '') {

    $stmt = $conn->prepare("
      SELECT id_penghuni
      FROM penghuni
      WHERE nik = ?
      LIMIT 1
    ");

    $stmt->bind_param(
      's',
      $nik
    );

    $stmt->execute();

    $exists = $stmt
      ->get_result()
      ->fetch_assoc();

    $stmt->close();

    if ($exists) {
      throw new Exception(
        'NIK tersebut sudah digunakan oleh penghuni lain.'
      );
    }
  }

  $stmt = $conn->prepare("
    INSERT INTO penghuni (
      id_kamar,
      id_user,
      nama,
      no_hp,
      nik,
      tanggal_masuk,
      status
    )
    VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, 'aktif')
  ");

  $stmt->bind_param(
    'iissss',
    $id_kamar,
    $id_user,
    $nama,
    $no_hp,
    $nik,
    $tanggal_masuk
  );

  $result = $stmt->execute();

  $id_penghuni = $stmt->insert_id;

  $stmt->close();

  if (!$result) {
    return false;
  }
  /*
  * Kelola periode dan tagihan.
  *
  * Jika ini penghuni pertama:
  *   buat periode + tagihan awal.
  *
  * Jika periode sudah ada:
  *   sesuaikan harga berdasarkan
  *   jumlah penghuni baru.
  */
  prosesPeriodeDanTagihanPenghuniMasuk(
    $id_kamar,
    $id_penghuni,
    $tanggal_masuk
  );

  /*
  * Otomatis ubah status kamar.
  */
  sinkronkanStatusKamar($id_kamar);

  return $id_penghuni;
}


function updatePenghuni(
  $id_penghuni,
  $data,
  $id_pemilik
) {
  $conn = db();

  $penghuni = findPenghuniByIdPemilik(
    $id_penghuni,
    $id_pemilik
  );

  if (!$penghuni) {
    throw new Exception(
      'Penghuni tidak ditemukan atau bukan milik Anda.'
    );
  }

  $id_kamar = (int) ($data['id_kamar'] ?? 0);
  $nama = trim($data['nama'] ?? '');
  $no_hp = trim($data['no_hp'] ?? '');
  $nik = trim($data['nik'] ?? '');

  $tanggal_masuk =
    trim($data['tanggal_masuk'] ?? '');

  if (!$id_kamar || $nama === '') {
    throw new Exception(
      'Kamar dan nama penghuni wajib diisi.'
    );
  }

  /*
   * Validasi kamar baru.
   */
  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      km.kapasitas,
      km.status

    FROM kamar km

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    WHERE km.id_kamar = ?
      AND k.id_pemilik = ?

    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_kamar,
    $id_pemilik
  );

  $stmt->execute();

  $kamar = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if (!$kamar) {
    throw new Exception(
      'Kamar tidak ditemukan atau bukan milik Anda.'
    );
  }

  if (
    $penghuni['status'] === 'aktif' &&
    (
      $kamar['status'] === 'perbaikan' ||
      $kamar['status'] === 'nonaktif'
    )
  ) {
    throw new Exception(
      'Kamar tidak dapat digunakan untuk penghuni.'
    );
  }

  /*
   * Jika pindah kamar, cek kapasitas kamar baru.
   */
  if (
    $id_kamar !== (int) $penghuni['id_kamar'] &&
    $penghuni['status'] === 'aktif'
  ) {

    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM penghuni
      WHERE id_kamar = ?
        AND status = 'aktif'
    ");

    $stmt->bind_param(
      'i',
      $id_kamar
    );

    $stmt->execute();

    $jumlahAktif = (int) (
      $stmt
        ->get_result()
        ->fetch_assoc()['total'] ?? 0
    );

    $stmt->close();

    if (
      $jumlahAktif >= (int) $kamar['kapasitas']
    ) {
      throw new Exception(
        'Kapasitas kamar tujuan sudah penuh.'
      );
    }
  }

  /*
   * Validasi NIK.
   */
  if ($nik !== '') {

    $stmt = $conn->prepare("
      SELECT id_penghuni
      FROM penghuni
      WHERE nik = ?
        AND id_penghuni != ?
      LIMIT 1
    ");

    $stmt->bind_param(
      'si',
      $nik,
      $id_penghuni
    );

    $stmt->execute();

    $exists = $stmt
      ->get_result()
      ->fetch_assoc();

    $stmt->close();

    if ($exists) {
      throw new Exception(
        'NIK tersebut sudah digunakan oleh penghuni lain.'
      );
    }
  }

  $oldKamar = (int) $penghuni['id_kamar'];

  $stmt = $conn->prepare("
    UPDATE penghuni
    SET
      id_kamar = ?,
      nama = ?,
      no_hp = ?,
      nik = NULLIF(?, ''),
      tanggal_masuk = ?
    WHERE id_penghuni = ?
  ");

  $stmt->bind_param(
    'issssi',
    $id_kamar,
    $nama,
    $no_hp,
    $nik,
    $tanggal_masuk,
    $id_penghuni
  );

  $result = $stmt->execute();

  $stmt->close();

  if (!$result) {
    return false;
  }

  /*
   * Sinkronkan kamar lama dan baru.
   */
  if ($oldKamar !== $id_kamar) {
    sinkronkanStatusKamar($oldKamar);
    sinkronkanStatusKamar($id_kamar);
  } else {
    sinkronkanStatusKamar($id_kamar);
  }

  return true;
}


function keluarkanPenghuni(
  $id_penghuni,
  $tanggal_keluar,
  $id_pemilik
) {
  $conn = db();

  $penghuni = findPenghuniByIdPemilik(
    $id_penghuni,
    $id_pemilik
  );

  if (!$penghuni) {
    throw new Exception(
      'Penghuni tidak ditemukan atau bukan milik Anda.'
    );
  }

  if ($penghuni['status'] !== 'aktif') {
    throw new Exception(
      'Penghuni sudah berstatus keluar.'
    );
  }

  if (!$tanggal_keluar) {
    throw new Exception(
      'Tanggal keluar wajib diisi.'
    );
  }

  if (
    $tanggal_keluar < $penghuni['tanggal_masuk']
  ) {
    throw new Exception(
      'Tanggal keluar tidak boleh sebelum tanggal masuk.'
    );
  }

  $stmt = $conn->prepare("
    UPDATE penghuni
    SET
      tanggal_keluar = ?,
      status = 'keluar'
    WHERE id_penghuni = ?
  ");

  $stmt->bind_param(
    'si',
    $tanggal_keluar,
    $id_penghuni
  );

  $result = $stmt->execute();

  $stmt->close();

  if (!$result) {
    return false;
  }

  sinkronkanStatusKamar(
    (int) $penghuni['id_kamar']
  );

  return true;
}


function deletePenghuni(
  $id_penghuni,
  $id_pemilik
) {
  $conn = db();

  $penghuni = findPenghuniByIdPemilik(
    $id_penghuni,
    $id_pemilik
  );

  if (!$penghuni) {
    throw new Exception(
      'Penghuni tidak ditemukan atau bukan milik Anda.'
    );
  }

  /*
   * Penghuni aktif tidak boleh dihapus.
   * Gunakan proses keluar.
   */
  if ($penghuni['status'] === 'aktif') {
    throw new Exception(
      'Penghuni aktif tidak dapat dihapus. Proses penghuni keluar terlebih dahulu.'
    );
  }

  $stmt = $conn->prepare("
    DELETE FROM penghuni
    WHERE id_penghuni = ?
  ");

  $stmt->bind_param(
    'i',
    $id_penghuni
  );

  $result = $stmt->execute();

  $stmt->close();

  return $result;
}
