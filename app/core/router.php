<?php
$routes = [];

function get($path, $callback, $middleware = [])
{
  global $routes;
  $routes['GET'][$path] = [
    'handler' => $callback,
    'middleware' => $middleware
  ];
}

function post($path, $callback, $middleware = [])
{
  global $routes;
  $routes['POST'][$path] = [
    'handler' => $callback,
    'middleware' => $middleware
  ];
}

function put($path, $callback, $middleware = [])
{
  global $routes;
  $routes['PUT'][$path] = [
    'handler' => $callback,
    'middleware' => $middleware
  ];
}

function delete($path, $callback, $middleware = [])
{
  global $routes;
  $routes['DELETE'][$path] = [
    'handler' => $callback,
    'middleware' => $middleware
  ];
}

function run()
{
  global $routes;

  $method = $_SERVER['REQUEST_METHOD'];
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  // hapus prefix jika ada
  if (str_starts_with($uri, '/platform_kos')) {
    $uri = substr($uri, 13);
  }

  $uri = rtrim($uri, '/');
  // kalau kosong, jadikan '/'
  if ($uri === '') {
    $uri = '/';
  }

  // support method spoofing (PUT, DELETE)
  if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
    unset($_POST['_method']);
  }

  foreach ($routes[$method] ?? [] as $routePath => $route) {

    $paramNames = [];

    // ubah {id} jadi regex + simpan nama param
    $pattern = preg_replace_callback('#\{([^}]+)\}#', function ($matches) use (&$paramNames) {
      $paramNames[] = $matches[1];
      return '([^/]+)';
    }, $routePath);

    $pattern = "#^" . $pattern . "$#";

    if (preg_match($pattern, $uri, $matches)) {

      array_shift($matches); // hapus full match

      // ubah ke associative array
      $params = [];
      foreach ($matches as $i => $value) {
        $params[$paramNames[$i]] = $value;
      }

      // simpan ke global helper
      $GLOBALS['route_params'] = $params;

      $handler = $route['handler'];
      $middleware = $route['middleware'] ?? [];

      // jalankan middleware
      if (!empty($middleware)) {
        run_middleware($middleware);
      }

      // jalankan controller
      [$class, $methodName] = explode('@', $handler);

      $obj = new $class();
      call_user_func([$obj, $methodName]);

      return;
    }
  }

  // jika tidak ditemukan
  response(['message' => 'Route not found'], 404);
}
