<?php

function rateLimit($key, $maxAttempts, $decaySeconds)
{
  if (!isset($_SESSION)) session_start();

  $now = time();

  if (!isset($_SESSION['rate_limit'][$key])) {
    $_SESSION['rate_limit'][$key] = [
      'count' => 0,
      'start' => $now
    ];
  }

  $data = &$_SESSION['rate_limit'][$key];

  // reset jika waktu sudah lewat
  if (($now - $data['start']) > $decaySeconds) {
    $data = [
      'count' => 0,
      'start' => $now
    ];
  }

  $data['count']++;

  if ($data['count'] > $maxAttempts) {
    $remaining = $decaySeconds - ($now - $data['start']);
    throw new Exception("Terlalu banyak percobaan. Coba lagi dalam {$remaining} detik.", 429);
  }
}
