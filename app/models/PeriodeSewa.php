<?php

/**
 * Ambil periode sewa aktif berdasarkan kamar.
 */
function getPeriodeAktifByKamar($id_kamar)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      ps.*,

      km.nomor_kamar,
      km.kapasitas,

      k.id_kos,
      k.nama_kos

    FROM periode_sewa ps

    INNER JOIN kamar km
      ON km.id_kamar = ps.id_kamar

    INNER JOIN kos k
      ON k.id_kos = km.id_kos

    WHERE ps.id_kamar = ?
      AND ps.status = 'aktif'

    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_kamar
  );

  $stmt->execute();

  $data = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $data;
}


/**
 * Ambil harga kamar berdasarkan jumlah penghuni.
 */
function getHargaKamarByJumlahOrang(
  $id_kamar,
  $jumlah_orang
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_harga,
      id_kamar,
      jumlah_orang,
      harga_total

    FROM harga_kamar

    WHERE id_kamar = ?
      AND jumlah_orang = ?

    LIMIT 1
  ");

  $stmt->bind_param(
    'ii',
    $id_kamar,
    $jumlah_orang
  );

  $stmt->execute();

  $data = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $data;
}


/**
 * Hitung jumlah penghuni aktif dalam kamar.
 */
function getJumlahPenghuniAktifKamar($id_kamar)
{
  $conn = db();

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

  $data = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return (int) ($data['total'] ?? 0);
}


/**
 * Membuat periode sewa pertama untuk kamar.
 *
 * Periode hanya dibuat jika kamar belum mempunyai
 * periode aktif.
 */
