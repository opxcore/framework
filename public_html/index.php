<?php

define('OPXCORE_START', hrtime(true));
define('OPXCORE_START_MEM', memory_get_usage());

// Register The Auto Loader and capture data for profiler
define('AUTOLOAD_START', hrtime(true));
define('AUTOLOAD_START_MEM', memory_get_usage());
require __DIR__ . '/../vendor/autoload.php';
define('AUTOLOAD_STOP', hrtime(true));
define('AUTOLOAD_STOP_MEM', memory_get_usage());

// Create and bootstrap application instance
/** @var OpxCore\App\Application $app */
$app = require __DIR__ . '/../app.php';

// Run application
//$app->run();

$total = (hrtime(true) - constant('OPXCORE_START')) / 1000;
echo "<p><b>Total run time: {$total} uS</b></p>";

$profiling = $app->profiler()->profiling();
if ($profiling !== null) {
    foreach ($profiling as $action) {
        $name = $action['action_name'];
        $timestamp = $action['started_at'] / 1000000;
        $time = $action['execution_time'] === null ? '' : ' execution time <b>' . ($action['execution_time'] / 1000) . '</b> uS;';
        $usedMemory = $action['used_memory'] / 1024;
        $totalMemory = $action['total_memory'] / 1024;

        echo "<p style='margin: 1.1rem 0 0.3rem;'><b>{$name}</b> [{$timestamp} mS]:{$time} memory: {$usedMemory} kB (total: {$totalMemory} kB)</p>";
        foreach ($action['trace'] as $entry) {
            echo "<p style='font-size: 0.9rem;margin: 0.2rem;'><i>{$entry['file']}:{$entry['line']}</i> " . ($entry['class'] ?? null) . ($entry['type'] ?? null) . ($entry['function'] ?? null) . "</p>";
        }
    }
}
