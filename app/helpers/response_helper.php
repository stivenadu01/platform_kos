<?php

/**
 * Return the application path regardless of whether BetaKos is installed at
 * the virtual-host root or below /platform_kos and/or /public.
 */
function normalized_request_path($requestUri = null)
{
  $uri = parse_url($requestUri ?? ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

  foreach (['/platform_kos/public', '/platform_kos', '/public'] as $prefix) {
    if ($uri === $prefix) return '/';
    if (str_starts_with($uri, $prefix . '/')) {
      $uri = substr($uri, strlen($prefix));
      break;
    }
  }

  $uri = '/' . ltrim($uri, '/');
  return rtrim($uri, '/') ?: '/';
}

function response($data, $status = 200)
{
  // Exception database dapat membawa kode vendor (contoh 1062), bukan status
  // HTTP. Jangan teruskan kode di luar rentang HTTP ke http_response_code().
  $status = (int) $status;
  if ($status < 100 || $status > 599) $status = 500;

  http_response_code($status);

  $uri = normalized_request_path();
  $isApi = $uri === '/api' || str_starts_with($uri, '/api/');

  if ($isApi) {
    header('Content-Type: application/json');
    echo json_encode($data);
  } else {
    $data['status'] = $status;
    $data['title'] = $status . ' ' . ($status === 404 ? 'Not Found' : 'Error');
    return view('error', $data);
  }

  exit;
}
