<?php

class ApiKosSearchController
{
  public function index()
  {
    model('Kos');

    response([
      'success' => true,
      'data' => searchKosPublik($_GET)
    ]);
  }

  public function show()
  {
    model('Kos');

    $id_kos = (int) params('id');
    $data = getDetailKosPublik($id_kos);

    if (!$data) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    response([
      'success' => true,
      'data' => $data
    ]);
  }
}
