<?php

/**
 * Lokasi pilihan publik bersumber dari tabel lokasi_referensi.
 * Kategori selain kampus/area dipetakan ke key "penting" agar kompatibel
 * dengan location picker yang sudah ada.
 */
function getPublicLocationPresets(): array
{
  model('LokasiReferensi');
  try {
    return getLokasiReferensiPublik();
  } catch (Throwable $e) {
    error_log('Public location presets error: ' . $e->getMessage());
    return ['kampus' => [], 'area' => [], 'penting' => []];
  }
}
