<?php

/**
 * Lokasi pilihan yang ditampilkan pada picker pencarian publik.
 *
 * Kampus diambil dari data kampus agar perubahan dari modul admin
 * otomatis tercermin di halaman publik. Area dan tempat penting
 * merupakan shortcut populer untuk wilayah Kota Kupang.
 */
function getPublicLocationPresets(): array
{
  model('Kampus');

  $kampus = [];
  try {
    $rows = getKampusUntukHome(12);
    foreach ($rows as $row) {
      if (!isset($row['latitude'], $row['longitude'])) continue;

      $kampus[] = [
        'nama' => trim((string)($row['nama_kampus'] ?? '')),
        'label' => trim((string)($row['nama_kampus'] ?? '')),
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'subtitle' => 'Cari kos di sekitar kampus',
      ];
    }
  } catch (Throwable $e) {
    // Fallback agar picker publik tetap usable bila data kampus belum tersedia.
  }

  if (!$kampus) {
    $kampus = [
      ['nama' => 'Politeknik Negeri Kupang', 'label' => 'Politeknik Negeri Kupang', 'latitude' => -10.1513944, 'longitude' => 123.6676240, 'subtitle' => 'Cari kos di sekitar kampus'],
      ['nama' => 'Universitas Nusa Cendana', 'label' => 'Universitas Nusa Cendana', 'latitude' => -10.1544480, 'longitude' => 123.6588310, 'subtitle' => 'Cari kos di sekitar kampus'],
      ['nama' => 'Universitas Muhammadiyah Kupang', 'label' => 'Universitas Muhammadiyah Kupang', 'latitude' => -10.1585622, 'longitude' => 123.6197763, 'subtitle' => 'Cari kos di sekitar kampus'],
      ['nama' => 'STIKOM Uyelindo Kupang', 'label' => 'STIKOM Uyelindo', 'latitude' => -10.1629100, 'longitude' => 123.6238010, 'subtitle' => 'Cari kos di sekitar kampus'],
      ['nama' => 'Universitas Citra Bangsa', 'label' => 'Universitas Citra Bangsa', 'latitude' => -10.1626807, 'longitude' => 123.6236238, 'subtitle' => 'Cari kos di sekitar kampus'],
      ['nama' => 'Poltekkes Kemenkes Kupang', 'label' => 'Poltekkes Kemenkes Kupang', 'latitude' => -10.1581806, 'longitude' => 123.6397611, 'subtitle' => 'Cari kos di sekitar kampus'],
    ];
  }

  return [
    'kampus' => array_slice($kampus, 0, 8),
    'area' => [
      ['nama' => 'Oesapa, Kupang', 'label' => 'Oesapa', 'latitude' => -10.1588, 'longitude' => 123.6536, 'subtitle' => 'Area populer mahasiswa'],
      ['nama' => 'Kelapa Lima, Kupang', 'label' => 'Kelapa Lima', 'latitude' => -10.1517, 'longitude' => 123.6200, 'subtitle' => 'Cari kos di area ini'],
      ['nama' => 'Oebobo, Kupang', 'label' => 'Oebobo', 'latitude' => -10.1690, 'longitude' => 123.6040, 'subtitle' => 'Cari kos di area ini'],
      ['nama' => 'Penfui, Kupang', 'label' => 'Penfui', 'latitude' => -10.1635, 'longitude' => 123.6680, 'subtitle' => 'Area sekitar kampus'],
      ['nama' => 'Liliba, Kupang', 'label' => 'Liliba', 'latitude' => -10.1708, 'longitude' => 123.6422, 'subtitle' => 'Cari kos di area ini'],
      ['nama' => 'Naikoten, Kupang', 'label' => 'Naikoten', 'latitude' => -10.1810, 'longitude' => 123.6007, 'subtitle' => 'Cari kos di area ini'],
      ['nama' => 'Kayu Putih, Kupang', 'label' => 'Kayu Putih', 'latitude' => -10.1738, 'longitude' => 123.6168, 'subtitle' => 'Cari kos di area ini'],
      ['nama' => 'Namosain, Kupang', 'label' => 'Namosain', 'latitude' => -10.1847, 'longitude' => 123.5736, 'subtitle' => 'Cari kos di area ini'],
    ],
    'penting' => [
      ['nama' => 'Bandar Udara El Tari, Kupang', 'label' => 'Bandara El Tari', 'latitude' => -10.1716, 'longitude' => 123.6711, 'subtitle' => 'Tempat penting'],
      ['nama' => 'Pelabuhan Tenau, Kupang', 'label' => 'Pelabuhan Tenau', 'latitude' => -10.1928, 'longitude' => 123.5279, 'subtitle' => 'Tempat penting'],
      ['nama' => 'Terminal Oebobo, Kupang', 'label' => 'Terminal Oebobo', 'latitude' => -10.1688, 'longitude' => 123.6063, 'subtitle' => 'Transportasi'],
      ['nama' => 'RSUD W.Z. Johannes Kupang', 'label' => 'RSUD W.Z. Johannes', 'latitude' => -10.1676, 'longitude' => 123.5988, 'subtitle' => 'Tempat penting'],
      ['nama' => 'Lippo Plaza Kupang', 'label' => 'Lippo Plaza Kupang', 'latitude' => -10.1618, 'longitude' => 123.6074, 'subtitle' => 'Tempat penting'],
    ],
  ];
}
