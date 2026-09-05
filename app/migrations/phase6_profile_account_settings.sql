-- Phase: Profil & Pengaturan Akun
-- Menambahkan versi sesi untuk fitur Logout Semua Perangkat.
-- Jalankan sekali pada database platform_kos.

SET @has_auth_session_version := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'users'
    AND column_name = 'auth_session_version'
);

SET @sql := IF(
  @has_auth_session_version = 0,
  'ALTER TABLE users ADD COLUMN auth_session_version BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER status',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
