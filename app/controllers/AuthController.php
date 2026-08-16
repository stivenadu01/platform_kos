<?php

class AuthController
{
  public function login()
  {
    view('auth/login', [
      'title' => 'Masuk'
    ]);
  }


  public function register()
  {
    view('auth/register', [
      'title' => 'Daftar'
    ]);
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
