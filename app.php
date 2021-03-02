<?php

use OpxCore\App\Application;
use OpxCore\Container\Container;

// Use try-catch to handle exceptions before ExceptionHandler would be registered.
try {
    // Path to project root
    $baseDir = __DIR__;

    $containerStartTimestamp = hrtime(true);
    $containerStartMemory = memory_get_usage();

    // Create container and bind necessary dependencies for application creation
    require_once 'core/bindings.php';
    $container = makeContainerBaseBindings(new Container, $baseDir);

    $containerStopTimestamp = hrtime(true);
    $containerStopMemory = memory_get_usage();

    // Create an application.
    $app = new Application($container, $baseDir);

} catch (Error | Throwable | Exception $e) {
    echo "<h1>Application creation error.</h1><p>{$e->getMessage()}</p>";
    $trace = $e->getTrace();
    foreach ($trace as $entry) {
        echo "<p style='font-size: 0.9rem;margin: 0.2rem;'><i>{$entry['file']}:{$entry['line']}</i> " . ($entry['class'] ?? null) . ($entry['type'] ?? null) . ($entry['function'] ?? null) . "</p>";
    }
    die();
}

// Now $app has registered container with bindings (see above) and configured profiler if it was bound.
// Also exception handler was registered if it was bound.
// $app->path() is available and points to project root directory.

// Write profiling for autoloader (with external captured stamps)
$app->profiler()->start('autoload', constant('AUTOLOAD_START'), constant('AUTOLOAD_START_MEM'));
$app->profiler()->stop('autoload', constant('AUTOLOAD_STOP'), constant('AUTOLOAD_STOP_MEM'));

// Write profiling (with external captured stamps) for container creation and binds after profiler is set
$app->profiler()->start('container.binding', $containerStartTimestamp, $containerStartMemory);
$app->profiler()->stop('container.binding', $containerStopTimestamp, $containerStopMemory);

// define function to access created application everywhere.
$app->profiler()->start('register.app.function');
if (!function_exists('app')) {
    /**
     * Get application instance.
     *
     * @return Application
     */
    function app(): Application
    {
        global $app;

        return $app;
    }
}
$app->profiler()->stop('register.app.function');

// Initialize application
$app->init();

// Run application bootstrap
$app->bootstrap();

return $app;