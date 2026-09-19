-- BetaKos - FINAL Master Icon & Master Data Migration
-- Baseline: BetaKos_Icon_Master_Deduplicated_Final
-- Tujuan: sinkronisasi master fasilitas/aturan dengan katalog icon canonical.
-- Catatan: tidak menghapus custom master yang tidak termasuk canonical.
USE `platform_kos`;
SET NAMES utf8mb4;
START TRANSACTION;

-- 1. Normalisasi nama legacy yang diketahui agar tidak membuat master semantik ganda.
UPDATE `fasilitas` f LEFT JOIN `fasilitas` c ON c.`nama_fasilitas`='Televisi' SET f.`nama_fasilitas`='Televisi' WHERE f.`nama_fasilitas`='TV' AND c.`id_fasilitas` IS NULL;
UPDATE `fasilitas` f LEFT JOIN `fasilitas` c ON c.`nama_fasilitas`='Dispenser Bersama' SET f.`nama_fasilitas`='Dispenser Bersama' WHERE f.`nama_fasilitas`='Dispenser' AND c.`id_fasilitas` IS NULL;
UPDATE `fasilitas` f LEFT JOIN `fasilitas` c ON c.`nama_fasilitas`='Dapur / Kitchen Set' SET f.`nama_fasilitas`='Dapur / Kitchen Set' WHERE f.`nama_fasilitas`='Kitchen Set' AND c.`id_fasilitas` IS NULL;
UPDATE `fasilitas` f LEFT JOIN `fasilitas` c ON c.`nama_fasilitas`='Rak Penyimpanan' SET f.`nama_fasilitas`='Rak Penyimpanan' WHERE f.`nama_fasilitas`='Rak' AND c.`id_fasilitas` IS NULL;
UPDATE `aturan` a LEFT JOIN `aturan` c ON c.`nama_aturan`='Jam bertamu sampai pukul 22.00' SET a.`nama_aturan`='Jam bertamu sampai pukul 22.00' WHERE a.`nama_aturan`='Jam bertamu sampai 22.00' AND c.`id_aturan` IS NULL;
UPDATE `aturan` a LEFT JOIN `aturan` c ON c.`nama_aturan`='Dilarang membawa kompor ke kamar' SET a.`nama_aturan`='Dilarang membawa kompor ke kamar' WHERE a.`nama_aturan`='Dilarang membawa kompor' AND c.`id_aturan` IS NULL;

-- 2. Insert fasilitas canonical yang belum ada.
INSERT INTO `fasilitas` (`nama_fasilitas`,`icon`,`status`,`kategori`) VALUES
  ('WiFi','wifi','aktif','kos'),
  ('Router / Internet','router','aktif','kos'),
  ('Parkir Motor','bike','aktif','kos'),
  ('Parkir Mobil','car','aktif','kos'),
  ('Area Parkir','parking','aktif','kos'),
  ('CCTV','camera','aktif','kos'),
  ('Keamanan 24 Jam','shield-check','aktif','kos'),
  ('Penjaga Kos','user-check','aktif','kos'),
  ('Akses Kartu','credit-card','aktif','kos'),
  ('Akses Kunci','key-round','aktif','kos'),
  ('Dapur Bersama','kitchen','aktif','kos'),
  ('Peralatan Makan','utensils','aktif','kos'),
  ('Ruang Tamu','sofa','aktif','kos'),
  ('Ruang Jemur','clothesline','aktif','kos'),
  ('Laundry','laundry','aktif','kos'),
  ('Mesin Cuci','washing-machine','aktif','kos'),
  ('Listrik','zap','aktif','kos'),
  ('Daya / Listrik Cadangan','battery-charging','aktif','kos'),
  ('Air Bersih','water','aktif','kos'),
  ('Dispenser Bersama','dispenser','aktif','kos'),
  ('Balkon','balcony','aktif','kos'),
  ('Taman','tree-pine','aktif','kos'),
  ('Area Outdoor','palmtree','aktif','kos'),
  ('Tempat Sampah','trash-2','aktif','kos'),
  ('Daur Ulang','recycle','aktif','kos'),
  ('Area Belajar','book-open','aktif','kos'),
  ('Area Kerja','briefcase','aktif','kos'),
  ('Televisi Bersama','tv','aktif','kos'),
  ('Speaker Bersama','speaker','aktif','kos'),
  ('Tempat Tidur','bed','aktif','kamar'),
  ('Kasur','bed-double','aktif','kamar'),
  ('Lampu Kamar','lamp','aktif','kamar'),
  ('AC','ac','aktif','kamar'),
  ('Kipas Angin','fan','aktif','kamar'),
  ('Televisi','tv','aktif','kamar'),
  ('Lemari','wardrobe','aktif','kamar'),
  ('Rak Penyimpanan','archive','aktif','kamar'),
  ('Meja Belajar','table','aktif','kamar'),
  ('Kursi','chair','aktif','kamar'),
  ('Kursi Santai','armchair','aktif','kamar'),
  ('Kamar Mandi Dalam','bath','aktif','kamar'),
  ('Kamar Mandi Luar','shower','aktif','kamar'),
  ('Air','droplets','aktif','kamar'),
  ('Dapur / Kitchen Set','cooking-pot','aktif','kamar'),
  ('Kompor / Gas','flame','aktif','kamar'),
  ('Kulkas','refrigerator','aktif','kamar'),
  ('Microwave','microwave','aktif','kamar'),
  ('Kopi / Minuman','coffee','aktif','kamar'),
  ('Pakaian / Jemur','shirt','aktif','kamar'),
  ('Ventilasi','wind','aktif','kamar'),
  ('Cahaya Matahari','sun','aktif','kamar'),
  ('Balkon Pribadi','balcony','aktif','kamar'),
  ('Area Outdoor Pribadi','palmtree','aktif','kamar'),
  ('Monitor','monitor','aktif','kamar'),
  ('Gorden','curtain','aktif','kamar') ON DUPLICATE KEY UPDATE `icon`=VALUES(`icon`), `status`=VALUES(`status`), `kategori`=VALUES(`kategori`);

