<?php

function run_middleware($middlewares = [])
{
  foreach ($middlewares as $mw) {
    // CSRF protection for all state-changing HTTP methods.
    // Login/register/reset are also protected because the token is rendered
    // into the public auth layout and sent automatically by API.js.
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
      csrf_validate_request();
    }

    // AUTH MIDDLEWARE
    if ($mw === 'auth') {
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
      header('Pragma: no-cache');
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
