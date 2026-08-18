# Cron Job Tagihan Otomatis

Entry point:
`php cron/generate_tagihan.php`

Simulasi tanggal:
`php cron/generate_tagihan.php --date=2026-09-03`

Aturan:
- hanya kamar dengan penghuni aktif yang diproses;
- kamar kosong tidak dibuatkan tagihan;
- tagihan terakhir yang periodenya sudah selesai menjadi dasar periode berikutnya;
- periode berikutnya dimulai pada tanggal jatuh tempo tagihan sebelumnya;
- jumlah penghuni aktif menentukan harga dasar periode baru;
- penyesuaian manual tidak dibuat oleh Cron;
- tagihan lama dan pembayaran lama tidak diubah;
- pengecekan periode dan UNIQUE database mencegah duplikasi;
- lock file mencegah dua proses Cron berjalan bersamaan.

Untuk hosting, jadwalkan `php cron/generate_tagihan.php` sekali sehari, misalnya pukul 00:05.