-- 3. Sinkronkan icon fasilitas berdasarkan nama canonical.

UPDATE `fasilitas` SET `icon`='wifi', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='WiFi';
UPDATE `fasilitas` SET `icon`='router', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Router / Internet';
UPDATE `fasilitas` SET `icon`='bike', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Parkir Motor';
UPDATE `fasilitas` SET `icon`='car', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Parkir Mobil';
UPDATE `fasilitas` SET `icon`='parking', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Area Parkir';
UPDATE `fasilitas` SET `icon`='camera', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='CCTV';
UPDATE `fasilitas` SET `icon`='shield-check', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Keamanan 24 Jam';
UPDATE `fasilitas` SET `icon`='user-check', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Penjaga Kos';
UPDATE `fasilitas` SET `icon`='credit-card', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Akses Kartu';
UPDATE `fasilitas` SET `icon`='key-round', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Akses Kunci';
UPDATE `fasilitas` SET `icon`='kitchen', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Dapur Bersama';
UPDATE `fasilitas` SET `icon`='utensils', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Peralatan Makan';
UPDATE `fasilitas` SET `icon`='sofa', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Ruang Tamu';
UPDATE `fasilitas` SET `icon`='clothesline', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Ruang Jemur';
UPDATE `fasilitas` SET `icon`='laundry', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Laundry';
UPDATE `fasilitas` SET `icon`='washing-machine', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Mesin Cuci';
UPDATE `fasilitas` SET `icon`='zap', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Listrik';
UPDATE `fasilitas` SET `icon`='battery-charging', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Daya / Listrik Cadangan';
UPDATE `fasilitas` SET `icon`='water', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Air Bersih';
UPDATE `fasilitas` SET `icon`='dispenser', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Dispenser Bersama';
UPDATE `fasilitas` SET `icon`='balcony', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Balkon';
UPDATE `fasilitas` SET `icon`='tree-pine', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Taman';
UPDATE `fasilitas` SET `icon`='palmtree', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Area Outdoor';
UPDATE `fasilitas` SET `icon`='trash-2', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Tempat Sampah';
UPDATE `fasilitas` SET `icon`='recycle', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Daur Ulang';
UPDATE `fasilitas` SET `icon`='book-open', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Area Belajar';
UPDATE `fasilitas` SET `icon`='briefcase', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Area Kerja';
UPDATE `fasilitas` SET `icon`='tv', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Televisi Bersama';
UPDATE `fasilitas` SET `icon`='speaker', `status`='aktif', `kategori`='kos' WHERE `nama_fasilitas`='Speaker Bersama';
UPDATE `fasilitas` SET `icon`='bed', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Tempat Tidur';
UPDATE `fasilitas` SET `icon`='bed-double', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kasur';
UPDATE `fasilitas` SET `icon`='lamp', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Lampu Kamar';
UPDATE `fasilitas` SET `icon`='ac', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='AC';
UPDATE `fasilitas` SET `icon`='fan', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kipas Angin';
UPDATE `fasilitas` SET `icon`='tv', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Televisi';
UPDATE `fasilitas` SET `icon`='wardrobe', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Lemari';
UPDATE `fasilitas` SET `icon`='archive', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Rak Penyimpanan';
UPDATE `fasilitas` SET `icon`='table', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Meja Belajar';
UPDATE `fasilitas` SET `icon`='chair', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kursi';
UPDATE `fasilitas` SET `icon`='armchair', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kursi Santai';
UPDATE `fasilitas` SET `icon`='bath', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kamar Mandi Dalam';
UPDATE `fasilitas` SET `icon`='shower', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kamar Mandi Luar';
UPDATE `fasilitas` SET `icon`='droplets', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Air';
UPDATE `fasilitas` SET `icon`='cooking-pot', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Dapur / Kitchen Set';
UPDATE `fasilitas` SET `icon`='flame', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kompor / Gas';
UPDATE `fasilitas` SET `icon`='refrigerator', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kulkas';
UPDATE `fasilitas` SET `icon`='microwave', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Microwave';
UPDATE `fasilitas` SET `icon`='coffee', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Kopi / Minuman';
UPDATE `fasilitas` SET `icon`='shirt', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Pakaian / Jemur';
UPDATE `fasilitas` SET `icon`='wind', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Ventilasi';
UPDATE `fasilitas` SET `icon`='sun', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Cahaya Matahari';
UPDATE `fasilitas` SET `icon`='balcony', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Balkon Pribadi';
UPDATE `fasilitas` SET `icon`='palmtree', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Area Outdoor Pribadi';
UPDATE `fasilitas` SET `icon`='monitor', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Monitor';
UPDATE `fasilitas` SET `icon`='curtain', `status`='aktif', `kategori`='kamar' WHERE `nama_fasilitas`='Gorden';

