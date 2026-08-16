<?php

function run_middleware($middlewares = [])
{
  foreach ($middlewares as $mw) {
    // AUTH MIDDLEWARE
    if ($mw === 'auth') {
      if (!isset($_SESSION['user'])) {
        response([
          'success' => false,
          'message' => 'Akses ditolak, silakan login terlebih dahulu'
        ], 401);
        exit;
      }
    }

    // ROLE MIDDLEWARE
    if (str_starts_with($mw, 'role:')) {
      $roles = explode(':', $mw)[1]  ?? '';
      $allowedRoles = explode(',', $roles);
      $user = $_SESSION['user'] ?? null;


      if (!$user || !in_array($user['role'], $allowedRoles)) {
        response([
          'success' => false,
          'message' => 'Akses ditolak, Anda tidak memiliki izin untuk mengakses sumber daya ini'
        ], 403);
        exit;
      }
    }
  }
}
