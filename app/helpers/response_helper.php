<?php
function response($data, $status = 200)
{
  http_response_code($status);


  $uri = $_SERVER['REQUEST_URI'];
  if (str_starts_with($uri, '/platform_kos')) $uri = substr($uri, 13);
  $isApi = str_starts_with($uri, '/api/');
  // $isApi = str_starts_with($_SERVER['REQUEST_URI'], '/api/');

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
