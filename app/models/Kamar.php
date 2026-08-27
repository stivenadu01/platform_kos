<?php

function getKamarListByPemilik($id_pemilik, $search = '', $id_kos = '', $status = '')
{
  $conn = db();

  $where = [
    'k.id_pemilik = ?'
  ];

  $params = [$id_pemilik];
  $types = 'i';

  if ($search !== '') {
    $where[] = '(km.nomor_kamar LIKE ? OR km.tipe_kamar LIKE ?)';
    $keyword = "%{$search}%";

    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'ss';
  }

  if ($id_kos !== '') {
    $where[] = 'km.id_kos = ?';
    $params[] = (int) $id_kos;
    $types .= 'i';
  }

  if ($status !== '') {
    $where[] = 'km.status = ?';
    $params[] = $status;
    $types .= 's';
  }

  $whereSql = 'WHERE ' . implode(' AND ', $where);

  $sql = "
    SELECT
      km.id_kamar,
      km.id_kos,
      km.nomor_kamar,
      km.tipe_kamar,
      km.kapasitas,
      km.status,
      km.deskripsi,
      km.created_at,
      km.updated_at,
      k.nama_kos,

      (
        SELECT MIN(hk.harga_total)
        FROM harga_kamar hk
        WHERE hk.id_kamar = km.id_kamar
      ) AS harga_min,

      (
        SELECT MAX(hk.harga_total)
        FROM harga_kamar hk
        WHERE hk.id_kamar = km.id_kamar
      ) AS harga_max

    FROM kamar km

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    {$whereSql}

    ORDER BY
      k.nama_kos ASC,
      km.nomor_kamar ASC
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();

  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();

  return $data;
}


function findKamarByIdPemilik(
  $id_kamar,
  $id_pemilik
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      km.*,
      k.nama_kos
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

  $data =
    $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if ($data) {

    $data['harga'] =
      getHargaKamarByKamar($id_kamar);
  }

  return $data;
}


function getKosListByPemilik($id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_kos,
      nama_kos,
      status
    FROM kos
    WHERE id_pemilik = ?
    ORDER BY nama_kos ASC
  ");

  $stmt->bind_param('i', $id_pemilik);

  $stmt->execute();

  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();

  return $data;
}


