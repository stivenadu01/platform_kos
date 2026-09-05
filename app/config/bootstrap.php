<?php

// 1. Load Composer Autoload
use Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

// 2. Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
$dotenv->load();

// 3. session
require_once __DIR__ . '/session.php';

// 4. Database Connection
require_once __DIR__ . '/database.php';


// 5. General Settings
require_once __DIR__ . '/app.php';

// helper
require_once ROOT_PATH . '/app/helpers/helper.php';
require_once ROOT_PATH . '/app/helpers/csrf_helper.php';

$appEnv = strtolower(trim((string)($_ENV['APP_ENV'] ?? 'production')));
if ($appEnv === 'production') {
  ini_set('display_errors', '0');
  ini_set('display_startup_errors', '0');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
}

// Baseline security headers.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; script-src 'self' https://unpkg.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://fonts.googleapis.com https://unpkg.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob: https://*.tile.openstreetmap.org https://api.sandbox.midtrans.com https://api.midtrans.com; connect-src 'self' https://nominatim.openstreetmap.org;");
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) {
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

