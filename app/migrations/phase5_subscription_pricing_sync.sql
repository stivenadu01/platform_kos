-- BetaKos Phase 5: synchronize subscription package pricing.
-- Run this after phase5_subscription_package_pricing.sql on an existing database.
-- Idempotent: safe to run more than once.

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

UPDATE paket_langganan
SET harga_bulanan = 0,
    harga_perpanjangan = 15000,
    durasi_bulan = 1,
    nama = 'Pro Bulanan',
    deskripsi = 'Paket Pro 1 bulan gratis untuk pelanggan baru. Perpanjangan Rp15.000/bulan.'
WHERE kode = 'pro';

UPDATE paket_langganan
SET harga_bulanan = 50000,
    harga_perpanjangan = 75000,
    durasi_bulan = 6,
    nama = 'Pro 6 Bulan',
    deskripsi = 'Paket Pro 6 bulan. Harga awal Rp50.000 (±Rp8.333/bulan), perpanjangan Rp75.000 (Rp12.500/bulan).',
    status = 'aktif'
WHERE kode = 'pro_6_bulan';

UPDATE paket_langganan
SET harga_bulanan = 100000,
    harga_perpanjangan = 120000,
    durasi_bulan = 12,
    nama = 'Pro 1 Tahun',
    deskripsi = 'Paket Pro 1 tahun. Harga awal Rp100.000 (±Rp8.333/bulan), perpanjangan Rp120.000 (Rp10.000/bulan).',
    status = 'aktif'
WHERE kode = 'pro_1_tahun';

-- Paket Pro lain tidak ditawarkan di checkout.
UPDATE paket_langganan
SET status = 'nonaktif'
WHERE kode NOT IN ('pro', 'pro_6_bulan', 'pro_1_tahun')
  AND kode LIKE 'pro%';
