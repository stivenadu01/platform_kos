<?php

class ApiKosSearchController
{
  public function __construct()
  {
    require_once ROOT_PATH . '/app/helpers/rate_limit_helper.php';
  }
  public function index()
  {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    rateLimit('kos_search_' . $ip, 120, 60);
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
