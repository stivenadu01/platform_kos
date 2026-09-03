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

    model('Langganan');
    $subscription = getStatusLanggananPemilik((int) $user['id_user']);

    $dashboard = getDashboardPemilik((int) $user['id_user'], $subscription['is_pro']);
    $dashboard['subscription'] = [
      'is_pro' => $subscription['is_pro'],
      'status' => $subscription['status'],
      'days_remaining' => $subscription['days_remaining'],
      'reminder' => $subscription['reminder']
    ];

    response([
      'success' => true,
      'data' => $dashboard
    ]);
  }
}
