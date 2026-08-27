<?php

class ApiAuthController
{
  public function __construct()
  {
    model('User');
    require_once ROOT_PATH . '/app/helpers/rate_limit_helper.php';
    require_once ROOT_PATH . '/app/helpers/email_helper.php';
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

      if (
        $input['nama'] === '' ||
        $input['email'] === '' ||
        $input['password'] === '' ||
        $input['no_hp'] === '' ||
        $input['nik'] === '' ||
        $input['konfirmasi_password'] === ''
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
      $input['role'] = 'pelanggan';

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
