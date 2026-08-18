CREATE DATABASE IF NOT EXISTS platform_kos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE platform_kos;

CREATE TABLE IF NOT EXISTS users (
    id_user BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    nik VARCHAR(16) UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('pelanggan', 'pemilik', 'admin') NOT NULL DEFAULT 'pelanggan',
    no_hp VARCHAR(30),
    foto VARCHAR(255),

    /* Verifikasi akun pemilik/mahasiswa */
    email_verified_at DATETIME NULL,

    status ENUM('aktif', 'nonaktif', 'ditangguhkan')
        NOT NULL DEFAULT 'aktif',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


/* =========================================================
   VERIFIKASI AKUN
   Token disimpan dalam bentuk hash.
   Satu token hanya dapat digunakan sekali.
   ========================================================= */

CREATE TABLE IF NOT EXISTS user_verification_tokens (
    id_token BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_user BIGINT UNSIGNED NOT NULL,

    token_hash CHAR(64) NOT NULL UNIQUE,

    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_verification_token_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_verification_token_user (id_user),
    INDEX idx_verification_token_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kos (
    id_kos BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_pemilik BIGINT UNSIGNED NOT NULL,

    nama_kos VARCHAR(200) NOT NULL,
    alamat TEXT NOT NULL,

    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,

    jenis ENUM('putra', 'putri', 'campur') NOT NULL,

    deskripsi TEXT,

    status ENUM(
        'draft',
        'menunggu_verifikasi',
        'aktif',
        'ditolak',
        'nonaktif'
    ) NOT NULL DEFAULT 'draft',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_kos_pemilik
        FOREIGN KEY (id_pemilik)
        REFERENCES users(id_user)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_kos_pemilik (id_pemilik),
    INDEX idx_kos_status (status),
    INDEX idx_kos_jenis (jenis)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kamar (
    id_kamar BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kos BIGINT UNSIGNED NOT NULL,

    nomor_kamar VARCHAR(50) NOT NULL,
    tipe_kamar VARCHAR(100),

    kapasitas TINYINT UNSIGNED NOT NULL,

    status ENUM(
        'tersedia',
        'terisi',
        'perbaikan',
        'nonaktif'
    ) NOT NULL DEFAULT 'tersedia',

    deskripsi TEXT,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_kamar_kos
        FOREIGN KEY (id_kos)
        REFERENCES kos(id_kos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT uq_kamar_nomor
        UNIQUE (id_kos, nomor_kamar),

    INDEX idx_kamar_kos (id_kos),
    INDEX idx_kamar_status (status),
    INDEX idx_kamar_kapasitas (kapasitas)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS harga_kamar (
    id_harga BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kamar BIGINT UNSIGNED NOT NULL,

    jumlah_orang TINYINT UNSIGNED NOT NULL,

    harga_total DECIMAL(12,2) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_harga_kamar
        FOREIGN KEY (id_kamar)
        REFERENCES kamar(id_kamar)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT uq_harga_jumlah_orang
        UNIQUE (id_kamar, jumlah_orang),

    CONSTRAINT chk_harga_positif
        CHECK (harga_total >= 0),

    CONSTRAINT chk_jumlah_orang_positif
        CHECK (jumlah_orang > 0),

    INDEX idx_harga_kamar (id_kamar)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fasilitas (
    id_fasilitas BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nama_fasilitas VARCHAR(100) NOT NULL UNIQUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kos_fasilitas (
    id_kos BIGINT UNSIGNED NOT NULL,
    id_fasilitas BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (id_kos, id_fasilitas),

    CONSTRAINT fk_kos_fasilitas_kos
        FOREIGN KEY (id_kos)
        REFERENCES kos(id_kos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_kos_fasilitas_fasilitas
        FOREIGN KEY (id_fasilitas)
        REFERENCES fasilitas(id_fasilitas)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kos_foto (
    id_foto BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kos BIGINT UNSIGNED NOT NULL,

    nama_file VARCHAR(255) NOT NULL,
    urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_thumbnail BOOLEAN NOT NULL DEFAULT FALSE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_kos_foto_kos
        FOREIGN KEY (id_kos)
        REFERENCES kos(id_kos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_kos_foto_kos (id_kos)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penghuni (
    id_penghuni BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kamar BIGINT UNSIGNED NOT NULL,

    id_user BIGINT UNSIGNED NULL,

    nama VARCHAR(150) NOT NULL,
    no_hp VARCHAR(30),
    nik VARCHAR(16) UNIQUE,

    tanggal_masuk DATE NOT NULL,
    tanggal_keluar DATE NULL,

    status ENUM(
        'aktif',
        'keluar'
    ) NOT NULL DEFAULT 'aktif',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_penghuni_kamar
        FOREIGN KEY (id_kamar)
        REFERENCES kamar(id_kamar)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_penghuni_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_penghuni_kamar (id_kamar),
    INDEX idx_penghuni_status (status),
    INDEX idx_penghuni_user (id_user)
) ENGINE=InnoDB;

/* =========================================================
   TAGIHAN
   Tidak menggunakan tabel periode_sewa.
   Satu tagihan mewakili satu periode penagihan untuk satu kamar.
   ========================================================= */

CREATE TABLE IF NOT EXISTS tagihan (
    id_tagihan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kamar BIGINT UNSIGNED NOT NULL,

    nomor_tagihan VARCHAR(50) NOT NULL UNIQUE,

    tanggal_terbit DATE NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,

    jumlah_orang TINYINT UNSIGNED NOT NULL,

    /* Harga dasar kamar pada saat tagihan dibuat.
       Nilai ini TIDAK diubah ketika ada penghuni tambahan
       di tengah periode. */
    harga_dasar DECIMAL(12,2) NOT NULL DEFAULT 0,

    /* Total seluruh penyesuaian pada tagihan.
       Positif = tambahan, negatif = pengurangan. */
    total_penyesuaian DECIMAL(12,2) NOT NULL DEFAULT 0,

    total_tagihan DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_dibayar DECIMAL(12,2) NOT NULL DEFAULT 0,

    status ENUM(
        'belum_lunas',
        'sebagian',
        'lunas',
        'dibatalkan'
    ) NOT NULL DEFAULT 'belum_lunas',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tagihan_kamar
        FOREIGN KEY (id_kamar)
        REFERENCES kamar(id_kamar)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT chk_tagihan_tanggal
        CHECK (tanggal_selesai >= tanggal_mulai),

    CONSTRAINT chk_tagihan_jatuh_tempo
        CHECK (tanggal_jatuh_tempo >= tanggal_mulai),

    CONSTRAINT chk_tagihan_jumlah_orang
        CHECK (jumlah_orang > 0),

    CONSTRAINT chk_tagihan_harga_dasar
        CHECK (harga_dasar >= 0),

    CONSTRAINT chk_tagihan_total_penyesuaian
        CHECK (total_penyesuaian >= -harga_dasar),

    CONSTRAINT chk_tagihan_total
        CHECK (total_tagihan >= 0),

    CONSTRAINT chk_tagihan_dibayar
        CHECK (total_dibayar >= 0),

    /* Mencegah Cron Job membuat tagihan periode yang sama dua kali. */
    CONSTRAINT uq_tagihan_periode_kamar
        UNIQUE (id_kamar, tanggal_mulai, tanggal_selesai),

    INDEX idx_tagihan_kamar (id_kamar),
    INDEX idx_tagihan_jatuh_tempo (tanggal_jatuh_tempo),
    INDEX idx_tagihan_status (status),
    INDEX idx_tagihan_periode (tanggal_mulai, tanggal_selesai)
) ENGINE=InnoDB;


/* =========================================================
   PENYESUAIAN TAGIHAN
   Menyimpan perubahan biaya di tengah periode.
   Contoh:
   harga dasar Rp600.000
   penghuni kedua masuk
   penyesuaian +Rp322.000
   total tagihan menjadi Rp922.000.
   ========================================================= */

CREATE TABLE IF NOT EXISTS penyesuaian_tagihan (
    id_penyesuaian BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_tagihan BIGINT UNSIGNED NOT NULL,
    id_penghuni BIGINT UNSIGNED NULL,

    jenis ENUM(
        'tambah',
        'kurang'
    ) NOT NULL,

    jumlah DECIMAL(12,2) NOT NULL,

    tanggal_efektif DATE NOT NULL,

    alasan VARCHAR(255) NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_penyesuaian_tagihan
        FOREIGN KEY (id_tagihan)
        REFERENCES tagihan(id_tagihan)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_penyesuaian_penghuni
        FOREIGN KEY (id_penghuni)
        REFERENCES penghuni(id_penghuni)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_penyesuaian_jumlah
        CHECK (jumlah > 0),

    INDEX idx_penyesuaian_tagihan (id_tagihan),
    INDEX idx_penyesuaian_penghuni (id_penghuni),
    INDEX idx_penyesuaian_tanggal (tanggal_efektif)
) ENGINE=InnoDB;


/* =========================================================
   PEMBAYARAN
   Pembayaran selalu mengacu ke satu tagihan.
   ========================================================= */

CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_tagihan BIGINT UNSIGNED NOT NULL,
    id_penghuni BIGINT UNSIGNED NULL,
    id_user BIGINT UNSIGNED NULL,

    nomor_pembayaran VARCHAR(50) NOT NULL UNIQUE,

    jumlah DECIMAL(12,2) NOT NULL,

    tanggal_bayar DATETIME NOT NULL,

    metode ENUM(
        'tunai',
        'transfer',
        'qris',
        'lainnya'
    ) NOT NULL,

    status ENUM(
        'menunggu',
        'berhasil',
        'ditolak',
        'dibatalkan'
    ) NOT NULL DEFAULT 'berhasil',

    catatan TEXT,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pembayaran_tagihan
        FOREIGN KEY (id_tagihan)
        REFERENCES tagihan(id_tagihan)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_pembayaran_penghuni
        FOREIGN KEY (id_penghuni)
        REFERENCES penghuni(id_penghuni)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_pembayaran_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_pembayaran_jumlah
        CHECK (jumlah > 0),

    INDEX idx_pembayaran_tagihan (id_tagihan),
    INDEX idx_pembayaran_penghuni (id_penghuni),
    INDEX idx_pembayaran_tanggal (tanggal_bayar)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorit (
    id_favorit BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_user BIGINT UNSIGNED NOT NULL,
    id_kos BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_favorit_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_favorit_kos
        FOREIGN KEY (id_kos)
        REFERENCES kos(id_kos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT uq_favorit
        UNIQUE (id_user, id_kos)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS verifikasi_kos (
    id_verifikasi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_kos BIGINT UNSIGNED NOT NULL,
    id_admin BIGINT UNSIGNED NULL,

    status ENUM(
        'menunggu',
        'disetujui',
        'ditolak'
    ) NOT NULL DEFAULT 'menunggu',

    catatan TEXT,

    tanggal_pengajuan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_verifikasi DATETIME NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_verifikasi_kos
        FOREIGN KEY (id_kos)
        REFERENCES kos(id_kos)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_verifikasi_admin
        FOREIGN KEY (id_admin)
        REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_verifikasi_kos (id_kos),
    INDEX idx_verifikasi_admin (id_admin),
    INDEX idx_verifikasi_status (status),
    INDEX idx_verifikasi_tanggal (tanggal_pengajuan)
) ENGINE=InnoDB;

/* =========================================================
   RESET PASSWORD
   Token reset password disimpan sebagai hash.
   Token hanya berlaku satu kali dan memiliki masa berlaku.
   ========================================================= */

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id_reset BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_user BIGINT UNSIGNED NOT NULL,

    token_hash CHAR(64) NOT NULL UNIQUE,

    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (id_user)
        REFERENCES users(id_user)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_password_reset_user (id_user),
    INDEX idx_password_reset_expires (expires_at)
) ENGINE=InnoDB;


/* =========================================================
   CATATAN IMPLEMENTASI
   =========================================================
   1. Registrasi:
      - password wajib disimpan menggunakan password_hash() PHP.
      - buat token acak menggunakan random_bytes().
      - simpan hash SHA-256 token ke user_verification_tokens.
      - kirim token/link verifikasi melalui email.
      - setelah berhasil, isi users.email_verified_at.

   2. Login:
      - gunakan password_verify().
      - untuk pemilik, aplikasi dapat mewajibkan
        email_verified_at IS NOT NULL sebelum mengakses modul pemilik.

   3. Reset password:
      - buat token acak menggunakan random_bytes().
      - simpan hanya hash token ke password_reset_tokens.
      - token memiliki expires_at.
      - setelah token digunakan, isi used_at.
      - password baru disimpan dengan password_hash().
      - jangan menyimpan token asli di database.

   4. Tagihan otomatis:
      - saat penghuni pertama masuk, sistem membuat tagihan pertama.
      - tanggal_mulai dan tanggal_selesai disimpan langsung pada tagihan.
      - saat penghuni tambahan masuk di tengah periode, harga_dasar
        tagihan lama tidak diubah; sistem hanya membuat penyesuaian.
      - Cron Job mencari tagihan yang sudah selesai dan membuat tagihan
        periode berikutnya berdasarkan jumlah penghuni aktif dan harga kamar.
      - unique (id_kamar, tanggal_mulai, tanggal_selesai) mencegah
        duplikasi tagihan oleh Cron Job.

   5. Verifikasi kos:
      - pemilik mengajukan kos -> verifikasi_kos.status = 'menunggu'
        dan kos.status = 'menunggu_verifikasi'.
      - admin menyetujui -> verifikasi_kos.status = 'disetujui'
        dan kos.status = 'aktif'.
      - admin menolak -> verifikasi_kos.status = 'ditolak'
        dan kos.status = 'ditolak'.
      - verifikasi_kos menyimpan riwayat pengajuan/verifikasi.
*/
