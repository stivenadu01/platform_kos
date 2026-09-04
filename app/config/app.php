<?php

date_default_timezone_set("Asia/Makassar");
define('APP_NAME', $_ENV['APP_NAME'] ?? 'My App');

define('ROOT_PATH', __DIR__ . '/../../');
define('UPLOADS_PATH', ROOT_PATH . '/public/uploads/');

define('BASE_URL', rtrim($_ENV['BASE_URL'] ?? '', '/'));
define('ASSETS_URL', BASE_URL . '/assets/');
define('UPLOADS_URL', BASE_URL . '/uploads/');

// Google Identity Services
define('GOOGLE_CLIENT_ID', trim($_ENV['GOOGLE_CLIENT_ID'] ?? ''));
define('GOOGLE_REDIRECT_URI', rtrim(BASE_URL, '/') . '/api/auth/google/callback');
