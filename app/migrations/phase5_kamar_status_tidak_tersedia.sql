-- Phase 5: tambahkan status manual 'tidak_tersedia' pada kamar.
-- Status 'terisi' tetap dikendalikan oleh penghuni aktif dan tidak boleh diatur manual.
ALTER TABLE kamar
  MODIFY COLUMN status ENUM('tersedia', 'terisi', 'tidak_tersedia', 'perbaikan', 'nonaktif') NOT NULL DEFAULT 'tersedia';
