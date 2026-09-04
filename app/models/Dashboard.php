<?php

function getDashboardPemilik($id_pemilik, $includeFinance = false)
{
  $conn = db();

  $stmt = $conn->prepare("\n    SELECT\n      COUNT(DISTINCT k.id_kos) AS total_kos,\n      COUNT(DISTINCT km.id_kamar) AS total_kamar,\n      COUNT(DISTINCT CASE WHEN km.status = 'terisi' THEN km.id_kamar END) AS kamar_terisi,\n      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia,
      COUNT(DISTINCT CASE WHEN km.status = 'tidak_tersedia' THEN km.id_kamar END) AS kamar_tidak_tersedia,\n      COUNT(DISTINCT CASE WHEN p.status = 'aktif' THEN p.id_penghuni END) AS penghuni_aktif\n    FROM kos k\n    LEFT JOIN kamar km ON km.id_kos = k.id_kos\n    LEFT JOIN penghuni p ON p.id_kamar = km.id_kamar\n    WHERE k.id_pemilik = ?\n  ");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $summary = $stmt->get_result()->fetch_assoc() ?: [];
  $stmt->close();

  $finance = ['tagihan_belum_lunas' => 0, 'total_piutang' => 0];
  $income = ['pendapatan_bulan' => 0];
  $tagihan = [];

  if ($includeFinance) {
    $stmt = $conn->prepare("\n      SELECT\n        COUNT(*) AS tagihan_belum_lunas,\n        COALESCE(SUM(GREATEST(t.total_tagihan - t.total_dibayar, 0)), 0) AS total_piutang\n      FROM tagihan t\n      INNER JOIN kamar km ON km.id_kamar = t.id_kamar\n      INNER JOIN kos k ON k.id_kos = km.id_kos\n      WHERE k.id_pemilik = ?\n        AND t.status IN ('belum_lunas', 'sebagian')\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $finance = $stmt->get_result()->fetch_assoc() ?: $finance;
    $stmt->close();

    $stmt = $conn->prepare("\n      SELECT\n        COALESCE(SUM(pb.jumlah), 0) AS pendapatan_bulan\n      FROM pembayaran pb\n      INNER JOIN tagihan t ON t.id_tagihan = pb.id_tagihan\n      INNER JOIN kamar km ON km.id_kamar = t.id_kamar\n      INNER JOIN kos k ON k.id_kos = km.id_kos\n      WHERE k.id_pemilik = ?\n        AND pb.status = 'berhasil'\n        AND pb.tanggal_bayar >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')\n        AND pb.tanggal_bayar < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $income = $stmt->get_result()->fetch_assoc() ?: $income;
    $stmt->close();

    $stmt = $conn->prepare("\n      SELECT\n        t.id_tagihan,\n        t.nomor_tagihan,\n        t.tanggal_jatuh_tempo,\n        t.total_tagihan,\n        t.total_dibayar,\n        GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan,\n        t.status,\n        km.nomor_kamar,\n        k.nama_kos\n      FROM tagihan t\n      INNER JOIN kamar km ON km.id_kamar = t.id_kamar\n      INNER JOIN kos k ON k.id_kos = km.id_kos\n      WHERE k.id_pemilik = ?\n        AND t.status IN ('belum_lunas', 'sebagian')\n      ORDER BY t.tanggal_jatuh_tempo ASC, t.created_at DESC\n      LIMIT 5\n    ");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $tagihan[] = $row;
    }
    $stmt->close();
  }

  return [
    'is_pro' => (bool) $includeFinance,
    'summary' => [
      'total_kos' => (int)($summary['total_kos'] ?? 0),
      'total_kamar' => (int)($summary['total_kamar'] ?? 0),
      'kamar_terisi' => (int)($summary['kamar_terisi'] ?? 0),
      'kamar_tersedia' => (int)($summary['kamar_tersedia'] ?? 0),
      'kamar_tidak_tersedia' => (int)($summary['kamar_tidak_tersedia'] ?? 0),
      'penghuni_aktif' => (int)($summary['penghuni_aktif'] ?? 0),
      'tagihan_belum_lunas' => (int)($finance['tagihan_belum_lunas'] ?? 0),
      'total_piutang' => (float)($finance['total_piutang'] ?? 0),
      'pendapatan_bulan' => (float)($income['pendapatan_bulan'] ?? 0),
    ],
    'tagihan_terdekat' => $tagihan,
  ];
}
