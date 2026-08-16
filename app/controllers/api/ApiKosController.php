<?php

class ApiKosController
{
  public function __construct()
  {
    model('Kos');
    model('Fasilitas');
  }

  private function owner()
  {
    return $_SESSION['user'];
  }


  public function index()
  {
    $user = $this->owner();

    response([
      'success' => true,
      'data' => getKosByPemilik($user['id_user'])
    ]);
  }


  public function show()
  {
    $id_kos = (int) params('id');
    $user = $this->owner();

    $kos = findKosById(
      $id_kos,
      $user['id_user']
    );

    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    $kos['fasilitas'] = getFasilitasByKos(
      $id_kos,
      $user['id_user']
    );

    response([
      'success' => true,
      'data' => $kos
    ]);
  }


  public function store()
  {
    $user = $this->owner();
    $data = input();

    $this->validate($data);

    $id_kos = createKos(
      $user['id_user'],
      $data
    );

    if (!$id_kos) {
      response([
        'success' => false,
        'message' => 'Gagal menambahkan kos'
      ], 500);
    }

    syncFasilitasKos(
      $id_kos,
      $user['id_user'],
      $data['fasilitas'] ?? []
    );

    response([
      'success' => true,
      'message' => 'Kos berhasil ditambahkan',
      'data' => [
        'id_kos' => $id_kos
      ]
    ], 201);
  }


  public function update()
  {
    $id_kos = (int) params('id');
    $user = $this->owner();
    $data = input();

    $this->validate($data);

    if (!findKosById(
      $id_kos,
      $user['id_user']
    )) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    $success = updateKos(
      $id_kos,
      $user['id_user'],
      $data
    );

    if (!$success) {
      response([
        'success' => false,
        'message' => 'Gagal mengubah data kos'
      ], 500);
    }

    syncFasilitasKos(
      $id_kos,
      $user['id_user'],
      $data['fasilitas'] ?? []
    );

    response([
      'success' => true,
      'message' => 'Data kos berhasil diperbarui'
    ]);
  }


  public function destroy()
  {

    $id_kos = (int) params('id');
    $user = $this->owner();

    if (!findKosById($id_kos, $user['id_user'])) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    if (!deleteKos($id_kos, $user['id_user'])) {
      response([
        'success' => false,
        'message' => 'Gagal menghapus kos'
      ], 500);
    }

    response([
      'success' => true,
      'message' => 'Kos berhasil dihapus'
    ]);
  }


  private function validate($data)
  {
    $required = [
      'nama_kos',
      'alamat',
      'latitude',
      'longitude',
      'jenis'
    ];

    foreach ($required as $field) {
      if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
        response([
          'success' => false,
          'message' => "Field {$field} wajib diisi"
        ], 422);
      }
    }

    if (!in_array($data['jenis'], ['putra', 'putri', 'campur'], true)) {
      response([
        'success' => false,
        'message' => 'Jenis kos tidak valid'
      ], 422);
    }

    if (
      !is_numeric($data['latitude']) ||
      $data['latitude'] < -90 ||
      $data['latitude'] > 90
    ) {
      response([
        'success' => false,
        'message' => 'Latitude tidak valid'
      ], 422);
    }

    if (
      !is_numeric($data['longitude']) ||
      $data['longitude'] < -180 ||
      $data['longitude'] > 180
    ) {
      response([
        'success' => false,
        'message' => 'Longitude tidak valid'
      ], 422);
    }
  }

  public function fasilitas()
  {
    $data = getAllFasilitas();

    response([
      'success' => true,
      'data' => $data
    ]);
  }
}
