<?php

class ApiLanggananController
{
  public function __construct()
  {
    model('Langganan');
    model('MetodePembayaranLangganan');
  }

  private function pemilikId()
  {
    return (int) ($_SESSION['user']['id_user'] ?? 0);
  }

  public function index()
  {
    try {
      $idPemilik = $this->pemilikId();
      $status = getStatusLanggananPemilik($idPemilik);

      response([
        'success' => true,
        'data' => [
          'status' => $status,
          'paket' => getPaketLanggananAktif(),
          'pending_payment' => getPendingPembayaranLanggananPemilik($idPemilik),
        ]
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => 'Gagal memuat informasi langganan.'
      ], 500);
    }
  }

  public function checkoutInfo()
  {
    try {
      $idPemilik = $this->pemilikId();
      $active = getLanggananAktifPemilik($idPemilik);
      $pending = getPendingPembayaranLanggananPemilik($idPemilik);
      model('MetodePembayaranLangganan');
      $methods = getMetodePembayaranLangganan(true);
      array_unshift($methods, [
        'id_metode_pembayaran' => 0,
        'jenis' => 'qris',
        'nama_provider' => 'QRIS',
        'nomor_tujuan' => '',
        'nama_penerima' => '',
        'keterangan' => 'QRIS dinamis. Nominal dan QR dibuat otomatis untuk setiap transaksi.',
      ]);
      $status = getStatusLanggananPemilik($idPemilik);
      $isRenewal = in_array($status['status'], ['aktif', 'berakhir'], true);

      response([
        'success' => true,
        'data' => [
          'status' => $status,
          'is_renewal' => $isRenewal,
          'paket' => getPaketLanggananAktif(),
          'active_subscription' => $active,
          'pending_payment' => $pending,
          'payment_methods' => $methods,
        ]
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => 'Gagal memuat checkout langganan.'
      ], 500);
    }
  }

