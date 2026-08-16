<?php

/**
 * Upload + konversi + kompres gambar ke direktori tertentu.
 *
 * @param array  $file        $_FILES['...']
 * @param string $folder      contoh: "produk", "hero"
 * @param string $filename    jika kosong → generate otomatis
 * @param int    $maxSizeMB   ukuran maksimal file (default 10MB)
 *
 * @return string  path yang disimpan di database (misal: "/produk/PRD_123.png")
 */
function uploadImageGeneral($file, $folder, $filename = null, $maxSizeMB = 10)
{
  if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("File tidak valid atau rusak", 400);
  }

  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed)) {
    throw new Exception("Format gambar tidak didukung", 422);
  }

  if ($file['size'] > $maxSizeMB * 1024 * 1024) {
    throw new Exception("Ukuran gambar maksimum {$maxSizeMB}MB", 413);
  }

  // Lokasi upload
  $baseDir = ROOT_PATH . "/public/uploads/";
  if (!file_exists($baseDir . $folder)) {
    mkdir($baseDir . $folder, 0777, true);
  }

  // Penamaan file
  if (!$filename) {
    $filename = strtoupper($folder) . "_" . time() . "_" . rand(1000, 9999);
  }

  $dest = "{$baseDir}{$folder}/{$filename}.{$ext}";
  $dbPath = "/{$folder}/{$filename}.{$ext}";

  if (!move_uploaded_file($file['tmp_name'], $dest)) {
    throw new Exception("Gagal menyimpan file", 500);
  }

  return $dbPath;
}
