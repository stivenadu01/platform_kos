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

      $metode = (int)input('metode_pembayaran', 0);
      $file = request_file('bukti_pembayaran');

      if ($metode <= 0) {
        throw new Exception('Metode pembayaran wajib dipilih.', 422);
      }
      if (!$file) {
        throw new Exception('Bukti pembayaran wajib diunggah.', 422);
      }

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
