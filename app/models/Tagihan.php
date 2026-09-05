<?php

/* Fungsi tanggal, harga kamar, dan nomor tagihan dipakai bersama Phase Penghuni/Cron. */
require_once ROOT_PATH . '/app/models/Penghuni.php';

function getTagihanListByPemilik(
  $id_pemilik,
  $search = '',
  $status = '',
  $id_kos = '',
  $id_kamar = ''
) {
  $conn = db();

  $where = ['k.id_pemilik = ?'];
  $params = [$id_pemilik];
  $types = 'i';

  if ($search !== '') {
    $where[] = '(t.nomor_tagihan LIKE ? OR km.nomor_kamar LIKE ? OR k.nama_kos LIKE ?)';
    $keyword = '%' . $search . '%';
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'sss';
  }

  if ($status !== '') {
    $where[] = 't.status = ?';
    $params[] = $status;
    $types .= 's';
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

  $whereSql = 'WHERE ' . implode(' AND ', $where);

  $sql = "
    SELECT
      t.id_tagihan,
      t.id_kamar,
      t.nomor_tagihan,
      t.tanggal_terbit,
      t.tanggal_mulai,
      t.tanggal_selesai,
      t.tanggal_jatuh_tempo,
      t.jumlah_orang,
      t.harga_dasar,
      t.total_penyesuaian,
      t.total_tagihan,
      t.total_dibayar,
      GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan,
      t.status,
      km.nomor_kamar,
      tk.nama_tipe AS tipe_kamar,
      k.id_kos,
      k.nama_kos,
      (
        SELECT COUNT(*)
        FROM tagihan_penghuni tp
        INNER JOIN penghuni p ON p.id_penghuni = tp.id_penghuni
        WHERE tp.id_tagihan = t.id_tagihan
          AND p.status = 'aktif'
      ) AS penghuni_aktif
    FROM tagihan t
    INNER JOIN kamar km ON km.id_kamar = t.id_kamar
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    {$whereSql}
    ORDER BY
      t.tanggal_mulai DESC,
      t.tanggal_selesai DESC,
      t.id_tagihan DESC
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

function getTagihanListByUser($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT DISTINCT t.id_tagihan, t.id_kamar, t.nomor_tagihan, t.tanggal_terbit, t.tanggal_mulai, t.tanggal_selesai, t.tanggal_jatuh_tempo, t.jumlah_orang, t.total_tagihan, t.total_dibayar, GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan, t.status, km.nomor_kamar, k.id_kos, k.nama_kos, u.nama AS nama_pemilik, u.foto AS foto_pemilik FROM tagihan t INNER JOIN tagihan_penghuni tp ON tp.id_tagihan = t.id_tagihan INNER JOIN penghuni p ON p.id_penghuni = tp.id_penghuni AND p.id_user = ? INNER JOIN kamar km ON km.id_kamar = t.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users u ON u.id_user = k.id_pemilik ORDER BY t.tanggal_mulai DESC, t.tanggal_selesai DESC, t.id_tagihan DESC");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function findTagihanByIdUser($id_tagihan, $id_user)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT DISTINCT t.id_tagihan, t.id_kamar, t.nomor_tagihan, t.tanggal_terbit, t.tanggal_mulai, t.tanggal_selesai, t.tanggal_jatuh_tempo, t.jumlah_orang, t.harga_dasar, t.total_penyesuaian, t.total_tagihan, t.total_dibayar, GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan, t.status, km.nomor_kamar, k.id_kos, k.nama_kos, u.nama AS nama_pemilik, u.foto AS foto_pemilik FROM tagihan t INNER JOIN tagihan_penghuni tp ON tp.id_tagihan = t.id_tagihan INNER JOIN penghuni p ON p.id_penghuni = tp.id_penghuni AND p.id_user = ? INNER JOIN kamar km ON km.id_kamar = t.id_kamar INNER JOIN kos k ON k.id_kos = km.id_kos INNER JOIN users u ON u.id_user = k.id_pemilik WHERE t.id_tagihan = ? LIMIT 1");
  $stmt->bind_param('ii', $id_user, $id_tagihan);
  $stmt->execute();
  $tagihan = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$tagihan) {
    return null;
  }

  $stmt = $conn->prepare("SELECT pb.id_pembayaran, pb.id_tagihan, pb.id_penghuni, pb.nomor_pembayaran, pb.jumlah, pb.tanggal_bayar, pb.metode, pb.status, pb.catatan, p.nama AS nama_penghuni FROM pembayaran pb INNER JOIN penghuni p ON p.id_penghuni = pb.id_penghuni WHERE pb.id_tagihan = ? AND p.id_user = ? ORDER BY pb.tanggal_bayar DESC, pb.id_pembayaran DESC");
  $stmt->bind_param('ii', $id_tagihan, $id_user);
  $stmt->execute();
  $tagihan['pembayaran'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $tagihan;
}


function findTagihanByIdPemilik($id_tagihan, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      t.*,
      GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan,
      km.nomor_kamar,
      tk.nama_tipe AS tipe_kamar,
      k.id_kos,
      k.nama_kos,
      k.id_pemilik
    FROM tagihan t
    INNER JOIN kamar km ON km.id_kamar = t.id_kamar
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    WHERE t.id_tagihan = ?
      AND k.id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_tagihan, $id_pemilik);
  $stmt->execute();
  $tagihan = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$tagihan) {
    return null;
  }

  $tagihan['penyesuaian'] = getPenyesuaianByTagihan($id_tagihan);
  $tagihan['pembayaran'] = getPembayaranByTagihan($id_tagihan);
  $tagihan['penghuni'] = getPenghuniByTagihan($id_tagihan);

  return $tagihan;
}


