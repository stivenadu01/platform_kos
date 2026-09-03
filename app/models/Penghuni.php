<?php

/*
|--------------------------------------------------------------------------
| PENGHUNI
|--------------------------------------------------------------------------
| Semua proses tambah penghuni dan penyesuaian tagihan berada di sini.
| Tagihan terhubung melalui tabel tagihan_penghuni.
|--------------------------------------------------------------------------
*/

function getPenghuniListByPemilik(
  $id_pemilik,
  $search = '',
  $id_kos = '',
  $id_kamar = '',
  $status = ''
) {
  $conn = db();

  $where = ['k.id_pemilik = ?'];
  $params = [$id_pemilik];
  $types = 'i';

  if ($search !== '') {
    $where[] = '(p.nama LIKE ? OR p.no_hp LIKE ? OR p.nik LIKE ?)';
    $keyword = "%{$search}%";
    array_push($params, $keyword, $keyword, $keyword);
    $types .= 'sss';
  }

  if ($id_kos !== '') {
    $where[] = 'k.id_kos = ?';
    $params[] = (int) $id_kos;
    $types .= 'i';
  }

  if ($id_kamar !== '') {
    $where[] = 'km.id_kamar = ?';
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
      km.id_kos,
      km.nomor_kamar,
      tk.nama_tipe AS tipe_kamar,
      tk.kapasitas,
      k.nama_kos
    FROM penghuni p
    INNER JOIN kamar km ON km.id_kamar = p.id_kamar
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    {$whereSql}
    ORDER BY
      p.status ASC,
      p.tanggal_masuk DESC,
      p.nama ASC
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

function getKamarListForPenghuni($id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      km.id_kos,
      km.nomor_kamar,
      tk.nama_tipe AS tipe_kamar,
      tk.kapasitas,
      km.status,
      k.nama_kos,
      (
        SELECT COUNT(*)
        FROM penghuni p
        WHERE p.id_kamar = km.id_kamar
          AND p.status = 'aktif'
      ) AS jumlah_penghuni
    FROM kamar km
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    WHERE k.id_pemilik = ?
      AND km.status <> 'nonaktif'
    ORDER BY k.nama_kos ASC, km.nomor_kamar ASC
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

function findPenghuniByIdPemilik($id_penghuni, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      p.*,
      km.id_kos,
      km.nomor_kamar,
      tk.nama_tipe AS tipe_kamar,
      tk.kapasitas,
      k.nama_kos
    FROM penghuni p
    INNER JOIN kamar km ON km.id_kamar = p.id_kamar
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    WHERE p.id_penghuni = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_penghuni, $id_pemilik);
  $stmt->execute();

  $data = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $data;
}

function validatePenghuniInput($data)
{
  $id_kamar = (int) ($data['id_kamar'] ?? 0);
  $nama = trim($data['nama'] ?? '');
  $tanggal_masuk = trim($data['tanggal_masuk'] ?? '');

  if (!$id_kamar) {
    throw new Exception('Kamar wajib dipilih.');
  }

  if ($nama === '') {
    throw new Exception('Nama penghuni wajib diisi.');
  }

  if (mb_strlen($nama) > 150) {
    throw new Exception('Nama penghuni terlalu panjang.');
  }

  if ($tanggal_masuk === '') {
    throw new Exception('Tanggal masuk wajib diisi.');
  }

  $date = DateTime::createFromFormat('Y-m-d', $tanggal_masuk);

  if (!$date || $date->format('Y-m-d') !== $tanggal_masuk) {
    throw new Exception('Format tanggal masuk tidak valid.');
  }

  $nik = trim($data['nik'] ?? '');

  if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
    throw new Exception('NIK harus terdiri dari 16 digit.');
  }

  $no_hp = trim($data['no_hp'] ?? '');

  if (mb_strlen($no_hp) > 30) {
    throw new Exception('Nomor HP terlalu panjang.');
  }

  return [
    'id_kamar' => $id_kamar,
    'nama' => $nama,
    'no_hp' => $no_hp !== '' ? $no_hp : null,
    'nik' => $nik !== '' ? $nik : null,
    'tanggal_masuk' => $tanggal_masuk
  ];
}

function findUserByNikForPenghuni($nik, $conn = null)
{
  if (!$nik) {
    return null;
  }

  $conn = $conn ?: db();
  $stmt = $conn->prepare("
    SELECT id_user, nama, no_hp
    FROM users
    WHERE nik = ?
      AND role = 'pelanggan'
      AND status = 'aktif'
    LIMIT 1
  ");
  $stmt->bind_param('s', $nik);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $user ?: null;
}

/*
|--------------------------------------------------------------------------
| HITUNG TANGGAL TAGIHAN
|--------------------------------------------------------------------------
| Periode pertama:
| mulai = tanggal masuk
| selesai = satu bulan setelah tanggal masuk - 1 hari
| jatuh tempo = tanggal yang sama pada bulan berikutnya.
|--------------------------------------------------------------------------
*/
function getRentDates($tanggal_mulai)
{
  $start = new DateTime($tanggal_mulai);

  /*
   * Untuk menghindari masalah tanggal 29/30/31,
   * tentukan tanggal jatuh tempo dengan aturan kalender.
   */
  $due = clone $start;
  $originalDay = (int) $start->format('d');

  $due->modify('first day of next month');

  $lastDay = (int) $due->format('t');
  $due->setDate(
    (int) $due->format('Y'),
    (int) $due->format('m'),
    min($originalDay, $lastDay)
  );

  $end = clone $due;
  $end->modify('-1 day');

  return [
    'mulai' => $start->format('Y-m-d'),
    'selesai' => $end->format('Y-m-d'),
    'jatuh_tempo' => $due->format('Y-m-d')
  ];
}

function getHargaKamarUntukJumlah($id_kamar, $jumlah_orang, $conn = null)
{
  $conn = $conn ?: db();

  $stmt = $conn->prepare("
    SELECT harga_total
    FROM harga_kamar hk
    INNER JOIN kamar km ON km.id_tipe_kamar = hk.id_tipe_kamar
    WHERE km.id_kamar = ?
      AND jumlah_orang = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_kamar, $jumlah_orang);
  $stmt->execute();

  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    throw new Exception(
      "Harga kamar untuk {$jumlah_orang} orang belum dikonfigurasi."
    );
  }

  return (float) $row['harga_total'];
}

/*
|--------------------------------------------------------------------------
| CARI TAGIHAN BERJALAN
|--------------------------------------------------------------------------
*/
function findTagihanBerjalan($id_kamar, $tanggal, $conn = null)
{
  $conn = $conn ?: db();

  $stmt = $conn->prepare("
    SELECT *
    FROM tagihan
    WHERE id_kamar = ?
      AND tanggal_mulai <= ?
      AND tanggal_selesai >= ?
      AND status <> 'dibatalkan'
    ORDER BY tanggal_mulai DESC
    LIMIT 1
  ");

  $stmt->bind_param('iss', $id_kamar, $tanggal, $tanggal);
  $stmt->execute();

  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row;
}

function generateNomorTagihan($conn)
{
  do {
    $nomor = 'TAG-' . date('YmdHis') . '-' . random_int(1000, 9999);

    $stmt = $conn->prepare("
      SELECT id_tagihan
      FROM tagihan
      WHERE nomor_tagihan = ?
      LIMIT 1
    ");

    $stmt->bind_param('s', $nomor);
    $stmt->execute();

    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();
  } while ($exists);

  return $nomor;
}

/*
|--------------------------------------------------------------------------
| BUAT TAGIHAN PERTAMA
|--------------------------------------------------------------------------
*/
function createTagihanPertama(
  $id_kamar,
  $tanggal_masuk,
  $jumlah_orang,
  $conn,
  $id_penghuni
) {
  $harga = getHargaKamarUntukJumlah(
    $id_kamar,
    $jumlah_orang,
    $conn
  );

  $dates = getRentDates($tanggal_masuk);

  $stmt = $conn->prepare(
    'SELECT id_kamar FROM kamar WHERE id_kamar = ? FOR UPDATE'
  );
  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();
  $kamarTerkunci = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$kamarTerkunci) {
    throw new Exception('Kamar tidak ditemukan saat membuat tagihan.');
  }

  $stmt = $conn->prepare("
    SELECT id_tagihan
    FROM tagihan
    WHERE id_kamar = ?
      AND tanggal_mulai = ?
      AND tanggal_selesai = ?
    LIMIT 1
  ");
  $stmt->bind_param(
    'iss',
    $id_kamar,
    $dates['mulai'],
    $dates['selesai']
  );
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($existing) {
    linkPenghuniTagihan(
      $existing['id_tagihan'],
      $id_penghuni,
      $conn
    );

    return (int) $existing['id_tagihan'];
  }

  $nomor = generateNomorTagihan($conn);

  $stmt = $conn->prepare("
    INSERT INTO tagihan (
      id_kamar,
      nomor_tagihan,
      tanggal_terbit,
      tanggal_mulai,
      tanggal_selesai,
      tanggal_jatuh_tempo,
      jumlah_orang,
      harga_dasar,
      total_penyesuaian,
      total_tagihan,
      total_dibayar,
      status
    )
    VALUES (
      ?, ?, CURDATE(), ?, ?, ?,
      ?, ?, 0, ?, 0, 'belum_lunas'
    )
  ");

  $stmt->bind_param(
    'issssidd',
    $id_kamar,
    $nomor,
    $dates['mulai'],
    $dates['selesai'],
    $dates['jatuh_tempo'],
    $jumlah_orang,
    $harga,
    $harga
  );

  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    throw new Exception('Gagal membuat tagihan: ' . $error);
  }

  $id_tagihan = $conn->insert_id;
  $stmt->close();

  linkPenghuniTagihan(
    $id_tagihan,
    $id_penghuni,
    $conn
  );

  return $id_tagihan;
}

function linkPenghuniTagihan($id_tagihan, $id_penghuni, $conn)
{
  $stmt = $conn->prepare("
    INSERT IGNORE INTO tagihan_penghuni (
      id_tagihan,
      id_penghuni
    ) VALUES (?, ?)
  ");

  $stmt->bind_param('ii', $id_tagihan, $id_penghuni);

  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal menghubungkan tagihan dengan penghuni.');
  }

  $stmt->close();
}

function linkPenghuniTagihanUntukPeriode(
  $id_tagihan,
  $id_kamar,
  $tanggal_mulai,
  $tanggal_selesai,
  $conn
) {
  $stmt = $conn->prepare("
    INSERT IGNORE INTO tagihan_penghuni (
      id_tagihan,
      id_penghuni
    )
    SELECT ?, p.id_penghuni
    FROM penghuni p
    WHERE p.id_kamar = ?
      AND p.tanggal_masuk <= ?
      AND (p.tanggal_keluar IS NULL OR p.tanggal_keluar >= ?)
  ");

  $stmt->bind_param(
    'iiss',
    $id_tagihan,
    $id_kamar,
    $tanggal_selesai,
    $tanggal_mulai
  );

  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal menyinkronkan penghuni dengan tagihan.');
  }

  $stmt->close();
}

/*
|--------------------------------------------------------------------------
| HITUNG PENYESUAIAN PENGHUNI TAMBAHAN
|--------------------------------------------------------------------------
| Dasar:
| selisih harga × sisa hari / jumlah hari kalender bulan mulai.
|
| Pembulatan dilakukan ke bawah ke ribuan rupiah.
| Contoh 400.000 × 25 / 31 = 322.580 -> 322.000.
|--------------------------------------------------------------------------
*/
function calculateProratedAdjustment(
  $harga_lama,
  $harga_baru,
  $tanggal_masuk,
  $tanggal_jatuh_tempo
) {
  $selisih = $harga_baru - $harga_lama;

  if ($selisih == 0) {
    return 0;
  }

  $masuk = new DateTime($tanggal_masuk);
  $jatuhTempo = new DateTime($tanggal_jatuh_tempo);

  /*
   * Sisa hari dihitung inklusif dari tanggal masuk
   * sampai tanggal jatuh tempo.
   */
  $sisaHari =
    $masuk->diff($jatuhTempo)->days + 1;

  /*
   * Jumlah hari menggunakan bulan saat penghuni masuk.
   * Ini membuat kasus:
   * 10 Agustus -> 3 September = 25/31.
   */
  $jumlahHariBulan =
    (int) $masuk->format('t');

  $nilai =
    $selisih *
    ($sisaHari / $jumlahHariBulan);

  /*
   * Untuk tagihan rupiah, bulatkan ke bawah
   * ke kelipatan Rp1.000.
   */
  if ($nilai >= 0) {
    return floor($nilai / 1000) * 1000;
  }

  return ceil($nilai / 1000) * 1000;
}

/*
|--------------------------------------------------------------------------
| TAMBAH PENGHUNI
|--------------------------------------------------------------------------
| Seluruh operasi berada dalam satu transaction.
|--------------------------------------------------------------------------
*/
function siapkanTagihanBerikutnyaUntukPenghuni($tagihan, $jumlah_orang, $tanggalTerbit, $conn)
{
  $dates = getRentDates($tagihan['tanggal_jatuh_tempo']);
  $idKamar = (int) $tagihan['id_kamar'];

  $stmt = $conn->prepare(
    'SELECT id_kamar FROM kamar WHERE id_kamar = ? FOR UPDATE'
  );
  $stmt->bind_param('i', $idKamar);
  $stmt->execute();
  $kamarTerkunci = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$kamarTerkunci) {
    throw new Exception('Kamar tidak ditemukan saat menyiapkan tagihan.');
  }

  $stmt = $conn->prepare("
    SELECT *
    FROM tagihan
    WHERE id_kamar = ?
      AND tanggal_mulai = ?
      AND tanggal_selesai = ?
    LIMIT 1
    FOR UPDATE
  ");
  $stmt->bind_param('iss', $idKamar, $dates['mulai'], $dates['selesai']);
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $harga = getHargaKamarUntukJumlah($idKamar, $jumlah_orang, $conn);

  if ($existing) {
    if ($existing['status'] === 'dibatalkan') {
      return (int) $existing['id_tagihan'];
    }

    /*
     * Periode berikutnya sudah dibuat sebelumnya, misalnya ketika
     * tagihan periode sekarang dilunasi. Jumlah penghuni berubah
     * sebelum periode berikutnya dimulai, sehingga harga dasar periode
     * berikutnya harus ikut berubah. Pembayaran yang sudah ada tetap
     * dipertahankan; status dihitung ulang berdasarkan total kewajiban.
     */
    $totalPenyesuaian = 0;
    $totalDibayar = (float) $existing['total_dibayar'];
    $totalTagihan = $harga + $totalPenyesuaian;

    if ($totalDibayar >= $totalTagihan) {
      $status = 'lunas';
    } elseif ($totalDibayar > 0) {
      $status = 'sebagian';
    } else {
      $status = 'belum_lunas';
    }

    $stmt = $conn->prepare("
      UPDATE tagihan
      SET
        jumlah_orang = ?,
        harga_dasar = ?,
        total_penyesuaian = ?,
        total_tagihan = ?,
        status = ?
      WHERE id_tagihan = ?
        AND status <> 'dibatalkan'
    ");
    $stmt->bind_param(
      'idddsi',
      $jumlah_orang,
      $harga,
      $totalPenyesuaian,
      $totalTagihan,
      $status,
      $existing['id_tagihan']
    );

    if (!$stmt->execute()) {
      $error = $stmt->error;
      $stmt->close();
      throw new Exception('Gagal menyinkronkan tagihan periode berikutnya: ' . $error);
    }
    $stmt->close();

    linkPenghuniTagihanUntukPeriode(
      $existing['id_tagihan'],
      $idKamar,
      $dates['mulai'],
      $dates['selesai'],
      $conn
    );

    return (int) $existing['id_tagihan'];
  }

  $nomor = generateNomorTagihan($conn);
  $tanggalTerbit = substr($tanggalTerbit, 0, 10);

  $stmt = $conn->prepare("
    INSERT INTO tagihan (
      id_kamar, nomor_tagihan, tanggal_terbit, tanggal_mulai,
      tanggal_selesai, tanggal_jatuh_tempo, jumlah_orang, harga_dasar,
      total_penyesuaian, total_tagihan, total_dibayar, status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, 'belum_lunas')
  ");
  $stmt->bind_param(
    'isssssidd',
    $idKamar,
    $nomor,
    $tanggalTerbit,
    $dates['mulai'],
    $dates['selesai'],
    $dates['jatuh_tempo'],
    $jumlah_orang,
    $harga,
    $harga
  );

  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    throw new Exception('Gagal membuat tagihan periode berikutnya: ' . $error);
  }

  $id = $stmt->insert_id;
  $stmt->close();

  linkPenghuniTagihanUntukPeriode(
    $id,
    $idKamar,
    $dates['mulai'],
    $dates['selesai'],
    $conn
  );

  return $id;
}

function createPenghuni($data, $id_pemilik)
{
  $data = validatePenghuniInput($data);
  $conn = db();

  /*
   * Ownership + data kamar.
   */
  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      km.id_kos,
      tk.kapasitas,
      km.status,
      k.nama_kos
    FROM kamar km
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    WHERE km.id_kamar = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $data['id_kamar'],
    $id_pemilik
  );

  $stmt->execute();

  $kamar = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$kamar) {
    throw new Exception(
      'Kamar tidak ditemukan atau bukan milik Anda.'
    );
  }

  if (in_array($kamar['status'], ['perbaikan', 'nonaktif'], true)) {
    throw new Exception(
      'Kamar sedang tidak dapat menerima penghuni.'
    );
  }

  /*
   * Hitung penghuni aktif.
   */
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param('i', $data['id_kamar']);
  $stmt->execute();

  $jumlahLama = (int) (
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
  );

  $stmt->close();

  $jumlahBaru = $jumlahLama + 1;

  if ($jumlahBaru > (int) $kamar['kapasitas']) {
    throw new Exception(
      "Kamar sudah mencapai kapasitas maksimal {$kamar['kapasitas']} orang."
    );
  }

  $user = findUserByNikForPenghuni($data['nik'], $conn);
  $id_user = $user ? (int) $user['id_user'] : null;

  /*
   * Jika NIK cocok dengan akun pelanggan aktif, identitas penghuni
   * harus berasal dari akun tersebut. Pemilik kos hanya memilih NIK;
   * nama dan nomor HP bukan data yang boleh diubah oleh pemilik.
   *
   * Jika NIK tidak ditemukan, penghuni dianggap belum memiliki akun
   * sehingga data identitas yang diinput pemilik tetap digunakan.
   */
  if ($user) {
    $data['nama'] = $user['nama'];
    $data['no_hp'] = $user['no_hp'];
    $data['nik'] = $data['nik'];
  }

  /*
   * Untuk penghuni tambahan, pastikan harga jumlah baru ada
   * sebelum INSERT agar transaction dapat dibatalkan dengan aman.
   */
  if ($jumlahBaru > 1) {
    getHargaKamarUntukJumlah(
      $data['id_kamar'],
      $jumlahBaru,
      $conn
    );
  } else {
    getHargaKamarUntukJumlah(
      $data['id_kamar'],
      1,
      $conn
    );
  }

  $conn->begin_transaction();

  try {
    /*
     * INSERT penghuni.
     */
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
      VALUES (?, ?, ?, ?, ?, ?, 'aktif')
    ");

    $stmt->bind_param(
      'iissss',
      $data['id_kamar'],
      $id_user,
      $data['nama'],
      $data['no_hp'],
      $data['nik'],
      $data['tanggal_masuk']
    );

    if (!$stmt->execute()) {
      $error = $stmt->error;
      $stmt->close();
      throw new Exception('Gagal menyimpan penghuni: ' . $error);
    }

    $id_penghuni = $conn->insert_id;
    $stmt->close();

    /*
     * Jika sebelumnya TIDAK ADA penghuni aktif, penghuni baru
     * memulai siklus tagihan baru.
     *
     * Penting:
     * Tagihan lama yang masih belum lunas tetap menjadi utang
     * penghuni lama dan TIDAK boleh dipakai untuk penghuni baru.
     */
    if ($jumlahLama === 0) {
      createTagihanPertama(
        $data['id_kamar'],
        $data['tanggal_masuk'],
        1,
        $conn,
        $id_penghuni
      );
    } else {
      /*
       * Masih ada penghuni aktif. Penghuni baru adalah penghuni
       * tambahan sehingga harus menggunakan tagihan berjalan
       * dan menghasilkan penyesuaian otomatis.
       */
      $tagihan = findTagihanBerjalan(
        $data['id_kamar'],
        $data['tanggal_masuk'],
        $conn
      );

      if (!$tagihan) {
        throw new Exception(
          'Tagihan berjalan untuk kamar ini tidak ditemukan. ' .
            'Periksa data tagihan sebelum menambahkan penghuni.'
        );
      }

      /*
       * Penghuni tambahan di tengah periode selalu menghasilkan
       * penyesuaian pada tagihan berjalan, termasuk jika sebelumnya
       * sudah lunas. Pembayaran lama tidak dihapus; status dihitung
       * ulang sehingga kembali menjadi 'sebagian' jika masih ada sisa.
       */
      $hargaLama = (float) $tagihan['harga_dasar'];

      $hargaBaru = getHargaKamarUntukJumlah(
        $data['id_kamar'],
        $jumlahBaru,
        $conn
      );

      $penyesuaian = calculateProratedAdjustment(
        $hargaLama,
        $hargaBaru,
        $data['tanggal_masuk'],
        $tagihan['tanggal_jatuh_tempo']
      );

      $totalPenyesuaian =
        (float) $tagihan['total_penyesuaian']
        + $penyesuaian;

      $totalTagihan =
        $hargaLama
        + $totalPenyesuaian;

      /*
       * Simpan histori penyesuaian jika memang ada perubahan.
       */
      if ($penyesuaian != 0) {
        $jenis =
          $penyesuaian > 0
          ? 'tambah'
          : 'kurang';

        $jumlah =
          abs($penyesuaian);

        $alasan =
          "Penyesuaian harga karena penghuni ke-{$jumlahBaru} masuk.";

        $stmt = $conn->prepare("
          INSERT INTO penyesuaian_tagihan (
            id_tagihan,
            id_penghuni,
            jenis,
            jumlah,
            tanggal_efektif,
            alasan
          )
          VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
          'iisiss',
          $tagihan['id_tagihan'],
          $id_penghuni,
          $jenis,
          $jumlah,
          $data['tanggal_masuk'],
          $alasan
        );

        if (!$stmt->execute()) {
          $error = $stmt->error;
          $stmt->close();
          throw new Exception(
            'Gagal menyimpan penyesuaian: ' . $error
          );
        }

        $stmt->close();
      }

      /*
       * Harga dasar TIDAK disentuh.
       */
      $totalDibayar = (float) $tagihan['total_dibayar'];
      if ($totalDibayar >= $totalTagihan) {
        $statusBaru = 'lunas';
      } elseif ($totalDibayar > 0) {
        $statusBaru = 'sebagian';
      } else {
        $statusBaru = 'belum_lunas';
      }

      $stmt = $conn->prepare("
        UPDATE tagihan
        SET
          jumlah_orang = ?,
          total_penyesuaian = ?,
          total_tagihan = ?,
          status = ?
        WHERE id_tagihan = ?
      ");

      $stmt->bind_param(
        'iddsi',
        $jumlahBaru,
        $totalPenyesuaian,
        $totalTagihan,
        $statusBaru,
        $tagihan['id_tagihan']
      );

      if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception(
          'Gagal memperbarui tagihan: ' . $error
        );
      }

      $stmt->close();

      linkPenghuniTagihan(
        $tagihan['id_tagihan'],
        $id_penghuni,
        $conn
      );

      /*
       * Periode berikutnya hanya boleh disiapkan setelah tagihan
       * berjalan lunas. Tagihan belum lunas tidak boleh membuat
       * kewajiban periode baru.
       */
      if ($tagihan['status'] === 'lunas') {
        siapkanTagihanBerikutnyaUntukPenghuni(
          $tagihan,
          $jumlahBaru,
          date('Y-m-d'),
          $conn
        );
      }
    }

    /*
     * Kamar otomatis TERISI jika ada penghuni.
     */
    $stmt = $conn->prepare("
      UPDATE kamar
      SET status = 'terisi'
      WHERE id_kamar = ?
    ");

    $stmt->bind_param('i', $data['id_kamar']);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception(
        'Gagal memperbarui status kamar.'
      );
    }

    $stmt->close();

    $conn->commit();

    return $id_penghuni;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

/*
|--------------------------------------------------------------------------
| EDIT DATA PENGHUNI
|--------------------------------------------------------------------------
| Edit hanya data identitas. Perubahan kamar/tanggal masuk tidak
| dilakukan lewat edit biasa karena dapat mengubah histori tagihan.
|--------------------------------------------------------------------------
*/
function updatePenghuni($id_penghuni, $data, $id_pemilik)
{
  $existing = findPenghuniByIdPemilik(
    $id_penghuni,
    $id_pemilik
  );

  if (!$existing) {
    throw new Exception('Penghuni tidak ditemukan.');
  }

  /*
   * Penghuni yang sudah terhubung ke akun pelanggan bukan milik
   * pemilik kos untuk diedit identitasnya. UI akan mengunci field,
   * tetapi aturan ini juga wajib ditegakkan di backend.
   */
  if (!empty($existing['id_user'])) {
    return true;
  }

  $nama = trim($data['nama'] ?? '');
  $no_hp = trim($data['no_hp'] ?? '');
  $nik = trim($data['nik'] ?? '');

  if ($nama === '') {
    throw new Exception('Nama penghuni wajib diisi.');
  }

  if ($nik !== '' && !preg_match('/^\d{16}$/', $nik)) {
    throw new Exception('NIK harus terdiri dari 16 digit.');
  }

  $conn = db();

  if ($nik !== '') {
    $stmt = $conn->prepare("
      SELECT id_penghuni
      FROM penghuni
      WHERE nik = ?
        AND id_penghuni <> ?
      LIMIT 1
    ");

    $stmt->bind_param('si', $nik, $id_penghuni);
    $stmt->execute();

    if ($stmt->get_result()->fetch_assoc()) {
      $stmt->close();
      throw new Exception('NIK tersebut sudah digunakan.');
    }

    $stmt->close();
  }

  $stmt = $conn->prepare("
    UPDATE penghuni p
    INNER JOIN kamar k ON k.id_kamar = p.id_kamar
    INNER JOIN kos ko ON ko.id_kos = k.id_kos
    SET
      p.nama = ?,
      p.no_hp = ?,
      p.nik = ?
    WHERE p.id_penghuni = ?
      AND ko.id_pemilik = ?
  ");

  $nikValue = $nik !== '' ? $nik : null;
  $noHpValue = $no_hp !== '' ? $no_hp : null;

  $stmt->bind_param(
    'sssii',
    $nama,
    $noHpValue,
    $nikValue,
    $id_penghuni,
    $id_pemilik
  );

  $result = $stmt->execute();
  $stmt->close();

  if (!$result) {
    throw new Exception('Gagal memperbarui data penghuni.');
  }

  return true;
}

/*
|--------------------------------------------------------------------------
| KELUAR
|--------------------------------------------------------------------------
*/
function keluarPenghuni(
  $id_penghuni,
  $tanggal_keluar,
  $id_pemilik
) {
  $existing = findPenghuniByIdPemilik(
    $id_penghuni,
    $id_pemilik
  );

  if (!$existing) {
    throw new Exception('Penghuni tidak ditemukan.');
  }

  if ($existing['status'] !== 'aktif') {
    throw new Exception('Penghuni sudah berstatus keluar.');
  }

  $date = DateTime::createFromFormat(
    'Y-m-d',
    $tanggal_keluar
  );

  if (
    !$date ||
    $date->format('Y-m-d') !== $tanggal_keluar
  ) {
    throw new Exception('Tanggal keluar tidak valid.');
  }

  if ($tanggal_keluar < $existing['tanggal_masuk']) {
    throw new Exception(
      'Tanggal keluar tidak boleh sebelum tanggal masuk.'
    );
  }

  $conn = db();
  $conn->begin_transaction();

  try {
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

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception(
        'Gagal mencatat penghuni keluar.'
      );
    }

    $stmt->close();

    hapusTagihanMasaDepanPenghuni(
      $id_penghuni,
      $tanggal_keluar,
      $conn
    );

    /*
     * Hitung penghuni aktif yang tersisa.
     */
    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM penghuni
      WHERE id_kamar = ?
        AND status = 'aktif'
    ");

    $stmt->bind_param(
      'i',
      $existing['id_kamar']
    );

    $stmt->execute();

    $jumlahAktif = (int) (
      $stmt->get_result()->fetch_assoc()['total'] ?? 0
    );

    $stmt->close();

    if ($jumlahAktif === 0) {
      /*
       * Jika tidak ada penghuni, kamar kembali tersedia
       * hanya jika sebelumnya TERISI.
       */
      $stmt = $conn->prepare("
        UPDATE kamar
        SET status = 'tersedia'
        WHERE id_kamar = ?
          AND status = 'terisi'
      ");

      $stmt->bind_param(
        'i',
        $existing['id_kamar']
      );

      $stmt->execute();
      $stmt->close();
    }

    $conn->commit();

    return true;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function hapusTagihanMasaDepanPenghuni(
  $id_penghuni,
  $tanggal_keluar,
  $conn
) {
  $stmt = $conn->prepare("
    SELECT tp.id_tagihan, t.total_dibayar
    FROM tagihan_penghuni tp
    INNER JOIN tagihan t ON t.id_tagihan = tp.id_tagihan
    WHERE tp.id_penghuni = ?
      AND t.tanggal_mulai > ?
    FOR UPDATE
  ");
  $stmt->bind_param('is', $id_penghuni, $tanggal_keluar);
  $stmt->execute();
  $result = $stmt->get_result();
  $tagihanMasaDepan = [];

  while ($row = $result->fetch_assoc()) {
    $tagihanMasaDepan[] = $row;
  }

  $stmt->close();

  if (!$tagihanMasaDepan) {
    return;
  }

  $stmt = $conn->prepare("
    DELETE FROM tagihan_penghuni
    WHERE id_penghuni = ?
      AND id_tagihan = ?
  ");

  foreach ($tagihanMasaDepan as $tagihan) {
    $stmt->bind_param(
      'ii',
      $id_penghuni,
      $tagihan['id_tagihan']
    );

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal memperbarui relasi tagihan masa depan.');
    }
  }

  $stmt->close();

  foreach ($tagihanMasaDepan as $tagihan) {
    if ((float) $tagihan['total_dibayar'] > 0) {
      continue;
    }

    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM pembayaran
      WHERE id_tagihan = ?
    ");
    $stmt->bind_param('i', $tagihan['id_tagihan']);
    $stmt->execute();
    $hasPayments = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    if ($hasPayments > 0) {
      continue;
    }

    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total
      FROM tagihan_penghuni
      WHERE id_tagihan = ?
    ");
    $stmt->bind_param('i', $tagihan['id_tagihan']);
    $stmt->execute();
    $hasOtherResidents = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    if ($hasOtherResidents > 0) {
      continue;
    }

    $stmt = $conn->prepare('DELETE FROM tagihan WHERE id_tagihan = ?');
    $stmt->bind_param('i', $tagihan['id_tagihan']);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal menghapus tagihan masa depan.');
    }

    $stmt->close();
  }
}

function deletePenghuni($id_penghuni, $id_pemilik)
{
  $existing = findPenghuniByIdPemilik($id_penghuni, $id_pemilik);

  if (!$existing) {
    throw new Exception('Penghuni tidak ditemukan.');
  }

  if ($existing['status'] === 'aktif') {
    throw new Exception('Penghuni aktif tidak dapat dihapus. Catat sebagai keluar terlebih dahulu.');
  }

  $conn = db();
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM pembayaran
    WHERE id_penghuni = ?
  ");
  $stmt->bind_param('i', $id_penghuni);
  $stmt->execute();
  $hasPaymentHistory = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  if ($hasPaymentHistory > 0) {
    throw new Exception('Penghuni yang sudah memiliki histori pembayaran tidak dapat dihapus.');
  }

  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("
      SELECT tp.id_tagihan, t.total_dibayar
      FROM tagihan_penghuni tp
      INNER JOIN tagihan t ON t.id_tagihan = tp.id_tagihan
      WHERE tp.id_penghuni = ?
      FOR UPDATE
    ");
    $stmt->bind_param('i', $id_penghuni);
    $stmt->execute();
    $result = $stmt->get_result();
    $tagihanTerkait = [];

    while ($row = $result->fetch_assoc()) {
      $tagihanTerkait[] = $row;
    }

    $stmt->close();

    $stmt = $conn->prepare(
      'DELETE FROM tagihan_penghuni WHERE id_penghuni = ?'
    );
    $stmt->bind_param('i', $id_penghuni);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal melepas relasi penghuni dari tagihan.');
    }

    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM penghuni WHERE id_penghuni = ?');
    $stmt->bind_param('i', $id_penghuni);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Gagal menghapus data penghuni.');
    }

    $stmt->close();

    foreach ($tagihanTerkait as $tagihan) {
      if ((float) $tagihan['total_dibayar'] > 0) {
        continue;
      }

      $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM pembayaran
        WHERE id_tagihan = ?
      ");
      $stmt->bind_param('i', $tagihan['id_tagihan']);
      $stmt->execute();
      $hasPayments = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
      $stmt->close();

      if ($hasPayments > 0) {
        continue;
      }

      $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM tagihan_penghuni
        WHERE id_tagihan = ?
      ");
      $stmt->bind_param('i', $tagihan['id_tagihan']);
      $stmt->execute();
      $hasOtherResidents = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
      $stmt->close();

      if ($hasOtherResidents > 0) {
        continue;
      }

      $stmt = $conn->prepare('DELETE FROM tagihan WHERE id_tagihan = ?');
      $stmt->bind_param('i', $tagihan['id_tagihan']);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Gagal menghapus tagihan otomatis.');
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
