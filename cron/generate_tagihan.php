<?php

/*
|--------------------------------------------------------------------------
| CRON JOB — GENERATE TAGIHAN OTOMATIS
|--------------------------------------------------------------------------
| Jalankan dari CLI:
|
|   php cron/generate_tagihan.php
|
| Untuk simulasi tanggal tertentu saat testing:
|
|   php cron/generate_tagihan.php --date=2026-09-03
|
| Cron ini hanya membuat tagihan periode berikutnya.
| Tagihan lama, pembayaran, dan penyesuaian lama tidak diubah.
|--------------------------------------------------------------------------
*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Cron Job hanya boleh dijalankan melalui CLI.\n");
}

$rootPath = dirname(__DIR__);

require_once $rootPath . '/app/config/bootstrap.php';
require_once $rootPath . '/app/models/Penghuni.php';
require_once $rootPath . '/app/models/Tagihan.php';

/*
|--------------------------------------------------------------------------
| LOCK
|--------------------------------------------------------------------------
| Mencegah dua proses Cron berjalan bersamaan.
|--------------------------------------------------------------------------
*/
$lockPath = $rootPath . '/storage/cron-generate-tagihan.lock';
$lockDir = dirname($lockPath);

if (!is_dir($lockDir)) {
    mkdir($lockDir, 0775, true);
}

$lockHandle = fopen($lockPath, 'c');

if (!$lockHandle) {
    fwrite(STDERR, "Gagal membuka file lock Cron.\n");
    exit(1);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(
        STDOUT,
        "Cron generate tagihan sedang berjalan. Proses ini dilewati.\n"
    );
    fclose($lockHandle);
    exit(0);
}

try {
    $tanggal = date('Y-m-d');

    foreach ($argv as $argument) {
        if (strpos($argument, '--date=') === 0) {
            $tanggal = substr($argument, 7);
            break;
        }
    }

    $hasil = generateTagihanBerikutnyaCron($tanggal);

    echo "========================================\n";
    echo " CRON GENERATE TAGIHAN\n";
    echo " Tanggal: {$hasil['tanggal']}\n";
    echo "========================================\n";
    echo "Kamar diperiksa : {$hasil['diperiksa']}\n";
    echo "Tagihan dibuat  : {$hasil['dibuat']}\n";
    echo "Dilewati        : {$hasil['dilewati']}\n";
    echo "Error           : {$hasil['error']}\n";
    echo "----------------------------------------\n";

    foreach ($hasil['detail'] as $detail) {
        $idKamar = $detail['id_kamar'];
        $aksi = strtoupper($detail['aksi']);

        echo "[{$aksi}] Kamar #{$idKamar}";

        if (isset($detail['id_tagihan'])) {
            echo " | Tagihan #{$detail['id_tagihan']}";
        }

        if (isset($detail['tanggal_mulai'])) {
            echo " | {$detail['tanggal_mulai']} s/d {$detail['tanggal_selesai']}";
        }

        if (isset($detail['jumlah_orang'])) {
            echo " | {$detail['jumlah_orang']} orang";
        }

        if (isset($detail['harga_dasar'])) {
            echo " | Rp" . number_format(
                (float) $detail['harga_dasar'],
                0,
                ',',
                '.'
            );
        }

        if (isset($detail['alasan'])) {
            echo " | {$detail['alasan']}";
        }

        echo "\n";
    }

    echo "========================================\n";

    exit($hasil['error'] > 0 ? 2 : 0);
} catch (Throwable $e) {
    fwrite(
        STDERR,
        "FATAL: {$e->getMessage()}\n"
    );

    exit(1);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}
