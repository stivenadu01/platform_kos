<?php

function generateNomorTagihan()
{
  return 'TAG-' .
    date('YmdHis') .
    '-' .
    strtoupper(
      substr(
        bin2hex(random_bytes(3)),
        0,
        6
      )
    );
}
function buatTagihanAwalPeriode(
  $id_periode
) {
  $conn = db();

  /*
   * Cek apakah tagihan sudah ada.
   */
  $stmt = $conn->prepare("
    SELECT id_tagihan
    FROM tagihan
    WHERE id_periode = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_periode
  );

  $stmt->execute();

  $existing =
    $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if ($existing) {
    return $existing['id_tagihan'];
  }


  /*
   * Ambil periode.
   */
  $stmt = $conn->prepare("
    SELECT
      id_periode,
      tanggal_mulai,
      tanggal_jatuh_tempo,
      harga_total
    FROM periode_sewa
    WHERE id_periode = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_periode
  );

  $stmt->execute();

  $periode =
    $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();


  if (!$periode) {
    throw new Exception(
      'Periode sewa tidak ditemukan.'
    );
  }


  $nomor =
    generateNomorTagihan();

  $tanggalTerbit =
    $periode['tanggal_mulai'];

  $tanggalJatuhTempo =
    $periode['tanggal_jatuh_tempo'];

  $total =
    (float) $periode['harga_total'];


  $stmt = $conn->prepare("
    INSERT INTO tagihan (
      id_periode,
      nomor_tagihan,
      tanggal_terbit,
      tanggal_jatuh_tempo,
      total_tagihan,
      total_dibayar,
      status
    )
    VALUES (?, ?, ?, ?, ?, 0, 'belum_lunas')
  ");

  $stmt->bind_param(
    'isssd',
    $id_periode,
    $nomor,
    $tanggalTerbit,
    $tanggalJatuhTempo,
    $total
  );

  $result =
    $stmt->execute();

  $id_tagihan =
    $stmt->insert_id;

  $error =
    $stmt->error;

  $stmt->close();


  if (!$result) {
    throw new Exception(
      'Gagal membuat tagihan: ' . $error
    );
  }


  return $id_tagihan;
}

function getOrCreateTagihanPeriode(
  $id_periode
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT id_tagihan
    FROM tagihan
    WHERE id_periode = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_periode
  );

  $stmt->execute();

  $data =
    $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();


  if ($data) {
    return (int) $data['id_tagihan'];
  }


  return (int) buatTagihanAwalPeriode(
    $id_periode
  );
}

function buatPenyesuaianTagihan(
  $id_tagihan,
  $id_penghuni,
  $jenis,
  $jumlah,
  $tanggal_efektif,
  $alasan
) {
  $conn = db();

  if (
    !in_array(
      $jenis,
      ['tambah', 'kurang'],
      true
    )
  ) {
    throw new Exception(
      'Jenis penyesuaian tidak valid.'
    );
  }

  if ((float) $jumlah <= 0) {
    throw new Exception(
      'Jumlah penyesuaian harus lebih besar dari 0.'
    );
  }

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
    'iisdss',
    $id_tagihan,
    $id_penghuni,
    $jenis,
    $jumlah,
    $tanggal_efektif,
    $alasan
  );

  $result =
    $stmt->execute();

  $error =
    $stmt->error;

  $stmt->close();

  if (!$result) {
    throw new Exception(
      'Gagal membuat penyesuaian tagihan: ' .
        $error
    );
  }

  /*
   * Hitung ulang total tagihan.
   */
  hitungUlangTotalTagihan(
    $id_tagihan
  );

  return true;
}

function hitungUlangTotalTagihan(
  $id_tagihan
) {
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      total_tagihan
    FROM tagihan
    WHERE id_tagihan = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_tagihan
  );

  $stmt->execute();

  $tagihan =
    $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  if (!$tagihan) {
    throw new Exception(
      'Tagihan tidak ditemukan.'
    );
  }


  /*
   * Ambil total penyesuaian.
   */
  $stmt = $conn->prepare("
    SELECT
      COALESCE(
        SUM(
          CASE
            WHEN jenis = 'tambah'
              THEN jumlah
            WHEN jenis = 'kurang'
              THEN -jumlah
            ELSE 0
          END
        ),
        0
      ) AS total_penyesuaian

    FROM penyesuaian_tagihan

    WHERE id_tagihan = ?
  ");

  $stmt->bind_param(
    'i',
    $id_tagihan
  );

  $stmt->execute();

  $penyesuaian =
    (float) (
      $stmt
        ->get_result()
        ->fetch_assoc()['total_penyesuaian'] ?? 0
    );

  $stmt->close();


  /*
   * Total dasar + adjustment.
   */
  $total =
    (float) $tagihan['total_tagihan'] +
    $penyesuaian;


  if ($total < 0) {
    $total = 0;
  }


  /*
   * Status tagihan.
   */
  $stmt = $conn->prepare("
    SELECT total_dibayar
    FROM tagihan
    WHERE id_tagihan = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    'i',
    $id_tagihan
  );

  $stmt->execute();

  $dibayar =
    (float) (
      $stmt
        ->get_result()
        ->fetch_assoc()['total_dibayar'] ?? 0
    );

  $stmt->close();


  if ($dibayar >= $total) {
    $status = 'lunas';
  } elseif ($dibayar > 0) {
    $status = 'sebagian';
  } else {
    $status = 'belum_lunas';
  }


  $stmt = $conn->prepare("
    UPDATE tagihan
    SET
      total_tagihan = ?,
      status = ?
    WHERE id_tagihan = ?
  ");

  $stmt->bind_param(
    'dsi',
    $total,
    $status,
    $id_tagihan
  );

  $result =
    $stmt->execute();

  $stmt->close();


  if (!$result) {
    throw new Exception(
      'Gagal memperbarui total tagihan.'
    );
  }

  return $total;
}
