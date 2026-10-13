<?php

class AuthController
{
  public function login()
  {
    if ($this->redirectSignedIn()) return;
    view('auth/login', [
      'title' => 'Masuk'
    ]);
  }


  public function register()
  {
    if ($this->redirectSignedIn()) return;
    view('auth/register', [
      'title' => 'Daftar'
    ]);
  }

  private function redirectSignedIn(): bool
  {
    if (empty($_SESSION['user']['id_user'])) return false;
    model('User');
    $user = findUser((int)$_SESSION['user']['id_user']);
    if (!$user || ($user['status'] ?? '') !== 'aktif' || (int)($user['auth_session_version'] ?? 1) !== (int)($_SESSION['user']['auth_session_version'] ?? 1)) {
      unset($_SESSION['user']);
      return false;
    }
    $path = $user['role'] === 'admin' ? '/admin' : ($user['role'] === 'pemilik' ? '/pemilik' : '/');
    header('Location: ' . BASE_URL . $path, true, 302);
    return true;
  }


  public function verify()
  {
    try {
      model('User');

      $token = trim($_GET['token'] ?? '');

      if (!$token) {
        throw new Exception("Token verifikasi tidak valid", 400);
      }

      $verification = findUserVerificationByToken($token);

      if (!$verification) {
        throw new Exception("Token verifikasi tidak ditemukan atau sudah digunakan", 404);
      }

      if (!empty($verification['used_at'])) {
        throw new Exception("Token verifikasi sudah digunakan", 400);
      }

      if (strtotime($verification['expires_at']) < time()) {
        throw new Exception("Token verifikasi sudah kadaluarsa", 410);
      }

      verifyUser($verification['id_user'], $verification['id_token']);

      view('auth/verify', [
        'status' => 'success',
        'message' => 'Akun berhasil diverifikasi. Sekarang Anda dapat masuk ke platform.'
      ]);

    } catch (Exception $e) {
      view('auth/verify', [
        'status' => 'error',
        'message' => $e->getMessage()
      ]);
    }
  }


  public function resetPassword()
  {
    view('auth/reset-password', [
      'title' => 'Reset Kata Sandi'
    ]);
  }
}
