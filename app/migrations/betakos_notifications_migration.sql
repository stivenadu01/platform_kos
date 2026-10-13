-- Pusat notifikasi BetaKos. Jalankan pada database proyek yang dipilih.
CREATE TABLE IF NOT EXISTS notifikasi (
 id_notifikasi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 id_user BIGINT UNSIGNED NOT NULL,
 event_key CHAR(64) NOT NULL,
 judul VARCHAR(180) NOT NULL,
 pesan VARCHAR(500) NOT NULL,
 tujuan VARCHAR(255) NOT NULL,
 tanggal DATETIME NOT NULL,
 dibaca_pada DATETIME NULL,
 PRIMARY KEY (id_notifikasi),
 UNIQUE KEY uq_notifikasi_event (id_user,event_key),
 KEY idx_notifikasi_user (id_user,dibaca_pada,id_notifikasi),
 CONSTRAINT fk_notifikasi_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS notifikasi_perangkat (
 id_notifikasi BIGINT UNSIGNED NOT NULL,
 perangkat CHAR(36) NOT NULL,
 diklaim_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (id_notifikasi,perangkat),
 CONSTRAINT fk_notifikasi_perangkat FOREIGN KEY (id_notifikasi) REFERENCES notifikasi(id_notifikasi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
