<?php

define('OPXCORE_START', microtime(true));

// Register The Auto Loader
require __DIR__ . '/../vendor/autoload.php';

// Create application instance
/** @var \OpxCoreFramework\Application $app */
$app = require __DIR__ . '/../core/Bootstrap/app.php';

// Run application
$app->run();