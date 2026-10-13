# BetaKos

Proyek pencarian dan pengelolaan kos. Konfigurasi lokal menggunakan PHP 8.2.12 dan MariaDB XAMPP, database `betakos`, serta URL `http://localhost/BetaKos/`.

## Menjalankan di XAMPP

1. Simpan proyek di `D:\xampp\htdocs\BetaKos` dan jalankan Apache serta MySQL.
2. Salin `.env.example` menjadi `.env` untuk instalasi baru. Sesuaikan koneksi database dan kredensial integrasi bila diperlukan.
3. Instal dependensi memakai PHP XAMPP di PowerShell:

   ```powershell
   & D:/xampp/php/php.exe C:/ProgramData/ComposerSetup/bin/composer.phar install
   & D:/xampp/php/php.exe C:/ProgramData/ComposerSetup/bin/composer.phar check-platform-reqs
   ```

4. Untuk database baru yang masih kosong, impor dump dan jalankan migrasi berikut secara berurutan:

   ```powershell
   & D:/xampp/mysql/bin/mysql.exe -u root --default-character-set=utf8mb4 -e "source D:/xampp/htdocs/BetaKos/data.sql"
   & D:/xampp/mysql/bin/mysql.exe -u root betakos --default-character-set=utf8mb4 -e "source D:/xampp/htdocs/BetaKos/app/migrations/betakos_xampp_migration.sql; source D:/xampp/htdocs/BetaKos/app/migrations/betakos_whatsapp_notification_migration.sql; source D:/xampp/htdocs/BetaKos/app/migrations/betakos_master_icon_final_migration.sql;"
   ```

   Dump berisi data awal, termasuk akun dan properti; jangan impor ulang ke database yang sudah berisi data. Migrasi XAMPP mempertahankan riwayat langganan. Migrasi `betakos_subscription_v2_migration.sql` merupakan skrip reset lama yang menghapus riwayat; gunakan migrasi XAMPP di atas untuk instalasi ini.

5. Buka `http://localhost/BetaKos/`. Build CSS bila mengubah sumber Tailwind: `npm ci`, lalu `npm run build`.

Composer dikonfigurasi menargetkan PHP 8.2.12 supaya pembaruan dependensi dari PHP CLI versi lebih baru tetap menghasilkan lock file yang kompatibel. Jangan menonaktifkan pemeriksaan platform Composer.

Login Google memerlukan pengaturan origin dan callback yang cocok dengan URL lokal. Email, pembayaran, dan WhatsApp memerlukan konfigurasi layanan masing-masing.

## Notifikasi dan QRIS sandbox

Untuk pusat notifikasi, jalankan `app/migrations/betakos_notifications_migration.sql` pada database `betakos`. Ikon lonceng menampilkan pembaruan verifikasi kos, pembayaran Pro, klaim penghuni, tagihan, dan laporan sesuai akun. Status dibaca tersimpan di server. Notifikasi browser dapat diaktifkan dari panel dan bekerja selama ada tab BetaKos terbuka; belum menggunakan Web Push saat seluruh tab ditutup. Pembaruan status diambil setiap 15 detik, dengan jeda lebih panjang ketika koneksi gagal.

Google One Tap resmi diaktifkan untuk pengunjung yang belum login. Tombol Google resmi tersedia di bawah Masuk pada formulir login dan halaman akun pengunjung, serta pada pendaftaran. Header hanya memakai panel akun resmi Google One Tap dalam warna gelap di kanan atas, tanpa tombol Google tambahan. Posisi kustom berlaku pada desktop tanpa FedCM; pada FedCM/mobile browser mengatur posisi panel. One Tap tetap tersedia ketika Google/browser mengizinkannya. Google/browser menentukan posisi dan kemunculan daftar akun. Google menentukan tampilan dan ketersediaan panel One Tap. Jika prompt ditutup, Google atau browser dapat memberlakukan cooldown (`suppressed_by_user`); aplikasi tidak membuat daftar akun pengganti atau melewati pilihan pengguna. Origin lokal `http://localhost` harus terdaftar di Authorized JavaScript origins. Server memverifikasi tanda tangan token, audience, issuer, kedaluwarsa, nonce sesi, dan CSRF melalui endpoint `/api/auth/google/credential`.

Pada mode sandbox, halaman pembayaran QRIS menyediakan URL gambar QR, tombol salin, tautan ke simulator Midtrans terbaru dan cek status. Tempel **URL gambar QR** pada kolom **QR Code Image Url** di `https://simulator.sandbox.midtrans.com/v2/qris/index`. QR baru memakai action `generate-qr-code-v2` bila tersedia. Order pending lama dipulihkan dari referensi QR yang tersimpan. Status di localhost disinkronkan dengan API Midtrans karena webhook eksternal tidak dapat menjangkau localhost langsung.

Pengujian CLI dengan PHP XAMPP:

```powershell
& D:/xampp/php/php.exe tests/notifications.php
& D:/xampp/php/php.exe tests/midtrans-qr.php
& D:/xampp/php/php.exe tests/google-identity.php
& D:/xampp/php/php.exe tests/google-login.php
```

Tes notifikasi membuat database sementara berawalan `betakos_test_notifications_`, menguji isolasi penerima dan deduplikasi, lalu menghapus database uji itu saja. Tes QR menggunakan fixture tanpa memanggil gateway.
