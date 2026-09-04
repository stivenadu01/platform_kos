/* =========================================================
   GOOGLE AUTHENTICATION
   Menyimpan subject (sub) unik dari Google.
   Token/password Google tidak disimpan.

   Jalankan migration ini satu kali pada database BetaKos.
   Script dibuat aman untuk dijalankan ulang.
   ========================================================= */

SET @db_name = DATABASE();

SET @has_google_sub = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'users'
    AND column_name = 'google_sub'
);

SET @sql = IF(
  @has_google_sub = 0,
  'ALTER TABLE users ADD COLUMN google_sub VARCHAR(255) NULL AFTER email',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_google_unique = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'users'
    AND index_name = 'uq_users_google_sub'
);

SET @sql = IF(
  @has_google_unique = 0,
  'ALTER TABLE users ADD UNIQUE KEY uq_users_google_sub (google_sub)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
