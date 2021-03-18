<?php

// Use try-catch to handle exceptions before ExceptionHandler would be registered.
try {
    // Register The Auto Loader and capture data for profiler
    define('AUTOLOAD_START', hrtime(true));
    define('AUTOLOAD_START_MEM', memory_get_usage());
    require __DIR__ . '/vendor/autoload.php';
    define('AUTOLOAD_STOP', hrtime(true));
    define('AUTOLOAD_STOP_MEM', memory_get_usage());

    // Create application with base dependencies
    require_once 'core/make-app.php';
    $app = makeApplication(__DIR__);

} catch (Error | Throwable | Exception $e) {
    echo "<h1>Application creation error.</h1><p>{$e->getMessage()}</p>";
    $trace = $e->getTrace();
    foreach ($trace as $entry) {
        echo "<p style='font-size: 0.9rem;margin: 0.2rem;'><i>{$entry['file']}:{$entry['line']}</i> " . ($entry['class'] ?? null) . ($entry['type'] ?? null) . ($entry['function'] ?? null) . "</p>";
    }
    die();
}

// Now $app has registered container with bindings (see core/make-app.php) and configured profiler if it was bound.
// Also exception handler was registered if it was bound.
// $app->path() is available and points to project root directory.

// Initialize application
$app->init();

// Run application bootstrap
$app->bootstrap();

return $app;