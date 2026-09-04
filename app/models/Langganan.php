<?php


function addMonthsSafe(DateTimeImmutable $date, $months)
{
  $months = (int)$months;
  if ($months <= 0) return $date;

  $day = (int)$date->format('d');
  $target = $date->modify('first day of +' . $months . ' month');
  $lastDay = (int)$target->format('t');
  return $target->setDate(
    (int)$target->format('Y'),
    (int)$target->format('m'),
    min($day, $lastDay)
  );
}


function getPaketLanggananAktif()
{
  $conn = db();

  $result = $conn->query("\n    SELECT id_paket_langganan, kode, nama, harga_bulanan, harga_perpanjangan, durasi_bulan, deskripsi, fitur_json\n    FROM paket_langganan\n    WHERE status = 'aktif'\n    ORDER BY harga_bulanan ASC, id_paket_langganan ASC\n  ");

  if (!$result) {
    throw new RuntimeException('Gagal mengambil paket langganan.');
  }

  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as &$row) {
    $row['harga_bulanan'] = (float) $row['harga_bulanan'];
    $row['harga_perpanjangan'] = (float) $row['harga_perpanjangan'];
    $row['durasi_bulan'] = (int) $row['durasi_bulan'];
    $decoded = json_decode((string) ($row['fitur_json'] ?? ''), true);
    $row['fitur'] = is_array($decoded) ? $decoded : [];
    unset($row['fitur_json']);
  }
  unset($row);

  return $rows;
}

function getPaketLanggananByKode($kode)
{
  $conn = db();
  $kode = trim((string) $kode);

  $stmt = $conn->prepare("\n    SELECT id_paket_langganan, kode, nama, harga_bulanan, harga_perpanjangan, durasi_bulan, deskripsi, fitur_json\n    FROM paket_langganan\n    WHERE kode = ? AND status = 'aktif'\n    LIMIT 1\n  ");
  $stmt->bind_param('s', $kode);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    return null;
  }

  $row['harga_bulanan'] = (float) $row['harga_bulanan'];
  $row['harga_perpanjangan'] = (float) $row['harga_perpanjangan'];
  $row['durasi_bulan'] = (int) $row['durasi_bulan'];
  $decoded = json_decode((string) ($row['fitur_json'] ?? ''), true);
  $row['fitur'] = is_array($decoded) ? $decoded : [];
  unset($row['fitur_json']);

  return $row;
}

