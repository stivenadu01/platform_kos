<?php

class ApiDashboardController
{
  public function __construct()
  {
    model('Dashboard');
  }

  public function index()
  {
    $user = $_SESSION['user'] ?? null;

    if (!$user) {
      response([
        'success' => false,
        'message' => 'Unauthorized'
      ], 401);
    }

    response([
      'success' => true,
      'data' => getDashboardPemilik((int)$user['id_user'])
    ]);
  }
}
