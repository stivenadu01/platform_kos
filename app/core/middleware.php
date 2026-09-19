<?php

function run_middleware($middlewares = [])
{
  // Jalankan validasi CSRF satu kali sebelum middleware lain. Endpoint yang
  // memakai autentikasi otomatis dilindungi; endpoint publik dapat memilih
  // middleware `csrf`. Webhook provider tidak memakai keduanya karena
  // diverifikasi dengan signature provider.
  $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  $requiresCsrf = in_array('auth', $middlewares, true) || in_array('csrf', $middlewares, true);
  if ($requiresCsrf && in_array($requestMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    csrf_validate_request();
  }

  foreach ($middlewares as $mw) {
    // AUTH MIDDLEWARE
    if ($mw === 'auth') {
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
      header('Pragma: no-cache');
      if (!isset($_SESSION['user']['id_user'])) {
        response([
          'success' => false,
          'message' => 'Akses ditolak, silakan login terlebih dahulu'
        ], 401);
        exit;
      }

      // Session user bukan sumber kebenaran untuk status/role.
      // Muat ulang akun dari database agar akun yang dinonaktifkan admin
      // tidak tetap dapat memakai session lama.
      model('User');
      $freshUser = findUser((int) $_SESSION['user']['id_user']);
      $sessionVersion = (int) ($_SESSION['user']['auth_session_version'] ?? 1);
      $dbSessionVersion = $freshUser ? (int) ($freshUser['auth_session_version'] ?? 1) : 0;
      if (!$freshUser || ($freshUser['status'] ?? 'aktif') !== 'aktif' || $sessionVersion !== $dbSessionVersion) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax'
          ]);
        }
        session_destroy();
        response([
          'success' => false,
          'message' => 'Sesi berakhir atau akun tidak lagi dapat digunakan. Silakan login kembali.'
        ], 401);
        exit;
      }

      unset($freshUser['password']);
      $_SESSION['user'] = $freshUser;
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