-- 4. Insert aturan canonical yang belum ada.
CREATE TEMPORARY TABLE `_betakos_rule_master` (
  `nama_aturan` VARCHAR(200) NOT NULL, `kategori` VARCHAR(50) NOT NULL, `icon` VARCHAR(80) NOT NULL, `deskripsi` VARCHAR(500) NULL, `urutan` INT NOT NULL, PRIMARY KEY (`nama_aturan`)
) ENGINE=InnoDB;
INSERT INTO `_betakos_rule_master` VALUES
  ('Tidak menerima pasangan','penghuni','heart-off','Kos tidak menerima pasangan sebagai penghuni.',10),
  ('Khusus putra','penghuni','user-male','Kos diperuntukkan khusus penghuni putra.',20),
  ('Khusus putri','penghuni','user-female','Kos diperuntukkan khusus penghuni putri.',30),
  ('Tamu wajib lapor','tamu','user-check','Setiap tamu wajib melapor kepada pemilik atau pengelola.',40),
  ('Tamu tidak diperbolehkan menginap','tamu','ban','Tamu tidak diperbolehkan menginap di kos.',50),
  ('Tamu hanya diperbolehkan di area bersama','tamu','users-round','Tamu hanya diperbolehkan berada di area bersama yang ditentukan.',60),
  ('Jam bertamu sampai pukul 22.00','jam','clock-3','Jam bertamu dibatasi sampai pukul 22.00.',70),
  ('Jam malam mulai pukul 23.00','jam','moon-off','Penghuni wajib menjaga ketenangan setelah pukul 23.00.',80),
  ('Wajib menjaga kebersihan','kebersihan','sparkles','Penghuni wajib menjaga kebersihan kamar dan area bersama.',90),
  ('Wajib membuang sampah pada tempatnya','kebersihan','trash-2','Sampah wajib dibuang pada tempat yang telah disediakan.',100),
  ('Wajib menjaga fasilitas bersama','kebersihan','clipboard-check','Penghuni wajib menjaga fasilitas bersama agar tetap bersih dan baik.',110),
  ('Tidak diperbolehkan membawa hewan peliharaan','hewan','paw-print','Hewan peliharaan tidak diperbolehkan di area kos.',120),
  ('Dilarang membuat keributan','keamanan','volume-x','Penghuni wajib menjaga ketenangan dan tidak membuat keributan.',130),
  ('Dilarang membawa kompor ke kamar','keamanan','flame','Penggunaan atau penyimpanan kompor di kamar tidak diperbolehkan.',140),
  ('Wajib mengunci kamar saat meninggalkan kamar','keamanan','lock','Penghuni wajib memastikan pintu kamar terkunci saat meninggalkan kamar.',150),
  ('Dilarang merusak fasilitas kos','keamanan','shield','Penghuni dilarang merusak atau menggunakan fasilitas kos secara tidak semestinya.',160),
  ('Wajib mengikuti ketentuan kos','umum','clipboard-check','Penghuni wajib mengikuti seluruh ketentuan yang berlaku di kos.',170),
  ('Menjaga ketertiban lingkungan kos','umum','info','Penghuni wajib menjaga ketertiban dan kenyamanan lingkungan kos.',180);

