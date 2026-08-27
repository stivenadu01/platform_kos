<?php

/**
 * CSRF protection for state-changing requests.
 * Token is stored only in the user's PHP session and sent via
 * X-CSRF-Token from the frontend.
 */
function csrf_token()
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

function csrf_request_token()
{
  $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if ($header !== '') {
    return trim($header);
  }

  return trim((string)($_POST['csrf_token'] ?? ''));
}

function csrf_validate_request()
{
  $token = csrf_request_token();
  $sessionToken = $_SESSION['csrf_token'] ?? '';

  if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
    response([
      'success' => false,
      'message' => 'Permintaan tidak valid atau token keamanan telah kedaluwarsa. Silakan muat ulang halaman.'
    ], 419);
    exit;
  }
}
