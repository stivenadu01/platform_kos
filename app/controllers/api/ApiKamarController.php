<?php

class ApiKamarController
{
  public function __construct()
  {
    model('Kamar');
  }

  private function pemilikId()
  {
    return (int) ($_SESSION['user']['id_user'] ?? 0);
  }

  public function index()
  {
    try {
      $id_pemilik = $this->pemilikId();

      $search = query('search') ?? '';
      $id_kos = query('id_kos') ?? '';
      $status = query('status') ?? '';

      $data = getKamarListByPemilik(
        $id_pemilik,
        $search,
        $id_kos,
        $status
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
      $id_pemilik = $this->pemilikId();
      $id_kamar = (int) (query('id_kamar') ?? 0);

      if (!$id_kamar) {
        response([
          'success' => false,
          'message' => 'ID kamar tidak valid.'
        ], 400);
        return;
      }

      $data = findKamarByIdPemilik(
        $id_kamar,
        $id_pemilik
      );

      if (!$data) {
        response([
          'success' => false,
          'message' => 'Kamar tidak ditemukan.'
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


  public function kos()
  {
    try {
      $data = getKosListByPemilik(
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
    try {
      $data = input();

      if (!is_array($data)) {
        response([
          'success' => false,
          'message' => 'Data tidak valid.'
        ], 400);
        return;
      }

      if (
        empty($data['id_kos']) ||
        empty(trim($data['nomor_kamar'] ?? '')) ||
        empty($data['kapasitas'])
      ) {
        response([
          'success' => false,
          'message' => 'Kos, nomor kamar, dan kapasitas wajib diisi.'
        ], 422);
        return;
      }

      $id_kamar = createKamar(
        $data,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Kamar berhasil ditambahkan.',
        'data' => [
          'id_kamar' => $id_kamar
        ]
      ], 201);
    } catch (Throwable $e) {
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

      $id_kamar =
        (int) (input('id_kamar') ?? 0);

      if (!$id_kamar) {

        response([
          'success' => false,
          'message' => 'ID kamar tidak valid.'
        ], 400);

        return;
      }


      /*
       * Validasi dasar kamar.
       */
      if (
        empty($data['id_kos']) ||
        empty(trim($data['nomor_kamar'] ?? '')) ||
        empty($data['kapasitas'])
      ) {

        response([
          'success' => false,
          'message' =>
          'Kos, nomor kamar, dan kapasitas wajib diisi.'
        ], 422);

        return;
      }


      /*
       * Update kamar + harga.
       */
      updateKamar(
        $id_kamar,
        $data,
        $this->pemilikId()
      );


      response([
        'success' => true,
        'message' =>
        'Kamar dan harga berhasil diperbarui.'
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

      $id_kamar = (int) (input('id_kamar') ?? 0);

      if (!$id_kamar) {
        response([
          'success' => false,
          'message' => 'ID kamar tidak valid.'
        ], 400);
        return;
      }

      deleteKamar(
        $id_kamar,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Kamar berhasil dihapus.'
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }


  public function status()
  {
    try {
      $data = input();

      $id_kamar = (int) ($data['id_kamar'] ?? 0);
      $status = $data['status'] ?? '';

      if (!$id_kamar || !$status) {
        response([
          'success' => false,
          'message' => 'Data status tidak lengkap.'
        ], 422);
        return;
      }

      updateKamarStatus(
        $id_kamar,
        $status,
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Status kamar berhasil diperbarui.'
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 422);
    }
  }
}