function buatPeriodeSewaAwal(
  $id_kamar,
  $tanggal_mulai
) {
  $conn = db();

  /*
   * Cek apakah sudah ada periode aktif.
   */
  $periode = getPeriodeAktifByKamar(
    $id_kamar
  );

  if ($periode) {
    return $periode['id_periode'];
  }

  /*
   * Ambil jumlah penghuni aktif.
   */
  $jumlah_orang =
    getJumlahPenghuniAktifKamar(
      $id_kamar
    );

  if ($jumlah_orang < 1) {
    throw new Exception(
      'Tidak dapat membuat periode sewa tanpa penghuni aktif.'
    );
  }

  /*
   * Ambil harga berdasarkan jumlah penghuni.
   */
  $harga = getHargaKamarByJumlahOrang(
    $id_kamar,
    $jumlah_orang
  );

  if (!$harga) {
    throw new Exception(
      "Harga kamar untuk {$jumlah_orang} orang belum diatur."
    );
  }

  $harga_total =
    (float) $harga['harga_total'];

  /*
   * Periode bulanan.
   *
   * Contoh:
   * 2026-08-03
   * sampai
   * 2026-09-02
   */
  $tanggal_selesai =
    date(
      'Y-m-d',
      strtotime(
        $tanggal_mulai . ' +1 month -1 day'
      )
    );

  /*
   * Jatuh tempo = tanggal masuk pertama.
   */
  $tanggal_jatuh_tempo =
    $tanggal_mulai;

  $stmt = $conn->prepare("
    INSERT INTO periode_sewa (
      id_kamar,
      tanggal_mulai,
      tanggal_selesai,
      tanggal_jatuh_tempo,
      jumlah_orang,
      harga_total,
      status
    )
    VALUES (?, ?, ?, ?, ?, ?, 'aktif')
  ");

  $stmt->bind_param(
    'isssid',
    $id_kamar,
    $tanggal_mulai,
    $tanggal_selesai,
    $tanggal_jatuh_tempo,
    $jumlah_orang,
    $harga_total
  );

  $result =
    $stmt->execute();

  $id_periode =
    $stmt->insert_id;

  $error =
    $stmt->error;

  $stmt->close();

  if (!$result) {
    throw new Exception(
      'Gagal membuat periode sewa: ' . $error
    );
  }

  return $id_periode;
}

function prosesPeriodeDanTagihanPenghuniMasuk(
  $id_kamar,
  $id_penghuni,
  $tanggal_masuk
) {
  $periode = getPeriodeAktifByKamar($id_kamar);

  /*
   * =====================================================
   * 1. PENGHUNI PERTAMA
   * =====================================================
   */

  if (!$periode) {

    $id_periode = buatPeriodeSewaAwal(
      $id_kamar,
      $tanggal_masuk
    );

    /*
     * Buat tagihan awal.
     */
    buatTagihanAwalPeriode(
      $id_periode
    );

    return;
  }


  /*
   * =====================================================
   * 2. PERIODE SUDAH ADA
   * =====================================================
   */

  $jumlahLama =
    (int) $periode['jumlah_orang'];

  $jumlahBaru =
    getJumlahPenghuniAktifKamar($id_kamar);

  /*
   * Tidak ada perubahan jumlah.
   */
  if ($jumlahBaru <= $jumlahLama) {
    return;
  }


  /*
   * Ambil harga kamar berdasarkan
   * jumlah penghuni baru.
   */
  $hargaBaru =
    getHargaKamarByJumlahOrang(
      $id_kamar,
      $jumlahBaru
    );

  if (!$hargaBaru) {
    throw new Exception(
      "Harga kamar untuk {$jumlahBaru} orang belum diatur."
    );
  }


  $hargaLama =
    (float) $periode['harga_total'];

  $hargaBaruTotal =
    (float) $hargaBaru['harga_total'];


  /*
   * Tidak ada perubahan harga.
   */
  if ($hargaBaruTotal == $hargaLama) {

    updateJumlahDanHargaPeriode(
      $periode['id_periode'],
      $jumlahBaru,
      $hargaBaruTotal
    );

    return;
  }


  /*
   * =====================================================
   * HITUNG PENYESUAIAN PRORATA
   * =====================================================
   */

  $tanggalMulaiPeriode =
    new DateTime(
      $periode['tanggal_mulai']
    );

  $tanggalSelesaiPeriode =
    new DateTime(
      $periode['tanggal_selesai']
    );

  $tanggalEfektif =
    new DateTime(
      $tanggal_masuk
    );


  /*
   * Jika tanggal masuk berada sebelum
   * awal periode, gunakan awal periode.
   */
  if (
    $tanggalEfektif < $tanggalMulaiPeriode
  ) {
    $tanggalEfektif =
      clone $tanggalMulaiPeriode;
  }


  /*
   * Total hari periode.
   *
   * Inclusive:
   * 10 Okt - 9 Nov = 31 hari
   */
  $totalHari =
    $tanggalMulaiPeriode
    ->diff($tanggalSelesaiPeriode)
    ->days + 1;


  /*
   * Hari yang masih tersisa.
   */
  $hariTersisa =
    $tanggalEfektif
    ->diff($tanggalSelesaiPeriode)
    ->days + 1;


  if ($hariTersisa < 0) {
    $hariTersisa = 0;
  }


  /*
   * Selisih harga bulanan.
   */
  $selisih =
    $hargaBaruTotal - $hargaLama;


  /*
   * Penyesuaian prorata.
   */
  $penyesuaian =
    round(
      $selisih *
        ($hariTersisa / $totalHari),
      2
    );


  /*
   * Update periode menjadi kondisi
   * terbaru.
   */
  updateJumlahDanHargaPeriode(
    $periode['id_periode'],
    $jumlahBaru,
    $hargaBaruTotal
  );


  /*
   * Jika ada selisih, catat sebagai
   * penyesuaian tagihan.
   */
  if ($penyesuaian > 0) {

    $id_tagihan =
      getOrCreateTagihanPeriode(
        $periode['id_periode']
      );

    buatPenyesuaianTagihan(
      $id_tagihan,
      $id_penghuni,
      'tambah',
      $penyesuaian,
      $tanggal_masuk,
      "Penyesuaian harga kamar menjadi {$jumlahBaru} orang"
    );
  }
}
function updateJumlahDanHargaPeriode(
  $id_periode,
  $jumlah_orang,
  $harga_total
) {
  $conn = db();

  $stmt = $conn->prepare("
    UPDATE periode_sewa
    SET
      jumlah_orang = ?,
      harga_total = ?
    WHERE id_periode = ?
      AND status = 'aktif'
  ");

  $stmt->bind_param(
    'idi',
    $jumlah_orang,
    $harga_total,
    $id_periode
  );

  $result =
    $stmt->execute();

  $error =
    $stmt->error;

  $stmt->close();

  if (!$result) {
    throw new Exception(
      'Gagal memperbarui periode sewa: ' . $error
    );
  }

  return true;
}