function getPenyesuaianByTagihan($id_tagihan)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      pt.id_penyesuaian,
      pt.id_tagihan,
      pt.id_penghuni,
      pt.jenis,
      pt.jumlah,
      pt.tanggal_efektif,
      pt.alasan,
      pt.created_at,
      p.nama AS nama_penghuni
    FROM penyesuaian_tagihan pt
    LEFT JOIN penghuni p ON p.id_penghuni = pt.id_penghuni
    WHERE pt.id_tagihan = ?
    ORDER BY pt.tanggal_efektif ASC, pt.id_penyesuaian ASC
  ");

  $stmt->bind_param('i', $id_tagihan);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();
  return $data;
}


function getPembayaranByTagihan($id_tagihan)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      pb.id_pembayaran,
      pb.id_tagihan,
      pb.id_penghuni,
      pb.id_user,
      pb.nomor_pembayaran,
      pb.jumlah,
      pb.tanggal_bayar,
      pb.metode,
      pb.status,
      pb.catatan,
      pb.created_at,
      p.nama AS nama_penghuni,
      u.nama AS dicatat_oleh
    FROM pembayaran pb
    LEFT JOIN penghuni p ON p.id_penghuni = pb.id_penghuni
    LEFT JOIN users u ON u.id_user = pb.id_user
    WHERE pb.id_tagihan = ?
    ORDER BY pb.tanggal_bayar DESC, pb.id_pembayaran DESC
  ");

  $stmt->bind_param('i', $id_tagihan);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();
  return $data;
}


function getPenghuniByTagihan($id_tagihan)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      p.id_penghuni,
      p.nama,
      p.no_hp,
      p.tanggal_masuk,
      p.tanggal_keluar,
      p.status
    FROM tagihan t
    INNER JOIN tagihan_penghuni tp
      ON tp.id_tagihan = t.id_tagihan
    INNER JOIN penghuni p
      ON p.id_penghuni = tp.id_penghuni
    WHERE t.id_tagihan = ?
    ORDER BY p.status ASC, p.nama ASC
  ");

  $stmt->bind_param('i', $id_tagihan);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();
  return $data;
}


