<?php
require_once '../app/config/bootstrap.php';

require_once ROOT_PATH . '/app/core/autoload.php';
require_once ROOT_PATH . '/app/core/router.php';
require_once ROOT_PATH . '/app/core/middleware.php';

// load routes
require_once ROOT_PATH . '/app/routes/web.php';
require_once ROOT_PATH . '/app/routes/api.php';

// jalankan router
run();
db()->close();
