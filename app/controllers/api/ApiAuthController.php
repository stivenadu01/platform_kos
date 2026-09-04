<?php

class ApiAuthController
{
  public function __construct()
  {
    model('User');
    require_once ROOT_PATH . '/app/helpers/rate_limit_helper.php';
    require_once ROOT_PATH . '/app/helpers/email_helper.php';
    require_once ROOT_PATH . '/app/services/GoogleAuthService.php';
  }

  public function login()
  {
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('login_' . $ip, 5, 60);

      $input = input();

      $email = strtolower(trim($input['email'] ?? ''));
      $password = $input['password'] ?? '';

      if (!$email || !$password) {
        throw new Exception("Email dan password wajib diisi", 422);
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email atau password tidak valid", 400);
      }

      $user = findUserByEmail($email);

      if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception("Email atau password tidak valid", 400);
      }

      if (empty($user['email_verified_at'])) {
        throw new Exception("Akun belum diverifikasi. Silakan cek email Anda.", 403);
      }

      if (($user['status'] ?? 'aktif') !== 'aktif') {
        throw new Exception("Akun tidak dapat digunakan. Silakan hubungi administrator.", 403);
      }

      // Catat login terakhir hanya setelah seluruh validasi login berhasil.
      updateLastLoginAt((int) $user['id_user']);
      $user['last_login_at'] = date('Y-m-d H:i:s');

      // Regenerasi session untuk mencegah session fixation.
      session_regenerate_id(true);

      unset($user['password']);
      $_SESSION['user'] = $user;

