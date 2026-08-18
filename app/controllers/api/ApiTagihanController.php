<?php

class ApiTagihanController
{
  public function __construct()
  {
    model('Tagihan');
  }

  private function pemilikId()
  {
    return (int) ($_SESSION['user']['id_user'] ?? 0);
  }

  public function index()
  {
    try {
      $data = getTagihanListByPemilik(
        $this->pemilikId(),
        trim(query('search') ?? ''),
        trim(query('status') ?? ''),
        trim(query('id_kos') ?? ''),
        trim(query('id_kamar') ?? '')
      );

      response([
        'success' => true,
        'data' => $data
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show()
  {
    try {
      $idTagihan = (int) (query('id_tagihan') ?? 0);

      if (!$idTagihan) {
        response([
          'success' => false,
          'message' => 'ID tagihan tidak valid.'
        ], 422);
        return;
      }

      $data = findTagihanByIdPemilik(
        $idTagihan,
        $this->pemilikId()
      );

      if (!$data) {
        response([
          'success' => false,
          'message' => 'Tagihan tidak ditemukan.'
        ], 404);
        return;
      }

      response([
        'success' => true,
        'data' => $data
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function adjustment()
  {
    try {
      $idTagihan = (int) (input('id_tagihan') ?? 0);
      $data = input();

      if (!$idTagihan) {
        response([
          'success' => false,
          'message' => 'ID tagihan tidak valid.'
        ], 422);
        return;
      }

      $tagihan = tambahPenyesuaianTagihan(
        $idTagihan,
        $data,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Penyesuaian tagihan berhasil ditambahkan.',
        'data' => $tagihan
      ], 201);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 422);
    }
  }

  public function payment()
  {
    try {
      $idTagihan = (int) (input('id_tagihan') ?? 0);
      $data = input();

      if (!$idTagihan) {
        response([
          'success' => false,
          'message' => 'ID tagihan tidak valid.'
        ], 422);
        return;
      }

      $result = catatPembayaranTagihan(
        $idTagihan,
        $data,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Pembayaran berhasil dicatat.',
        'data' => $result
      ], 201);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 422);
    }
  }
}
