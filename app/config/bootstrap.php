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

// Baseline security headers. CSP is intentionally not forced here because
// the current UI loads trusted external font/map libraries that are already
// part of the project.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) {
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

