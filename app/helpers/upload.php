<?php

/**
 * Validates and stores a real raster image in public/uploads.
 * The MIME type is verified from file contents, not only the filename.
 */
function uploadImageGeneral($file, $folder, $filename = null, $maxSizeMB = 10)
{
  if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new Exception('File tidak valid atau rusak', 400);
  }

  if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) {
    throw new Exception('File upload tidak valid', 400);
  }

  if ($file['size'] <= 0 || $file['size'] > $maxSizeMB * 1024 * 1024) {
    throw new Exception("Ukuran gambar maksimum {$maxSizeMB}MB", 413);
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($allowed[$mime])) {
    throw new Exception('Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.', 422);
  }

  if (@getimagesize($file['tmp_name']) === false) {
    throw new Exception('File bukan gambar yang valid', 422);
  }

  $folder = trim($folder, '/');
  if ($folder === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $folder)) {
    throw new Exception('Folder upload tidak valid', 500);
  }

  $baseDir = ROOT_PATH . '/public/uploads/';
  $targetDir = $baseDir . $folder;

  if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
    throw new Exception('Gagal menyiapkan direktori upload', 500);
  }

  // Always generate a random server-side filename; never trust user filenames.
  $safeName = $filename && preg_match('/^[A-Za-z0-9_-]+$/', $filename)
    ? $filename
    : strtoupper($folder) . '_' . bin2hex(random_bytes(16));

  $extension = $allowed[$mime];
  $dest = $targetDir . '/' . $safeName . '.' . $extension;
  $dbPath = '/' . $folder . '/' . $safeName . '.' . $extension;

  if (!move_uploaded_file($file['tmp_name'], $dest)) {
    throw new Exception('Gagal menyimpan file', 500);
  }

  @chmod($dest, 0644);
  return $dbPath;
}