  public function pembayaran()
  {
    try {
      $rows = getPembayaranLanggananPemilik($this->pemilikId());
      response(['success' => true, 'data' => $rows]);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => 'Gagal memuat pembayaran langganan.'], 500);
    }
  }

  public function pembayaranShow()
  {
    $id = (int)params('id');
    $row = getPembayaranLanggananByIdPemilik($id, $this->pemilikId());
    if (!$row) {
      response(['success' => false, 'message' => 'Pembayaran langganan tidak ditemukan.'], 404);
    }
    response(['success' => true, 'data' => $row]);
  }

  public function buatPembayaran()
  {
    require_once ROOT_PATH . '/app/helpers/upload.php';

    try {
      $idPemilik = $this->pemilikId();
      $kodePaket = trim((string)input('kode_paket', ''));
      if ($kodePaket === '') {
        throw new Exception('Paket wajib dipilih.', 422);
      }

      $status = getStatusLanggananPemilik($idPemilik);
      $isRenewal = in_array($status['status'], ['aktif', 'berakhir'], true);
      $paket = getPaketLanggananByKode($kodePaket);
      if (!$paket) {
        throw new Exception('Paket langganan tidak tersedia.', 404);
      }

      // Pro 1 bulan pertama gratis: tidak membuat pembayaran dan langsung aktif.
      if (!$isRenewal && (float)$paket['harga_bulanan'] <= 0 && (int)$paket['durasi_bulan'] === 1) {
        $idLangganan = aktifkanLanggananGratisPertama($idPemilik, $kodePaket);
        response([
          'success' => true,
          'message' => 'Pro 1 bulan gratis berhasil diaktifkan.',
          'data' => [
            'id_langganan' => $idLangganan,
            'gratis' => true,
          ]
        ], 201);
        return;
      }

      $metodeInput = trim((string)input('metode_pembayaran', ''));

      // QRIS Midtrans tidak memakai upload bukti manual.
      if (strtolower($metodeInput) === 'qris') {
        $payment = createPembayaranLanggananQris($idPemilik, $kodePaket);
        try {
          $midtrans = new MidtransService();
          $gateway = $midtrans->createQris($payment['nomor_order'], $payment['nominal']);
          updatePembayaranMidtransCreated($payment['id_pembayaran_langganan'], $gateway);
        } catch (Throwable $e) {
          try { batalkanPembayaranMidtransGagal($payment['id_pembayaran_langganan']); } catch (Throwable $ignore) {}
          throw new Exception('QRIS gagal dibuat. Silakan coba lagi atau gunakan transfer manual.', 502);
        }

        response([
          'success' => true,
          'message' => 'QRIS berhasil dibuat. Silakan lakukan pembayaran.',
          'data' => ['id_pembayaran_langganan' => $payment['id_pembayaran_langganan']]
        ], 201);
        return;
      }

      $metode = (int)$metodeInput;
      $file = request_file('bukti_pembayaran');
      if ($metode <= 0) throw new Exception('Metode pembayaran wajib dipilih.', 422);
      if (!$file) throw new Exception('Bukti pembayaran wajib diunggah.', 422);

      $path = uploadImageGeneral($file, 'pembayaran-langganan', null, 5);
      try {
        $idPembayaran = createPembayaranLangganan($idPemilik, $kodePaket, $metode, $path);
      } catch (Throwable $e) {
        @unlink(ROOT_PATH . '/public/uploads' . $path);
        throw $e;
      }

      response([
        'success' => true,
        'message' => 'Pembayaran langganan berhasil dikirim dan menunggu verifikasi admin.',
        'data' => ['id_pembayaran_langganan' => $idPembayaran]
      ], 201);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function midtransStatus()
  {
    try {
      $id = (int)params('id');
      $row = getPembayaranLanggananByIdPemilik($id, $this->pemilikId());
      if (!$row) throw new Exception('Pembayaran langganan tidak ditemukan.', 404);
      if (($row['provider_pembayaran'] ?? '') !== 'midtrans' || empty($row['provider_order_id'])) {
        throw new Exception('Pembayaran ini bukan pembayaran Midtrans.', 422);
      }

      $midtrans = new MidtransService();
      $gateway = $midtrans->getStatus((string)$row['provider_order_id']);

      // Pulihkan URL QR untuk order lama jika belum tersimpan. Midtrans memberikan
      // transaction_id saat charge berhasil, dan URL QRIS dapat dibentuk dari ID tersebut.
      if (($gateway['transaction_status'] ?? '') === 'pending' && empty($row['qr_code_url'])) {
        $transactionId = (string)($gateway['transaction_id'] ?? $row['provider_transaction_id'] ?? '');
        if ($transactionId !== '') {
          updatePembayaranMidtransCreated($id, [
            'order_id' => $gateway['order_id'] ?? $row['provider_order_id'],
            'transaction_id' => $transactionId,
            'transaction_status' => 'pending',
            'qr_string' => (string)($gateway['qr_string'] ?? ''),
            'qr_code_url' => $midtrans->getQrisCodeUrl($transactionId),
          ]);
        }
      }

      // Sinkronisasi status dari status API hanya untuk data gateway; aktivasi tetap memakai proses settlement yang idempotent.
      if (($gateway['transaction_status'] ?? '') === 'settlement') {
        prosesNotifikasiMidtrans($gateway);
      } elseif (in_array(($gateway['transaction_status'] ?? ''), ['expire','deny','cancel'], true)) {
        prosesNotifikasiMidtrans($gateway);
      }

      $latest = getPembayaranLanggananByIdPemilik($id, $this->pemilikId());
      response(['success' => true, 'data' => $latest]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function midtransNotification()
  {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '', true);
    if (!is_array($payload)) {
      response(['success' => false, 'message' => 'Payload Midtrans tidak valid.'], 400);
    }

    try {
      $midtrans = new MidtransService();
      if (!$midtrans->verifyNotificationSignature($payload)) {
        response(['success' => false, 'message' => 'Signature Midtrans tidak valid.'], 401);
      }
      $result = prosesNotifikasiMidtrans($payload);
      response(['success' => true, 'data' => $result]);
    } catch (Exception $e) {
      $status = $e->getCode();
      if ($status < 400 || $status > 599) $status = 500;
      response(['success' => false, 'message' => $e->getMessage()], $status);
    }
  }


  public function buktiPembayaran()
  {
    try {
      $id = (int)params('id');
      if ($id <= 0) throw new Exception('Bukti pembayaran tidak ditemukan.', 404);

      $user = $_SESSION['user'] ?? [];
      $role = (string)($user['role'] ?? '');

      if ($role === 'admin') {
        $row = getAdminPembayaranLanggananDetail($id);
      } elseif ($role === 'pemilik') {
        $row = getPembayaranLanggananByIdPemilik($id, (int)$user['id_user']);
      } else {
        throw new Exception('Akses ditolak.', 403);
      }

      if (!$row) throw new Exception('Bukti pembayaran tidak ditemukan.', 404);

      $relativePath = ltrim(str_replace('\\', '/', trim((string)($row['bukti_pembayaran'] ?? ''))), '/');
      if ($relativePath === '' || !preg_match('#^pembayaran-langganan/[A-Za-z0-9_-]+\.(jpg|jpeg|png|webp)$#i', $relativePath)) {
        throw new Exception('Bukti pembayaran tidak ditemukan.', 404);
      }

      $baseDir = realpath(ROOT_PATH . '/public/uploads/pembayaran-langganan');
      $target = ROOT_PATH . '/public/uploads/' . $relativePath;
      $realTarget = realpath($target);
      if ($baseDir === false || $realTarget === false || !is_file($realTarget)) {
        throw new Exception('Bukti pembayaran tidak ditemukan.', 404);
      }

      $basePrefix = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      if (!str_starts_with($realTarget, $basePrefix)) {
        throw new Exception('Bukti pembayaran tidak ditemukan.', 404);
      }

      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($realTarget);
      $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
      ];
      if (!isset($allowedMime[$mime])) {
        throw new Exception('Bukti pembayaran tidak valid.', 422);
      }

      header('Content-Type: ' . $mime);
      header('Content-Length: ' . (string)filesize($realTarget));
      header('Content-Disposition: inline; filename="bukti-pembayaran-' . $id . '.' . $allowedMime[$mime] . '"');
      header('Cache-Control: private, no-store, max-age=0');
      header('Pragma: no-cache');
      header('X-Content-Type-Options: nosniff');
      readfile($realTarget);
      exit;
    } catch (Exception $e) {
      $status = $e->getCode();
      if ($status < 400 || $status > 599) $status = 404;
      response(['success' => false, 'message' => $e->getMessage()], $status);
    }
  }

  public function uploadBukti()
  {
    require_once ROOT_PATH . '/app/helpers/upload.php';

    try {
      $idPemilik = $this->pemilikId();
      $idPembayaran = (int)input('id_pembayaran_langganan', 0);
      if ($idPembayaran <= 0) throw new Exception('Pembayaran tidak valid.', 422);

      $file = request_file('bukti_pembayaran');
      if (!$file) throw new Exception('Bukti pembayaran wajib diunggah.', 422);

      $path = uploadImageGeneral($file, 'pembayaran-langganan', null, 5);
      try {
        submitBuktiPembayaranLangganan($idPembayaran, $idPemilik, $path);
      } catch (Throwable $e) {
        @unlink(ROOT_PATH . '/public/uploads' . $path);
        throw $e;
      }

      response([
        'success' => true,
        'message' => 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.'
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function history()
  {
    try {
      response([
        'success' => true,
        'data' => getRiwayatLanggananPemilik($this->pemilikId())
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => 'Gagal memuat riwayat langganan.'
      ], 500);
    }
  }
}