function tambahPenyesuaianTagihan($id_tagihan, $data, $id_pemilik)
{
  $conn = db();

  $tagihan = findTagihanByIdPemilik($id_tagihan, $id_pemilik);

  if (!$tagihan) {
    throw new Exception('Tagihan tidak ditemukan atau bukan milik Anda.', 404);
  }

  if ($tagihan['status'] === 'dibatalkan') {
    throw new Exception('Tagihan yang dibatalkan tidak dapat diberi penyesuaian.', 422);
  }

  if ($tagihan['status'] === 'lunas') {
    throw new Exception('Tagihan yang sudah lunas tidak dapat diberi penyesuaian.', 422);
  }

  $jenis = trim($data['jenis'] ?? '');
  $jumlah = (float) ($data['jumlah'] ?? 0);
  $tanggalEfektif = trim($data['tanggal_efektif'] ?? date('Y-m-d'));
  $alasan = trim($data['alasan'] ?? '');

  if (!in_array($jenis, ['tambah', 'kurang'], true)) {
    throw new Exception('Jenis penyesuaian tidak valid.', 422);
  }

  if ($jumlah <= 0) {
    throw new Exception('Jumlah penyesuaian harus lebih besar dari 0.', 422);
  }

  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalEfektif)) {
    throw new Exception('Tanggal efektif tidak valid.', 422);
  }

  if ($tanggalEfektif < $tagihan['tanggal_mulai'] || $tanggalEfektif > $tagihan['tanggal_selesai']) {
    throw new Exception('Tanggal efektif harus berada di dalam periode tagihan.', 422);
  }

  if ($alasan === '') {
    throw new Exception('Alasan penyesuaian wajib diisi.', 422);
  }

  $nilaiPenyesuaian = $jenis === 'tambah' ? $jumlah : -$jumlah;
  $totalPenyesuaianBaru = (float) $tagihan['total_penyesuaian'] + $nilaiPenyesuaian;
  $totalTagihanBaru = (float) $tagihan['harga_dasar'] + $totalPenyesuaianBaru;
  $totalDibayar = (float) $tagihan['total_dibayar'];

  if ($totalTagihanBaru < 0) {
    throw new Exception('Total tagihan tidak boleh kurang dari Rp0.', 422);
  }

  if ($totalTagihanBaru < $totalDibayar) {
    throw new Exception('Penyesuaian tidak boleh membuat total tagihan lebih kecil dari jumlah yang sudah dibayar.', 422);
  }

  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("
      INSERT INTO penyesuaian_tagihan (
        id_tagihan,
        id_penghuni,
        jenis,
        jumlah,
        tanggal_efektif,
        alasan
      ) VALUES (?, NULL, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
      'isdss',
      $id_tagihan,
      $jenis,
      $jumlah,
      $tanggalEfektif,
      $alasan
    );

    if (!$stmt->execute()) {
      throw new Exception('Gagal menyimpan penyesuaian.', 500);
    }

    $stmt->close();

    $stmt = $conn->prepare("
      UPDATE tagihan
      SET
        total_penyesuaian = ?,
        total_tagihan = ?
      WHERE id_tagihan = ?
    ");

    $stmt->bind_param(
      'ddi',
      $totalPenyesuaianBaru,
      $totalTagihanBaru,
      $id_tagihan
    );

    if (!$stmt->execute()) {
      throw new Exception('Gagal memperbarui total tagihan.', 500);
    }

    $stmt->close();

    /*
     * Penyesuaian dapat mengubah total tagihan, sehingga status
     * harus dihitung ulang agar tidak terjadi kondisi:
     * total_tagihan berubah tetapi status tetap "lunas".
     */
    $statusBaru = 'belum_lunas';

    if ($totalDibayar >= $totalTagihanBaru) {
      $statusBaru = 'lunas';
    } elseif ($totalDibayar > 0) {
      $statusBaru = 'sebagian';
    }

    $stmt = $conn->prepare("
      UPDATE tagihan
      SET status = ?
      WHERE id_tagihan = ?
    ");

    $stmt->bind_param(
      'si',
      $statusBaru,
      $id_tagihan
    );

    if (!$stmt->execute()) {
      throw new Exception('Gagal memperbarui status tagihan.', 500);
    }

    $stmt->close();
    $conn->commit();

    return findTagihanByIdPemilik($id_tagihan, $id_pemilik);
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}


function catatPembayaranTagihan($id_tagihan, $data, $id_pemilik)
{
  $conn = db();

  $tagihan = findTagihanByIdPemilik($id_tagihan, $id_pemilik);

  if (!$tagihan) {
    throw new Exception('Tagihan tidak ditemukan atau bukan milik Anda.', 404);
  }

  if ($tagihan['status'] === 'dibatalkan') {
    throw new Exception('Tagihan yang dibatalkan tidak dapat menerima pembayaran.', 422);
  }

  $jumlah = (float) ($data['jumlah'] ?? 0);
  $idPenghuni = !empty($data['id_penghuni']) ? (int) $data['id_penghuni'] : null;
  $metode = trim($data['metode'] ?? '');
  $tanggalBayar = trim($data['tanggal_bayar'] ?? date('Y-m-d H:i:s'));
  $catatan = trim($data['catatan'] ?? '');

  if ($idPenghuni === null) {
    throw new Exception('Penghuni wajib dipilih agar pembayaran tercatat sebagai histori.', 422);
  }

  if ($jumlah <= 0) {
    throw new Exception('Jumlah pembayaran harus lebih besar dari 0.', 422);
  }

  $sisa = max((float) $tagihan['total_tagihan'] - (float) $tagihan['total_dibayar'], 0);

  if ($sisa <= 0) {
    throw new Exception('Tagihan sudah lunas.', 422);
  }

  if ($jumlah > $sisa) {
    throw new Exception('Jumlah pembayaran melebihi sisa tagihan.', 422);
  }

  if (!in_array($metode, ['tunai', 'transfer', 'qris', 'lainnya'], true)) {
    throw new Exception('Metode pembayaran tidak valid.', 422);
  }

  $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $tanggalBayar);
  if (!$timestamp || $timestamp->format('Y-m-d H:i:s') !== $tanggalBayar) {
    throw new Exception('Tanggal pembayaran tidak valid.', 422);
  }

  if ($idPenghuni !== null) {
    $stmt = $conn->prepare("
      SELECT p.id_penghuni
      FROM penghuni p
      INNER JOIN tagihan_penghuni tp
        ON tp.id_penghuni = p.id_penghuni
      INNER JOIN kamar km ON km.id_kamar = p.id_kamar
      INNER JOIN kos k ON k.id_kos = km.id_kos
      WHERE p.id_penghuni = ?
        AND tp.id_tagihan = ?
        AND k.id_pemilik = ?
      LIMIT 1
    ");

    $stmt->bind_param('iii', $idPenghuni, $id_tagihan, $id_pemilik);
    $stmt->execute();
    $validPenghuni = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$validPenghuni) {
      throw new Exception('Penghuni tidak valid untuk tagihan ini.', 422);
    }
  }

  $userId = (int) ($_SESSION['user']['id_user'] ?? 0);
  $totalDibayarBaru = (float) $tagihan['total_dibayar'] + $jumlah;
  $statusBaru = $totalDibayarBaru >= (float) $tagihan['total_tagihan']
    ? 'lunas'
    : 'sebagian';

  $nomorPembayaran = 'BYR-' . date('YmdHis') . '-' . random_int(100, 999);

  $conn->begin_transaction();

  try {
    $stmt = $conn->prepare("SELECT * FROM tagihan WHERE id_tagihan=? LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_tagihan);
    $stmt->execute();
    $tagihanTerkunci = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$tagihanTerkunci) throw new Exception('Tagihan tidak ditemukan.', 404);
    if ($tagihanTerkunci['status'] === 'dibatalkan') throw new Exception('Tagihan yang dibatalkan tidak dapat menerima pembayaran.', 422);
    $sisa = max((float)$tagihanTerkunci['total_tagihan'] - (float)$tagihanTerkunci['total_dibayar'], 0);
    if ($sisa <= 0) throw new Exception('Tagihan sudah lunas.', 422);
    if ($jumlah > $sisa) throw new Exception('Jumlah pembayaran melebihi sisa tagihan.', 422);
    $tagihan = $tagihanTerkunci;
    $totalDibayarBaru = (float)$tagihan['total_dibayar'] + $jumlah;
    $statusBaru = $totalDibayarBaru >= (float)$tagihan['total_tagihan'] ? 'lunas' : 'sebagian';

    $stmt = $conn->prepare("
      INSERT INTO pembayaran (
        id_tagihan,
        id_penghuni,
        id_user,
        nomor_pembayaran,
        jumlah,
        tanggal_bayar,
        metode,
        status,
        catatan
      ) VALUES (?, ?, ?, ?, ?, ?, ?, 'berhasil', ?)
    ");

    $stmt->bind_param(
      'iiisdsss',
      $id_tagihan,
      $idPenghuni,
      $userId,
      $nomorPembayaran,
      $jumlah,
      $tanggalBayar,
      $metode,
      $catatan
    );

    if (!$stmt->execute()) {
      throw new Exception('Gagal menyimpan pembayaran.', 500);
    }

    $idPembayaran = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("
      UPDATE tagihan
      SET
        total_dibayar = ?,
        status = ?
      WHERE id_tagihan = ?
    ");

    $stmt->bind_param(
      'dsi',
      $totalDibayarBaru,
      $statusBaru,
      $id_tagihan
    );

    if (!$stmt->execute()) {
      throw new Exception('Gagal memperbarui status tagihan.', 500);
    }

    $stmt->close();

    /*
     * Jika pembayaran membuat tagihan menjadi LUNAS, langsung siapkan
     * tagihan untuk periode berikutnya. Ini sengaja dilakukan di luar Cron:
     * Cron tetap menjadi fallback apabila tagihan belum lunas sampai periode
     * berakhir.
     */
    $tagihanBerikutnya = null;

    if ($statusBaru === 'lunas') {
      $tagihanBerikutnya = buatTagihanBerikutnyaSetelahLunas(
        $tagihan,
        $tanggalBayar,
        $conn
      );
    }

    $conn->commit();

    return [
      'id_pembayaran' => $idPembayaran,
      'nomor_pembayaran' => $nomorPembayaran,
      'tagihan' => findTagihanByIdPemilik($id_tagihan, $id_pemilik),
      'tagihan_berikutnya' => $tagihanBerikutnya
    ];
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

/*
|--------------------------------------------------------------------------
| BUAT TAGIHAN BERIKUTNYA SETELAH LUNAS
|--------------------------------------------------------------------------
| Dipanggil setelah pembayaran membuat tagihan menjadi lunas.
| Tidak mengubah Cron; fungsi ini hanya menyiapkan periode berikutnya
| lebih awal agar pembayaran periode berikutnya dapat langsung dicatat.
|--------------------------------------------------------------------------
*/
function buatTagihanBerikutnyaSetelahLunas($tagihan, $tanggalTerbit, $conn)
{
  $idKamar = (int) $tagihan['id_kamar'];

  $jumlahOrang = getPenghuniAktifCountUntukCron($idKamar, $conn);

  /* Kamar sudah kosong: tidak boleh membuat tagihan baru. */
  if ($jumlahOrang <= 0) {
    return null;
  }

  /*
   * Periode berikutnya dimulai tepat pada tanggal jatuh tempo tagihan
   * yang baru saja dilunasi. getRentDates() menjaga aturan tanggal
   * 28/29/30/31 tetap konsisten.
   */
  $dates = getRentDates($tagihan['tanggal_jatuh_tempo']);

  /* Jika Cron sudah lebih dulu membuatnya, jangan duplikasi. */
  $existing = tagihanPeriodeSudahAdaUntukCron(
    $idKamar,
    $dates['mulai'],
    $dates['selesai'],
    $conn
  );

  if ($existing) {
    return findTagihanByIdInternal((int) $existing['id_tagihan'], $conn);
  }

  $harga = getHargaKamarUntukJumlah(
    $idKamar,
    $jumlahOrang,
    $conn
  );

  $idTagihan = createTagihanCron(
    $idKamar,
    $jumlahOrang,
    substr($tanggalTerbit, 0, 10),
    $dates['mulai'],
    $dates['selesai'],
    $dates['jatuh_tempo'],
    $harga,
    $conn
  );

  return findTagihanByIdInternal($idTagihan, $conn);
}

function findTagihanByIdInternal($idTagihan, $conn)
{
  $stmt = $conn->prepare("
    SELECT
      t.id_tagihan,
      t.id_kamar,
      t.nomor_tagihan,
      t.tanggal_terbit,
      t.tanggal_mulai,
      t.tanggal_selesai,
      t.tanggal_jatuh_tempo,
      t.jumlah_orang,
      t.harga_dasar,
      t.total_penyesuaian,
      t.total_tagihan,
      t.total_dibayar,
      GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan,
      t.status
    FROM tagihan t
    WHERE t.id_tagihan = ?
    LIMIT 1
  ");

  $stmt->bind_param('i', $idTagihan);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row ?: null;
}

/*
|--------------------------------------------------------------------------
| CRON JOB - GENERATE TAGIHAN BERIKUTNYA
|--------------------------------------------------------------------------
| Membuat tagihan periode berikutnya untuk kamar yang:
| - memiliki penghuni aktif;
| - tagihan terakhirnya sudah melewati tanggal selesai;
| - belum memiliki tagihan untuk periode berikutnya.
|
| Tagihan lama tidak diubah dan tidak memengaruhi total tagihan baru.
| Unique (id_kamar, tanggal_mulai, tanggal_selesai) di database
| menjadi lapisan terakhir pencegah duplikasi.
|--------------------------------------------------------------------------
*/

function getKamarUntukCronTagihan($conn)
{
  $stmt = $conn->prepare("
    SELECT
      km.id_kamar,
      tk.kapasitas
    FROM kamar km
    INNER JOIN tipe_kamar tk ON tk.id_tipe_kamar = km.id_tipe_kamar
    WHERE km.status <> 'nonaktif'
    ORDER BY km.id_kamar ASC
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

function getPenghuniAktifCountUntukCron($id_kamar, $conn)
{
  $stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM penghuni
    WHERE id_kamar = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $total = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  return $total;
}

function getTagihanTerakhirUntukCron($id_kamar, $conn)
{
  $stmt = $conn->prepare("
    SELECT *
    FROM tagihan
    WHERE id_kamar = ?
    ORDER BY tanggal_mulai DESC, id_tagihan DESC
    LIMIT 1
  ");

  $stmt->bind_param('i', $id_kamar);
  $stmt->execute();

  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row ?: null;
}

function tagihanPeriodeSudahAdaUntukCron(
  $id_kamar,
  $tanggalMulai,
  $tanggalSelesai,
  $conn
) {
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
    $tanggalMulai,
    $tanggalSelesai
  );

  $stmt->execute();
  $exists = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $exists ?: null;
}

function createTagihanCron(
  $id_kamar,
  $jumlahOrang,
  $tanggalTerbit,
  $tanggalMulai,
  $tanggalSelesai,
  $tanggalJatuhTempo,
  $harga,
  $conn
) {
  $stmt = $conn->prepare("SELECT id_kamar FROM kamar WHERE id_kamar = ? FOR UPDATE");
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
    $tanggalMulai,
    $tanggalSelesai
  );
  $stmt->execute();
  $existing = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($existing) {
    linkPenghuniTagihanUntukPeriode(
      $existing['id_tagihan'],
      $id_kamar,
      $tanggalMulai,
      $tanggalSelesai,
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
      ?, ?, ?, ?, ?, ?,
      ?, ?, 0, ?, 0, 'belum_lunas'
    )
  ");

  $stmt->bind_param(
    'isssssidd',
    $id_kamar,
    $nomor,
    $tanggalTerbit,
    $tanggalMulai,
    $tanggalSelesai,
    $tanggalJatuhTempo,
    $jumlahOrang,
    $harga,
    $harga
  );

  try {
    if (!$stmt->execute()) {
      error_log('Cron create tagihan DB error: ' . $stmt->error);
      throw new Exception('Gagal membuat tagihan Cron.');
    }

    $idTagihan = $stmt->insert_id;

    linkPenghuniTagihanUntukPeriode(
      $idTagihan,
      $id_kamar,
      $tanggalMulai,
      $tanggalSelesai,
      $conn
    );

    return $idTagihan;
  } finally {
    $stmt->close();
  }
}

/**
 * Generate maksimal satu tagihan berikutnya per kamar dalam satu eksekusi.
 *
 * Jika server sempat mati beberapa hari, eksekusi berikutnya akan
 * melanjutkan dari tagihan terakhir. Ini menghindari pembuatan banyak
 * periode sekaligus dan tetap menjaga histori periode secara berurutan.
 */
function generateTagihanBerikutnyaCron($tanggalHariIni = null)
{
  $conn = db();

  $tanggalHariIni = $tanggalHariIni ?: date('Y-m-d');

  $dateCheck = DateTime::createFromFormat(
    'Y-m-d',
    $tanggalHariIni
  );

  if (
    !$dateCheck ||
    $dateCheck->format('Y-m-d') !== $tanggalHariIni
  ) {
    throw new Exception('Tanggal Cron tidak valid.');
  }

  /*
   * Mengambil kamar satu per satu. Transaksi dibuat per kamar
   * agar satu kegagalan tidak membatalkan seluruh proses.
   */
  $kamarList = getKamarUntukCronTagihan($conn);

  $hasil = [
    'tanggal' => $tanggalHariIni,
    'diperiksa' => 0,
    'dibuat' => 0,
    'dilewati' => 0,
    'error' => 0,
    'detail' => []
  ];

  foreach ($kamarList as $kamar) {
    $idKamar = (int) $kamar['id_kamar'];
    $hasil['diperiksa']++;

    try {
      $jumlahOrang = getPenghuniAktifCountUntukCron(
        $idKamar,
        $conn
      );

      /*
       * Kamar kosong tidak mempunyai kewajiban tagihan baru.
       */
      if ($jumlahOrang <= 0) {
        $hasil['dilewati']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'skip',
          'alasan' => 'Tidak ada penghuni aktif.'
        ];
        continue;
      }

      if ($jumlahOrang > (int) $kamar['kapasitas']) {
        throw new Exception(
          "Jumlah penghuni aktif ({$jumlahOrang}) " .
            "melebihi kapasitas kamar ({$kamar['kapasitas']})."
        );
      }

      $tagihanTerakhir = getTagihanTerakhirUntukCron(
        $idKamar,
        $conn
      );

      /*
       * Secara normal kamar berpenghuni selalu sudah memiliki
       * tagihan dari proses tambah penghuni. Jika belum ada,
       * jangan menebak tanggal/tagihan dari Cron.
       */
      if (!$tagihanTerakhir) {
        $hasil['dilewati']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'skip',
          'alasan' => 'Belum ada tagihan sebelumnya.'
        ];
        continue;
      }

      /*
       * Tagihan terakhir masih berjalan.
       */
      if ($tagihanTerakhir['tanggal_selesai'] >= $tanggalHariIni) {
        $hasil['dilewati']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'skip',
          'alasan' => 'Tagihan terakhir masih berjalan.',
          'id_tagihan' => (int) $tagihanTerakhir['id_tagihan']
        ];
        continue;
      }

      if ($tagihanTerakhir['status'] !== 'lunas') {
        $hasil['dilewati']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'skip',
          'alasan' => 'Tagihan terakhir belum lunas.',
          'id_tagihan' => (int) $tagihanTerakhir['id_tagihan']
        ];
        continue;
      }

      /*
       * Periode berikutnya dimulai tepat pada tanggal jatuh tempo
       * tagihan sebelumnya.
       *
       * getRentDates() menangani tanggal 28/29/30/31 dengan aman.
       */
      $dates = getRentDates(
        $tagihanTerakhir['tanggal_jatuh_tempo']
      );

      /*
       * Jika periode yang akan dibuat sudah ada, jangan INSERT.
       * UNIQUE constraint database juga menjaga dari duplikasi
       * akibat eksekusi Cron bersamaan.
       */
      if (
        tagihanPeriodeSudahAdaUntukCron(
          $idKamar,
          $dates['mulai'],
          $dates['selesai'],
          $conn
        )
      ) {
        $hasil['dilewati']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'skip',
          'alasan' => 'Tagihan periode berikutnya sudah ada.'
        ];
        continue;
      }

      $harga = getHargaKamarUntukJumlah(
        $idKamar,
        $jumlahOrang,
        $conn
      );

      $conn->begin_transaction();

      try {
        $idTagihan = createTagihanCron(
          $idKamar,
          $jumlahOrang,
          $tanggalHariIni,
          $dates['mulai'],
          $dates['selesai'],
          $dates['jatuh_tempo'],
          $harga,
          $conn
        );

        $conn->commit();

        $hasil['dibuat']++;
        $hasil['detail'][] = [
          'id_kamar' => $idKamar,
          'aksi' => 'buat',
          'id_tagihan' => (int) $idTagihan,
          'tanggal_mulai' => $dates['mulai'],
          'tanggal_selesai' => $dates['selesai'],
          'tanggal_jatuh_tempo' => $dates['jatuh_tempo'],
          'jumlah_orang' => $jumlahOrang,
          'harga_dasar' => $harga
        ];
      } catch (Throwable $e) {
        $conn->rollback();

        /*
         * Jika dua proses Cron berjalan bersamaan, UNIQUE constraint
         * dapat menolak salah satunya. Dalam kasus tersebut tidak perlu
         * menganggap database rusak; catat sebagai dilewati.
         */
        if (
          strpos(
            strtolower($e->getMessage()),
            'duplicate'
          ) !== false ||
          strpos(
            strtolower($e->getMessage()),
            'unique'
          ) !== false
        ) {
          $hasil['dilewati']++;
          $hasil['detail'][] = [
            'id_kamar' => $idKamar,
            'aksi' => 'skip',
            'alasan' => 'Periode sudah dibuat oleh proses lain.'
          ];
          continue;
        }

        throw $e;
      }
    } catch (Throwable $e) {
      $hasil['error']++;
      $hasil['detail'][] = [
        'id_kamar' => $idKamar,
        'aksi' => 'error',
        'alasan' => $e->getMessage()
      ];
    }
  }

  return $hasil;
}
