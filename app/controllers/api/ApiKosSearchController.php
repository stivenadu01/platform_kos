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

    $result = searchKosPublik($_GET);
    $user = $_SESSION['user'] ?? null;
    $favoriteIds = ($user && ($user['role'] ?? '') === 'pelanggan')
      ? array_flip(getFavoritKosIds((int)$user['id_user']))
      : [];

    foreach ($result['items'] as &$item) {
      $item['is_favorited'] = isset($favoriteIds[(int)$item['id_kos']]);
    }
    unset($item);

    response([
      'success' => true,
      'data' => $result
    ]);
  }

  public function favoriteStatus()
  {
    model('Kos');
    $user = $_SESSION['user'] ?? null;
    $id_kos = (int) params('id');
    response([
      'success' => true,
      'data' => ['favorited' => isKosFavorit((int)$user['id_user'], $id_kos)]
    ]);
  }

  public function toggleFavorite()
  {
    model('Kos');
    $user = $_SESSION['user'] ?? null;
    $data = input();
    $id_kos = (int)($data['id_kos'] ?? 0);
    if ($id_kos <= 0) response(['success' => false, 'message' => 'Kos tidak valid.'], 422);
    try {
      $favorited = toggleKosFavorit((int)$user['id_user'], $id_kos);
      response([
        'success' => true,
        'message' => $favorited ? 'Kos ditambahkan ke favorit.' : 'Kos dihapus dari favorit.',
        'data' => ['favorited' => $favorited]
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function favoriteList()
  {
    model('Kos');
    $user = $_SESSION['user'] ?? null;
    response(['success' => true, 'data' => getFavoritKosByUser((int)$user['id_user'])]);
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

    $data['rekomendasi'] = getKosRekomendasiPublik($id_kos, 4);
    $user = $_SESSION['user'] ?? null;
    $data['is_favorited'] = $user && ($user['role'] ?? '') === 'pelanggan'
      ? isKosFavorit((int)$user['id_user'], $id_kos)
      : false;

    response([
      'success' => true,
      'data' => $data
    ]);
  }
}
