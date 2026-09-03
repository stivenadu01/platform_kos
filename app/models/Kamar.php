<?php

function getKamarListByPemilik($id_pemilik, $search = '', $id_kos = '', $status = '', $id_tipe_kamar = '')
{
  $conn = db();

  $where = [
    'k.id_pemilik = ?'
  ];

  $params = [$id_pemilik];
  $types = 'i';

  if ($search !== '') {
    // Pencarian sengaja dibatasi hanya pada nomor kamar agar cepat dan tidak membingungkan.
    $where[] = 'km.nomor_kamar LIKE ?';
    $params[] = "%{$search}%";
    $types .= 's';
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

  if ($id_tipe_kamar !== '') {
    $where[] = 'km.id_tipe_kamar = ?';
    $params[] = (int) $id_tipe_kamar;
    $types .= 'i';
  }

  $whereSql = 'WHERE ' . implode(' AND ', $where);

  $sql = "
    SELECT
      km.id_kamar,
      km.id_kos,
      km.nomor_kamar,
      km.id_tipe_kamar,
      tk.nama_tipe AS tipe_kamar,
      tk.kapasitas,
      km.status,
      km.deskripsi,
      km.created_at,
      km.updated_at,
      k.nama_kos,

      (
        SELECT MIN(hk.harga_total)
        FROM harga_kamar hk
        WHERE hk.id_tipe_kamar = km.id_tipe_kamar
      ) AS harga_min,

      (
        SELECT MAX(hk.harga_total)
        FROM harga_kamar hk
        WHERE hk.id_tipe_kamar = km.id_tipe_kamar
      ) AS harga_max

    FROM kamar km

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    INNER JOIN tipe_kamar tk
      ON tk.id_tipe_kamar = km.id_tipe_kamar

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
      tk.nama_tipe AS tipe_kamar,
      tk.kapasitas,
      k.nama_kos
    FROM kamar km
    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    INNER JOIN tipe_kamar tk
      ON tk.id_tipe_kamar = km.id_tipe_kamar
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

  $id_tipe_kamar = (int) ($data['id_tipe_kamar'] ?? 0);
  $nomor_kamar = trim($data['nomor_kamar'] ?? '');
  $stmt = $conn->prepare("
    SELECT t.id_tipe_kamar, t.id_kos
    FROM tipe_kamar t
    INNER JOIN kos k ON k.id_kos = t.id_kos
    WHERE t.id_tipe_kamar = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_tipe_kamar, $id_pemilik);
  $stmt->execute();
  $tipe = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$tipe || $nomor_kamar === '') {
    throw new Exception('Tipe kamar dan nomor kamar wajib diisi.');
  }

  $id_kos = (int) $tipe['id_kos'];
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

  $stmt = $conn->prepare("
    INSERT INTO kamar (
      id_kos,
      id_tipe_kamar,
      nomor_kamar
    )
    VALUES (?, ?, ?)
  ");

  $stmt->bind_param(
    'iis',
    $id_kos,
    $id_tipe_kamar,
    $nomor_kamar
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

  $id_tipe_kamar = (int) ($data['id_tipe_kamar'] ?? 0);
  $nomor_kamar = trim($data['nomor_kamar'] ?? '');
  /*
   * Pastikan kos baru juga milik pemilik
   */
  $stmt = $conn->prepare("
    SELECT t.id_kos
    FROM tipe_kamar t
    INNER JOIN kos k ON k.id_kos = t.id_kos
    WHERE t.id_tipe_kamar = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_tipe_kamar, $id_pemilik);
  $stmt->execute();

  $kos = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  if (!$kos) {
    throw new Exception('Kos tidak ditemukan atau bukan milik Anda.');
  }

  if (!$kos || !$id_tipe_kamar || $nomor_kamar === '') {
    throw new Exception('Tipe kamar dan nomor kamar wajib diisi.');
  }

  $id_kos = (int) $kos['id_kos'];

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
      id_tipe_kamar = ?,
      nomor_kamar = ?
    WHERE id_kamar = ?
  ");

  $stmt->bind_param(
    'iisi',
    $id_kos,
    $id_tipe_kamar,
    $nomor_kamar,
    $id_kamar
  );

  $result = $stmt->execute();

  $stmt->close();

  if (!$result) {
    return false;
  }


  return true;
}

function createBulkKamar($data, $id_pemilik)
{
  $id_tipe_kamar = (int) ($data['id_tipe_kamar'] ?? 0);
  $nomor_awal = trim((string) ($data['nomor_awal'] ?? ''));
  $jumlah = (int) ($data['jumlah'] ?? 0);
  if (!$id_tipe_kamar || $nomor_awal === '' || $jumlah < 1) {
    throw new Exception('Tipe kamar, nomor awal, dan jumlah wajib diisi.', 422);
  }
  if (!ctype_digit($nomor_awal)) {
    throw new Exception('Nomor awal harus berupa angka.', 422);
  }

  $conn = db();
  $stmt = $conn->prepare("SELECT t.id_kos FROM tipe_kamar t INNER JOIN kos k ON k.id_kos = t.id_kos WHERE t.id_tipe_kamar = ? AND k.id_pemilik = ? LIMIT 1");
  $stmt->bind_param('ii', $id_tipe_kamar, $id_pemilik);
  $stmt->execute();
  $tipe = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$tipe) {
    throw new Exception('Tipe kamar tidak ditemukan atau bukan milik Anda.', 403);
  }

  $nomorInt = (int) $nomor_awal;
  if ($nomorInt < 0 || $nomorInt + $jumlah - 1 > 999999999) {
    throw new Exception('Rentang nomor kamar tidak valid.', 422);
  }

  $nomorList = [];
  for ($index = 0; $index < $jumlah; $index++) {
    $nomorList[] = (string) ($nomorInt + $index);
  }

  $placeholders = implode(',', array_fill(0, count($nomorList), '?'));
  $types = 'i' . str_repeat('s', count($nomorList));
  $params = array_merge([(int) $tipe['id_kos']], $nomorList);
  $stmt = $conn->prepare("SELECT nomor_kamar FROM kamar WHERE id_kos = ? AND nomor_kamar IN ($placeholders)");
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  if ($existing) {
    throw new Exception('Sebagian nomor kamar sudah digunakan: ' . implode(', ', array_column($existing, 'nomor_kamar')), 422);
  }

  $conn->begin_transaction();
  try {
    $id_kos = (int) $tipe['id_kos'];
    $stmt = $conn->prepare('INSERT INTO kamar (id_kos, id_tipe_kamar, nomor_kamar) VALUES (?, ?, ?)');
    foreach ($nomorList as $nomor) {
      $stmt->bind_param('iis', $id_kos, $id_tipe_kamar, $nomor);
      if (!$stmt->execute()) {
        throw new Exception('Gagal membuat kamar secara massal.', 500);
      }
    }
    $stmt->close();
    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }

  return ['jumlah' => $jumlah, 'nomor_awal' => $nomorList[0], 'nomor_akhir' => end($nomorList)];
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
      id_tipe_kamar,
      jumlah_orang,
      harga_total,
      created_at,
      updated_at
    FROM harga_kamar
    WHERE id_tipe_kamar = (
      SELECT id_tipe_kamar
      FROM kamar
      WHERE id_kamar = ?
    )
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

  $stmt = $conn->prepare('SELECT id_tipe_kamar, kapasitas FROM kamar INNER JOIN tipe_kamar USING (id_tipe_kamar) WHERE id_kamar = ? LIMIT 1');
  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();
  $room = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$room) {
    throw new Exception('Kamar tidak ditemukan.', 404);
  }

  $id_tipe_kamar = (int) $room['id_tipe_kamar'];
  $kapasitas = (int) $room['kapasitas'];

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
    WHERE id_tipe_kamar = ?
  ");

  $stmt->bind_param(
    'i',
    $id_tipe_kamar
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
      id_tipe_kamar,
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
      $id_tipe_kamar,
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
