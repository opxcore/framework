<?php

define('OPXCORE_START', hrtime(true));
define('OPXCORE_START_MEM', memory_get_usage());

// Register The Auto Loader
require __DIR__ . '/../vendor/autoload.php';

// Create and bootstrap application instance
/** @var OpxCore\App\Application $app */
$app = require __DIR__ . '/../app.php';

// Run application
//$app->run();

$profiling = $app->profiler()->profiling();

foreach ($profiling as $action) {
    $name = $action['action_name'];
    $timestamp = $action['started_at'] / 1000000;
    $time = $action['execution_time'] === null ? '' : ' execution time <b>' . ($action['execution_time'] / 1000) . '</b> uS;';
    $usedMemory = $action['used_memory'] / 1024;
    $totalMemory = $action['total_memory'] / 1024;

    echo "<p><b>{$name}</b> [{$timestamp} mS]:{$time} memory: {$usedMemory} kB (total: {$totalMemory} kB)</p>";
    // var_dump($action['trace']);
}

$total = (hrtime(true) - constant('OPXCORE_START')) / 1000;
echo "<p><b>Total run time: {$total} uS</b></p>";
