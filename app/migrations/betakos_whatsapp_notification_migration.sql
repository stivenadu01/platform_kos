START TRANSACTION;

CREATE TABLE IF NOT EXISTS `notifikasi_whatsapp` (
  `id_notifikasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pemilik` bigint unsigned DEFAULT NULL,
  `id_penghuni` bigint unsigned DEFAULT NULL,
  `id_tagihan` bigint unsigned DEFAULT NULL,
  `id_pembayaran` bigint unsigned DEFAULT NULL,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_tujuan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','queued','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fonnte',
  `provider_request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idempotency_key` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah_percobaan` tinyint unsigned NOT NULL DEFAULT 0,
  `error_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notifikasi`),
  UNIQUE KEY `uq_notifikasi_whatsapp_idempotency` (`idempotency_key`),
  KEY `idx_notifikasi_whatsapp_tagihan` (`id_tagihan`),
  KEY `idx_notifikasi_whatsapp_pembayaran` (`id_pembayaran`),
  KEY `idx_notifikasi_whatsapp_status_created` (`status`,`created_at`),
  KEY `idx_notifikasi_whatsapp_pemilik` (`id_pemilik`),
  CONSTRAINT `fk_notifikasi_whatsapp_pemilik` FOREIGN KEY (`id_pemilik`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_notifikasi_whatsapp_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_notifikasi_whatsapp_tagihan` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_notifikasi_whatsapp_pembayaran` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
