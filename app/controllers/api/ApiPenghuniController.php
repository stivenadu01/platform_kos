<?php

class ApiPenghuniController
{
  public function __construct()
  {
    model('Penghuni');
  }

  private function pemilikId()
  {
    return (int) ($_SESSION['user']['id_user'] ?? 0);
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

  public function kamar()
  {
    try {
      response([
        'success' => true,
        'data' => getKamarListForPenghuni(
          $this->pemilikId()
        )
      ]);
    } catch (Throwable $e) {
      response([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function userByNik()
  {
    try {
      $nik = trim(query('nik') ?? '');

      if (!preg_match('/^\d{16}$/', $nik)) {
        response([
          'success' => false,
          'message' => 'NIK harus terdiri dari 16 digit.'
        ], 422);
        return;
      }

      $user = findUserByNikForPenghuni($nik);

      response([
        'success' => true,
        'found' => (bool) $user,
        'data' => $user ? [
          'nama' => $user['nama'],
          'no_hp' => $user['no_hp']
        ] : null
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
      $id_penghuni = (int) query('id_penghuni');

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

  public function store()
  {
    try {
      $id_penghuni = createPenghuni(
        input(),
        $this->pemilikId()
      );

      response([
        'success' => true,
        'message' => 'Penghuni berhasil ditambahkan dan tagihan otomatis dibuat/diperbarui.',
        'data' => [
          'id_penghuni' => $id_penghuni
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
      $id_penghuni = (int) input('id_penghuni');

      if (!$id_penghuni) {
        response([
          'success' => false,
          'message' => 'ID penghuni tidak valid.'
        ], 400);
        return;
      }

      updatePenghuni(
        $id_penghuni,
        input(),
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
      $id_penghuni = (int) input('id_penghuni');
      $tanggal_keluar = trim(input('tanggal_keluar') ?? '');

      if (!$id_penghuni || !$tanggal_keluar) {
        response([
          'success' => false,
          'message' => 'ID penghuni dan tanggal keluar wajib diisi.'
        ], 422);
        return;
      }

      keluarPenghuni(
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
      $id_penghuni = (int) input('id_penghuni');

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
