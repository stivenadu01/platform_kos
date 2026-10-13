-- BetaKos: upgrade non-destructive untuk MariaDB bawaan XAMPP.
USE `betakos`;
ALTER TABLE langganan ADD COLUMN IF NOT EXISTS promo_digunakan_at DATETIME NULL AFTER catatan;
ALTER TABLE pembayaran_langganan
 ADD COLUMN IF NOT EXISTS harga_bulanan_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER nominal,
 ADD COLUMN IF NOT EXISTS durasi_bulan_snapshot TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER harga_bulanan_snapshot,
 ADD COLUMN IF NOT EXISTS harga_normal DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER durasi_bulan_snapshot,
 ADD COLUMN IF NOT EXISTS promo_bulan TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER harga_normal,
 ADD COLUMN IF NOT EXISTS diskon_promo DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER promo_bulan;
ALTER TABLE pembayaran_langganan DROP CONSTRAINT IF EXISTS chk_pembayaran_langganan_nominal;
ALTER TABLE pembayaran_langganan ADD CONSTRAINT chk_pembayaran_langganan_nominal CHECK (nominal >= 0);
UPDATE paket_langganan SET status='nonaktif' WHERE kode='pro';
INSERT INTO paket_langganan (kode,nama,harga_bulanan,durasi_bulan,deskripsi,status) VALUES
 ('pro_6_bulan','Pro 6 Bulan',15000,6,'Pilihan ringan untuk mulai menggunakan seluruh fitur BetaKos Pro.','aktif'),
 ('pro_1_tahun','Pro 1 Tahun',12500,12,'Lebih hemat untuk pengelolaan kos selama satu tahun.','aktif'),
 ('pro_2_tahun','Pro 2 Tahun',10000,24,'Harga bulanan terbaik untuk pengelolaan jangka panjang.','aktif')
ON DUPLICATE KEY UPDATE nama=VALUES(nama),harga_bulanan=VALUES(harga_bulanan),durasi_bulan=VALUES(durasi_bulan),deskripsi=VALUES(deskripsi),status=VALUES(status);
