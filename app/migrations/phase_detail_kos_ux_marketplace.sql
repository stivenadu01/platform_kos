-- Phase: Detail Kos + UX Marketplace
-- Menambah aturan kos yang ditampilkan pada detail publik. Rerunnable.
SET @has_aturan = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'kos' AND column_name = 'aturan'
);
SET @sql = IF(@has_aturan = 0,
  'ALTER TABLE kos ADD COLUMN aturan TEXT NULL AFTER deskripsi',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