INSERT INTO `aturan` (`nama_aturan`,`kategori`,`icon`,`deskripsi`,`urutan`,`status`) SELECT m.`nama_aturan`,m.`kategori`,m.`icon`,m.`deskripsi`,m.`urutan`,'aktif' FROM `_betakos_rule_master` m LEFT JOIN `aturan` a ON a.`nama_aturan`=m.`nama_aturan` WHERE a.`id_aturan` IS NULL;
UPDATE `aturan` a JOIN `_betakos_rule_master` m ON m.`nama_aturan`=a.`nama_aturan` SET a.`kategori`=m.`kategori`, a.`icon`=m.`icon`, a.`deskripsi`=m.`deskripsi`, a.`urutan`=m.`urutan`, a.`status`='aktif';

-- 5. Deduplicate aturan canonical sambil memindahkan relasi kos_aturan.
CREATE TEMPORARY TABLE `_betakos_rule_duplicate_map` (`duplicate_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY, `keep_id` BIGINT UNSIGNED NOT NULL) ENGINE=InnoDB;
INSERT INTO `_betakos_rule_duplicate_map` (`duplicate_id`,`keep_id`) SELECT a.`id_aturan`, MIN(b.`id_aturan`) FROM `aturan` a JOIN `aturan` b ON a.`id_aturan`>b.`id_aturan` AND LOWER(TRIM(a.`nama_aturan`))=LOWER(TRIM(b.`nama_aturan`)) AND a.`kategori`=b.`kategori` JOIN `_betakos_rule_master` m ON LOWER(TRIM(m.`nama_aturan`))=LOWER(TRIM(a.`nama_aturan`)) GROUP BY a.`id_aturan`;
INSERT IGNORE INTO `kos_aturan` (`id_kos`,`id_aturan`) SELECT ka.`id_kos`,m.`keep_id` FROM `kos_aturan` ka JOIN `_betakos_rule_duplicate_map` m ON m.`duplicate_id`=ka.`id_aturan`;
DELETE ka FROM `kos_aturan` ka JOIN `_betakos_rule_duplicate_map` m ON m.`duplicate_id`=ka.`id_aturan`;
DELETE a FROM `aturan` a JOIN `_betakos_rule_duplicate_map` m ON m.`duplicate_id`=a.`id_aturan`;
DROP TEMPORARY TABLE `_betakos_rule_duplicate_map`;

-- 6. Normalize legacy/invalid icon keys to a safe canonical fallback only after known mappings.
UPDATE `fasilitas` SET `icon`='info' WHERE `icon` IS NULL OR TRIM(`icon`)='' OR `icon` NOT IN ('wifi','router','tv','monitor','speaker','bed','bed-double','lamp','ac','fan','door-open','key-round','lock','wardrobe','archive','table','chair','sofa','armchair','bath','shower','droplets','water','dispenser','kitchen','utensils','cooking-pot','flame','refrigerator','microwave','coffee','washing-machine','laundry','shirt','zap','battery-charging','wind','sun','balcony','curtain','clothesline','tree-pine','palmtree','bike','car','car-front','parking','camera','shield-check','shield','credit-card','user-check','users-round','user-round','user-male','user-female','user-plus','sparkles','trash-2','recycle','heart-off','ban','volume-x','moon-off','paw-print','clock-3','calendar-days','clipboard-check','info','alert-circle','check-circle-2','circle-x','circle-help','map-pin','map','navigation','building-2','graduation-cap','school','hospital','pill','store','shopping-basket','shopping-bag','utensils-crossed','plane','bus','train-front','footprints','briefcase','book-open');
UPDATE `aturan` SET `icon`='info' WHERE `icon` IS NULL OR TRIM(`icon`)='' OR `icon` NOT IN ('wifi','router','tv','monitor','speaker','bed','bed-double','lamp','ac','fan','door-open','key-round','lock','wardrobe','archive','table','chair','sofa','armchair','bath','shower','droplets','water','dispenser','kitchen','utensils','cooking-pot','flame','refrigerator','microwave','coffee','washing-machine','laundry','shirt','zap','battery-charging','wind','sun','balcony','curtain','clothesline','tree-pine','palmtree','bike','car','car-front','parking','camera','shield-check','shield','credit-card','user-check','users-round','user-round','user-male','user-female','user-plus','sparkles','trash-2','recycle','heart-off','ban','volume-x','moon-off','paw-print','clock-3','calendar-days','clipboard-check','info','alert-circle','check-circle-2','circle-x','circle-help','map-pin','map','navigation','building-2','graduation-cap','school','hospital','pill','store','shopping-basket','shopping-bag','utensils-crossed','plane','bus','train-front','footprints','briefcase','book-open');
DROP TEMPORARY TABLE `_betakos_rule_master`;
COMMIT;

-- Verification: seluruh query berikut idealnya menghasilkan 0 baris kecuali count master.
SELECT COUNT(*) AS total_fasilitas FROM `fasilitas` WHERE `status`='aktif';
SELECT COUNT(*) AS total_aturan FROM `aturan` WHERE `status`='aktif';
SELECT `nama_fasilitas`,COUNT(*) jumlah FROM `fasilitas` GROUP BY `nama_fasilitas` HAVING COUNT(*)>1;
SELECT LOWER(TRIM(`nama_aturan`)) nama_normal,`kategori`,COUNT(*) jumlah FROM `aturan` GROUP BY LOWER(TRIM(`nama_aturan`)),`kategori` HAVING COUNT(*)>1;
SELECT 'fasilitas' sumber,`id_fasilitas` id,`nama_fasilitas`,`icon` FROM `fasilitas` WHERE `icon` NOT IN ('wifi','router','tv','monitor','speaker','bed','bed-double','lamp','ac','fan','door-open','key-round','lock','wardrobe','archive','table','chair','sofa','armchair','bath','shower','droplets','water','dispenser','kitchen','utensils','cooking-pot','flame','refrigerator','microwave','coffee','washing-machine','laundry','shirt','zap','battery-charging','wind','sun','balcony','curtain','clothesline','tree-pine','palmtree','bike','car','car-front','parking','camera','shield-check','shield','credit-card','user-check','users-round','user-round','user-male','user-female','user-plus','sparkles','trash-2','recycle','heart-off','ban','volume-x','moon-off','paw-print','clock-3','calendar-days','clipboard-check','info','alert-circle','check-circle-2','circle-x','circle-help','map-pin','map','navigation','building-2','graduation-cap','school','hospital','pill','store','shopping-basket','shopping-bag','utensils-crossed','plane','bus','train-front','footprints','briefcase','book-open');
SELECT 'aturan' sumber,`id_aturan` id,`nama_aturan`,`icon` FROM `aturan` WHERE `icon` NOT IN ('wifi','router','tv','monitor','speaker','bed','bed-double','lamp','ac','fan','door-open','key-round','lock','wardrobe','archive','table','chair','sofa','armchair','bath','shower','droplets','water','dispenser','kitchen','utensils','cooking-pot','flame','refrigerator','microwave','coffee','washing-machine','laundry','shirt','zap','battery-charging','wind','sun','balcony','curtain','clothesline','tree-pine','palmtree','bike','car','car-front','parking','camera','shield-check','shield','credit-card','user-check','users-round','user-round','user-male','user-female','user-plus','sparkles','trash-2','recycle','heart-off','ban','volume-x','moon-off','paw-print','clock-3','calendar-days','clipboard-check','info','alert-circle','check-circle-2','circle-x','circle-help','map-pin','map','navigation','building-2','graduation-cap','school','hospital','pill','store','shopping-basket','shopping-bag','utensils-crossed','plane','bus','train-front','footprints','briefcase','book-open');
