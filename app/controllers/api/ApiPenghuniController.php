<?php

class ApiPenghuniController
{
  public function __construct()
  {
    model('Penghuni');
    model('Kamar');
    model('PeriodeSewa');
    model('Tagihan');
  }


  private function pemilikId()
  {
    return (int) (
      $_SESSION['user']['id_user'] ?? 0
    );
  }


  public function index()
  {
    try {

      $data = getPenghuniListByPemilik(
        $this->pemilikId(),
        query('search') ?? '',
        query('id_kos') ?? '',
        query('id_kamar') ?? '',
        query('status') ?? ''
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

      $id_penghuni =
        (int) (query('id_penghuni') ?? 0);

      if (!$id_penghuni) {
        response([
          'success' => false,
          'message' => 'ID penghuni tidak valid.'
        ], 400);

        return;
      }

      $data = findPenghuniByIdPemilik(
        $id_penghuni,
        $this->pemilikId()
      );

      if (!$data) {
        response([
          'success' => false,
          'message' => 'Penghuni tidak ditemukan.'
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


  public function kamar()
  {
    try {

      $data = getKamarListForPenghuni(
        $this->pemilikId()
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


  public function store()
  {
    $conn = db();
    $conn->begin_transaction();
    try {

      $data = input();

      if (!is_array($data)) {
        throw new Exception("Error Processing Request", 1);
      }

      if (
        empty($data['id_kamar']) ||
        empty(trim($data['nama'] ?? '')) ||
        empty($data['tanggal_masuk'])
      ) {
        throw new Exception(
          'Kamar, nama, dan tanggal masuk wajib diisi.'
        );
      }

      $id_penghuni = createPenghuni(
        $data,
        $this->pemilikId()
      );
      $conn->commit();
      response([
        'success' => true,
        'message' => 'Penghuni berhasil ditambahkan.',
        'data' => [
          'id_penghuni' => $id_penghuni
        ]
      ], 201);
    } catch (Throwable $e) {
      $conn->rollback();

      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }


  public function update()
  {
    try {

      $data = input();

      $id_penghuni =
        (int) (input('id_penghuni') ?? 0);

      if (!$id_penghuni) {
        response([
          'success' => false,
          'message' => 'ID penghuni tidak valid.'
        ], 400);

        return;
      }

      updatePenghuni(
        $id_penghuni,
        $data,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Data penghuni berhasil diperbarui.'
      ]);
    } catch (Throwable $e) {

      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }


  public function keluar()
  {
    try {

      $data = input();

      $id_penghuni =
        (int) ($data['id_penghuni'] ?? 0);

      $tanggal_keluar =
        trim($data['tanggal_keluar'] ?? '');

      if (!$id_penghuni || !$tanggal_keluar) {
        response([
          'success' => false,
          'message' =>
          'ID penghuni dan tanggal keluar wajib diisi.'
        ], 422);

        return;
      }

      keluarkanPenghuni(
        $id_penghuni,
        $tanggal_keluar,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Penghuni berhasil dicatat keluar.'
      ]);
    } catch (Throwable $e) {

      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }


  public function destroy()
  {
    try {

      $data = input();

      $id_penghuni =
        (int) ($data['id_penghuni'] ?? 0);

      if (!$id_penghuni) {
        response([
          'success' => false,
          'message' => 'ID penghuni tidak valid.'
        ], 400);

        return;
      }

      deletePenghuni(
        $id_penghuni,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Data penghuni berhasil dihapus.'
      ]);
    } catch (Throwable $e) {

      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }
}
