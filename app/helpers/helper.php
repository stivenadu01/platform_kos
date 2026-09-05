<?php

function model($name)
{
  $name = ucfirst($name);
  require_once ROOT_PATH . "/app/models/{$name}.php";
}

function view($page, $data = [], $layout = 'user')
{
  extract($data);

  ob_start();
  require ROOT_PATH . "/app/views/pages/$page.php";
  $content = ob_get_clean();

  require ROOT_PATH . "/app/views/layouts/$layout.php";
}


require_once __DIR__ . '/request_helper.php';
require_once __DIR__ . '/response_helper.php';


/**
 * Safely embed JSON into HTML/JavaScript contexts.
 * Hex-encodes HTML-sensitive characters to prevent quote/tag breakout.
 */
function json_encode_safe($value, $flags = 0)
{
  return json_encode(
    $value,
    $flags | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
  );
}
