-- BetaKos Phase 3: manual Pro subscription payment + admin verification.
-- Run after subscription_foundation.sql.
-- This migration intentionally keeps payment data separate from penghuni billing.

ALTER TABLE langganan
  MODIFY COLUMN status ENUM('menunggu','aktif','berakhir','dibatalkan')
  NOT NULL DEFAULT 'aktif';

CREATE TABLE IF NOT EXISTS pembayaran_langganan (
  id_pembayaran_langganan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  nomor_order VARCHAR(60) NOT NULL UNIQUE,
  id_langganan BIGINT UNSIGNED NOT NULL,
  id_paket_langganan INT UNSIGNED NOT NULL,
  id_pemilik BIGINT UNSIGNED NOT NULL,

  jenis_pembayaran ENUM('baru','renewal') NOT NULL DEFAULT 'baru',
  nominal DECIMAL(12,2) NOT NULL,

  metode_pembayaran ENUM('transfer_bank','e_wallet') NOT NULL,

  tanggal_pembayaran DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  bukti_pembayaran VARCHAR(255) NULL,

  status ENUM('menunggu','diverifikasi','ditolak','dibatalkan')
    NOT NULL DEFAULT 'menunggu',

  id_admin_verifikasi BIGINT UNSIGNED NULL,
  tanggal_verifikasi DATETIME NULL,
  catatan_admin TEXT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_pembayaran_langganan_langganan
    FOREIGN KEY (id_langganan)
    REFERENCES langganan(id_langganan)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT fk_pembayaran_langganan_paket
    FOREIGN KEY (id_paket_langganan)
    REFERENCES paket_langganan(id_paket_langganan)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT fk_pembayaran_langganan_pemilik
    FOREIGN KEY (id_pemilik)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT fk_pembayaran_langganan_admin
    FOREIGN KEY (id_admin_verifikasi)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT chk_pembayaran_langganan_nominal
    CHECK (nominal > 0),

  INDEX idx_pl_pemilik_status (id_pemilik, status),
  INDEX idx_pl_langganan (id_langganan),
  INDEX idx_pl_status_created (status, created_at),
  INDEX idx_pl_admin (id_admin_verifikasi)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
