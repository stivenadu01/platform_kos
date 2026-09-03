-- BetaKos Phase 5+: Admin-managed manual subscription payment destinations.
-- Non-destructive. Run after phase3_subscription_payment.sql.

CREATE TABLE IF NOT EXISTS metode_pembayaran_langganan (
  id_metode_pembayaran INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jenis ENUM('transfer_bank','e_wallet') NOT NULL,
  nama_provider VARCHAR(80) NOT NULL,
  nomor_tujuan VARCHAR(100) NOT NULL,
  nama_penerima VARCHAR(120) NOT NULL,
  keterangan VARCHAR(255) NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_mpl_aktif (is_aktif),
  INDEX idx_mpl_jenis (jenis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pembayaran_langganan
  ADD COLUMN id_metode_pembayaran INT UNSIGNED NULL AFTER metode_pembayaran,
  ADD COLUMN provider_pembayaran VARCHAR(80) NULL AFTER id_metode_pembayaran,
  ADD COLUMN nomor_tujuan_pembayaran VARCHAR(100) NULL AFTER provider_pembayaran,
  ADD COLUMN nama_penerima_pembayaran VARCHAR(120) NULL AFTER nomor_tujuan_pembayaran;

ALTER TABLE pembayaran_langganan
  ADD INDEX idx_pl_metode (id_metode_pembayaran);

ALTER TABLE pembayaran_langganan
  ADD CONSTRAINT fk_pl_metode_pembayaran
  FOREIGN KEY (id_metode_pembayaran)
  REFERENCES metode_pembayaran_langganan(id_metode_pembayaran)
  ON UPDATE CASCADE
  ON DELETE SET NULL;
