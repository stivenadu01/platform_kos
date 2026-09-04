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

    // PRO SUBSCRIPTION MIDDLEWARE
    // Memastikan pemilik memiliki langganan Pro yang masih berlaku.
    // Untuk halaman web, arahkan ke halaman Langganan agar pengguna dapat
    // melihat alasan akses ditolak dan paket yang tersedia. Untuk API,
    // kembalikan 403 agar frontend dapat menampilkan locked state.
    if ($mw === 'pro') {
      $user = $_SESSION['user'] ?? null;

      if (!$user || ($user['role'] ?? '') !== 'pemilik') {
        response([
          'success' => false,
          'message' => 'Fitur ini hanya tersedia untuk pemilik kos.'
        ], 403);
        exit;
      }

      model('Langganan');
      $status = getStatusLanggananPemilik((int) $user['id_user']);

      if (!$status['is_pro']) {
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $isApi = str_contains($requestUri, '/api/');

        if ($isApi) {
          response([
            'success' => false,
            'code' => 'PRO_REQUIRED',
            'message' => 'Fitur ini membutuhkan BetaKos Pro.',
            'data' => [
              'requires_pro' => true,
              'upgrade_url' => BASE_URL . '/pemilik/langganan?upgrade=1'
            ]
          ], 403);
        }

        header('Location: ' . BASE_URL . '/pemilik/langganan?upgrade=1');
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
