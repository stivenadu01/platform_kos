-- BetaKos Phase 5: multi-duration subscription packages and renewal pricing.
-- Safe to run after the existing subscription foundation.

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'paket_langganan'
    AND column_name = 'harga_perpanjangan'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE paket_langganan ADD COLUMN harga_perpanjangan DECIMAL(12,2) NOT NULL DEFAULT 15000 AFTER harga_bulanan',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Paket pengenalan pertama kali. Renewal memakai harga_perpanjangan.
UPDATE paket_langganan
SET nama = 'Pro Bulanan',
    harga_bulanan = 0,
    harga_perpanjangan = 15000,
    durasi_bulan = 1,
    deskripsi = 'Paket Pro 1 bulan gratis untuk pelanggan baru. Perpanjangan Rp15.000/bulan.'
WHERE kode = 'pro';

INSERT INTO paket_langganan
  (kode, nama, harga_bulanan, harga_perpanjangan, durasi_bulan, deskripsi, fitur_json, status)
SELECT 'pro_6_bulan', 'Pro 6 Bulan', 50000, 75000, 6,
       'Paket Pro 6 bulan. Harga awal Rp50.000 (±Rp8.333/bulan), perpanjangan Rp75.000 (Rp12.500/bulan).',
       fitur_json, 'aktif'
FROM paket_langganan
WHERE kode = 'pro'
  AND NOT EXISTS (SELECT 1 FROM paket_langganan WHERE kode = 'pro_6_bulan');

INSERT INTO paket_langganan
  (kode, nama, harga_bulanan, harga_perpanjangan, durasi_bulan, deskripsi, fitur_json, status)
SELECT 'pro_1_tahun', 'Pro 1 Tahun', 100000, 120000, 12,
       'Paket Pro 1 tahun. Harga awal Rp100.000 (±Rp8.333/bulan), perpanjangan Rp120.000 (Rp10.000/bulan).',
       fitur_json, 'aktif'
FROM paket_langganan
WHERE kode = 'pro'
  AND NOT EXISTS (SELECT 1 FROM paket_langganan WHERE kode = 'pro_1_tahun');

-- Sinkronkan harga renewal secara eksplisit agar database lama juga mengikuti harga terbaru.
UPDATE paket_langganan SET harga_perpanjangan = 15000 WHERE kode = 'pro';
UPDATE paket_langganan SET harga_perpanjangan = 75000 WHERE kode = 'pro_6_bulan';
UPDATE paket_langganan SET harga_perpanjangan = 120000 WHERE kode = 'pro_1_tahun';

-- Normalisasi paket agar hanya tiga pilihan Pro yang ditawarkan pada checkout.
UPDATE paket_langganan SET status = 'nonaktif' WHERE kode NOT IN ('pro', 'pro_6_bulan', 'pro_1_tahun') AND kode LIKE 'pro%';
