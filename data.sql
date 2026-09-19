-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for platform_kos
CREATE DATABASE IF NOT EXISTS `platform_kos` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `platform_kos`;

-- Dumping structure for table platform_kos.aturan
CREATE TABLE IF NOT EXISTS `aturan` (
  `id_aturan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_aturan` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya',
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_aturan`),
  KEY `idx_aturan_kategori_status` (`kategori`,`status`,`urutan`),
  KEY `idx_aturan_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.aturan: ~18 rows (approximately)
INSERT INTO `aturan` (`id_aturan`, `nama_aturan`, `kategori`, `icon`, `deskripsi`, `urutan`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Tidak menerima pasangan', 'penghuni', 'heart-off', 'Kos tidak menerima pasangan sebagai penghuni.', 10, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(2, 'Khusus putra', 'penghuni', 'user-male', 'Kos diperuntukkan khusus penghuni putra.', 20, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(3, 'Khusus putri', 'penghuni', 'user-female', 'Kos diperuntukkan khusus penghuni putri.', 30, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(4, 'Tamu wajib lapor', 'tamu', 'user-check', 'Setiap tamu wajib melapor kepada pemilik atau pengelola.', 40, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(5, 'Tamu tidak diperbolehkan menginap', 'tamu', 'ban', 'Tamu tidak diperbolehkan menginap di kos.', 50, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(6, 'Tamu hanya diperbolehkan di area bersama', 'tamu', 'users-round', 'Tamu hanya diperbolehkan berada di area bersama yang ditentukan.', 60, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(7, 'Jam bertamu sampai pukul 22.00', 'jam', 'clock-3', 'Jam bertamu dibatasi sampai pukul 22.00.', 70, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(8, 'Jam malam mulai pukul 23.00', 'jam', 'moon-off', 'Penghuni wajib menjaga ketenangan setelah pukul 23.00.', 80, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(9, 'Wajib menjaga kebersihan', 'kebersihan', 'sparkles', 'Penghuni wajib menjaga kebersihan kamar dan area bersama.', 90, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(10, 'Wajib membuang sampah pada tempatnya', 'kebersihan', 'trash-2', 'Sampah wajib dibuang pada tempat yang telah disediakan.', 100, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(11, 'Wajib menjaga fasilitas bersama', 'kebersihan', 'clipboard-check', 'Penghuni wajib menjaga fasilitas bersama agar tetap bersih dan baik.', 110, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(12, 'Tidak diperbolehkan membawa hewan peliharaan', 'hewan', 'paw-print', 'Hewan peliharaan tidak diperbolehkan di area kos.', 120, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(13, 'Dilarang membuat keributan', 'keamanan', 'volume-x', 'Penghuni wajib menjaga ketenangan dan tidak membuat keributan.', 130, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(14, 'Dilarang membawa kompor ke kamar', 'keamanan', 'flame', 'Penggunaan atau penyimpanan kompor di kamar tidak diperbolehkan.', 140, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(15, 'Wajib mengunci kamar saat meninggalkan kamar', 'keamanan', 'lock', 'Penghuni wajib memastikan pintu kamar terkunci saat meninggalkan kamar.', 150, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(16, 'Dilarang merusak fasilitas kos', 'keamanan', 'shield', 'Penghuni dilarang merusak atau menggunakan fasilitas kos secara tidak semestinya.', 160, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(17, 'Wajib mengikuti ketentuan kos', 'umum', 'clipboard-check', 'Penghuni wajib mengikuti seluruh ketentuan yang berlaku di kos.', 170, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00'),
	(18, 'Menjaga ketertiban lingkungan kos', 'umum', 'info', 'Penghuni wajib menjaga ketertiban dan kenyamanan lingkungan kos.', 180, 'aktif', '2026-09-08 00:00:00', '2026-09-08 00:00:00');

-- Dumping structure for table platform_kos.claim_riwayat
CREATE TABLE IF NOT EXISTS `claim_riwayat` (
  `id_claim` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_penghuni` bigint unsigned NOT NULL,
  `id_user` bigint unsigned NOT NULL,
  `nik_diajukan` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan_mahasiswa` text COLLATE utf8mb4_unicode_ci,
  `catatan_pemilik` text COLLATE utf8mb4_unicode_ci,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_keputusan` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_claim`),
  UNIQUE KEY `uq_claim_penghuni` (`id_penghuni`),
  KEY `idx_claim_user` (`id_user`),
  KEY `idx_claim_status` (`status`),
  CONSTRAINT `fk_claim_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_claim_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.claim_riwayat: ~1 rows (approximately)
INSERT INTO `claim_riwayat` (`id_claim`, `id_penghuni`, `id_user`, `nik_diajukan`, `status`, `catatan_mahasiswa`, `catatan_pemilik`, `tanggal_pengajuan`, `tanggal_keputusan`, `created_at`, `updated_at`) VALUES
	(1, 5, 9, '5318928183918371', 'disetujui', '', '', '2026-09-04 11:15:38', '2026-09-04 11:16:01', '2026-09-04 03:15:38', '2026-09-04 03:16:01');

-- Dumping structure for table platform_kos.fasilitas
CREATE TABLE IF NOT EXISTS `fasilitas` (
  `id_fasilitas` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_fasilitas` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sparkles',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `kategori` enum('kos','kamar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kos',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_fasilitas`),
  UNIQUE KEY `nama_fasilitas` (`nama_fasilitas`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.fasilitas: ~55 rows (approximately)
INSERT INTO `fasilitas` (`id_fasilitas`, `nama_fasilitas`, `icon`, `status`, `kategori`, `created_at`) VALUES
	(1, 'WiFi', 'wifi', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(2, 'Router / Internet', 'router', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(3, 'Parkir Motor', 'bike', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(4, 'Parkir Mobil', 'car', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(5, 'Area Parkir', 'parking', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(6, 'CCTV', 'camera', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(7, 'Keamanan 24 Jam', 'shield-check', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(8, 'Penjaga Kos', 'user-check', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(9, 'Akses Kartu', 'credit-card', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(10, 'Akses Kunci', 'key-round', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(11, 'Dapur Bersama', 'kitchen', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(12, 'Peralatan Makan', 'utensils', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(13, 'Ruang Tamu', 'sofa', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(14, 'Ruang Jemur', 'clothesline', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(15, 'Laundry', 'laundry', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(16, 'Mesin Cuci', 'washing-machine', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(17, 'Listrik', 'zap', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(18, 'Daya / Listrik Cadangan', 'battery-charging', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(19, 'Air Bersih', 'water', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(20, 'Dispenser Bersama', 'dispenser', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(21, 'Balkon', 'balcony', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(22, 'Taman', 'tree-pine', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(23, 'Area Outdoor', 'palmtree', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(24, 'Tempat Sampah', 'trash-2', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(25, 'Daur Ulang', 'recycle', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(26, 'Area Belajar', 'book-open', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(27, 'Area Kerja', 'briefcase', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(28, 'Televisi Bersama', 'tv', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(29, 'Speaker Bersama', 'speaker', 'aktif', 'kos', '2026-09-08 00:00:00'),
	(30, 'Tempat Tidur', 'bed', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(31, 'Kasur', 'bed-double', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(32, 'Lampu Kamar', 'lamp', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(33, 'AC', 'ac', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(34, 'Kipas Angin', 'fan', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(35, 'Televisi', 'tv', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(36, 'Lemari', 'wardrobe', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(37, 'Rak Penyimpanan', 'archive', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(38, 'Meja Belajar', 'table', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(39, 'Kursi', 'chair', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(40, 'Kursi Santai', 'armchair', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(41, 'Kamar Mandi Dalam', 'bath', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(42, 'Kamar Mandi Luar', 'shower', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(43, 'Air', 'droplets', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(44, 'Dapur / Kitchen Set', 'cooking-pot', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(45, 'Kompor / Gas', 'flame', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(46, 'Kulkas', 'refrigerator', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(47, 'Microwave', 'microwave', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(48, 'Kopi / Minuman', 'coffee', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(49, 'Pakaian / Jemur', 'shirt', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(50, 'Ventilasi', 'wind', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(51, 'Cahaya Matahari', 'sun', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(52, 'Balkon Pribadi', 'balcony', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(53, 'Area Outdoor Pribadi', 'palmtree', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(54, 'Monitor', 'monitor', 'aktif', 'kamar', '2026-09-08 00:00:00'),
	(55, 'Gorden', 'curtain', 'aktif', 'kamar', '2026-09-08 00:00:00');

-- Dumping structure for table platform_kos.favorit
CREATE TABLE IF NOT EXISTS `favorit` (
  `id_favorit` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `id_kos` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_favorit`),
  UNIQUE KEY `uq_favorit` (`id_user`,`id_kos`),
  KEY `fk_favorit_kos` (`id_kos`),
  CONSTRAINT `fk_favorit_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorit_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.favorit: ~0 rows (approximately)

-- Dumping structure for table platform_kos.harga_kamar
CREATE TABLE IF NOT EXISTS `harga_kamar` (
  `id_harga` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tipe_kamar` bigint unsigned NOT NULL,
  `jumlah_orang` tinyint unsigned NOT NULL,
  `harga_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_harga`),
  UNIQUE KEY `uq_harga_jumlah_orang` (`id_tipe_kamar`,`jumlah_orang`),
  KEY `idx_harga_tipe` (`id_tipe_kamar`),
  CONSTRAINT `fk_harga_kamar` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_harga_positif` CHECK ((`harga_total` >= 0)),
  CONSTRAINT `chk_jumlah_orang_positif` CHECK ((`jumlah_orang` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.harga_kamar: ~3 rows (approximately)
INSERT INTO `harga_kamar` (`id_harga`, `id_tipe_kamar`, `jumlah_orang`, `harga_total`, `created_at`, `updated_at`) VALUES
	(15, 14, 1, 700000.00, '2026-09-04 03:01:42', '2026-09-04 03:01:42'),
	(16, 14, 2, 1000000.00, '2026-09-04 03:01:42', '2026-09-04 03:01:42'),
	(18, 13, 1, 1000000.00, '2026-09-07 14:30:56', '2026-09-07 14:30:56'),
	(19, 15, 1, 1200000.00, '2026-09-07 14:35:39', '2026-09-07 14:35:39');

-- Dumping structure for table platform_kos.kamar
CREATE TABLE IF NOT EXISTS `kamar` (
  `id_kamar` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kos` bigint unsigned NOT NULL,
  `id_tipe_kamar` bigint unsigned NOT NULL,
  `nomor_kamar` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('tersedia','terisi','tidak_tersedia','perbaikan','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tersedia',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kamar`),
  UNIQUE KEY `uq_kamar_nomor` (`id_kos`,`nomor_kamar`),
  KEY `idx_kamar_kos` (`id_kos`),
  KEY `idx_kamar_tipe` (`id_tipe_kamar`),
  KEY `idx_kamar_status` (`status`),
  KEY `idx_kamar_nomor` (`nomor_kamar`),
  CONSTRAINT `fk_kamar_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kamar_tipe` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kamar: ~15 rows (approximately)
INSERT INTO `kamar` (`id_kamar`, `id_kos`, `id_tipe_kamar`, `nomor_kamar`, `status`, `deskripsi`, `created_at`, `updated_at`) VALUES
	(67, 10, 13, '1', 'terisi', NULL, '2026-09-03 12:56:25', '2026-09-04 08:32:18'),
	(68, 10, 13, '2', 'tersedia', NULL, '2026-09-03 14:19:15', '2026-09-03 18:36:52'),
	(69, 10, 13, '3', 'tersedia', NULL, '2026-09-03 14:19:15', '2026-09-03 18:36:55'),
	(70, 10, 13, '4', 'tersedia', NULL, '2026-09-03 14:19:15', '2026-09-03 18:36:56'),
	(71, 10, 13, '5', 'tersedia', NULL, '2026-09-03 14:19:15', '2026-09-04 09:31:52'),
	(72, 11, 14, '1', 'terisi', NULL, '2026-09-04 03:02:40', '2026-09-04 03:07:11'),
	(73, 11, 14, '2', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(74, 11, 14, '3', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(75, 11, 14, '4', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(76, 11, 14, '5', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(77, 11, 14, '6', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(78, 11, 14, '7', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(79, 11, 14, '8', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(80, 11, 14, '9', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(81, 11, 14, '10', 'tersedia', NULL, '2026-09-04 03:02:40', '2026-09-04 03:02:40'),
	(82, 10, 15, '101', 'tersedia', NULL, '2026-09-07 14:46:48', '2026-09-07 14:46:48'),
	(83, 10, 15, '102', 'tersedia', NULL, '2026-09-07 14:46:48', '2026-09-07 14:46:48'),
	(84, 10, 15, '103', 'tersedia', NULL, '2026-09-07 14:46:48', '2026-09-07 14:46:48'),
	(85, 10, 15, '104', 'tersedia', NULL, '2026-09-07 14:46:48', '2026-09-07 14:46:48'),
	(86, 10, 15, '105', 'tersedia', NULL, '2026-09-07 14:46:48', '2026-09-07 14:46:48');

-- Dumping structure for table platform_kos.kos
CREATE TABLE IF NOT EXISTS `kos` (
  `id_kos` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pemilik` bigint unsigned NOT NULL,
  `nama_kos` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `google_maps_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis` enum('putra','putri','campur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','menunggu_verifikasi','aktif','ditolak','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kos`),
  KEY `idx_kos_pemilik` (`id_pemilik`),
  KEY `idx_kos_status` (`status`),
  KEY `idx_kos_jenis` (`jenis`),
  KEY `idx_kos_google_maps_url` (`google_maps_url`(191)),
  CONSTRAINT `fk_kos_pemilik` FOREIGN KEY (`id_pemilik`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kos: ~2 rows (approximately)
INSERT INTO `kos` (`id_kos`, `id_pemilik`, `nama_kos`, `alamat`, `latitude`, `longitude`, `google_maps_url`, `jenis`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
	(10, 2, 'Kost Benhapuk', 'RMX8+66X, Lasiana, Kec. Klp. Lima, Kota Kupang, Nusa Tenggara Tim.', -10.15107220, 123.66602380, 'https://www.google.com/maps/place/Kost+Benhapuk/@-10.1512382,123.6638464,15.5z/data=!4m15!1m8!3m7!1s0x2c5683deb42a06c9:0x14b1a711349277f!2sKost+Benhapuk!8m2!3d-10.1510722!4d123.6660238!10e5!16s%2Fg%2F11kqrl3_f_!3m5!1s0x2c5683deb42a06c9:0x14b1a711349277f!8m2!3d-10.1510722!4d123.6660238!16s%2Fg%2F11kqrl3_f_?entry=ttu&g_ep=EgoyMDI2MDkwMi4wIKXMDSoASAFQAw%3D%3D', 'campur', 'Green Kost Oesapa/Kelapa Lima – Hunian Nyaman, Aman, dan Strategis Sedang mencari kost nyaman dengan harga terjangkau di Kupang? Green Kost Oesapa adalah pilihan yang tepat! Keunggulan Green Kost Oesapa Harga terjangkau – hanya Rp400.000/bulan Kamar kosong tersedia– siap huni! Kamar mandi luar – tersedia 4 unit, bersih & terawat Lokasi strategis – dekat dengan Universitas Nusa Cendana (UNDANA), minimarket, warung makan, dan pusat aktivitas lainnya Keamanan terjamin – kost sudah difasilitasi pagar, sehingga lebih aman dan nyaman Fasilitas parkir – tersedia area parkir untuk penghuni Dengan lingkungan yang nyaman dan akses mudah ke berbagai tempat penting, Green Kost Oesapa adalah pilihan ideal bagi mahasiswa maupun pekerja yang mencari hunian praktis dan aman di Kupang. Tertarik? Hubungi kami sekarang dan dapatkan kamar terbaik sebelum kehabisan! Kontak Pemilik', 'aktif', '2026-09-03 12:54:40', '2026-09-06 07:48:17'),
	(11, 8, 'KOST BENEDICT 1', 'Jl. Ade Irma No.3-19, Klp. Lima, Kec. Klp. Lima, Kota Kupang, Nusa Tenggara Tim.', -10.15774180, 123.61579080, 'https://maps.app.goo.gl/3bFW2CQVtaJAHQus7', 'campur', '', 'aktif', '2026-09-04 03:00:11', '2026-09-06 07:57:18');

-- Dumping structure for table platform_kos.kos_aturan
CREATE TABLE IF NOT EXISTS `kos_aturan` (
  `id_kos` bigint unsigned NOT NULL,
  `id_aturan` bigint unsigned NOT NULL,
  PRIMARY KEY (`id_kos`,`id_aturan`),
  KEY `idx_kos_aturan_aturan` (`id_aturan`),
  CONSTRAINT `fk_kos_aturan_aturan` FOREIGN KEY (`id_aturan`) REFERENCES `aturan` (`id_aturan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kos_aturan_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kos_aturan: ~0 rows (approximately)
INSERT INTO `kos_aturan` (`id_kos`, `id_aturan`) VALUES
	(10, 1),
	(10, 4),
	(10, 5),
	(10, 14);

-- Dumping structure for table platform_kos.kos_fasilitas
CREATE TABLE IF NOT EXISTS `kos_fasilitas` (
  `id_kos` bigint unsigned NOT NULL,
  `id_fasilitas` bigint unsigned NOT NULL,
  PRIMARY KEY (`id_kos`,`id_fasilitas`),
  KEY `fk_kos_fasilitas_fasilitas` (`id_fasilitas`),
  CONSTRAINT `fk_kos_fasilitas_fasilitas` FOREIGN KEY (`id_fasilitas`) REFERENCES `fasilitas` (`id_fasilitas`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kos_fasilitas_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kos_fasilitas: ~1 rows (approximately)
INSERT INTO `kos_fasilitas` (`id_kos`, `id_fasilitas`) VALUES
	(10, 1),
	(10, 3),
	(10, 6),
	(10, 7),
	(10, 14),
	(10, 43),
	(11, 43),
	(10, 7),
	(10, 24);

-- Dumping structure for table platform_kos.kos_favorit
CREATE TABLE IF NOT EXISTS `kos_favorit` (
  `id_favorit` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `id_kos` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_favorit`),
  UNIQUE KEY `uq_kos_favorit_user_kos` (`id_user`,`id_kos`),
  KEY `idx_kos_favorit_user` (`id_user`),
  KEY `idx_kos_favorit_kos` (`id_kos`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kos_favorit: ~0 rows (approximately)
INSERT INTO `kos_favorit` (`id_favorit`, `id_user`, `id_kos`, `created_at`) VALUES
	(3, 3, 10, '2026-09-07 15:23:05');

-- Dumping structure for table platform_kos.kos_foto
CREATE TABLE IF NOT EXISTS `kos_foto` (
  `id_foto` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kos` bigint unsigned NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `is_thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_foto`),
  KEY `idx_kos_foto_kos` (`id_kos`),
  CONSTRAINT `fk_kos_foto_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.kos_foto: ~4 rows (approximately)
INSERT INTO `kos_foto` (`id_foto`, `id_kos`, `nama_file`, `urutan`, `is_thumbnail`, `created_at`) VALUES
	(7, 10, '/kos/KOS_4e3ee90a746434184435cb6d0c03b9be.png', 1, 1, '2026-09-03 12:54:59'),
	(8, 11, '/kos/KOS_32b4b8cf6f9a4dc6ce72652df0493de8.png', 1, 0, '2026-09-04 03:00:48'),
	(9, 11, '/kos/KOS_f64f47bf4e30ceada00531aa2b800049.png', 2, 0, '2026-09-05 07:32:46'),
	(10, 11, '/kos/KOS_3bf025a9a0098586ac556f69554d0833.png', 3, 1, '2026-09-05 07:34:19'),
	(11, 11, '/kos/KOS_cf7922195a23c73bec7775d282f3b6b4.png', 4, 0, '2026-09-05 07:34:46');

-- Dumping structure for table platform_kos.langganan
CREATE TABLE IF NOT EXISTS `langganan` (
  `id_langganan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pemilik` bigint unsigned NOT NULL,
  `id_paket_langganan` int unsigned NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `status` enum('menunggu','aktif','berakhir','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `catatan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_langganan`),
  KEY `idx_langganan_pemilik_status` (`id_pemilik`,`status`),
  KEY `idx_langganan_berakhir` (`tanggal_berakhir`),
  KEY `fk_langganan_paket` (`id_paket_langganan`),
  CONSTRAINT `fk_langganan_paket` FOREIGN KEY (`id_paket_langganan`) REFERENCES `paket_langganan` (`id_paket_langganan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_langganan_pemilik` FOREIGN KEY (`id_pemilik`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.langganan: ~1 rows (approximately)
INSERT INTO `langganan` (`id_langganan`, `id_pemilik`, `id_paket_langganan`, `tanggal_mulai`, `tanggal_berakhir`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
	(27, 2, 3, '2026-09-05', '2030-09-05', 'aktif', NULL, '2026-09-05 00:16:05', '2026-09-07 22:45:04');

-- Dumping structure for table platform_kos.laporan_kos
CREATE TABLE IF NOT EXISTS `laporan_kos` (
  `id_laporan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `id_kos` bigint unsigned NOT NULL,
  `id_admin` bigint unsigned DEFAULT NULL,
  `alasan` enum('informasi_tidak_sesuai','foto_tidak_sesuai','kos_sudah_tidak_tersedia','informasi_menyesatkan','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('menunggu','diproses','selesai','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_laporan`),
  KEY `idx_laporan_kos_user` (`id_user`),
  KEY `idx_laporan_kos_kos` (`id_kos`),
  KEY `idx_laporan_kos_admin` (`id_admin`),
  KEY `idx_laporan_kos_status` (`status`),
  KEY `idx_laporan_kos_created` (`created_at`),
  CONSTRAINT `fk_laporan_kos_admin` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_laporan_kos_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_laporan_kos_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.laporan_kos: ~0 rows (approximately)

-- Dumping structure for table platform_kos.lokasi_referensi
CREATE TABLE IF NOT EXISTS `lokasi_referensi` (
  `id_lokasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('kampus','area','rumah_sakit','rumah_makan','toko','pusat_perbelanjaan','transportasi','tempat_wisata') COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'map-pin',
  `alamat` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `google_maps_url` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lokasi`),
  KEY `idx_lokasi_kategori_status` (`kategori`,`status`,`urutan`),
  KEY `idx_lokasi_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.lokasi_referensi: ~35 rows (approximately)
INSERT INTO `lokasi_referensi` (`id_lokasi`, `nama`, `kategori`, `icon`, `alamat`, `latitude`, `longitude`, `google_maps_url`, `urutan`, `status`, `created_at`, `updated_at`) VALUES
	(33, 'Universitas Nusa Cendana', 'kampus', 'graduation-cap', 'Jalan Adisucipto Penfui, Manulai II, Alak, Kota Kupang', -10.15444800, 123.65883100, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Nusa%20Cendana,%20Jalan%20Adisucipto%20Penfui,%20Manulai%20II,%20Alak,%20Kota%20Kupang', 10, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(34, 'Politeknik Negeri Kupang', 'kampus', 'graduation-cap', 'Jalan Adisucipto Penfui, Manulai II, Alak, Kota Kupang', -10.15139440, 123.66762400, 'https://www.google.com/maps/search/?api=1&query=Politeknik%20Negeri%20Kupang,%20Jalan%20Adisucipto%20Penfui,%20Manulai%20II,%20Alak,%20Kota%20Kupang', 20, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(35, 'Politeknik Pertanian Negeri Kupang', 'kampus', 'graduation-cap', 'Jalan Adisucipto Penfui, Manulai II, Alak, Kota Kupang', -10.15218600, 123.67056100, 'https://www.google.com/maps/search/?api=1&query=Politeknik%20Pertanian%20Negeri%20Kupang,%20Jalan%20Adisucipto%20Penfui,%20Manulai%20II,%20Alak,%20Kota%20Kupang', 30, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(36, 'Universitas Kristen Artha Wacana', 'kampus', 'graduation-cap', 'Jalan Adisucipto 147, Oesapa, Kelapa Lima, Kota Kupang', -10.14921400, 123.65332100, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Kristen%20Artha%20Wacana,%20Jalan%20Adisucipto%20147,%20Oesapa,%20Kelapa%20Lima,%20Kota%20Kupang', 40, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(37, 'Universitas Muhammadiyah Kupang', 'kampus', 'graduation-cap', 'Jl. K.H. Ahmad Dahlan No. 17, Kayu Putih, Oebobo, Kota Kupang', -10.15856220, 123.61977630, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Muhammadiyah%20Kupang,%20Jl.%20K.H.%20Ahmad%20Dahlan%20No.%2017,%20Kayu%20Putih,%20Oebobo,%20Kota%20Kupang', 50, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(38, 'Universitas Citra Bangsa', 'kampus', 'graduation-cap', 'Jalan Manafe No. 17, Kayu Putih, Oebobo, Kota Kupang', -10.16268070, 123.62362380, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Citra%20Bangsa,%20Jalan%20Manafe%20No.%2017,%20Kayu%20Putih,%20Oebobo,%20Kota%20Kupang', 60, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(39, 'Universitas Aryasatya Deo Muri', 'kampus', 'graduation-cap', 'Jl. Amanuban RT 18 RW 04, Oebufu, Oebobo, Kota Kupang', -10.17496540, 123.62494470, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Aryasatya%20Deo%20Muri,%20Jl.%20Amanuban%20RT%2018%20RW%2004,%20Oebufu,%20Oebobo,%20Kota%20Kupang', 70, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(40, 'Universitas Persatuan Guru 1945 NTT', 'kampus', 'graduation-cap', 'Jl. P.A. Manafe, Belakang Polresta Kota Kupang No. 7, Kayu Putih, Oebobo', -10.15878630, 123.62125380, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Persatuan%20Guru%201945%20NTT,%20Jl.%20P.A.%20Manafe,%20Belakang%20Polresta%20Kota%20Kupang%20No.%207,%20Kayu%20Putih,%20Oebobo', 80, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(41, 'Universitas Katolik Widya Mandira Kupang', 'kampus', 'graduation-cap', 'Kampus Merdeka, Jl. Jend. Achmad Yani No. 50-52, Merdeka, Kota Lama, Kota Kupang', -10.16370380, 123.58956710, 'https://www.google.com/maps/search/?api=1&query=Universitas%20Katolik%20Widya%20Mandira%20Kupang,%20Kampus%20Merdeka,%20Jl.%20Jend.%20Achmad%20Yani%20No.%2050-52,%20Merdeka,%20Kota%20Lama,%20Kota%20Kupang', 90, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(42, 'Sekolah Tinggi Manajemen Informatika Komputer Uyelindo Kupang', 'kampus', 'graduation-cap', 'Jl. Perintis Kemerdekaan I, Kayu Putih/Oebufu, Oebobo, Kota Kupang', -10.16291000, 123.62380100, 'https://www.google.com/maps/search/?api=1&query=Sekolah%20Tinggi%20Manajemen%20Informatika%20Komputer%20Uyelindo%20Kupang,%20Jl.%20Perintis%20Kemerdekaan%20I,%20Kayu%20Putih/Oebufu,%20Oebobo,%20Kota%20Kupang', 100, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(43, 'STIMIK Kupang', 'kampus', 'graduation-cap', 'Jalan Adisucipto Penfui, Oesapa, Kelapa Lima, Kota Kupang', -10.14987000, 123.66063500, 'https://www.google.com/maps/search/?api=1&query=STIMIK%20Kupang,%20Jalan%20Adisucipto%20Penfui,%20Oesapa,%20Kelapa%20Lima,%20Kota%20Kupang', 110, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(44, 'Sekolah Tinggi Ilmu Manajemen Kupang', 'kampus', 'graduation-cap', 'Jalan Adisucipto Penfui, Oesapa, Kelapa Lima, Kota Kupang', -10.14991820, 123.65946350, 'https://www.google.com/maps/search/?api=1&query=Sekolah%20Tinggi%20Ilmu%20Manajemen%20Kupang,%20Jalan%20Adisucipto%20Penfui,%20Oesapa,%20Kelapa%20Lima,%20Kota%20Kupang', 120, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(45, 'Sekolah Tinggi Ilmu Ekonomi Oemathonis', 'kampus', 'graduation-cap', 'Jalan El Tari, Kelapa Lima, Kota Kupang', -10.15603080, 123.62593630, 'https://www.google.com/maps/search/?api=1&query=Sekolah%20Tinggi%20Ilmu%20Ekonomi%20Oemathonis,%20Jalan%20El%20Tari,%20Kelapa%20Lima,%20Kota%20Kupang', 130, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(46, 'Sekolah Tinggi Ilmu Ekonomi Putra Timor', 'kampus', 'graduation-cap', 'Jalan Veteran, Kelapa Lima, Kota Kupang', -10.16565200, 123.61636900, 'https://www.google.com/maps/search/?api=1&query=Sekolah%20Tinggi%20Ilmu%20Ekonomi%20Putra%20Timor,%20Jalan%20Veteran,%20Kelapa%20Lima,%20Kota%20Kupang', 140, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(47, 'Sekolah Tinggi Informatika Komputer Artha Buana', 'kampus', 'graduation-cap', 'Jl. Sam Ratulangi III No. 1, Oesapa, Kelapa Lima, Kota Kupang', -10.15572590, 123.62816560, 'https://www.google.com/maps/search/?api=1&query=Sekolah%20Tinggi%20Informatika%20Komputer%20Artha%20Buana,%20Jl.%20Sam%20Ratulangi%20III%20No.%201,%20Oesapa,%20Kelapa%20Lima,%20Kota%20Kupang', 150, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(48, 'Institut Agama Kristen Negeri Kupang', 'kampus', 'graduation-cap', 'Jalan Tajoin Tuan, Naimata, Maulafa, Kota Kupang', -10.18609460, 123.64287430, 'https://www.google.com/maps/search/?api=1&query=Institut%20Agama%20Kristen%20Negeri%20Kupang,%20Jalan%20Tajoin%20Tuan,%20Naimata,%20Maulafa,%20Kota%20Kupang', 160, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(49, 'STIKES Maranatha Kupang', 'kampus', 'graduation-cap', 'Jl. Kampung Bajawa, Baumata, Kabupaten Kupang', -10.18560980, 123.67541310, 'https://www.google.com/maps/search/?api=1&query=STIKES%20Maranatha%20Kupang,%20Jl.%20Kampung%20Bajawa,%20Baumata,%20Kabupaten%20Kupang', 170, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(50, 'Kecamatan Alak', 'area', 'map-pin', 'Kecamatan Alak, Kota Kupang, Nusa Tenggara Timur', -10.19500000, 123.56000000, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Alak,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 10, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(51, 'Kecamatan Maulafa', 'area', 'map-pin', 'Kecamatan Maulafa, Kota Kupang, Nusa Tenggara Timur', -10.18609460, 123.64287430, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Maulafa,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 20, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(52, 'Kecamatan Oebobo', 'area', 'map-pin', 'Kecamatan Oebobo, Kota Kupang, Nusa Tenggara Timur', -10.16012900, 123.60880700, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Oebobo,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 30, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(53, 'Kecamatan Kota Raja', 'area', 'map-pin', 'Kecamatan Kota Raja, Kota Kupang, Nusa Tenggara Timur', -10.17872030, 123.60169070, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Kota%20Raja,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 40, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(54, 'Kecamatan Kelapa Lima', 'area', 'map-pin', 'Kecamatan Kelapa Lima, Kota Kupang, Nusa Tenggara Timur', -10.14987000, 123.66063500, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Kelapa%20Lima,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 50, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(55, 'Kecamatan Kota Lama', 'area', 'map-pin', 'Kecamatan Kota Lama, Kota Kupang, Nusa Tenggara Timur', -10.16370380, 123.58956710, 'https://www.google.com/maps/search/?api=1&query=Kecamatan%20Kota%20Lama,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 60, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(56, 'Bandar Udara El Tari', 'transportasi', 'plane', 'Penfui, Maulafa, Kota Kupang, Nusa Tenggara Timur', -10.17777778, 123.66388889, 'https://www.google.com/maps/search/?api=1&query=Bandar%20Udara%20El%20Tari,%20Penfui,%20Maulafa,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 10, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(57, 'Pelabuhan Tenau Kupang', 'transportasi', 'plane', 'Jl. Yos Sudarso, Tenau, Alak, Kota Kupang, Nusa Tenggara Timur', -10.20525000, 123.52150000, 'https://www.google.com/maps/search/?api=1&query=Pelabuhan%20Tenau%20Kupang,%20Jl.%20Yos%20Sudarso,%20Tenau,%20Alak,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 20, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(58, 'Lippo Plaza Kupang', 'pusat_perbelanjaan', 'shopping-bag', 'Jl. Veteran, Fatululi, Oebobo, Kota Kupang, Nusa Tenggara Timur', -10.15885719, 123.61121142, 'https://www.google.com/maps/search/?api=1&query=Lippo%20Plaza%20Kupang,%20Jl.%20Veteran,%20Fatululi,%20Oebobo,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 30, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(59, 'Flobamora Mall', 'pusat_perbelanjaan', 'shopping-bag', 'Jl. W.J. Lalamentik, Oebufu, Oebobo, Kota Kupang, Nusa Tenggara Timur', -10.17259101, 123.61248529, 'https://www.google.com/maps/search/?api=1&query=Flobamora%20Mall,%20Jl.%20W.J.%20Lalamentik,%20Oebufu,%20Oebobo,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 40, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(60, 'Transmart Kupang', 'pusat_perbelanjaan', 'shopping-bag', 'Jl. W.J. Lalamentik, Fatululi, Oebobo, Kota Kupang, Nusa Tenggara Timur', -10.17006993, 123.60866263, 'https://www.google.com/maps/search/?api=1&query=Transmart%20Kupang,%20Jl.%20W.J.%20Lalamentik,%20Fatululi,%20Oebobo,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 50, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(61, 'Jabalmart Kupang', 'toko', 'shopping-basket', 'Kelapa Lima, Kota Kupang, Nusa Tenggara Timur', -10.15258250, 123.62207237, 'https://www.google.com/maps/search/?api=1&query=Jabalmart%20Kupang,%20Kelapa%20Lima,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 60, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(62, 'Pantai Lasiana', 'tempat_wisata', 'palmtree', 'Pantai Lasiana, Kelapa Lima, Kota Kupang, Nusa Tenggara Timur', -10.13195000, 123.66912000, 'https://www.google.com/maps/search/?api=1&query=Pantai%20Lasiana,%20Kelapa%20Lima,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 70, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(63, 'Taman Nostalgia', 'tempat_wisata', 'palmtree', 'Jl. Ade Irma No. 12, Kelapa Lima, Kota Kupang, Nusa Tenggara Timur', -10.15880000, 123.61637000, 'https://www.google.com/maps/search/?api=1&query=Taman%20Nostalgia,%20Jl.%20Ade%20Irma%20No.%2012,%20Kelapa%20Lima,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 80, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(64, 'Pasar Oebobo', 'toko', 'shopping-basket', 'Fatululi, Oebobo, Kota Kupang, Nusa Tenggara Timur', -10.16012900, 123.60880700, 'https://www.google.com/maps/search/?api=1&query=Pasar%20Oebobo,%20Fatululi,%20Oebobo,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 90, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(65, 'Pasar Kasih Naikoten', 'toko', 'shopping-basket', 'Jl. Kenari, Naikoten I, Kota Raja, Kota Kupang, Nusa Tenggara Timur', -10.17975851, 123.59868708, 'https://www.google.com/maps/search/?api=1&query=Pasar%20Kasih%20Naikoten,%20Jl.%20Kenari,%20Naikoten%20I,%20Kota%20Raja,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 100, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(66, 'Pasar Inpres Naikoten I', 'toko', 'shopping-basket', 'Jl. Jend. Soeharto, Naikoten I, Kota Raja, Kota Kupang, Nusa Tenggara Timur', -10.17872028, 123.60169073, 'https://www.google.com/maps/search/?api=1&query=Pasar%20Inpres%20Naikoten%20I,%20Jl.%20Jend.%20Soeharto,%20Naikoten%20I,%20Kota%20Raja,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 110, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18'),
	(67, 'RSUD S.K. Lerik', 'rumah_sakit', 'hospital', 'Kota Lama, Kota Kupang, Nusa Tenggara Timur', -10.15030000, 123.60898000, 'https://www.google.com/maps/search/?api=1&query=RSUD%20S.K.%20Lerik,%20Kota%20Lama,%20Kota%20Kupang,%20Nusa%20Tenggara%20Timur', 120, 'aktif', '2026-09-06 09:13:50', '2026-09-07 15:00:18');

-- Dumping structure for table platform_kos.metode_pembayaran_langganan
CREATE TABLE IF NOT EXISTS `metode_pembayaran_langganan` (
  `id_metode_pembayaran` int unsigned NOT NULL AUTO_INCREMENT,
  `jenis` enum('transfer_bank','e_wallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_provider` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_tujuan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_penerima` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_metode_pembayaran`),
  KEY `idx_mpl_aktif` (`is_aktif`),
  KEY `idx_mpl_jenis` (`jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.metode_pembayaran_langganan: ~2 rows (approximately)
INSERT INTO `metode_pembayaran_langganan` (`id_metode_pembayaran`, `jenis`, `nama_provider`, `nomor_tujuan`, `nama_penerima`, `keterangan`, `is_aktif`, `created_at`, `updated_at`) VALUES
	(1, 'e_wallet', 'DANA', '081338609228', 'Stiven Melkianus Adu', '', 1, '2026-09-04 00:22:01', '2026-09-04 00:22:01'),
	(2, 'transfer_bank', 'BRI', '003901139474509', 'Stiven Melkianus Adu', '', 1, '2026-09-04 00:22:40', '2026-09-04 00:22:40');

-- Dumping structure for table platform_kos.paket_langganan
CREATE TABLE IF NOT EXISTS `paket_langganan` (
  `id_paket_langganan` int unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_bulanan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_perpanjangan` decimal(12,2) NOT NULL DEFAULT '15000.00',
  `durasi_bulan` tinyint unsigned NOT NULL DEFAULT '1',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `fitur_json` json DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_paket_langganan`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.paket_langganan: ~3 rows (approximately)
INSERT INTO `paket_langganan` (`id_paket_langganan`, `kode`, `nama`, `harga_bulanan`, `harga_perpanjangan`, `durasi_bulan`, `deskripsi`, `fitur_json`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'pro', 'Pro Bulanan', 0.00, 15000.00, 1, 'Paket Pro 1 bulan gratis untuk pelanggan baru. Perpanjangan Rp15.000/bulan.', '["Kelola penghuni", "Tagihan penghuni", "Pencatatan pembayaran", "Riwayat penghuni", "Ringkasan keuangan"]', 'aktif', '2026-09-03 23:01:12', '2026-09-04 12:03:00'),
	(2, 'pro_6_bulan', 'Pro 6 Bulan', 50000.00, 75000.00, 6, 'Paket Pro 6 bulan. Harga awal Rp50.000 (±Rp8.333/bulan), perpanjangan Rp75.000 (Rp12.500/bulan).', '["Kelola penghuni", "Tagihan penghuni", "Pencatatan pembayaran", "Riwayat penghuni", "Ringkasan keuangan"]', 'aktif', '2026-09-04 00:57:43', '2026-09-04 12:03:00'),
	(3, 'pro_1_tahun', 'Pro 1 Tahun', 100000.00, 120000.00, 12, 'Paket Pro 1 tahun. Harga awal Rp100.000 (±Rp8.333/bulan), perpanjangan Rp120.000 (Rp10.000/bulan).', '["Kelola penghuni", "Tagihan penghuni", "Pencatatan pembayaran", "Riwayat penghuni", "Ringkasan keuangan"]', 'aktif', '2026-09-04 00:57:43', '2026-09-04 12:03:00');

-- Dumping structure for table platform_kos.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id_reset` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_password_reset_user` (`id_user`),
  KEY `idx_password_reset_expires` (`expires_at`),
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table platform_kos.pembayaran
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tagihan` bigint unsigned NOT NULL,
  `id_penghuni` bigint unsigned NOT NULL,
  `id_user` bigint unsigned DEFAULT NULL,
  `nomor_pembayaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `metode` enum('tunai','transfer','qris','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('menunggu','berhasil','ditolak','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'berhasil',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pembayaran`),
  UNIQUE KEY `nomor_pembayaran` (`nomor_pembayaran`),
  KEY `idx_pembayaran_tagihan` (`id_tagihan`),
  KEY `idx_pembayaran_penghuni` (`id_penghuni`),
  KEY `idx_pembayaran_tanggal` (`tanggal_bayar`),
  KEY `fk_pembayaran_user` (`id_user`),
  CONSTRAINT `fk_pembayaran_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_tagihan` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_pembayaran_jumlah` CHECK ((`jumlah` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.pembayaran: ~2 rows (approximately)
INSERT INTO `pembayaran` (`id_pembayaran`, `id_tagihan`, `id_penghuni`, `id_user`, `nomor_pembayaran`, `jumlah`, `tanggal_bayar`, `metode`, `status`, `catatan`, `created_at`) VALUES
	(3, 7, 5, 8, 'BYR-20260904110848-563', 500000.00, '2026-09-04 11:08:00', 'tunai', 'berhasil', '', '2026-09-04 03:08:48'),
	(4, 7, 5, 8, 'BYR-20260904110913-309', 200000.00, '2026-09-04 11:09:00', 'transfer', 'berhasil', '', '2026-09-04 03:09:13'),
	(5, 7, 6, 8, 'BYR-20260904111206-704', 300000.00, '2026-09-04 11:11:00', 'tunai', 'berhasil', '', '2026-09-04 03:12:06');

-- Dumping structure for table platform_kos.pembayaran_langganan
CREATE TABLE IF NOT EXISTS `pembayaran_langganan` (
  `id_pembayaran_langganan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_order` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_langganan` bigint unsigned NOT NULL,
  `id_paket_langganan` int unsigned NOT NULL,
  `id_pemilik` bigint unsigned NOT NULL,
  `jenis_pembayaran` enum('baru','renewal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baru',
  `nominal` decimal(12,2) NOT NULL,
  `metode_pembayaran` enum('transfer_bank','e_wallet','qris') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_metode_pembayaran` int unsigned DEFAULT NULL,
  `provider_pembayaran` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_order_id` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_status` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_string` text COLLATE utf8mb4_unicode_ci,
  `qr_code_url` text COLLATE utf8mb4_unicode_ci,
  `paid_at` datetime DEFAULT NULL,
  `nomor_tujuan_pembayaran` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_penerima_pembayaran` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_pembayaran` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('menunggu','diverifikasi','ditolak','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `id_admin_verifikasi` bigint unsigned DEFAULT NULL,
  `tanggal_verifikasi` datetime DEFAULT NULL,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pembayaran_langganan`),
  UNIQUE KEY `nomor_order` (`nomor_order`),
  UNIQUE KEY `uq_pl_provider_order_id` (`provider_order_id`),
  KEY `fk_pembayaran_langganan_paket` (`id_paket_langganan`),
  KEY `idx_pl_pemilik_status` (`id_pemilik`,`status`),
  KEY `idx_pl_langganan` (`id_langganan`),
  KEY `idx_pl_status_created` (`status`,`created_at`),
  KEY `idx_pl_admin` (`id_admin_verifikasi`),
  KEY `idx_pl_metode` (`id_metode_pembayaran`),
  KEY `idx_pl_provider_status` (`provider_pembayaran`,`provider_status`),
  CONSTRAINT `fk_pembayaran_langganan_admin` FOREIGN KEY (`id_admin_verifikasi`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_langganan_langganan` FOREIGN KEY (`id_langganan`) REFERENCES `langganan` (`id_langganan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_langganan_paket` FOREIGN KEY (`id_paket_langganan`) REFERENCES `paket_langganan` (`id_paket_langganan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pembayaran_langganan_pemilik` FOREIGN KEY (`id_pemilik`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pl_metode_pembayaran` FOREIGN KEY (`id_metode_pembayaran`) REFERENCES `metode_pembayaran_langganan` (`id_metode_pembayaran`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_pembayaran_langganan_nominal` CHECK ((`nominal` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.pembayaran_langganan: ~4 rows (approximately)
INSERT INTO `pembayaran_langganan` (`id_pembayaran_langganan`, `nomor_order`, `id_langganan`, `id_paket_langganan`, `id_pemilik`, `jenis_pembayaran`, `nominal`, `metode_pembayaran`, `id_metode_pembayaran`, `provider_pembayaran`, `provider_order_id`, `provider_transaction_id`, `provider_status`, `qr_string`, `qr_code_url`, `paid_at`, `nomor_tujuan_pembayaran`, `nama_penerima_pembayaran`, `tanggal_pembayaran`, `bukti_pembayaran`, `status`, `id_admin_verifikasi`, `tanggal_verifikasi`, `catatan_admin`, `created_at`, `updated_at`) VALUES
	(28, 'SUB-20260905-036D4041', 27, 3, 2, 'baru', 100000.00, 'qris', NULL, 'midtrans', 'SUB-20260905-036D4041', '67dbc353-ce47-4bbd-b968-538c3505824a', 'settlement', '00020101021226620014COM.GO-JEK.WWW011993600914309465464520210G0946546450303UKE51440014ID.CO.QRIS.WWW0215AID1503851320960303UKE52043409530336054061000005802ID5909stivenadu6008JAYAPURA61059922462395028A120260904161606QKSDkogbiCID0703A016304DC74', 'https://api.sandbox.midtrans.com/v2/qris/67dbc353-ce47-4bbd-b968-538c3505824a/qr-code', '2026-09-05 00:17:13', NULL, NULL, '2026-09-05 00:16:05', NULL, 'diverifikasi', NULL, '2026-09-05 00:17:13', NULL, '2026-09-05 00:16:05', '2026-09-05 00:17:13'),
	(29, 'SUB-20260905-FBB1985A', 27, 3, 2, 'renewal', 120000.00, 'transfer_bank', 2, 'BRI', NULL, NULL, NULL, NULL, NULL, NULL, '003901139474509', 'Stiven Melkianus Adu', '2026-09-05 14:31:14', '/pembayaran-langganan/PEMBAYARAN-LANGGANAN_3461c277e4a1c0f25dbc00a217a1e89b.png', 'diverifikasi', 1, '2026-09-05 14:32:46', '', '2026-09-05 14:31:14', '2026-09-05 14:32:46'),
	(30, 'SUB-20260905-0FDEAA2B', 27, 3, 2, 'renewal', 120000.00, 'qris', NULL, 'midtrans', 'SUB-20260905-0FDEAA2B', '8be755cb-1d9d-48cd-b03f-444170e8df6b', 'settlement', '00020101021226620014COM.GO-JEK.WWW011993600914309465464520210G0946546450303UKE51440014ID.CO.QRIS.WWW0215AID1503851320960303UKE52043409530336054061200005802ID5909stivenadu6008JAYAPURA61059922462395028A120260905070449EI1UVY2WGyID0703A016304B655', 'https://api.sandbox.midtrans.com/v2/qris/8be755cb-1d9d-48cd-b03f-444170e8df6b/qr-code', '2026-09-05 15:05:09', NULL, NULL, '2026-09-05 15:04:48', NULL, 'diverifikasi', NULL, '2026-09-05 15:05:09', NULL, '2026-09-05 15:04:48', '2026-09-05 15:05:09'),
	(31, 'SUB-20260905-85DE50F7', 27, 3, 2, 'renewal', 120000.00, 'qris', NULL, 'midtrans', 'SUB-20260905-85DE50F7', '0ff696e4-f12e-4750-bd55-3a8932679cd1', 'expire', '00020101021226620014COM.GO-JEK.WWW011993600914309465464520210G0946546450303UKE51440014ID.CO.QRIS.WWW0215AID1503851320960303UKE52043409530336054061200005802ID5909stivenadu6008JAYAPURA61059922462395028A120260905070542PB8jokR7n4ID0703A0163047544', 'https://api.sandbox.midtrans.com/v2/qris/0ff696e4-f12e-4750-bd55-3a8932679cd1/qr-code', NULL, NULL, NULL, '2026-09-05 15:05:39', NULL, 'dibatalkan', NULL, NULL, NULL, '2026-09-05 15:05:39', '2026-09-07 22:44:02'),
	(32, 'SUB-20260907-494440AB', 27, 3, 2, 'renewal', 120000.00, 'qris', NULL, 'midtrans', 'SUB-20260907-494440AB', '178c5c51-cd25-4006-95a3-6370305ac198', 'settlement', '00020101021226620014COM.GO-JEK.WWW011993600914309465464520210G0946546450303UKE51440014ID.CO.QRIS.WWW0215AID1503851320960303UKE52043409530336054061200005802ID5909stivenadu6008JAYAPURA61059922462395028A1202609071444357IjIyzLD6cID0703A016304782A', 'https://api.sandbox.midtrans.com/v2/qris/178c5c51-cd25-4006-95a3-6370305ac198/qr-code', '2026-09-07 22:45:04', NULL, NULL, '2026-09-07 22:44:34', NULL, 'diverifikasi', NULL, '2026-09-07 22:45:04', NULL, '2026-09-07 22:44:34', '2026-09-07 22:45:04');

-- Dumping structure for table platform_kos.penghuni
CREATE TABLE IF NOT EXISTS `penghuni` (
  `id_penghuni` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kamar` bigint unsigned NOT NULL,
  `id_user` bigint unsigned DEFAULT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `status` enum('aktif','keluar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penghuni`),
  KEY `idx_penghuni_kamar` (`id_kamar`),
  KEY `idx_penghuni_status` (`status`),
  KEY `idx_penghuni_user` (`id_user`),
  CONSTRAINT `fk_penghuni_kamar` FOREIGN KEY (`id_kamar`) REFERENCES `kamar` (`id_kamar`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_penghuni_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.penghuni: ~3 rows (approximately)
INSERT INTO `penghuni` (`id_penghuni`, `id_kamar`, `id_user`, `nama`, `no_hp`, `nik`, `tanggal_masuk`, `tanggal_keluar`, `status`, `created_at`, `updated_at`) VALUES
	(5, 72, 9, 'Rian', '081338609230', '5318928183918371', '2026-09-04', NULL, 'aktif', '2026-09-04 03:07:11', '2026-09-04 03:16:01'),
	(6, 72, NULL, 'kedua', '012739283821', '8728373289632763', '2026-09-10', NULL, 'aktif', '2026-09-04 03:10:20', '2026-09-04 03:10:20'),
	(7, 67, 3, 'Deva Adu', '085184583812', '5371000000000005', '2026-09-04', NULL, 'aktif', '2026-09-04 08:32:18', '2026-09-04 08:32:18');

-- Dumping structure for table platform_kos.penyesuaian_tagihan
CREATE TABLE IF NOT EXISTS `penyesuaian_tagihan` (
  `id_penyesuaian` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tagihan` bigint unsigned NOT NULL,
  `id_penghuni` bigint unsigned DEFAULT NULL,
  `jenis` enum('tambah','kurang') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `tanggal_efektif` date NOT NULL,
  `alasan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penyesuaian`),
  KEY `idx_penyesuaian_tagihan` (`id_tagihan`),
  KEY `idx_penyesuaian_penghuni` (`id_penghuni`),
  KEY `idx_penyesuaian_tanggal` (`tanggal_efektif`),
  CONSTRAINT `fk_penyesuaian_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_penyesuaian_tagihan` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_penyesuaian_jumlah` CHECK ((`jumlah` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.penyesuaian_tagihan: ~2 rows (approximately)
INSERT INTO `penyesuaian_tagihan` (`id_penyesuaian`, `id_tagihan`, `id_penghuni`, `jenis`, `jumlah`, `tanggal_efektif`, `alasan`, `created_at`) VALUES
	(1, 7, 6, 'tambah', 250000.00, '2026-09-10', 'Penyesuaian harga karena penghuni ke-2 masuk.', '2026-09-04 03:10:20'),
	(2, 7, NULL, 'tambah', 50000.00, '2026-09-04', 'tambah 50 supaya pas 300', '2026-09-04 03:11:43');

-- Dumping structure for table platform_kos.tagihan
CREATE TABLE IF NOT EXISTS `tagihan` (
  `id_tagihan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kamar` bigint unsigned NOT NULL,
  `nomor_tagihan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `jumlah_orang` tinyint unsigned NOT NULL,
  `harga_dasar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_penyesuaian` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_tagihan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_dibayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('belum_lunas','sebagian','lunas','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_lunas',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tagihan`),
  UNIQUE KEY `nomor_tagihan` (`nomor_tagihan`),
  UNIQUE KEY `uq_tagihan_periode_kamar` (`id_kamar`,`tanggal_mulai`,`tanggal_selesai`),
  KEY `idx_tagihan_kamar` (`id_kamar`),
  KEY `idx_tagihan_jatuh_tempo` (`tanggal_jatuh_tempo`),
  KEY `idx_tagihan_status` (`status`),
  KEY `idx_tagihan_periode` (`tanggal_mulai`,`tanggal_selesai`),
  CONSTRAINT `fk_tagihan_kamar` FOREIGN KEY (`id_kamar`) REFERENCES `kamar` (`id_kamar`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_tagihan_dibayar` CHECK ((`total_dibayar` >= 0)),
  CONSTRAINT `chk_tagihan_harga_dasar` CHECK ((`harga_dasar` >= 0)),
  CONSTRAINT `chk_tagihan_jatuh_tempo` CHECK ((`tanggal_jatuh_tempo` >= `tanggal_mulai`)),
  CONSTRAINT `chk_tagihan_jumlah_orang` CHECK ((`jumlah_orang` > 0)),
  CONSTRAINT `chk_tagihan_tanggal` CHECK ((`tanggal_selesai` >= `tanggal_mulai`)),
  CONSTRAINT `chk_tagihan_total` CHECK ((`total_tagihan` >= 0)),
  CONSTRAINT `chk_tagihan_total_penyesuaian` CHECK ((`total_penyesuaian` >= -(`harga_dasar`)))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.tagihan: ~2 rows (approximately)
INSERT INTO `tagihan` (`id_tagihan`, `id_kamar`, `nomor_tagihan`, `tanggal_terbit`, `tanggal_mulai`, `tanggal_selesai`, `tanggal_jatuh_tempo`, `jumlah_orang`, `harga_dasar`, `total_penyesuaian`, `total_tagihan`, `total_dibayar`, `status`, `created_at`, `updated_at`) VALUES
	(7, 72, 'TAG-20260904110711-6560', '2026-09-04', '2026-09-04', '2026-10-03', '2026-10-04', 2, 700000.00, 300000.00, 1000000.00, 1000000.00, 'lunas', '2026-09-04 03:07:11', '2026-09-04 03:12:06'),
	(8, 72, 'TAG-20260904110913-2314', '2026-09-04', '2026-10-04', '2026-11-03', '2026-11-04', 2, 1000000.00, 0.00, 1000000.00, 0.00, 'belum_lunas', '2026-09-04 03:09:13', '2026-09-04 03:10:20'),
	(9, 67, 'TAG-20260904163218-6805', '2026-09-04', '2026-09-04', '2026-10-03', '2026-10-04', 1, 1000000.00, 0.00, 1000000.00, 0.00, 'belum_lunas', '2026-09-04 08:32:18', '2026-09-04 08:32:18');

-- Dumping structure for table platform_kos.tagihan_penghuni
CREATE TABLE IF NOT EXISTS `tagihan_penghuni` (
  `id_tagihan_penghuni` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tagihan` bigint unsigned NOT NULL,
  `id_penghuni` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tagihan_penghuni`),
  UNIQUE KEY `uq_tagihan_penghuni` (`id_tagihan`,`id_penghuni`),
  KEY `idx_tagihan_penghuni_tagihan` (`id_tagihan`),
  KEY `idx_tagihan_penghuni_penghuni` (`id_penghuni`),
  CONSTRAINT `fk_tagihan_penghuni_penghuni` FOREIGN KEY (`id_penghuni`) REFERENCES `penghuni` (`id_penghuni`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tagihan_penghuni_tagihan` FOREIGN KEY (`id_tagihan`) REFERENCES `tagihan` (`id_tagihan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.tagihan_penghuni: ~4 rows (approximately)
INSERT INTO `tagihan_penghuni` (`id_tagihan_penghuni`, `id_tagihan`, `id_penghuni`, `created_at`) VALUES
	(5, 7, 5, '2026-09-04 03:07:11'),
	(6, 8, 5, '2026-09-04 03:09:13'),
	(7, 7, 6, '2026-09-04 03:10:20'),
	(8, 8, 6, '2026-09-04 03:10:20'),
	(9, 9, 7, '2026-09-04 08:32:18');

-- Dumping structure for table platform_kos.tipe_kamar
CREATE TABLE IF NOT EXISTS `tipe_kamar` (
  `id_tipe_kamar` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kos` bigint unsigned NOT NULL,
  `nama_tipe` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` tinyint unsigned NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipe_kamar`),
  UNIQUE KEY `uq_tipe_kamar_nama` (`id_kos`,`nama_tipe`),
  KEY `idx_tipe_kamar_kos` (`id_kos`),
  CONSTRAINT `fk_tipe_kamar_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_tipe_kamar_kapasitas` CHECK ((`kapasitas` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.tipe_kamar: ~0 rows (approximately)
INSERT INTO `tipe_kamar` (`id_tipe_kamar`, `id_kos`, `nama_tipe`, `kapasitas`, `deskripsi`, `created_at`, `updated_at`) VALUES
	(13, 10, 'Standard', 1, '', '2026-09-03 12:55:48', '2026-09-03 12:55:48'),
	(14, 11, 'Standard', 2, '', '2026-09-04 03:01:42', '2026-09-04 03:01:42'),
	(15, 10, 'VIP', 1, '', '2026-09-06 10:01:20', '2026-09-06 10:01:20');

-- Dumping structure for table platform_kos.tipe_kamar_fasilitas
CREATE TABLE IF NOT EXISTS `tipe_kamar_fasilitas` (
  `id_tipe_kamar` bigint unsigned NOT NULL,
  `id_fasilitas` bigint unsigned NOT NULL,
  PRIMARY KEY (`id_tipe_kamar`,`id_fasilitas`),
  KEY `idx_tipe_kamar_fasilitas_fasilitas` (`id_fasilitas`),
  CONSTRAINT `fk_tipe_kamar_fasilitas_fasilitas` FOREIGN KEY (`id_fasilitas`) REFERENCES `fasilitas` (`id_fasilitas`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tipe_kamar_fasilitas_tipe` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.tipe_kamar_fasilitas: ~12 rows (approximately)
INSERT INTO `tipe_kamar_fasilitas` (`id_tipe_kamar`, `id_fasilitas`) VALUES
	(13, 30),
	(15, 30),
	(13, 31),
	(15, 31),
	(13, 36),
	(15, 36),
	(13, 38),
	(15, 38),
	(13, 39),
	(15, 39),
	(13, 41),
	(14, 41),
	(15, 41),
	(13, 33),
	(15, 33),
	(15, 20),
	(13, 44),
	(15, 44),
	(13, 37),
	(15, 37),
	(15, 55);

-- Dumping structure for table platform_kos.tipe_kamar_foto
CREATE TABLE IF NOT EXISTS `tipe_kamar_foto` (
  `id_foto` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tipe_kamar` bigint unsigned NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `is_thumbnail` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_foto`),
  KEY `idx_tipe_kamar_foto_tipe` (`id_tipe_kamar`),
  CONSTRAINT `fk_tipe_kamar_foto_tipe` FOREIGN KEY (`id_tipe_kamar`) REFERENCES `tipe_kamar` (`id_tipe_kamar`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.tipe_kamar_foto: ~0 rows (approximately)
INSERT INTO `tipe_kamar_foto` (`id_foto`, `id_tipe_kamar`, `nama_file`, `urutan`, `is_thumbnail`, `created_at`) VALUES
	(7, 13, '/tipe-kamar/TIPE-KAMAR_a7a01991a0fa5375512d97bfe2f4db00.png', 1, 1, '2026-09-03 12:56:09'),
	(8, 14, '/tipe-kamar/TIPE-KAMAR_65ea5c8474fab108ed2f671e54efcafd.png', 1, 1, '2026-09-04 03:02:20'),
	(9, 15, '/tipe-kamar/TIPE-KAMAR_8d29f5c2c3165bd48bff12bf38b7f04d.png', 1, 0, '2026-09-07 14:33:29'),
	(10, 15, '/tipe-kamar/TIPE-KAMAR_a7bc621d2c0d9ef3cd8eff2f04f370ef.png', 2, 0, '2026-09-07 14:34:02'),
	(11, 15, '/tipe-kamar/TIPE-KAMAR_c93242da2b8fa8c52dd016363e65f95e.png', 3, 1, '2026-09-07 14:34:36');

-- Dumping structure for table platform_kos.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_sub` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `role` enum('pelanggan','pemilik','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pelanggan',
  `no_hp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `status` enum('aktif','nonaktif','ditangguhkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `auth_session_version` bigint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nik` (`nik`),
  UNIQUE KEY `uq_users_google_sub` (`google_sub`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.users: ~7 rows (approximately)
INSERT INTO `users` (`id_user`, `nama`, `nik`, `email`, `google_sub`, `password`, `last_login_at`, `role`, `no_hp`, `foto`, `email_verified_at`, `status`, `auth_session_version`, `created_at`, `updated_at`) VALUES
	(1, 'Stiven Melkianus Adu', '5371040105040001', 'stivenadu01@gmail.com', '110943803175322052448', '$2y$10$DVu3Pb3QAnHqEPYG4DAfv.ywYHykap18gQCFcHBabUy0blqPg9KM.', '2026-09-07 23:19:37', 'admin', '081338609228', NULL, '2026-08-27 17:01:32', 'aktif', 1, '2026-08-27 09:01:14', '2026-09-07 15:19:37'),
	(2, 'Deny Pating', '5371090928390005', 'deny@gmail.com', NULL, '$2y$10$AQC1oLxWZ8eDrvX02NkapelNyZgAkt7fYF8KQdK855ikukBRqsHPS', '2026-09-07 23:09:07', 'pemilik', '081338609303', '/profil/PROFIL_fa76e9f340ad8bb9d80dd2912d740628.png', '2026-08-27 17:03:41', 'aktif', 2, '2026-08-27 09:03:06', '2026-09-07 15:09:07'),
	(3, 'Deva Adu', '5371000000000005', 'deva@gmail.com', NULL, '$2y$10$51fM4h0LZC9LTq4/CByTz.Ahr4CQZFKmQ00AwQieq3Iba54PN5DF2', '2026-09-07 23:21:38', 'pelanggan', '085184583812', NULL, '2026-08-27 17:43:53', 'aktif', 1, '2026-08-27 09:42:57', '2026-09-07 15:21:38'),
	(4, 'Marfin Ambi', '5371000000000006', 'marfin@gmail.com', NULL, '$2y$10$uNaSXiDsiKJqvr1B02qAxe9Fd2r4t4Nf0wG18GHgpMYVQ4cFC7J.e', NULL, 'pelanggan', '082340723169', NULL, '2026-08-27 18:51:38', 'aktif', 1, '2026-08-27 10:51:27', '2026-08-27 10:51:38'),
	(5, 'Alven Seran', '5371000000000007', 'alven@gmail.com', NULL, '$2y$10$yBwylHsdYtgFm0HcrbIsOeajOWn3W9xl5vIMEw4CGLmVRAxQcy6g2', '2026-09-04 12:56:25', 'pelanggan', '081236164802', NULL, '2026-08-27 20:24:12', 'aktif', 1, '2026-08-27 12:23:48', '2026-09-04 04:56:25'),
	(8, 'Stiven Melkianus Adu', '5371040105040004', 'stivenadu9@gmail.com', NULL, '$2y$10$N3AXLofdIM.3EVgfZKqXjOhTBeh/v.j4Uk9rCheX1wHgJDXuBAEKi', '2026-09-06 15:56:49', 'pemilik', '081338609228', NULL, '2026-09-04 10:58:41', 'aktif', 1, '2026-09-04 02:57:20', '2026-09-06 07:56:49'),
	(9, 'Rian Waang', '5318928183918371', 'rian@gmail.com', NULL, '$2y$10$EJblfBBynmmN6vZf5OO7QuevEyFqIx6blK7yqGN9YERg8speoHjni', '2026-09-04 11:16:10', 'pelanggan', '081338609230', NULL, '2026-09-04 11:14:58', 'aktif', 1, '2026-09-04 03:14:08', '2026-09-04 03:16:10');

-- Dumping structure for table platform_kos.user_verification_tokens
CREATE TABLE IF NOT EXISTS `user_verification_tokens` (
  `id_token` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_verification_token_user` (`id_user`),
  KEY `idx_verification_token_expires` (`expires_at`),
  CONSTRAINT `fk_verification_token_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.user_verification_tokens: ~3 rows (approximately)
INSERT INTO `user_verification_tokens` (`id_token`, `id_user`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
	(2, 8, '2f50a31d205d1243287129fe6a2a3ee9f82815d36e88cdc7605107cbda15c886', '2026-09-05 10:57:20', '2026-09-04 10:58:41', '2026-09-04 02:57:20'),
	(3, 9, '4cd72abc93af40964c5a9f0ff937103446fbf5eaa6a9acee9fd55e5f3798f63a', '2026-09-05 11:14:08', NULL, '2026-09-04 03:14:08');

-- Dumping structure for table platform_kos.verifikasi_kos
CREATE TABLE IF NOT EXISTS `verifikasi_kos` (
  `id_verifikasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kos` bigint unsigned NOT NULL,
  `id_admin` bigint unsigned DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `tanggal_pengajuan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_verifikasi` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_verifikasi`),
  KEY `idx_verifikasi_kos` (`id_kos`),
  KEY `idx_verifikasi_admin` (`id_admin`),
  KEY `idx_verifikasi_status` (`status`),
  KEY `idx_verifikasi_tanggal` (`tanggal_pengajuan`),
  CONSTRAINT `fk_verifikasi_admin` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_verifikasi_kos` FOREIGN KEY (`id_kos`) REFERENCES `kos` (`id_kos`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table platform_kos.verifikasi_kos: ~2 rows (approximately)
INSERT INTO `verifikasi_kos` (`id_verifikasi`, `id_kos`, `id_admin`, `status`, `catatan`, `tanggal_pengajuan`, `tanggal_verifikasi`, `created_at`) VALUES
	(7, 10, 1, 'disetujui', '', '2026-09-03 20:56:33', '2026-09-03 20:57:15', '2026-09-03 12:56:33'),
	(8, 11, 1, 'disetujui', '', '2026-09-04 11:02:52', '2026-09-04 11:03:16', '2026-09-04 03:02:52');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
