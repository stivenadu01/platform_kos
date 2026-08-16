<?php


function get_query_data()
{
  static $query = null;

  if ($query !== null) return $query;

  parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

  return $query;
}

function get_post_data()
{
  return $_POST ?? [];
}

function get_json_data()
{
  static $json = null;

  if ($json !== null) return $json;

  $json = [];

  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
  if (strpos($contentType, 'application/json') !== false) {
    $json = json_decode(file_get_contents("php://input"), true) ?? [];
  }
  return $json;
}

function get_file_data()
{
  return $_FILES ?? [];
}


// fungsi utama untuk mengambil data request (gabungan query, post, json)
function request($key = null, $default = null)
{
  static $data = null;

  if ($data === null) {
    $data = array_merge(
      get_query_data(),
      get_post_data(),
      get_json_data()
    );
  }

  if ($key === null) return $data;

  return $data[$key] ?? $default;
}

function params($key = null)
{
  $params = $GLOBALS['route_params'] ?? [];

  if ($key) {
    return $params[$key] ?? null;
  }

  return $params;
}

function query($key = null, $default = null)
{
  $data = get_query_data();

  if ($key === null) return $data;

  return $data[$key] ?? $default;
}

function input($key = null, $default = null)
{
  $data = array_merge(get_post_data(), get_json_data());

  if ($key === null) return $data;

  return $data[$key] ?? $default;
}

function request_file($key = null)
{
  $files = get_file_data();

  if ($key === null) return $files;

  if (!isset($files[$key])) return null;

  $file = $files[$key];

  if ($file['error'] !== UPLOAD_ERR_OK) {
    return null;
  }

  return $file;
}
