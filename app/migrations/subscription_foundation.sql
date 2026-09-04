-- BetaKos Monetisasi - Phase 1: Subscription Foundation
-- Scope: paket langganan, langganan per pemilik, dan riwayat.
-- Tidak mengubah status pada tabel kos.

CREATE TABLE IF NOT EXISTS paket_langganan (
  id_paket_langganan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  harga_bulanan DECIMAL(12,2) NOT NULL DEFAULT 0,
  durasi_bulan TINYINT UNSIGNED NOT NULL DEFAULT 1,
  deskripsi TEXT NULL,
  fitur_json JSON NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS langganan (
  id_langganan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pemilik BIGINT UNSIGNED NOT NULL,
  id_paket_langganan INT UNSIGNED NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_berakhir DATE NOT NULL,
  status ENUM('aktif','berakhir','dibatalkan') NOT NULL DEFAULT 'aktif',
  catatan VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_langganan_pemilik_status (id_pemilik, status),
  INDEX idx_langganan_berakhir (tanggal_berakhir),

  CONSTRAINT fk_langganan_pemilik
    FOREIGN KEY (id_pemilik)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

  CONSTRAINT fk_langganan_paket
    FOREIGN KEY (id_paket_langganan)
    REFERENCES paket_langganan(id_paket_langganan)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO paket_langganan
  (kode, nama, harga_bulanan, durasi_bulan, deskripsi, fitur_json, status)
VALUES
  (
    'pro',
    'BetaKos Pro',
    0,
    1,
    'Fitur manajemen operasional dan keuangan untuk pemilik kos.',
    JSON_ARRAY(
      'Kelola penghuni',
      'Tagihan penghuni',
      'Pencatatan pembayaran',
      'Riwayat penghuni',
      'Ringkasan keuangan'
    ),
    'aktif'
  )
ON DUPLICATE KEY UPDATE
  nama = VALUES(nama),
  harga_bulanan = VALUES(harga_bulanan),
  durasi_bulan = VALUES(durasi_bulan),
  deskripsi = VALUES(deskripsi),
  fitur_json = VALUES(fitur_json),
  status = VALUES(status);