function createKamar($data, $id_pemilik)
{
  $conn = db();

  $id_kos = (int) $data['id_kos'];
  $nomor_kamar = trim($data['nomor_kamar']);
  $tipe_kamar = trim($data['tipe_kamar'] ?? '');
  $kapasitas = (int) $data['kapasitas'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  /*
   * Pastikan kos benar-benar milik pemilik
   */
  $stmt = $conn->prepare("
    SELECT id_kos
    FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();

  $kos = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if (!$kos) {
    throw new Exception('Kos tidak ditemukan atau bukan milik Anda.');
  }

  /*
   * Cegah nomor kamar duplikat pada kos yang sama
   */
  $stmt = $conn->prepare("
    SELECT id_kamar
    FROM kamar
    WHERE id_kos = ?
      AND nomor_kamar = ?
    LIMIT 1
  ");

  $stmt->bind_param('is', $id_kos, $nomor_kamar);
  $stmt->execute();

  $exists = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if ($exists) {
    throw new Exception('Nomor kamar tersebut sudah digunakan pada kos ini.');
  }

  /*
   * Validasi kapasitas
   */
  if ($kapasitas < 1) {
    throw new Exception('Kapasitas kamar minimal 1 orang.');
  }

  $stmt = $conn->prepare("
    INSERT INTO kamar (
      id_kos,
      nomor_kamar,
      tipe_kamar,
      kapasitas,
      deskripsi
    )
    VALUES (?, ?, ?, ?, ?)
  ");

  $stmt->bind_param(
    'issis',
    $id_kos,
    $nomor_kamar,
    $tipe_kamar,
    $kapasitas,
    $deskripsi
  );

  $result = $stmt->execute();

  $id_kamar = $stmt->insert_id;

  $stmt->close();

  return $result ? $id_kamar : false;
}


function updateKamar($id_kamar, $data, $id_pemilik)
{
  $conn = db();

  /*
   * Ambil kamar sekaligus validasi ownership
   */
  $kamar = findKamarByIdPemilik($id_kamar, $id_pemilik);

  if (!$kamar) {
    throw new Exception('Kamar tidak ditemukan atau bukan milik Anda.');
  }

  $id_kos = (int) $data['id_kos'];
  $nomor_kamar = trim($data['nomor_kamar']);
  $tipe_kamar = trim($data['tipe_kamar'] ?? '');
  $kapasitas = (int) $data['kapasitas'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  /*
   * Pastikan kos baru juga milik pemilik
   */
  $stmt = $conn->prepare("
    SELECT id_kos
    FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();

  $kos = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if (!$kos) {
    throw new Exception('Kos tidak ditemukan atau bukan milik Anda.');
  }

  if ($kapasitas < 1) {
    throw new Exception('Kapasitas kamar minimal 1 orang.');
  }

  /*
   * Cek nomor kamar duplikat.
   * Abaikan kamar yang sedang diedit.
   */
  $stmt = $conn->prepare("
    SELECT id_kamar
    FROM kamar
    WHERE id_kos = ?
      AND nomor_kamar = ?
      AND id_kamar != ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'isi',
    $id_kos,
    $nomor_kamar,
    $id_kamar
  );

  $stmt->execute();

  $exists = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if ($exists) {
    throw new Exception('Nomor kamar tersebut sudah digunakan pada kos ini.');
  }

  $stmt = $conn->prepare("
    UPDATE kamar
    SET
      id_kos = ?,
      nomor_kamar = ?,
      tipe_kamar = ?,
      kapasitas = ?,
      deskripsi = ?
    WHERE id_kamar = ?
  ");

  $stmt->bind_param(
    'issisi',
    $id_kos,
    $nomor_kamar,
    $tipe_kamar,
    $kapasitas,
    $deskripsi,
    $id_kamar
  );

  $result = $stmt->execute();

  $stmt->close();

  if (!$result) {
    return false;
  }


  return true;
}


function deleteKamar($id_kamar, $id_pemilik)
{
  $conn = db();

  /*
   * Ownership check
   */
  $kamar = findKamarByIdPemilik($id_kamar, $id_pemilik);

  if (!$kamar) {
    throw new Exception('Kamar tidak ditemukan atau bukan milik Anda.');
  }

  /*
   * Jangan hapus kamar yang sudah memiliki penghuni aktif.
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $total = (int) $stmt->get_result()->fetch_assoc()['total'];

  $stmt->close();

  if ($total > 0) {
    throw new Exception(
      'Kamar tidak dapat dihapus karena masih memiliki penghuni aktif.'
    );
  }

  $stmt = $conn->prepare("
    DELETE FROM kamar
    WHERE id_kamar = ?
  ");

  $stmt->bind_param('i', $id_kamar);

  $result = $stmt->execute();

  $stmt->close();

  return $result;
}


function updateKamarStatus($id_kamar, $status, $id_pemilik)
{
  $conn = db();

  $kamar = findKamarByIdPemilik(
    $id_kamar,
    $id_pemilik
  );

  if (!$kamar) {
    throw new Exception(
      'Kamar tidak ditemukan atau bukan milik Anda.'
    );
  }

  /*
   * Status yang boleh diatur manual.
   *
   * TERISI tidak boleh diatur manual
   * karena ditentukan oleh penghuni aktif.
   */
  $allowedStatus = [
    'tersedia',
    'perbaikan',
    'nonaktif'
  ];

  if (!in_array($status, $allowedStatus, true)) {
    throw new Exception(
      'Status kamar harus ditentukan oleh sistem.'
    );
  }

  /*
   * Jangan izinkan kamar yang masih memiliki
   * penghuni aktif menjadi tersedia/perbaikan/nonaktif.
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $total = (int) (
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
  );

  $stmt->close();

  if ($total > 0) {
    throw new Exception(
      'Status kamar tidak dapat diubah karena masih memiliki penghuni aktif.'
    );
  }

  $stmt = $conn->prepare("
    UPDATE kamar
    SET status = ?
    WHERE id_kamar = ?
  ");

  $stmt->bind_param(
    'si',
    $status,
    $id_kamar
  );

  $result = $stmt->execute();

  $stmt->close();

  return $result;
}

function sinkronkanStatusKamar($id_kamar)
{
  $conn = db();

  /*
   * Pastikan kamar ada
   */
  $stmt = $conn->prepare("
    SELECT status
    FROM kamar
    WHERE id_kamar = ?
    LIMIT 1
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $kamar = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if (!$kamar) {
    return false;
  }

  /*
   * Hitung penghuni aktif
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $result = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  $totalPenghuni = (int) ($result['total'] ?? 0);

  /*
   * Jika ada penghuni aktif,
   * kamar otomatis menjadi TERISI.
   */
  if ($totalPenghuni > 0) {
    $status = 'terisi';
  }

  /*
   * Jika tidak ada penghuni aktif:
   *
   * - terisi     -> tersedia
   * - perbaikan  -> tetap perbaikan
   * - nonaktif   -> tetap nonaktif
   * - tersedia   -> tetap tersedia
   */ else {
    $status = $kamar['status'] === 'terisi'
      ? 'tersedia'
      : $kamar['status'];
  }

  /*
   * Update hanya jika status berubah.
   */
  if ($status !== $kamar['status']) {

    $stmt = $conn->prepare("
      UPDATE kamar
      SET status = ?
      WHERE id_kamar = ?
    ");

    $stmt->bind_param(
      'si',
      $status,
      $id_kamar
    );

    $success = $stmt->execute();

    $stmt->close();

    if (!$success) {
      return false;
    }
  }

  return $status;
}


/*
|--------------------------------------------------------------------------
| HARGA KAMAR
|--------------------------------------------------------------------------
*/

/**
 * Ambil seluruh harga berdasarkan kamar.
 */
function getHargaKamarByKamar($id_kamar)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_harga,
      id_kamar,
      jumlah_orang,
      harga_total,
      created_at,
      updated_at
    FROM harga_kamar
    WHERE id_kamar = ?
    ORDER BY jumlah_orang ASC
  ");

  $stmt->bind_param(
    'i',
    $id_kamar
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
 * Validasi dan simpan konfigurasi harga kamar.
 *
 * $harga berbentuk:
 *
 * [
 *   [
 *     'jumlah_orang' => 1,
 *     'harga_total'  => 700000
 *   ],
 *   [
 *     'jumlah_orang' => 2,
 *     'harga_total'  => 1000000
 *   ]
 * ]
 */
function saveHargaKamar(
  $id_kamar,
  $harga,
  $kapasitas,
  $conn = null
) {
  $ownConnection = false;

  if (!$conn) {
    $conn = db();
    $ownConnection = true;
  }

  if (!is_array($harga)) {
    throw new Exception(
      'Format harga kamar tidak valid.'
    );
  }

  /*
   * Normalisasi data harga.
   */
  $normalized = [];

  foreach ($harga as $item) {

    if (!is_array($item)) {
      throw new Exception(
        'Format data harga tidak valid.'
      );
    }

    $jumlah_orang = (int) (
      $item['jumlah_orang'] ?? 0
    );

    $harga_total = $item['harga_total'] ?? null;

    /*
     * Harga bisa dikirim sebagai:
     * 700000
     * "700000"
     * "700000.00"
     */
    if (
      $harga_total === null ||
      $harga_total === ''
    ) {
      throw new Exception(
        'Harga kamar wajib diisi.'
      );
    }

    if (!is_numeric($harga_total)) {
      throw new Exception(
        'Harga kamar harus berupa angka.'
      );
    }

    $harga_total = (float) $harga_total;

    /*
     * Validasi jumlah orang.
     */
    if ($jumlah_orang < 1) {
      throw new Exception(
        'Jumlah orang minimal 1.'
      );
    }

    if ($jumlah_orang > $kapasitas) {
      throw new Exception(
        "Harga untuk {$jumlah_orang} orang melebihi kapasitas kamar."
      );
    }

    /*
     * Mengikuti schema:
     * harga_total >= 0
     */
    if ($harga_total < 0) {
      throw new Exception(
        'Harga kamar tidak boleh kurang dari Rp0.'
      );
    }

    /*
     * Cegah jumlah orang duplikat.
     */
    if (isset($normalized[$jumlah_orang])) {
      throw new Exception(
        "Harga untuk {$jumlah_orang} orang hanya boleh satu."
      );
    }

    $normalized[$jumlah_orang] = [
      'jumlah_orang' => $jumlah_orang,
      'harga_total' => $harga_total
    ];
  }

  /*
   * Urutkan berdasarkan jumlah orang.
   */
  ksort($normalized);

  /*
   * Hapus konfigurasi harga lama.
   *
   * Karena seluruh harga dikirim dari form,
   * database akan disinkronkan dengan data terbaru.
   */
  $stmt = $conn->prepare("
    DELETE FROM harga_kamar
    WHERE id_kamar = ?
  ");

  $stmt->bind_param(
    'i',
    $id_kamar
  );

  if (!$stmt->execute()) {
    $stmt->close();

    throw new Exception(
      'Gagal menghapus konfigurasi harga lama.'
    );
  }

  $stmt->close();

  /*
   * Tidak ada harga.
   * Ini tetap valid.
   */
  if (empty($normalized)) {
    return true;
  }

  /*
   * Insert harga baru.
   */
  $stmt = $conn->prepare("
    INSERT INTO harga_kamar (
      id_kamar,
      jumlah_orang,
      harga_total
    )
    VALUES (?, ?, ?)
  ");

  foreach ($normalized as $item) {

    $jumlah_orang =
      $item['jumlah_orang'];

    $harga_total =
      $item['harga_total'];

    $stmt->bind_param(
      'iid',
      $id_kamar,
      $jumlah_orang,
      $harga_total
    );

    if (!$stmt->execute()) {

      $stmt->close();

      throw new Exception(
        'Gagal menyimpan harga kamar.'
      );
    }
  }

  $stmt->close();

  return true;
}
