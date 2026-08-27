<?php

/**
 * Small file-backed rate limiter so limits do not disappear when a client
 * clears its PHP session cookie. Suitable for the current single-server app.
 */
function rateLimit($key, $maxAttempts, $decaySeconds)
{
  $maxAttempts = max(1, (int)$maxAttempts);
  $decaySeconds = max(1, (int)$decaySeconds);

  $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'betakos_rate_limit';
  if (!is_dir($dir)) {
    @mkdir($dir, 0700, true);
  }

  $file = $dir . DIRECTORY_SEPARATOR . hash('sha256', (string)$key) . '.json';
  $handle = @fopen($file, 'c+');

  // Fail closed for sensitive endpoints if the limiter cannot be opened.
  if (!$handle) {
    throw new Exception('Layanan sementara tidak tersedia. Silakan coba lagi.', 503);
  }

  try {
    if (!flock($handle, LOCK_EX)) {
      throw new Exception('Layanan sementara tidak tersedia. Silakan coba lagi.', 503);
    }

    rewind($handle);
    $raw = stream_get_contents($handle);
    $data = json_decode($raw ?: '', true);
    $now = time();

    if (!is_array($data) || ($now - (int)($data['start'] ?? 0)) >= $decaySeconds) {
      $data = ['count' => 0, 'start' => $now];
    }

    $data['count'] = (int)$data['count'] + 1;

    if ($data['count'] > $maxAttempts) {
      $remaining = max(1, $decaySeconds - ($now - (int)$data['start']));
      ftruncate($handle, 0);
      rewind($handle);
      fwrite($handle, json_encode($data));
      fflush($handle);
      throw new Exception("Terlalu banyak percobaan. Coba lagi dalam {$remaining} detik.", 429);
    }

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data));
    fflush($handle);
    flock($handle, LOCK_UN);
  } finally {
    fclose($handle);
  }
}
