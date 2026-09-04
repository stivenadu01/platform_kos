<?php
spl_autoload_register(function ($class) {
  // folder utama controller
  $baseDirs = [
    ROOT_PATH . '/app/controllers/',
    ROOT_PATH . '/app/controllers/api/',
    ROOT_PATH . '/app/services/'
  ];

  foreach ($baseDirs as $baseDir) {

    $file = $baseDir . $class . '.php';

    if (file_exists($file)) {
      require_once $file;
      return;
    }
  }
});
