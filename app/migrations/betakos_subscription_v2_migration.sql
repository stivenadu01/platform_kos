-- BetaKos Pro v2: paket 6/12/24 bulan dan promo 6 bulan untuk kos terverifikasi.
-- Riwayat lama sengaja dihapus sesuai keputusan produk.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pembayaran_langganan;
TRUNCATE TABLE langganan;
TRUNCATE TABLE paket_langganan;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE paket_langganan
  DROP COLUMN harga_perpanjangan,
  DROP COLUMN fitur_json;

ALTER TABLE langganan
  ADD COLUMN promo_digunakan_at DATETIME NULL AFTER catatan;

ALTER TABLE pembayaran_langganan
  ADD COLUMN harga_bulanan_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER nominal,
  ADD COLUMN durasi_bulan_snapshot TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER harga_bulanan_snapshot,
  ADD COLUMN harga_normal DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER durasi_bulan_snapshot,
  ADD COLUMN promo_bulan TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER harga_normal,
  ADD COLUMN diskon_promo DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER promo_bulan,
  DROP CHECK chk_pembayaran_langganan_nominal,
  ADD CONSTRAINT chk_pembayaran_langganan_nominal CHECK (nominal >= 0);

INSERT INTO paket_langganan (kode, nama, harga_bulanan, durasi_bulan, deskripsi, status) VALUES
  ('pro_6_bulan', 'Pro 6 Bulan', 15000, 6, 'Pilihan ringan untuk mulai menggunakan seluruh fitur BetaKos Pro.', 'aktif'),
  ('pro_1_tahun', 'Pro 1 Tahun', 12500, 12, 'Lebih hemat untuk pengelolaan kos selama satu tahun.', 'aktif'),
  ('pro_2_tahun', 'Pro 2 Tahun', 10000, 24, 'Harga bulanan terbaik untuk pengelolaan jangka panjang.', 'aktif');