      return response([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => $_SESSION['user']
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function register()
  {
    $conn = db();
    $conn->begin_transaction();

    try {
      $input = input();

      $input['nama'] = trim($input['nama'] ?? '');
      $input['email'] = strtolower(trim($input['email'] ?? ''));
      $input['no_hp'] = trim($input['no_hp'] ?? '');
      $input['nik'] = trim($input['nik'] ?? '');
      $input['password'] = $input['password'] ?? '';
      $input['konfirmasi_password'] = $input['konfirmasi_password'] ?? '';
      $input['role'] = trim($input['role'] ?? '');

      if (
        $input['nama'] === '' ||
        $input['email'] === '' ||
        $input['password'] === '' ||
        $input['no_hp'] === '' ||
        $input['nik'] === '' ||
        $input['konfirmasi_password'] === '' ||
        $input['role'] === ''
      ) {
        throw new Exception("Semua field wajib diisi", 422);
      }

      if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email tidak valid", 422);
      }

      if (!preg_match('/^\d{16}$/', $input['nik'])) {
        throw new Exception("NIK harus terdiri dari 16 digit", 422);
      }

      if (strlen($input['password']) < 8) {
        throw new Exception("Kata sandi minimal 8 karakter", 422);
      }

      if ($input['password'] !== $input['konfirmasi_password']) {
        throw new Exception("Kata sandi tidak cocok", 422);
      }

      if (findUserByEmail($input['email'])) {
        throw new Exception("Email sudah digunakan", 409);
      }
      if (!in_array($input['role'], ['pelanggan', 'pemilik'], true)) {
        throw new Exception("Jenis akun tidak valid", 422);
      }

      /*
       * Model wajib:
       * - membuat users.email_verified_at = NULL
       * - membuat token acak
       * - menyimpan SHA-256 token ke user_verification_tokens
       * - mengembalikan token plaintext hanya untuk dikirim melalui email.
       */
      $result = tambahUser($input, true);

      if (empty($result['id_user']) || empty($result['token'])) {
        throw new Exception("Gagal membuat token verifikasi", 500);
      }

      if (!sendVerificationEmail($input['email'], $result['token'])) {
        throw new Exception("Gagal mengirim email verifikasi", 500);
      }

      $conn->commit();

      return response([
        'success' => true,
        'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi akun.'
      ], 201);
    } catch (Exception $e) {
      $conn->rollback();

      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function googleStart()
  {
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('google_start_' . $ip, 10, 60);

      $mode = trim((string) query('mode', 'login'));
      $role = trim((string) query('role', ''));

      if (!in_array($mode, ['login', 'register'], true)) {
        throw new Exception('Mode Google tidak valid.', 400);
      }
      if ($mode === 'register' && $role !== '' && !in_array($role, ['pelanggan', 'pemilik'], true)) {
        throw new Exception('Jenis akun tidak valid.', 422);
      }

      $state = bin2hex(random_bytes(32));
      $_SESSION['google_oauth_state'] = $state;
      $_SESSION['google_oauth_mode'] = $mode;
      $_SESSION['google_oauth_role'] = $role;
      $_SESSION['google_oauth_started_at'] = time();

      header('Location: ' . (new GoogleAuthService())->buildAuthorizationUrl($state), true, 302);
      exit;
    } catch (Exception $e) {
      $_SESSION['google_auth_error'] = $e->getMessage();
      header('Location: ' . rtrim(BASE_URL, '/') . '/login', true, 302);
      exit;
    }
  }

  public function googleCallback()
  {
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('google_callback_' . $ip, 10, 60);

      $error = trim((string) query('error', ''));
      if ($error !== '') {
        $this->clearGoogleOAuthSession();
        $_SESSION['google_auth_error'] = $error === 'access_denied'
          ? 'Login dengan Google dibatalkan.'
          : 'Google tidak dapat menyelesaikan proses login.';
        header('Location: ' . rtrim(BASE_URL, '/') . '/login', true, 302);
        exit;
      }

      $state = trim((string) query('state', ''));
      $sessionState = (string) ($_SESSION['google_oauth_state'] ?? '');
      $startedAt = (int) ($_SESSION['google_oauth_started_at'] ?? 0);
      if ($state === '' || $sessionState === '' || !hash_equals($sessionState, $state)) {
        $this->clearGoogleOAuthSession();
        throw new Exception('Sesi keamanan Google tidak valid. Silakan coba lagi.', 400);
      }
      if ($startedAt <= 0 || (time() - $startedAt) > 600) {
        $this->clearGoogleOAuthSession();
        throw new Exception('Sesi login Google sudah kedaluwarsa. Silakan coba lagi.', 400);
      }

      $code = trim((string) query('code', ''));
      if ($code === '') {
        $this->clearGoogleOAuthSession();
        throw new Exception('Authorization code Google tidak ditemukan.', 400);
      }

      $mode = (string) ($_SESSION['google_oauth_mode'] ?? 'login');
      $role = (string) ($_SESSION['google_oauth_role'] ?? '');
      $service = new GoogleAuthService();
      $tokens = $service->exchangeCode($code);
      $google = $service->verifyIdToken($tokens['id_token']);
      $this->clearGoogleOAuthSession();

      $user = findUserByGoogleSub($google['sub']);
      if ($user) {
        if (($user['status'] ?? 'aktif') !== 'aktif') {
          throw new Exception('Akun tidak dapat digunakan. Silakan hubungi administrator.', 403);
        }
        updateLastLoginAt((int) $user['id_user']);
        $user['last_login_at'] = date('Y-m-d H:i:s');
        unset($user['password']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        $destination = (($user['role'] ?? '') === 'pemilik') ? '/pemilik' : (($user['role'] ?? '') === 'admin' ? '/admin' : '/');
        header('Location: ' . rtrim(BASE_URL, '/') . $destination, true, 302);
        exit;
      }

      /*
       * Jika email Google yang sudah diverifikasi ternyata sudah memiliki akun
       * BetaKos, hubungkan google_sub ke akun tersebut.
       *
       * Ini aman karena identitas Google sudah diverifikasi server (issuer,
       * audience, email_verified, dan exp) sebelum sampai ke blok ini.
       * Tidak ada penggabungan jika google_sub tersebut sudah dimiliki akun lain.
       */
      $existingByEmail = findUserByEmail($google['email']);
      if ($existingByEmail) {
        if (($existingByEmail['status'] ?? 'aktif') !== 'aktif') {
          throw new Exception('Akun BetaKos tidak dapat digunakan. Silakan hubungi administrator.', 403);
        }

        $existingGoogleSub = trim((string) ($existingByEmail['google_sub'] ?? ''));
        if ($existingGoogleSub !== '' && !hash_equals($existingGoogleSub, $google['sub'])) {
          throw new Exception('Email Google ini sudah terhubung ke akun BetaKos lain. Silakan hubungi administrator.', 409);
        }

        if ($existingGoogleSub === '') {
          $linked = linkGoogleSubToUser((int) $existingByEmail['id_user'], $google['sub']);
          if (!$linked) {
            // Cek ulang untuk menangani race condition secara aman.
            $latestUser = findUser((int) $existingByEmail['id_user']);
            $latestGoogleSub = trim((string) ($latestUser['google_sub'] ?? ''));
            if ($latestGoogleSub === '' || !hash_equals($latestGoogleSub, $google['sub'])) {
              throw new Exception('Akun Google gagal dihubungkan ke akun BetaKos. Silakan coba lagi.', 409);
            }
            $existingByEmail = $latestUser;
          } else {
            $existingByEmail = findUser((int) $existingByEmail['id_user']);
          }
        }

        updateLastLoginAt((int) $existingByEmail['id_user']);
        $existingByEmail['last_login_at'] = date('Y-m-d H:i:s');
        unset($existingByEmail['password']);
        session_regenerate_id(true);
        $_SESSION['user'] = $existingByEmail;
        $destination = (($existingByEmail['role'] ?? '') === 'pemilik') ? '/pemilik' : (($existingByEmail['role'] ?? '') === 'admin' ? '/admin' : '/');
        header('Location: ' . rtrim(BASE_URL, '/') . $destination, true, 302);
        exit;
      }

      $_SESSION['google_pending_registration'] = [
        'sub' => $google['sub'],
        'email' => $google['email'],
        'nama' => $google['nama'] ?: $google['email'],
        'picture' => $google['picture'],
        'role' => in_array($role, ['pelanggan', 'pemilik'], true) ? $role : ''
      ];

      header('Location: ' . rtrim(BASE_URL, '/') . '/register?google=1', true, 302);
      exit;
    } catch (Exception $e) {
      $this->clearGoogleOAuthSession();
      $_SESSION['google_auth_error'] = $e->getMessage();
      header('Location: ' . rtrim(BASE_URL, '/') . '/login', true, 302);
      exit;
    }
  }

  public function googleComplete()
  {
    $conn = db();
    $conn->begin_transaction();
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('google_complete_' . $ip, 10, 60);
      $pending = $_SESSION['google_pending_registration'] ?? null;
      if (!is_array($pending) || empty($pending['sub']) || empty($pending['email'])) {
        throw new Exception('Sesi pendaftaran Google tidak ditemukan. Silakan ulangi dari tombol Google.', 400);
      }

      $input = input();
      $role = trim((string) ($input['role'] ?? ($pending['role'] ?? '')));
      $noHp = trim((string) ($input['no_hp'] ?? ''));
      $nik = trim((string) ($input['nik'] ?? ''));
      if (!in_array($role, ['pelanggan', 'pemilik'], true)) {
        throw new Exception('Pilih jenis akun: Pemilik Kos atau Pencari Kos.', 422);
      }
      if ($noHp === '' || $nik === '') {
        throw new Exception('Nomor HP dan NIK wajib diisi.', 422);
      }
      if (!preg_match('/^\d{16}$/', $nik)) {
        throw new Exception('NIK harus terdiri dari 16 digit.', 422);
      }
      if (findUserByEmail($pending['email'])) {
        throw new Exception('Email sudah digunakan. Silakan masuk dengan email dan kata sandi.', 409);
      }
      if (findUserByGoogleSub($pending['sub'])) {
        throw new Exception('Akun Google ini sudah terdaftar. Silakan masuk dengan Google.', 409);
      }
      if (findUserByNik($nik)) {
        throw new Exception('NIK sudah digunakan.', 409);
      }

      $idUser = createGoogleUser([
        'nama' => $pending['nama'],
        'email' => $pending['email'],
        'no_hp' => $noHp,
        'nik' => $nik,
        'role' => $role,
        'google_sub' => $pending['sub']
      ]);
      $conn->commit();

      $user = findUser($idUser);
      unset($user['password']);
      unset($_SESSION['google_pending_registration']);
      session_regenerate_id(true);
      $_SESSION['user'] = $user;

      return response([
        'success' => true,
        'message' => 'Pendaftaran dengan Google berhasil.',
        'data' => $user
      ], 201);
    } catch (Exception $e) {
      $conn->rollback();
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }

  private function clearGoogleOAuthSession(): void
  {
    unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_mode'], $_SESSION['google_oauth_role'], $_SESSION['google_oauth_started_at']);
  }

  public function requestReset()
  {
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('reset_' . $ip, 3, 300);

      $input = input();
      $email = strtolower(trim($input['email'] ?? ''));

      if (!$email) {
        throw new Exception("Email wajib diisi", 422);
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email tidak valid", 422);
      }

      $user = findUserByEmail($email);

      /*
       * Untuk keamanan, respons tetap generik jika email tidak ditemukan.
       * Dengan begitu endpoint tidak menjadi alat enumerasi email.
       */
      if (!$user) {
        return response([
          'success' => true,
          'message' => 'Jika email terdaftar, instruksi reset password akan dikirim.'
        ]);
      }

      /*
       * Model wajib menyimpan SHA-256 token, bukan token plaintext.
       * Token plaintext hanya dikirim melalui email.
       */
      $token = bin2hex(random_bytes(32));

      savePasswordResetToken(
        $user['id_user'],
        $token,
        date('Y-m-d H:i:s', strtotime('+1 hour'))
      );

      if (!sendResetToken($email, $token)) {
        throw new Exception("Gagal mengirim email reset password", 500);
      }

      return response([
        'success' => true,
        'message' => 'Jika email terdaftar, instruksi reset password akan dikirim.'
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function resetPassword()
  {
    try {
      $input = input();

      $token = trim($input['token'] ?? '');
      $newPassword = $input['password'] ?? '';

      if (!$token || !$newPassword) {
        throw new Exception("Token dan password baru wajib diisi", 422);
      }

      if (strlen($newPassword) < 8) {
        throw new Exception("Kata sandi minimal 8 karakter", 422);
      }

      $resetRequest = findPasswordResetByToken($token);

      if (!$resetRequest) {
        throw new Exception("Token reset tidak valid atau sudah digunakan", 400);
      }

      if (!empty($resetRequest['used_at'])) {
        throw new Exception("Token reset sudah digunakan", 400);
      }

      if (strtotime($resetRequest['expires_at']) < time()) {
        throw new Exception("Token reset telah kadaluarsa", 410);
      }

      updateUserPassword($resetRequest['id_user'], $newPassword);

      /*
       * Token wajib ditandai used setelah password berhasil diperbarui.
       * Implementasikan di model sebagai UPDATE ... SET used_at = NOW().
       */
      markPasswordResetTokenUsed($resetRequest['id_reset']);

      return response([
        'success' => true,
        'message' => 'Password berhasil direset. Silakan login dengan password baru.'
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function updateProfile()
  {
    try {
      $userId = $_SESSION['user']['id_user'] ?? null;
      if (!$userId) {
        return response(['success' => false, 'message' => 'Unauthorized'], 401);
      }

      $data = input();
      updateUserProfile((int)$userId, $data);

      $user = findUser((int)$userId);
      unset($user['password']);
      $_SESSION['user'] = $user;

      return response([
        'success' => true,
        'message' => 'Profil berhasil diperbarui',
        'data' => $user
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function uploadFotoProfil()
  {
    try {
      $userId = $_SESSION['user']['id_user'] ?? null;
      if (!$userId) {
        return response(['success' => false, 'message' => 'Unauthorized'], 401);
      }

      if (!isset($_FILES['foto'])) {
        throw new Exception('Foto profil wajib dipilih', 422);
      }

      require_once ROOT_PATH . '/app/helpers/upload.php';
      $path = uploadImageGeneral($_FILES['foto'], 'profil', null, 5);
      updateUserFoto((int)$userId, $path);

      $user = findUser((int)$userId);
      unset($user['password']);
      $_SESSION['user'] = $user;

      return response([
        'success' => true,
        'message' => 'Foto profil berhasil diperbarui',
        'data' => $user
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function changePassword()
  {
    try {
      $userId = $_SESSION['user']['id_user'] ?? null;
      if (!$userId) {
        return response(['success' => false, 'message' => 'Unauthorized'], 401);
      }

      $data = input();
      $lama = $data['password_lama'] ?? '';
      $baru = $data['password_baru'] ?? '';
      $konfirmasi = $data['password_konfirmasi'] ?? '';

      if ($lama === '' || $baru === '' || $konfirmasi === '') {
        throw new Exception('Semua kolom kata sandi wajib diisi', 422);
      }

      if ($baru !== $konfirmasi) {
        throw new Exception('Konfirmasi kata sandi tidak cocok', 422);
      }

      changeUserPassword((int)$userId, $lama, $baru);

      return response([
        'success' => true,
        'message' => 'Kata sandi berhasil diubah'
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }


  public function logout()
  {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();

      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }

    session_destroy();

    return response([
      'success' => true,
      'message' => 'Logout berhasil'
    ]);
  }


  public function me()
  {
    $userId = $_SESSION['user']['id_user'] ?? null;

    if (!$userId) {
      return response([
        'success' => false,
        'message' => 'Unauthorized'
      ], 401);
    }

    try {
      $user = findUser($userId);

      if (!$user) {
        unset($_SESSION['user']);

        throw new Exception('User tidak ditemukan', 404);
      }

      unset($user['password']);

      $_SESSION['user'] = $user;

      return response([
        'success' => true,
        'data' => $_SESSION['user']
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }
}