function syncExpiredLangganan($id_pemilik = null)
{
  $conn = db();

  if ($id_pemilik !== null) {
    $id_pemilik = (int)$id_pemilik;
    if ($id_pemilik <= 0) return 0;

    $stmt = $conn->prepare("\n      UPDATE langganan\n      SET status = 'berakhir', updated_at = CURRENT_TIMESTAMP\n      WHERE id_pemilik = ?\n        AND status = 'aktif'\n        AND tanggal_berakhir < CURDATE()\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return max(0, (int)$affected);
  }

  $result = $conn->query("\n    UPDATE langganan\n    SET status = 'berakhir', updated_at = CURRENT_TIMESTAMP\n    WHERE status = 'aktif'\n      AND tanggal_berakhir < CURDATE()\n  ");

  if ($result === false) {
    throw new RuntimeException('Gagal memperbarui status subscription yang berakhir.');
  }

  return max(0, (int)$conn->affected_rows);
}

function getLatestLanggananPemilik($id_pemilik)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("\n    SELECT\n      l.id_langganan,\n      l.id_pemilik,\n      l.id_paket_langganan,\n      l.tanggal_mulai,\n      l.tanggal_berakhir,\n      l.status,\n      p.kode AS kode_paket,\n      p.nama AS nama_paket,\n      p.harga_bulanan,\n      p.durasi_bulan,\n      p.deskripsi,\n      p.fitur_json\n    FROM langganan l\n    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan\n    WHERE l.id_pemilik = ?\n      AND l.status IN ('aktif', 'berakhir')\n    ORDER BY l.id_langganan DESC\n    LIMIT 1\n  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) return null;

  $row['harga_bulanan'] = (float)$row['harga_bulanan'];
  $row['durasi_bulan'] = (int)$row['durasi_bulan'];
  $decoded = json_decode((string)($row['fitur_json'] ?? ''), true);
  $row['fitur'] = is_array($decoded) ? $decoded : [];
  unset($row['fitur_json']);

  return $row;
}

function getLanggananAktifPemilik($id_pemilik)
{
  syncExpiredLangganan($id_pemilik);

  $conn = db();
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("\n    SELECT\n      l.id_langganan,\n      l.id_pemilik,\n      l.tanggal_mulai,\n      l.tanggal_berakhir,\n      l.status,\n      p.id_paket_langganan,\n      p.kode AS kode_paket,\n      p.nama AS nama_paket,\n      p.harga_bulanan,\n      p.durasi_bulan,\n      p.deskripsi,\n      p.fitur_json\n    FROM langganan l\n    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan\n    WHERE l.id_pemilik = ?\n      AND l.status = 'aktif'\n      AND l.tanggal_mulai <= CURDATE()\n      AND l.tanggal_berakhir >= CURDATE()\n    ORDER BY l.tanggal_berakhir DESC, l.id_langganan DESC\n    LIMIT 1\n  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) return null;

  $row['harga_bulanan'] = (float)$row['harga_bulanan'];
  $row['durasi_bulan'] = (int)$row['durasi_bulan'];
  $decoded = json_decode((string)($row['fitur_json'] ?? ''), true);
  $row['fitur'] = is_array($decoded) ? $decoded : [];
  unset($row['fitur_json']);

  return $row;
}

function getStatusLanggananPemilik($id_pemilik)
{
  $id_pemilik = (int)$id_pemilik;

  $active = getLanggananAktifPemilik($id_pemilik);

  if ($active) {
    $today = new DateTimeImmutable('today');
    $end = new DateTimeImmutable($active['tanggal_berakhir']);
    $daysRemaining = max(0, (int)$today->diff($end)->format('%r%a'));

    $reminder = null;
    if ($daysRemaining === 7) {
      $reminder = 'BetaKos Pro Anda akan berakhir dalam 7 hari.';
    } elseif ($daysRemaining === 3) {
      $reminder = 'BetaKos Pro Anda akan berakhir dalam 3 hari.';
    } elseif ($daysRemaining === 1) {
      $reminder = 'BetaKos Pro Anda akan berakhir besok.';
    }

    return [
      'is_pro' => true,
      'status' => 'aktif',
      'package' => [
        'kode' => $active['kode_paket'],
        'nama' => $active['nama_paket'],
        'harga_bulanan' => $active['harga_bulanan'],
      ],
      'subscription' => $active,
      'days_remaining' => $daysRemaining,
      'reminder' => $reminder,
    ];
  }

  $latest = getLatestLanggananPemilik($id_pemilik);
  $expired = $latest && $latest['status'] === 'berakhir';

  return [
    'is_pro' => false,
    'status' => $expired ? 'berakhir' : 'gratis',
    'package' => $expired ? [
      'kode' => $latest['kode_paket'],
      'nama' => $latest['nama_paket'],
      'harga_bulanan' => $latest['harga_bulanan'],
    ] : null,
    'subscription' => $expired ? $latest : null,
    'days_remaining' => 0,
    'reminder' => $expired ? 'BetaKos Pro Anda telah berakhir. Perpanjang untuk kembali menggunakan fitur Pro.' : null,
  ];
}


function getRiwayatLanggananPemilik($id_pemilik)
{
  syncExpiredLangganan($id_pemilik);
  $conn = db();
  $id_pemilik = (int) $id_pemilik;

  // Riwayat pembayaran menyimpan paket yang benar-benar dibeli pada saat transaksi.
  // Ini penting karena renewal dapat memilih durasi berbeda dari paket sebelumnya.
  $stmt = $conn->prepare("
    SELECT
      CONCAT('payment-', pl.id_pembayaran_langganan) AS riwayat_id,
      pl.id_pembayaran_langganan,
      pl.id_langganan,
      pl.id_paket_langganan,
      pl.nomor_order,
      pl.jenis_pembayaran,
      pl.nominal,
      pl.tanggal_pembayaran AS tanggal_transaksi,
      pl.status AS status_pembayaran,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.durasi_bulan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      0 AS is_gratis
    FROM pembayaran_langganan pl
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    INNER JOIN langganan l ON l.id_langganan = pl.id_langganan
    WHERE pl.id_pemilik = ?
    ORDER BY pl.tanggal_pembayaran DESC, pl.id_pembayaran_langganan DESC
  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  // Aktivasi Pro gratis tidak membuat record pembayaran. Tetap tampilkan di histori
  // agar pelanggan baru melihat transaksi/aktivasi pertamanya secara utuh.
  $stmt = $conn->prepare("
    SELECT
      CONCAT('free-', l.id_langganan) AS riwayat_id,
      NULL AS id_pembayaran_langganan,
      l.id_langganan,
      l.id_paket_langganan,
      NULL AS nomor_order,
      'baru' AS jenis_pembayaran,
      0 AS nominal,
      l.tanggal_mulai AS tanggal_transaksi,
      CASE
        WHEN l.status = 'aktif' AND l.tanggal_berakhir < CURDATE() THEN 'berakhir'
        ELSE l.status
      END AS status_pembayaran,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.durasi_bulan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      1 AS is_gratis
    FROM langganan l
    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan
    WHERE l.id_pemilik = ?
      AND NOT EXISTS (
        SELECT 1 FROM pembayaran_langganan pl
        WHERE pl.id_langganan = l.id_langganan
      )
    ORDER BY l.tanggal_mulai DESC, l.id_langganan DESC
  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $freeRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $rows = array_merge($rows, $freeRows);
  usort($rows, static function ($a, $b) {
    $dateCompare = strcmp((string)($b['tanggal_transaksi'] ?? ''), (string)($a['tanggal_transaksi'] ?? ''));
    if ($dateCompare !== 0) return $dateCompare;
    return strcmp((string)($b['riwayat_id'] ?? ''), (string)($a['riwayat_id'] ?? ''));
  });

  foreach ($rows as &$row) {
    $row['nominal'] = (float)($row['nominal'] ?? 0);
    $row['durasi_bulan'] = (int)$row['durasi_bulan'];
    $row['is_gratis'] = (int)$row['is_gratis'] === 1;
  }
  unset($row);

  return $rows;
}

function getPendingPembayaranLanggananPemilik($id_pemilik)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("
    SELECT
      pl.id_pembayaran_langganan,
      pl.nomor_order,
      pl.id_langganan,
      pl.id_paket_langganan,
      pl.jenis_pembayaran,
      pl.nominal,
      pl.metode_pembayaran,
      pl.tanggal_pembayaran,
      pl.bukti_pembayaran,
      pl.status,
      pl.provider_pembayaran,
      pl.provider_order_id,
      pl.provider_transaction_id,
      pl.provider_status,
      pl.qr_string,
      pl.qr_code_url,
      pl.paid_at,
      pl.catatan_admin,
      pl.created_at,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.durasi_bulan
    FROM pembayaran_langganan pl
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    WHERE pl.id_pemilik = ? AND pl.status = 'menunggu'
    ORDER BY pl.id_pembayaran_langganan DESC
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) return null;

  $row['nominal'] = (float)$row['nominal'];
  $row['durasi_bulan'] = (int)$row['durasi_bulan'];
  return $row;
}

function getPembayaranLanggananPemilik($id_pemilik, $limit = 50)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;
  $limit = max(1, min(100, (int)$limit));

  $stmt = $conn->prepare("
    SELECT
      pl.id_pembayaran_langganan,
      pl.nomor_order,
      pl.id_langganan,
      pl.id_paket_langganan,
      pl.jenis_pembayaran,
      pl.nominal,
      pl.metode_pembayaran,
      pl.tanggal_pembayaran,
      pl.bukti_pembayaran,
      pl.status,
      pl.catatan_admin,
      pl.created_at,
      pl.updated_at,
      p.kode AS kode_paket,
      p.nama AS nama_paket
    FROM pembayaran_langganan pl
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    WHERE pl.id_pemilik = ?
    ORDER BY pl.id_pembayaran_langganan DESC
    LIMIT ?
  ");
  $stmt->bind_param('ii', $id_pemilik, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  foreach ($rows as &$row) {
    $row['nominal'] = (float)$row['nominal'];
  }
  unset($row);

  return $rows;
}

function getPembayaranLanggananByIdPemilik($id_pembayaran, $id_pemilik)
{
  $conn = db();
  $id_pembayaran = (int)$id_pembayaran;
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("
    SELECT
      pl.*,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.durasi_bulan,
      p.harga_bulanan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      l.status AS status_langganan
    FROM pembayaran_langganan pl
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    INNER JOIN langganan l ON l.id_langganan = pl.id_langganan
    WHERE pl.id_pembayaran_langganan = ? AND pl.id_pemilik = ?
    LIMIT 1
  ");
  $stmt->bind_param('ii', $id_pembayaran, $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) return null;
  $row['nominal'] = (float)$row['nominal'];
  $row['harga_bulanan'] = (float)$row['harga_bulanan'];
  return $row;
}

function aktifkanLanggananGratisPertama($id_pemilik, $kode_paket)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;
  $kode_paket = trim((string)$kode_paket);

  if ($id_pemilik <= 0) throw new Exception('Pemilik tidak valid.', 422);

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$owner) throw new Exception('Pemilik tidak ditemukan.', 404);

    $stmt = $conn->prepare("SELECT id_pembayaran_langganan FROM pembayaran_langganan WHERE id_pemilik = ? AND status = 'menunggu' LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($pending) throw new Exception('Masih ada pembayaran langganan yang menunggu verifikasi.', 409);

    $stmt = $conn->prepare("
      UPDATE langganan
      SET status = 'berakhir', updated_at = CURRENT_TIMESTAMP
      WHERE id_pemilik = ? AND status = 'aktif' AND tanggal_berakhir < CURDATE()
    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $stmt->close();

    $paket = getPaketLanggananByKode($kode_paket);
    if (!$paket) throw new Exception('Paket langganan tidak tersedia.', 404);
    if ((int)$paket['durasi_bulan'] !== 1 || (float)$paket['harga_bulanan'] > 0) {
      throw new Exception('Paket ini bukan paket Pro gratis pertama.', 422);
    }

    $stmt = $conn->prepare("SELECT id_langganan FROM langganan WHERE id_pemilik = ? AND status IN ('aktif', 'berakhir') ORDER BY id_langganan DESC LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $latest = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($latest) throw new Exception('Promo Pro gratis hanya berlaku untuk pemilik yang belum pernah memiliki Pro.', 409);

    $start = new DateTimeImmutable('today');
    $end = addMonthsSafe($start, 1);
    $startDate = $start->format('Y-m-d');
    $endDate = $end->format('Y-m-d');

    $stmt = $conn->prepare("
      INSERT INTO langganan
        (id_pemilik, id_paket_langganan, tanggal_mulai, tanggal_berakhir, status, catatan)
      VALUES (?, ?, ?, ?, 'aktif', NULL)
    ");
    $stmt->bind_param('iiss', $id_pemilik, $paket['id_paket_langganan'], $startDate, $endDate);
    if (!$stmt->execute()) throw new Exception('Gagal mengaktifkan Pro gratis.', 500);
    $id_langganan = (int)$conn->insert_id;
    $stmt->close();

    $conn->commit();
    return $id_langganan;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}

function createPembayaranLangganan($id_pemilik, $kode_paket, $metode, $buktiFile = null)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;
  $kode_paket = trim((string)$kode_paket);
  $metode = (int)$metode;

  if ($id_pemilik <= 0) throw new Exception('Pemilik tidak valid.', 422);

  $metodeData = getMetodePembayaranLanggananById($metode, true);
  if (!$metodeData) {
    throw new Exception('Metode pembayaran tidak tersedia atau sedang dinonaktifkan.', 422);
  }

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$owner) throw new Exception('Pemilik tidak ditemukan.', 404);

    $stmt = $conn->prepare("SELECT id_pembayaran_langganan FROM pembayaran_langganan WHERE id_pemilik = ? AND status = 'menunggu' LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($pending) throw new Exception('Masih ada pembayaran langganan yang menunggu verifikasi.', 409);

    // Pastikan subscription yang melewati tanggal berakhir tidak lagi dianggap aktif.
    $stmt = $conn->prepare("\n      UPDATE langganan\n      SET status = 'berakhir', updated_at = CURRENT_TIMESTAMP\n      WHERE id_pemilik = ? AND status = 'aktif' AND tanggal_berakhir < CURDATE()\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $stmt->close();

    $paket = getPaketLanggananByKode($kode_paket);
    if (!$paket) throw new Exception('Paket langganan tidak tersedia.', 404);

    $stmt = $conn->prepare("\n      SELECT l.id_langganan, l.status, l.tanggal_berakhir\n      FROM langganan l\n      WHERE l.id_pemilik = ? AND l.status = 'aktif'\n        AND l.tanggal_mulai <= CURDATE() AND l.tanggal_berakhir >= CURDATE()\n      ORDER BY l.tanggal_berakhir DESC, l.id_langganan DESC\n      LIMIT 1\n      FOR UPDATE\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $active = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Owner yang pernah memiliki Pro dan subscription terakhirnya sudah berakhir
    // tetap dihitung sebagai renewal. Ini penting agar harga renewal (mis. 75rb/6 bulan)
    // sinkron dengan nominal yang ditampilkan dan dicatat di riwayat pembayaran.
    $latestLangganan = getLatestLanggananPemilik($id_pemilik);
    $isRenewal = (bool)$active || ($latestLangganan && $latestLangganan['status'] === 'berakhir');
    $jenis = $isRenewal ? 'renewal' : 'baru';
    $nominalPembayaran = $jenis === 'renewal' ? (float)$paket['harga_perpanjangan'] : (float)$paket['harga_bulanan'];
    $id_langganan = null;

    if ($active) {
      // Renewal aktif memakai subscription yang sama agar tidak ada duplicate active subscription.
      $id_langganan = (int)$active['id_langganan'];
    } else {
      // Untuk owner yang pernah Pro tetapi sudah expired, buat record renewal baru.
      // Record lama tetap utuh sebagai histori; record baru akan menjadi aktif setelah approve.
      $today = new DateTimeImmutable('today');
      $end = addMonthsSafe($today, (int)$paket['durasi_bulan']);

      $stmt = $conn->prepare("\n        INSERT INTO langganan\n          (id_pemilik, id_paket_langganan, tanggal_mulai, tanggal_berakhir, status, catatan)\n        VALUES (?, ?, ?, ?, 'menunggu', ?)\n      ");
      $catatan = $isRenewal
        ? 'Menunggu verifikasi renewal setelah subscription sebelumnya berakhir.'
        : 'Menunggu verifikasi pembayaran langganan.';
      $startDate = $today->format('Y-m-d');
      $endDate = $end->format('Y-m-d');
      $stmt->bind_param('iisss', $id_pemilik, $paket['id_paket_langganan'], $startDate, $endDate, $catatan);
      if (!$stmt->execute()) throw new Exception('Gagal membuat order langganan.', 500);
      $id_langganan = (int)$conn->insert_id;
      $stmt->close();
    }

    $nomor = 'SUB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $bukti = $buktiFile ? (string)$buktiFile : null;

    $stmt = $conn->prepare("\n      INSERT INTO pembayaran_langganan\n        (nomor_order, id_langganan, id_paket_langganan, id_pemilik, jenis_pembayaran,\n         nominal, metode_pembayaran, id_metode_pembayaran, provider_pembayaran,\n         nomor_tujuan_pembayaran, nama_penerima_pembayaran, tanggal_pembayaran, bukti_pembayaran, status)\n      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'menunggu')\n    ");
    $metodeJenis = $metodeData['jenis'];
    $provider = $metodeData['nama_provider'];
    $nomorTujuan = $metodeData['nomor_tujuan'];
    $penerima = $metodeData['nama_penerima'];
    $stmt->bind_param(
      'siiisdsissss',
      $nomor,
      $id_langganan,
      $paket['id_paket_langganan'],
      $id_pemilik,
      $jenis,
      $nominalPembayaran,
      $metodeJenis,
      $metode,
      $provider,
      $nomorTujuan,
      $penerima,
      $bukti
    );
    if (!$stmt->execute()) throw new Exception('Gagal membuat pembayaran langganan.', 500);
    $id_pembayaran = (int)$conn->insert_id;
    $stmt->close();

    $conn->commit();
    return $id_pembayaran;
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}


function createPembayaranLanggananQris($id_pemilik, $kode_paket)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;
  $kode_paket = trim((string)$kode_paket);
  if ($id_pemilik <= 0) throw new Exception('Pemilik tidak valid.', 422);

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik); $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$owner) throw new Exception('Pemilik tidak ditemukan.', 404);

    $stmt = $conn->prepare("SELECT id_pembayaran_langganan FROM pembayaran_langganan WHERE id_pemilik = ? AND status = 'menunggu' LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik); $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($pending) throw new Exception('Masih ada pembayaran langganan yang menunggu pembayaran/verifikasi.', 409);

    $stmt = $conn->prepare("UPDATE langganan SET status='berakhir', updated_at=CURRENT_TIMESTAMP WHERE id_pemilik=? AND status='aktif' AND tanggal_berakhir < CURDATE()");
    $stmt->bind_param('i', $id_pemilik); $stmt->execute(); $stmt->close();

    $paket = getPaketLanggananByKode($kode_paket);
    if (!$paket) throw new Exception('Paket langganan tidak tersedia.', 404);

    $stmt = $conn->prepare("SELECT id_langganan,status,tanggal_berakhir FROM langganan WHERE id_pemilik=? AND status='aktif' AND tanggal_mulai<=CURDATE() AND tanggal_berakhir>=CURDATE() ORDER BY tanggal_berakhir DESC,id_langganan DESC LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $id_pemilik); $stmt->execute();
    $active = $stmt->get_result()->fetch_assoc(); $stmt->close();

    $latest = getLatestLanggananPemilik($id_pemilik);
    $isRenewal = (bool)$active || ($latest && $latest['status'] === 'berakhir');
    $jenis = $isRenewal ? 'renewal' : 'baru';
    $nominal = $jenis === 'renewal' ? (float)$paket['harga_perpanjangan'] : (float)$paket['harga_bulanan'];
    if ($nominal <= 0) throw new Exception('Paket ini tidak memerlukan pembayaran QRIS.', 422);
    if ($nominal > 10000000) throw new Exception('Nominal QRIS melebihi batas transaksi.', 422);

    if ($active) {
      $id_langganan = (int)$active['id_langganan'];
    } else {
      $today = new DateTimeImmutable('today');
      $end = addMonthsSafe($today, (int)$paket['durasi_bulan']);
      $startDate = $today->format('Y-m-d'); $endDate = $end->format('Y-m-d');
      $catatan = $isRenewal ? 'Menunggu pembayaran QRIS untuk renewal setelah subscription sebelumnya berakhir.' : 'Menunggu pembayaran QRIS untuk langganan baru.';
      $stmt = $conn->prepare("INSERT INTO langganan (id_pemilik,id_paket_langganan,tanggal_mulai,tanggal_berakhir,status,catatan) VALUES (?,?,?,?, 'menunggu',?)");
      $stmt->bind_param('iisss',$id_pemilik,$paket['id_paket_langganan'],$startDate,$endDate,$catatan);
      if (!$stmt->execute()) throw new Exception('Gagal membuat order langganan.',500);
      $id_langganan=(int)$conn->insert_id; $stmt->close();
    }

    $nomor='SUB-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
    $metode='qris'; $provider='midtrans';
    $stmt=$conn->prepare("INSERT INTO pembayaran_langganan (nomor_order,id_langganan,id_paket_langganan,id_pemilik,jenis_pembayaran,nominal,metode_pembayaran,provider_pembayaran,tanggal_pembayaran,status) VALUES (?,?,?,?,?,?,?, ?,NOW(),'menunggu')");
    $stmt->bind_param('siiisdss',$nomor,$id_langganan,$paket['id_paket_langganan'],$id_pemilik,$jenis,$nominal,$metode,$provider);
    if (!$stmt->execute()) throw new Exception('Gagal membuat pembayaran QRIS.',500);
    $idPembayaran=(int)$conn->insert_id; $stmt->close();
    $conn->commit();
    return ['id_pembayaran_langganan'=>$idPembayaran,'nomor_order'=>$nomor,'nominal'=>(int)round($nominal),'jenis_pembayaran'=>$jenis];
  } catch(Throwable $e) { $conn->rollback(); throw $e; }
}

function updatePembayaranMidtransCreated($id_pembayaran, array $gateway)
{
  $conn=db(); $id_pembayaran=(int)$id_pembayaran;
  $providerOrder=(string)($gateway['order_id'] ?? '');
  if ($providerOrder==='') throw new Exception('Midtrans tidak mengembalikan order ID.',502);
  $transactionId=(string)($gateway['transaction_id'] ?? '');
  $providerStatus=(string)($gateway['transaction_status'] ?? 'pending');
  $qrString=(string)($gateway['qr_string'] ?? '');
  $qrUrl=(string)($gateway['qr_code_url'] ?? '');
  $stmt=$conn->prepare("SELECT id_pembayaran_langganan FROM pembayaran_langganan WHERE id_pembayaran_langganan=? LIMIT 1");
  $stmt->bind_param('i',$id_pembayaran); $stmt->execute(); $exists=$stmt->get_result()->fetch_assoc(); $stmt->close();
  if(!$exists) throw new Exception('Pembayaran QRIS tidak ditemukan.',404);
  $stmt=$conn->prepare("UPDATE pembayaran_langganan SET provider_pembayaran='midtrans',provider_order_id=COALESCE(NULLIF(?,''),provider_order_id),provider_transaction_id=COALESCE(NULLIF(?,''),provider_transaction_id),provider_status=COALESCE(NULLIF(?,''),provider_status),qr_string=COALESCE(NULLIF(?,''),qr_string),qr_code_url=COALESCE(NULLIF(?,''),qr_code_url) WHERE id_pembayaran_langganan=?");
  $stmt->bind_param('sssssi',$providerOrder,$transactionId,$providerStatus,$qrString,$qrUrl,$id_pembayaran);
  if(!$stmt->execute()){ $stmt->close(); throw new Exception('Data pembayaran QRIS gagal disimpan.',500); } $stmt->close();
}

function batalkanPembayaranMidtransGagal($id_pembayaran)
{
  $conn=db(); $id_pembayaran=(int)$id_pembayaran;
  $conn->begin_transaction();
  try {
    $stmt=$conn->prepare("SELECT id_langganan,status FROM pembayaran_langganan WHERE id_pembayaran_langganan=? FOR UPDATE"); $stmt->bind_param('i',$id_pembayaran); $stmt->execute(); $p=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if(!$p) throw new Exception('Pembayaran tidak ditemukan.',404);
    if($p['status']==='menunggu'){
      $stmt=$conn->prepare("UPDATE pembayaran_langganan SET status='dibatalkan',provider_status='failed',catatan_admin='Order Midtrans gagal dibuat.' WHERE id_pembayaran_langganan=? AND status='menunggu'"); $stmt->bind_param('i',$id_pembayaran); $stmt->execute(); $stmt->close();
      $stmt=$conn->prepare("UPDATE langganan SET status='dibatalkan' WHERE id_langganan=? AND status='menunggu'"); $stmt->bind_param('i',$p['id_langganan']); $stmt->execute(); $stmt->close();
    }
    $conn->commit();
  } catch(Throwable $e){$conn->rollback();throw $e;}
}

function prosesNotifikasiMidtrans(array $notification)
{
  $conn=db();
  $orderId=trim((string)($notification['order_id']??''));
  $status=(string)($notification['transaction_status']??'');
  $providerStatus=$status;
  $transactionId=(string)($notification['transaction_id']??'');
  if($orderId==='') throw new Exception('Order ID Midtrans tidak ada.',422);

  $conn->begin_transaction();
  try {
    $stmt=$conn->prepare("SELECT * FROM pembayaran_langganan WHERE nomor_order=? AND provider_pembayaran='midtrans' FOR UPDATE");
    $stmt->bind_param('s',$orderId); $stmt->execute(); $payment=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if(!$payment) throw new Exception('Order Midtrans tidak ditemukan.',404);
    if(($notification['payment_type'] ?? '') !== 'qris') throw new Exception('Notifikasi Midtrans bukan transaksi QRIS.',422);

    $gross=(int)round((float)$payment['nominal']);
    $notifiedGross=(int)round((float)($notification['gross_amount']??0));
    if($gross!==$notifiedGross) throw new Exception('Nominal pembayaran Midtrans tidak cocok.',422);

    $stmt=$conn->prepare("UPDATE pembayaran_langganan SET provider_status=?,provider_transaction_id=COALESCE(NULLIF(?,''),provider_transaction_id) WHERE id_pembayaran_langganan=?");
    $stmt->bind_param('ssi',$providerStatus,$transactionId,$payment['id_pembayaran_langganan']); $stmt->execute(); $stmt->close();

    if($payment['status']!=='menunggu'){ $conn->commit(); return ['processed'=>false,'status'=>$payment['status'],'duplicate'=>true]; }

    if(in_array($status,['expire','deny','cancel'],true)){
      $stmt=$conn->prepare("UPDATE pembayaran_langganan SET status='dibatalkan' WHERE id_pembayaran_langganan=? AND status='menunggu'"); $stmt->bind_param('i',$payment['id_pembayaran_langganan']); $stmt->execute(); $stmt->close();
      if(in_array($payment['jenis_pembayaran'], ['baru','renewal'], true)){
        // Hanya subscription pending yang dibatalkan. Subscription aktif untuk renewal tidak disentuh.
        $stmt=$conn->prepare("UPDATE langganan SET status='dibatalkan', updated_at=CURRENT_TIMESTAMP WHERE id_langganan=? AND status='menunggu'"); $stmt->bind_param('i',$payment['id_langganan']); $stmt->execute(); $stmt->close();
      }
      $conn->commit(); return ['processed'=>true,'status'=>'dibatalkan'];
    }

    if(!in_array($status,['settlement','capture'],true)){
      $conn->commit(); return ['processed'=>false,'status'=>'menunggu'];
    }
    if($status==='capture' && isset($notification['fraud_status']) && $notification['fraud_status']!=='accept'){
      $conn->commit(); return ['processed'=>false,'status'=>'menunggu'];
    }

    $stmt=$conn->prepare("SELECT l.*,p.durasi_bulan FROM langganan l INNER JOIN paket_langganan p ON p.id_paket_langganan=? WHERE l.id_langganan=? FOR UPDATE");
    $stmt->bind_param('ii',$payment['id_paket_langganan'],$payment['id_langganan']); $stmt->execute(); $sub=$stmt->get_result()->fetch_assoc(); $stmt->close();
    if(!$sub) throw new Exception('Subscription terkait tidak ditemukan.',404);

    $start=null;$end=null;
    if($payment['jenis_pembayaran']==='renewal' && $sub['status']==='aktif'){
      $start=new DateTimeImmutable($sub['tanggal_mulai']); $end=addMonthsSafe(new DateTimeImmutable($sub['tanggal_berakhir']),(int)$sub['durasi_bulan']);
      $stmt=$conn->prepare("UPDATE langganan SET id_paket_langganan=?,tanggal_berakhir=?,updated_at=CURRENT_TIMESTAMP WHERE id_langganan=? AND status='aktif'"); $endDate=$end->format('Y-m-d'); $stmt->bind_param('isi',$payment['id_paket_langganan'],$endDate,$sub['id_langganan']);
    } else {
      if(!in_array($sub['status'],['menunggu'],true)) throw new Exception('Subscription tidak dapat diaktifkan dari status saat ini.',409);
      $start=new DateTimeImmutable('today'); $end=addMonthsSafe($start,(int)$sub['durasi_bulan']); $startDate=$start->format('Y-m-d'); $endDate=$end->format('Y-m-d');
      $stmt=$conn->prepare("UPDATE langganan SET status='aktif',tanggal_mulai=?,tanggal_berakhir=?,catatan=NULL,updated_at=CURRENT_TIMESTAMP WHERE id_langganan=? AND status='menunggu'"); $stmt->bind_param('ssi',$startDate,$endDate,$sub['id_langganan']);
    }
    if(!$stmt->execute() || $stmt->affected_rows!==1){$stmt->close();throw new Exception('Gagal mengaktifkan subscription.',409);} $stmt->close();

    $stmt=$conn->prepare("UPDATE pembayaran_langganan SET status='diverifikasi',paid_at=COALESCE(paid_at,CURRENT_TIMESTAMP),tanggal_verifikasi=COALESCE(tanggal_verifikasi,CURRENT_TIMESTAMP) WHERE id_pembayaran_langganan=? AND status='menunggu'"); $stmt->bind_param('i',$payment['id_pembayaran_langganan']); if(!$stmt->execute()||$stmt->affected_rows!==1){$stmt->close();throw new Exception('Gagal menandai pembayaran sebagai terverifikasi.',409);} $stmt->close();

    $stmt=$conn->prepare("SELECT COUNT(*) total FROM langganan WHERE id_pemilik=? AND status='aktif' AND tanggal_mulai<=CURDATE() AND tanggal_berakhir>=CURDATE()"); $stmt->bind_param('i',$payment['id_pemilik']); $stmt->execute(); $count=(int)($stmt->get_result()->fetch_assoc()['total']??0); $stmt->close();
    if($count>1) throw new Exception('Terdeteksi lebih dari satu subscription aktif.',409);
    $conn->commit(); return ['processed'=>true,'status'=>'diverifikasi'];
  } catch(Throwable $e){$conn->rollback();throw $e;}
}

function hapusFileBuktiPembayaranLangganan($relativePath)
{
  $relativePath = trim((string)$relativePath);
  if ($relativePath === '') return;

  // Hanya izinkan file yang memang berada di folder bukti pembayaran langganan.
  $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
  if (!str_starts_with($relativePath, 'pembayaran-langganan/')) return;
  if (str_contains($relativePath, '..') || !preg_match('#^pembayaran-langganan/[A-Za-z0-9_-]+\\.[A-Za-z0-9]+$#', $relativePath)) return;

  $baseDir = realpath(ROOT_PATH . '/public/uploads/pembayaran-langganan');
  if ($baseDir === false) return;

  $target = ROOT_PATH . '/public/uploads/' . $relativePath;
  $realTarget = realpath($target);
  if ($realTarget === false) return;

  $basePrefix = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  if (!str_starts_with($realTarget, $basePrefix) || !is_file($realTarget)) return;

  @unlink($realTarget);
}

function submitBuktiPembayaranLangganan($id_pembayaran, $id_pemilik, $buktiFile)
{
  $conn = db();
  $id_pembayaran = (int)$id_pembayaran;
  $id_pemilik = (int)$id_pemilik;
  $buktiFile = trim((string)$buktiFile);

  // Ambil bukti lama terlebih dahulu agar file fisik dapat dibersihkan
  // setelah database berhasil menunjuk ke bukti yang baru.
  $stmt = $conn->prepare("
    SELECT bukti_pembayaran
    FROM pembayaran_langganan
    WHERE id_pembayaran_langganan = ? AND id_pemilik = ? AND status = 'menunggu'
    LIMIT 1
  ");
  $stmt->bind_param('ii', $id_pembayaran, $id_pemilik);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) throw new Exception('Pembayaran tidak ditemukan atau sudah diproses.', 404);

  $oldFile = trim((string)($row['bukti_pembayaran'] ?? ''));

  $stmt = $conn->prepare("
    UPDATE pembayaran_langganan
    SET bukti_pembayaran = ?, updated_at = CURRENT_TIMESTAMP
    WHERE id_pembayaran_langganan = ? AND id_pemilik = ? AND status = 'menunggu'
  ");
  $stmt->bind_param('sii', $buktiFile, $id_pembayaran, $id_pemilik);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal memperbarui bukti pembayaran.', 500);
  }
  $affected = $stmt->affected_rows;
  $stmt->close();

  if ($affected < 1) throw new Exception('Bukti pembayaran tidak berubah atau pembayaran sudah diproses.', 409);

  // Hanya hapus file lama setelah database sukses diperbarui.
  if ($oldFile !== '' && $oldFile !== $buktiFile) {
    hapusFileBuktiPembayaranLangganan($oldFile);
  }
}

function getAdminLanggananList($status = '')
{
  $conn = db();
  syncExpiredLangganan();
  $allowed = ['', 'aktif', 'menunggu', 'berakhir', 'dibatalkan', 'akan_berakhir'];
  if (!in_array($status, $allowed, true)) $status = '';

  $statusWhere = '';
  if ($status === 'aktif') {
    $statusWhere = "WHERE l.status = 'aktif' AND l.tanggal_mulai <= CURDATE() AND l.tanggal_berakhir >= CURDATE()";
  } elseif ($status === 'berakhir') {
    $statusWhere = "WHERE l.status = 'berakhir'";
  } elseif ($status === 'menunggu') {
    $statusWhere = "WHERE l.status = 'menunggu'";
  } elseif ($status === 'dibatalkan') {
    $statusWhere = "WHERE l.status = 'dibatalkan'";
  } elseif ($status === 'akan_berakhir') {
    $statusWhere = "WHERE l.status = 'aktif' AND l.tanggal_berakhir >= CURDATE() AND l.tanggal_berakhir <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
  }

  $sql = "
    SELECT
      l.id_langganan,
      l.id_pemilik,
      l.id_paket_langganan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      CASE
        WHEN l.status = 'aktif' AND l.tanggal_berakhir < CURDATE() THEN 'berakhir'
        ELSE l.status
      END AS status,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.harga_bulanan,
      p.harga_perpanjangan,
      p.durasi_bulan,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      (
        SELECT pl.status
        FROM pembayaran_langganan pl
        WHERE pl.id_langganan = l.id_langganan
        ORDER BY pl.id_pembayaran_langganan DESC
        LIMIT 1
      ) AS status_pembayaran_terakhir,
      (
        SELECT pl.id_pembayaran_langganan
        FROM pembayaran_langganan pl
        WHERE pl.id_langganan = l.id_langganan
        ORDER BY pl.id_pembayaran_langganan DESC
        LIMIT 1
      ) AS id_pembayaran_terakhir,
      (
        SELECT pl.nomor_order
        FROM pembayaran_langganan pl
        WHERE pl.id_langganan = l.id_langganan
        ORDER BY pl.id_pembayaran_langganan DESC
        LIMIT 1
      ) AS nomor_order_terakhir,
      (
        SELECT pl.tanggal_pembayaran
        FROM pembayaran_langganan pl
        WHERE pl.id_langganan = l.id_langganan
        ORDER BY pl.id_pembayaran_langganan DESC
        LIMIT 1
      ) AS tanggal_pembayaran_terakhir
    FROM langganan l
    INNER JOIN users u ON u.id_user = l.id_pemilik
    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan
    $statusWhere
    ORDER BY
      CASE WHEN l.status = 'menunggu' THEN 0 ELSE 1 END,
      l.tanggal_berakhir DESC,
      l.id_langganan DESC
  ";

  $result = $conn->query($sql);
  if (!$result) throw new RuntimeException('Gagal mengambil data langganan admin.');

  $rows = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($rows as &$row) {
    $row['harga_bulanan'] = (float)$row['harga_bulanan'];
    $row['harga_perpanjangan'] = (float)$row['harga_perpanjangan'];
    $row['durasi_bulan'] = (int)$row['durasi_bulan'];
    $row['id_langganan'] = (int)$row['id_langganan'];
    $row['id_pemilik'] = (int)$row['id_pemilik'];
    $row['id_paket_langganan'] = (int)$row['id_paket_langganan'];
    $row['id_pembayaran_terakhir'] = $row['id_pembayaran_terakhir'] !== null ? (int)$row['id_pembayaran_terakhir'] : null;
  }
  unset($row);
  return $rows;
}

function getAdminLanggananDetail($id_langganan)
{
  syncExpiredLangganan();
  $conn = db();
  $id_langganan = (int)$id_langganan;
  if ($id_langganan <= 0) return null;

  $stmt = $conn->prepare("
    SELECT
      l.id_langganan,
      l.id_pemilik,
      l.id_paket_langganan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      CASE
        WHEN l.status = 'aktif' AND l.tanggal_berakhir < CURDATE() THEN 'berakhir'
        ELSE l.status
      END AS status,
      l.catatan,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.harga_bulanan,
      p.harga_perpanjangan,
      p.durasi_bulan,
      p.deskripsi AS deskripsi_paket,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      u.no_hp AS no_hp_pemilik
    FROM langganan l
    INNER JOIN users u ON u.id_user = l.id_pemilik
    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan
    WHERE l.id_langganan = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_langganan);
  $stmt->execute();
  $subscription = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$subscription) return null;

  $subscription['harga_bulanan'] = (float)$subscription['harga_bulanan'];
  $subscription['harga_perpanjangan'] = (float)$subscription['harga_perpanjangan'];
  $subscription['durasi_bulan'] = (int)$subscription['durasi_bulan'];

  $stmt = $conn->prepare("
    SELECT
      pl.id_pembayaran_langganan,
      pl.nomor_order,
      pl.id_langganan,
      pl.id_paket_langganan,
      pl.id_pemilik,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.durasi_bulan,
      pl.jenis_pembayaran,
      pl.nominal,
      pl.metode_pembayaran,
      pl.tanggal_pembayaran,
      pl.bukti_pembayaran,
      pl.status,
      pl.provider_pembayaran,
      pl.provider_order_id,
      pl.provider_transaction_id,
      pl.provider_status,
      pl.qr_code_url,
      pl.paid_at,
      pl.id_admin_verifikasi,
      pl.tanggal_verifikasi,
      pl.catatan_admin,
      pl.created_at,
      pl.updated_at
    FROM pembayaran_langganan pl
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    WHERE pl.id_langganan = ?
    ORDER BY pl.id_pembayaran_langganan DESC
  ");
  $stmt->bind_param('i', $id_langganan);
  $stmt->execute();
  $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  foreach ($payments as &$payment) {
    $payment['nominal'] = (float)$payment['nominal'];
  }
  unset($payment);

  // Histori subscription milik owner, tanpa mengubah atau menghapus histori lama.
  $ownerHistory = getAdminLanggananHistoryPemilik((int)$subscription['id_pemilik']);

  $subscription['pembayaran'] = $payments;
  $subscription['histori_langganan_pemilik'] = $ownerHistory;
  return $subscription;
}

function getAdminLanggananHistoryPemilik($id_pemilik)
{
  syncExpiredLangganan($id_pemilik);
  $conn = db();
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("
    SELECT
      l.id_langganan,
      l.id_paket_langganan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      CASE
        WHEN l.status = 'aktif' AND l.tanggal_berakhir < CURDATE() THEN 'berakhir'
        ELSE l.status
      END AS status,
      p.kode AS kode_paket,
      p.nama AS nama_paket,
      p.harga_bulanan,
      p.harga_perpanjangan,
      p.durasi_bulan
    FROM langganan l
    INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan
    WHERE l.id_pemilik = ?
    ORDER BY l.id_langganan DESC
  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  foreach ($rows as &$row) {
    $row['harga_bulanan'] = (float)$row['harga_bulanan'];
    $row['durasi_bulan'] = (int)$row['durasi_bulan'];
  }
  unset($row);
  return $rows;
}

function getAdminSubscriptionSummary()
{
  syncExpiredLangganan();
  $conn = db();
  $sql = "
    SELECT
      SUM(CASE WHEN l.status = 'aktif' AND l.tanggal_mulai <= CURDATE() AND l.tanggal_berakhir >= CURDATE() THEN 1 ELSE 0 END) AS aktif,
      SUM(CASE WHEN l.status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
      SUM(CASE WHEN l.status = 'berakhir' THEN 1 ELSE 0 END) AS berakhir,
      SUM(CASE WHEN l.status = 'aktif' AND l.tanggal_berakhir >= CURDATE() AND l.tanggal_berakhir <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS akan_berakhir_7_hari,
      SUM(CASE WHEN l.status = 'dibatalkan' THEN 1 ELSE 0 END) AS dibatalkan,
      (SELECT COUNT(*) FROM pembayaran_langganan WHERE status = 'menunggu' AND COALESCE(provider_pembayaran, '') <> 'midtrans') AS pembayaran_menunggu,
      (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_langganan WHERE status = 'diverifikasi') AS total_pendapatan,
      (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_langganan WHERE status = 'diverifikasi' AND tanggal_verifikasi >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')) AS pendapatan_bulan_ini
    FROM langganan l
  ";
  $row = $conn->query($sql)->fetch_assoc() ?: [];
  return [
    'aktif' => (int)($row['aktif'] ?? 0),
    'menunggu' => (int)($row['menunggu'] ?? 0),
    'berakhir' => (int)($row['berakhir'] ?? 0),
    'akan_berakhir_7_hari' => (int)($row['akan_berakhir_7_hari'] ?? 0),
    'dibatalkan' => (int)($row['dibatalkan'] ?? 0),
    'pembayaran_menunggu' => (int)($row['pembayaran_menunggu'] ?? 0),
    'total_pendapatan' => (float)($row['total_pendapatan'] ?? 0),
    'pendapatan_bulan_ini' => (float)($row['pendapatan_bulan_ini'] ?? 0),
  ];
}

function getAdminPembayaranLangganan($status = 'menunggu')
{
  syncExpiredLangganan();
  $conn = db();
  $allowed = ['menunggu', 'diverifikasi', 'ditolak', 'dibatalkan'];
  if (!in_array($status, $allowed, true)) $status = 'menunggu';

  $stmt = $conn->prepare("
    SELECT
      pl.id_pembayaran_langganan,
      pl.nomor_order,
      pl.id_langganan,
      pl.id_pemilik,
      pl.jenis_pembayaran,
      pl.nominal,
      pl.metode_pembayaran,
      pl.provider_pembayaran,
      pl.provider_status,
      pl.tanggal_pembayaran,
      pl.bukti_pembayaran,
      pl.status,
      pl.catatan_admin,
      pl.created_at,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      p.nama AS nama_paket
    FROM pembayaran_langganan pl
    INNER JOIN users u ON u.id_user = pl.id_pemilik
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    WHERE pl.status = ?
      AND (? <> 'menunggu' OR COALESCE(pl.provider_pembayaran, '') <> 'midtrans')
    ORDER BY pl.id_pembayaran_langganan DESC
  ");
  $stmt->bind_param('ss', $status, $status);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  foreach ($rows as &$row) $row['nominal'] = (float)$row['nominal'];
  unset($row);
  return $rows;
}

function getAdminPembayaranLanggananDetail($id_pembayaran)
{
  syncExpiredLangganan();
  $conn = db();
  $id_pembayaran = (int)$id_pembayaran;

  $stmt = $conn->prepare("
    SELECT
      pl.*,
      u.nama AS nama_pemilik,
      u.email AS email_pemilik,
      u.no_hp AS no_hp_pemilik,
      p.nama AS nama_paket,
      p.kode AS kode_paket,
      p.durasi_bulan,
      l.tanggal_mulai,
      l.tanggal_berakhir,
      l.status AS status_langganan
    FROM pembayaran_langganan pl
    INNER JOIN users u ON u.id_user = pl.id_pemilik
    INNER JOIN paket_langganan p ON p.id_paket_langganan = pl.id_paket_langganan
    INNER JOIN langganan l ON l.id_langganan = pl.id_langganan
    WHERE pl.id_pembayaran_langganan = ?
    LIMIT 1
  ");
  $stmt->bind_param('i', $id_pembayaran);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) return null;

  $row['nominal'] = (float)$row['nominal'];
  return $row;
}

function prosesVerifikasiPembayaranLangganan($id_pembayaran, $id_admin, $keputusan, $catatan = '')
{
  $conn = db();
  $id_pembayaran = (int)$id_pembayaran;
  $id_admin = (int)$id_admin;
  $keputusan = trim((string)$keputusan);
  $catatan = trim((string)$catatan);

  if (!in_array($keputusan, ['diverifikasi', 'ditolak'], true)) {
    throw new Exception('Keputusan pembayaran tidak valid.', 422);
  }
  if ($keputusan === 'ditolak' && $catatan === '') {
    throw new Exception('Catatan wajib diisi ketika pembayaran ditolak.', 422);
  }

  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare("\n      SELECT *\n      FROM pembayaran_langganan\n      WHERE id_pembayaran_langganan = ?\n      FOR UPDATE\n    ");
    $stmt->bind_param('i', $id_pembayaran);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$payment) throw new Exception('Pembayaran tidak ditemukan.', 404);
    if ($payment['status'] !== 'menunggu') throw new Exception('Pembayaran sudah diproses.', 409);
    if (($payment['provider_pembayaran'] ?? '') === 'midtrans') {
      throw new Exception('Pembayaran QRIS Midtrans diverifikasi otomatis oleh sistem.', 409);
    }

    $stmt = $conn->prepare("\n      SELECT l.*, p.durasi_bulan AS durasi_langganan, pp.durasi_bulan AS durasi_pembayaran\n      FROM langganan l\n      INNER JOIN paket_langganan p ON p.id_paket_langganan = l.id_paket_langganan\n      INNER JOIN paket_langganan pp ON pp.id_paket_langganan = ?\n      WHERE l.id_langganan = ?\n      FOR UPDATE\n    ");
    $stmt->bind_param('ii', $payment['id_paket_langganan'], $payment['id_langganan']);
    $stmt->execute();
    $subscription = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$subscription) throw new Exception('Subscription terkait tidak ditemukan.', 404);

    $stmt = $conn->prepare("\n      UPDATE pembayaran_langganan\n      SET status = ?, id_admin_verifikasi = ?, catatan_admin = ?, tanggal_verifikasi = CURRENT_TIMESTAMP\n      WHERE id_pembayaran_langganan = ? AND status = 'menunggu'\n    ");
    $stmt->bind_param('sisi', $keputusan, $id_admin, $catatan, $id_pembayaran);
    if (!$stmt->execute() || $stmt->affected_rows !== 1) throw new Exception('Pembayaran gagal diperbarui.', 409);
    $stmt->close();

    if ($keputusan === 'ditolak') {
      // Hanya record pending yang dibatalkan. Subscription expired lama tidak disentuh.
      if ($subscription['status'] === 'menunggu') {
        $stmt = $conn->prepare("UPDATE langganan SET status = 'dibatalkan', catatan = ? WHERE id_langganan = ? AND status = 'menunggu'");
        $stmt->bind_param('si', $catatan, $subscription['id_langganan']);
        $stmt->execute();
        $stmt->close();
      }
      $conn->commit();
      return;
    }

    $start = null;
    $end = null;

    if ($payment['jenis_pembayaran'] === 'baru') {
      if ($subscription['status'] !== 'menunggu') throw new Exception('Subscription baru tidak lagi menunggu aktivasi.', 409);
      $start = new DateTimeImmutable('today');
      $end = addMonthsSafe($start, (int)$subscription['durasi_pembayaran']);
    } else {
      if ($subscription['status'] === 'aktif') {
        // Renewal sebelum expired: pertahankan periode lama dan tambahkan durasi paket.
        $currentEnd = new DateTimeImmutable($subscription['tanggal_berakhir']);
        $start = new DateTimeImmutable($subscription['tanggal_mulai']);
        $end = addMonthsSafe($currentEnd, (int)$subscription['durasi_pembayaran']);
      } elseif ($subscription['status'] === 'menunggu') {
        // Renewal setelah expired: record renewal baru dimulai pada tanggal approval.
        $start = new DateTimeImmutable('today');
        $end = addMonthsSafe($start, (int)$subscription['durasi_pembayaran']);
      } else {
        throw new Exception('Subscription renewal tidak dapat diaktifkan dari status saat ini.', 409);
      }
    }

    $startDate = $start->format('Y-m-d');
    $endDate = $end->format('Y-m-d');

    if ($payment['jenis_pembayaran'] === 'renewal' && $subscription['status'] === 'aktif') {
      $stmt = $conn->prepare("\n        UPDATE langganan\n        SET id_paket_langganan = ?, tanggal_berakhir = ?, updated_at = CURRENT_TIMESTAMP\n        WHERE id_langganan = ? AND status = 'aktif'\n      ");
      $stmt->bind_param('isi', $payment['id_paket_langganan'], $endDate, $subscription['id_langganan']);
    } else {
      $stmt = $conn->prepare("\n        UPDATE langganan\n        SET status = 'aktif', tanggal_mulai = ?, tanggal_berakhir = ?, catatan = NULL, updated_at = CURRENT_TIMESTAMP\n        WHERE id_langganan = ? AND status = 'menunggu'\n      ");
      $stmt->bind_param('ssi', $startDate, $endDate, $subscription['id_langganan']);
    }

    if (!$stmt->execute() || $stmt->affected_rows !== 1) throw new Exception('Gagal mengaktifkan/perpanjang subscription.', 409);
    $stmt->close();

    // Safety check: satu owner tidak boleh memiliki dua subscription aktif.
    $stmt = $conn->prepare("\n      SELECT COUNT(*) AS total\n      FROM langganan\n      WHERE id_pemilik = ? AND status = 'aktif'\n        AND tanggal_mulai <= CURDATE() AND tanggal_berakhir >= CURDATE()\n    ");
    $stmt->bind_param('i', $payment['id_pemilik']);
    $stmt->execute();
    $activeCount = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();
    if ($activeCount > 1) throw new Exception('Terdeteksi lebih dari satu subscription aktif untuk pemilik.', 409);

    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    throw $e;
  }
}
